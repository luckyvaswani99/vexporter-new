<?php

namespace App\Services\Ai;

use App\Models\Category;
use App\Models\Product;
use App\Support\Countries;

/**
 * "Auto-fill with AI": turns a product name into a ready-to-review listing —
 * pharma details, SEO copy and a full description — so a seller only has to
 * add photos and a price.
 *
 * Nothing is saved here. The result is merged into the open form and the
 * listing still goes through admin approval, so a wrong guess never reaches
 * buyers unreviewed. Price, manufacturer and regulatory claims are never
 * invented: the model is told to leave them empty when it is not sure.
 */
class ProductAiGenerator
{
    public function __construct(private readonly GroqClient $groq) {}

    public function available(): bool
    {
        return $this->groq->hasKey();
    }

    /**
     * @return array<string, mixed> form state keyed by field name; blanks removed
     */
    public function generate(string $name, ?string $vendorName = null): array
    {
        $name = trim($name);

        $fields = $this->groq->structured(
            [
                ['role' => 'system', 'content' => $this->fieldsPrompt()],
                ['role' => 'user', 'content' => $this->fieldsRequest($name, $vendorName)],
            ],
            $this->schema(),
            'product_listing',
        );

        $facts = array_filter($fields, fn ($v) => $v !== null && $v !== [] && $v !== '');

        $description = $this->groq->text([
            ['role' => 'system', 'content' => $this->descriptionPrompt()],
            ['role' => 'user', 'content' => "Write the product page for: \"{$name}\".\nKnown facts (JSON): ".json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
        ]);

        return $this->toFormState($name, $fields, $this->stripFences($description));
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function toFormState(string $name, array $fields, string $description): array
    {
        $category = filled($fields['category'] ?? null)
            ? Category::query()->whereRaw('lower(name) = ?', [mb_strtolower((string) $fields['category'])])->first()
            : null;

        $country = strtoupper((string) ($fields['country_of_origin'] ?? ''));
        $unit = (string) ($fields['unit'] ?? '');

        $state = [
            'name' => $name,
            'category_id' => $category?->id,
            'short_description' => $fields['short_description'] ?? null,
            'description' => $description !== '' ? $description : null,
            'generic_name' => $fields['generic_name'] ?? null,
            'brand_name' => $fields['brand_name'] ?? null,
            'strength' => $fields['strength'] ?? null,
            'dosage_form' => $fields['dosage_form'] ?? null,
            'pack_size' => $fields['pack_size'] ?? null,
            'ingredients' => array_values(array_filter((array) ($fields['ingredients'] ?? []))),
            'manufacturer' => $fields['manufacturer'] ?? null,
            'country_of_origin' => array_key_exists($country, Countries::NAMES) ? $country : null,
            'pharmacopoeia_standard' => $fields['pharmacopoeia_standard'] ?? null,
            'schedule_class' => $fields['schedule_class'] ?? null,
            'cas_number' => $fields['cas_number'] ?? null,
            'storage_conditions' => $fields['storage_conditions'] ?? null,
            'shelf_life_months' => $fields['shelf_life_months'] ?? null,
            'is_cold_chain' => (bool) ($fields['is_cold_chain'] ?? false),
            'humidity_sensitive' => (bool) ($fields['humidity_sensitive'] ?? false),
            'light_sensitive' => (bool) ($fields['light_sensitive'] ?? false),
            'hsn_code' => $fields['hsn_code'] ?? null,
            'unit' => array_key_exists($unit, Product::UNIT_LABELS) ? $unit : null,
            'seo_title' => $fields['seo_title'] ?? null,
            'seo_description' => $fields['seo_description'] ?? null,
            'seo_keywords' => $fields['seo_keywords'] ?? null,
            'focus_keyword' => $fields['focus_keyword'] ?? null,
        ];

        // Booleans are legitimate when false; drop only real blanks.
        return array_filter($state, fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    private function fieldsRequest(string $name, ?string $vendorName): string
    {
        $categories = Category::query()->orderBy('name')->pluck('name')->implode(', ');
        $units = implode(', ', array_keys(Product::UNIT_LABELS));

        return "Product: \"{$name}\"".($vendorName ? "\nSeller: {$vendorName}" : '')
            ."\n\nPick `category` exactly from: [{$categories}] (null if none fits)."
            ."\nPick `unit` from: [{$units}]."
            ."\nCountry codes are ISO 3166 alpha-2.";
    }

    private function fieldsPrompt(): string
    {
        return <<<'TXT'
You are a product-data specialist for VEXPORTER, a B2B export marketplace (pharma, solar, electronics, machinery, textiles).
Fill the listing fields for the named product using well-established, verifiable knowledge only.

Rules:
- generic_name = active molecule(s) ONLY, no strength (e.g. "Paracetamol", "Mifepristone + Misoprostol"). strength = numbers/units ONLY, same order (e.g. "650 mg"). Never repeat the same text in both.
- For medicines also fill, when the molecule is known: dosage_form, pharmacopoeia_standard (IP/BP/USP/Ph. Eur.), schedule_class (Indian schedule: OTC, Schedule G, Schedule H, Schedule H1, Schedule X), ingredients (each with strength), storage_conditions, shelf_life_months, and the handling booleans (true only if genuinely required, e.g. insulin = cold chain).
- For non-pharma goods leave the pharma fields null and fill category, unit, hsn_code and the SEO fields.
- NEVER invent manufacturer, brand ownership, price, certificates or regulatory approvals. Use null when unsure — a blank is better than a wrong value.
- seo_title <= 60 chars. seo_description 140-160 chars, factual wholesale/export wording. seo_keywords: 8-12 comma-separated terms. focus_keyword: one primary phrase.
- short_description: one or two plain sentences, no marketing claims.
Respond ONLY with the JSON object.
TXT;
    }

    private function descriptionPrompt(): string
    {
        return <<<'TXT'
You are a senior product content writer and SEO specialist for a B2B export marketplace.
Write the body of a product page as clean semantic HTML (h2, h3, p, ul, li, strong only — no html/body tags, no inline styles, no images, no markdown).

Structure: Overview; Key specifications (use the known facts); Applications / uses; Packaging, storage and shelf life; Export and compliance notes; Frequently asked questions (3-4 Q&As).

Rules:
- Be specific and useful to a professional buyer; no filler, no keyword stuffing, no guaranteed claims.
- Use ONLY the supplied facts and well-established knowledge. Never invent studies, certificates, prices, dosages or warnings.
- For medicines: do not encourage self-medication; state that use is as directed by a registered medical practitioner and that regulatory requirements of the destination country apply.
- If something is unknown, leave it out rather than guessing.
Return the HTML only.
TXT;
    }

    private function stripFences(string $html): string
    {
        $html = trim($html);
        $html = preg_replace('/^```(?:html)?\s*/i', '', $html) ?? $html;

        return trim(preg_replace('/\s*```$/', '', $html) ?? $html);
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        $str = ['type' => ['string', 'null']];
        $num = ['type' => ['number', 'null']];
        $bool = ['type' => ['boolean', 'null']];

        $props = [
            'category' => $str, 'unit' => $str, 'hsn_code' => $str,
            'short_description' => $str,
            'generic_name' => $str, 'brand_name' => $str, 'strength' => $str, 'dosage_form' => $str,
            'pack_size' => $str, 'ingredients' => ['type' => 'array', 'items' => ['type' => 'string']],
            'manufacturer' => $str, 'country_of_origin' => $str, 'cas_number' => $str,
            'pharmacopoeia_standard' => $str, 'schedule_class' => $str, 'storage_conditions' => $str,
            'shelf_life_months' => $num, 'is_cold_chain' => $bool, 'humidity_sensitive' => $bool, 'light_sensitive' => $bool,
            'seo_title' => $str, 'seo_description' => $str, 'seo_keywords' => $str, 'focus_keyword' => $str,
        ];

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => $props,
            'required' => array_keys($props),
        ];
    }
}
