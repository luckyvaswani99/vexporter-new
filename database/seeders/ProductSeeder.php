<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCertificate;
use App\Models\ProductTierPrice;
use App\Models\Vendor;
use App\Models\Vertical;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * The eight hero products from the approved design, then a broader
     * catalogue so listings, filters and search have something to work with.
     *
     * [name, vendor slug, category slug, price, compare at, unit, moq, rating,
     *  badge, badge tone, certificate, type]
     */
    private const HERO_PRODUCTS = [
        ['Paracetamol API BP/USP Grade', 'medichem-labs', 'active-pharmaceutical-ingredients', 8.50, 10.00, 'kg', 25, 4.8, '-15%', 'red', 'FDA', Product::TYPE_SIMPLE],
        ['Stainless Steel Surgical Scissors Set', 'surgitech-india', 'surgical-instruments', 45.00, null, 'set', 10, 5.0, null, null, 'ISO', Product::TYPE_SIMPLE],
        ['Digital Microscope 1000x Magnification', 'labpro-solutions', 'lab-equipment', 320.00, 400.00, 'unit', 1, 4.2, '-20%', 'red', 'CE', Product::TYPE_SIMPLE],
        ['Amoxicillin 500mg Capsules (1000s)', 'cureall-pharma', 'formulations', 28.00, null, 'pack', 50, 4.7, null, null, 'WHO-GMP', Product::TYPE_QUOTE_ONLY],
        ['Mono PERC 540W Solar Panel', 'sunpower-india', 'solar-panels', 185.00, null, 'unit', 30, 4.9, 'Bestseller', 'red', 'MNRE', Product::TYPE_SIMPLE],
        ['LiFePO4 48V 100Ah Battery Bank', 'voltstorage-co', 'battery-storage', 1250.00, 1389.00, 'unit', 2, 4.6, '-10%', 'green', 'IEC', Product::TYPE_SIMPLE],
        ['Grid-Tie Inverter 10kW Three Phase', 'inverttech-solar', 'inverters', 890.00, null, 'unit', 1, 5.0, null, null, 'BIS', Product::TYPE_SIMPLE],
        ['500kW Rooftop Solar EPC Package', 'greenfield-solar', 'epc-solutions', 145000.00, null, 'turnkey', 1, 4.8, 'EPC', 'red', 'ALMM', Product::TYPE_SERVICE_EPC],
    ];

    public function run(): void
    {
        foreach (self::HERO_PRODUCTS as $definition) {
            $this->createHeroProduct($definition);
        }

        $this->createCatalogueDepth();
        $this->refreshVerticalCounts();
    }

    private function createHeroProduct(array $definition): void
    {
        [$name, $vendorSlug, $categorySlug, $price, $compareAt, $unit, $moq, $rating, $badge, $badgeTone, $certificate, $type] = $definition;

        $vendor = Vendor::where('slug', $vendorSlug)->firstOrFail();
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        $product = Product::updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'vendor_id' => $vendor->id,
                'vertical_id' => $category->vertical_id,
                'category_id' => $category->id,
                'type' => $type,
                'name' => $name,
                'sku' => strtoupper(Str::of($name)->substr(0, 3)->append('-')->append(fake()->numerify('#####'))),
                'hsn_code' => fake()->numerify('########'),
                'short_description' => "Export-grade {$name} supplied by {$vendor->name}, ready for international shipment with full documentation.",
                'description' => "<p>{$name} from {$vendor->name}. Manufactured under strict quality control with complete export documentation, batch traceability and third-party testing available on request.</p>",
                'unit' => $unit,
                'moq' => $moq,
                'base_price' => (int) round($price * 100),
                'compare_at_price' => $compareAt ? (int) round($compareAt * 100) : null,
                'currency' => 'USD',
                'stock_qty' => fake()->numberBetween(200, 5000),
                'lead_time_days' => fake()->numberBetween(7, 30),
                'is_active' => true,
                'is_featured' => true,
                'is_bestseller' => $badge === 'Bestseller',
                'requires_license' => $categorySlug === 'formulations',
                'approval_status' => Product::APPROVAL_APPROVED,
                'rating_cache' => $rating,
                'reviews_count' => fake()->numberBetween(15, 320),
                'badge' => $badge,
                'badge_tone' => $badgeTone,
                'icon' => $category->icon,
                'icon_color' => $category->icon_color,
                'image_gradient' => $category->image_gradient,
                'published_at' => now()->subDays(fake()->numberBetween(5, 200)),
            ],
        );

        ProductCertificate::updateOrCreate(
            ['product_id' => $product->id, 'type' => $certificate],
            [
                'number' => strtoupper(fake()->bothify('??-####-####')),
                'expires_at' => now()->addYears(2),
                'is_primary' => true,
            ],
        );

        $this->attachTierPrices($product);
        $this->attachSpecs($product);
    }

    /** Volume slabs — the core of B2B pricing. */
    private function attachTierPrices(Product $product): void
    {
        $base = $product->base_price;
        $moq = max(1, $product->moq);

        $tiers = [
            [$moq, $moq * 5 - 1, $base],
            [$moq * 5, $moq * 20 - 1, (int) round($base * 0.94)],
            [$moq * 20, null, (int) round($base * 0.88)],
        ];

        foreach ($tiers as [$min, $max, $price]) {
            ProductTierPrice::updateOrCreate(
                ['product_id' => $product->id, 'min_qty' => $min],
                ['max_qty' => $max, 'price' => $price, 'currency' => $product->currency],
            );
        }
    }

    /** Fills a few vertical-specific attributes so PDP specs are not empty. */
    private function attachSpecs(Product $product): void
    {
        $samples = [
            'pharma' => [
                'grade' => 'BP',
                'purity' => 99.5,
                'shelf_life_months' => 36,
                'storage_conditions' => 'Store below 25°C in a dry place',
                'gmp_standard' => 'WHO-GMP',
            ],
            'solar' => [
                'wattage' => 540,
                'cell_type' => 'Mono PERC',
                'efficiency' => 21.3,
                'product_warranty' => 12,
                'performance_warranty' => 25,
            ],
            'main-store' => [
                'material' => 'Mixed',
                'packaging' => 'Export carton',
            ],
        ];

        $verticalSlug = $product->vertical->slug;

        foreach ($samples[$verticalSlug] ?? [] as $code => $value) {
            $attribute = ProductAttribute::where('vertical_id', $product->vertical_id)
                ->where('code', $code)
                ->first();

            if (! $attribute) {
                continue;
            }

            ProductAttributeValue::updateOrCreate(
                ['product_id' => $product->id, 'product_attribute_id' => $attribute->id],
                is_numeric($value) ? ['value_number' => $value] : ['value_text' => $value],
            );
        }
    }

    /** Generates enough products for listings, pagination and facets. */
    private function createCatalogueDepth(): void
    {
        $vendors = Vendor::approved()->get();
        $categories = Category::with('vertical')->get();

        foreach ($categories as $category) {
            Product::factory()
                ->count(6)
                ->sequence(fn ($sequence) => [
                    'vendor_id' => $vendors->random()->id,
                    'vertical_id' => $category->vertical_id,
                    'category_id' => $category->id,
                    'icon' => $category->icon,
                    'icon_color' => $category->icon_color,
                    'image_gradient' => $category->image_gradient,
                    'is_bestseller' => $sequence->index === 0,
                ])
                ->create();
        }

        // A handful awaiting moderation for the admin queue.
        Product::factory()
            ->pendingApproval()
            ->count(4)
            ->sequence(fn () => [
                'vendor_id' => $vendors->random()->id,
                'category_id' => $categories->random()->id,
            ])
            ->create()
            ->each(fn (Product $product) => $product->update([
                'vertical_id' => $product->category->vertical_id,
            ]));
    }

    private function refreshVerticalCounts(): void
    {
        foreach (Category::all() as $category) {
            $category->update([
                'products_count_cache' => Product::where('category_id', $category->id)->visible()->count(),
            ]);
        }

        foreach (Vertical::all() as $vertical) {
            $vertical->update([
                'products_count_cache' => Product::where('vertical_id', $vertical->id)->visible()->count(),
            ]);
        }

        foreach (Vendor::all() as $vendor) {
            $vendor->update([
                'products_count_cache' => Product::where('vendor_id', $vendor->id)->visible()->count(),
            ]);
        }
    }
}
