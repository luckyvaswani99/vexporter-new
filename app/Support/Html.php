<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

/**
 * Rich-text sanitiser for vendor- and admin-authored copy.
 *
 * Product and category descriptions are rendered unescaped, and vendors are
 * only semi-trusted, so everything written through the editors is reduced to an
 * allow-list of tags and attributes on the way into the database. Filtering at
 * the model keeps it enforced no matter which panel or endpoint did the write.
 */
final class Html
{
    /** @var array<int, string> */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'hr',
        'h2', 'h3', 'h4', 'a', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    /** Attributes kept per tag; everything else (including every on* handler) is dropped. */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
    ];

    /** @var array<int, string> */
    private const SAFE_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public static function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html === null ? null : '';
        }

        $document = new DOMDocument;

        $loaded = @$document->loadHTML(
            '<?xml encoding="UTF-8"><div id="root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        if (! $loaded) {
            // Unparseable markup is not worth guessing at — keep the text only.
            return strip_tags($html);
        }

        $root = $document->getElementById('root');

        if (! $root instanceof DOMElement) {
            return strip_tags($html);
        }

        self::clean($root);

        $inner = '';

        foreach ($root->childNodes as $child) {
            $inner .= $document->saveHTML($child);
        }

        return trim($inner);
    }

    /** Strips the text out entirely — for meta descriptions and JSON-LD. */
    public static function toText(?string $html, ?int $limit = null): string
    {
        $text = trim(html_entity_decode(strip_tags((string) str_replace(['</p>', '<br>', '<br/>', '<br />'], ' ', (string) $html))));
        $text = (string) preg_replace('/\s+/', ' ', $text);

        return $limit === null ? $text : Str::limit($text, $limit);
    }

    private static function clean(DOMNode $node): void
    {
        // Walk backwards: removing a child shifts the live NodeList.
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Keep the words, drop the tag — a stray <div> should not blank
                // the paragraph inside it.
                self::unwrap($child);

                continue;
            }

            self::stripAttributes($child, $tag);
            self::clean($child);
        }
    }

    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent instanceof DOMNode) {
            return;
        }

        if (in_array(strtolower($element->nodeName), ['script', 'style', 'iframe', 'object', 'embed'], true)) {
            $parent->removeChild($element);

            return;
        }

        self::clean($element);

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private static function stripAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

        for ($i = $element->attributes->length - 1; $i >= 0; $i--) {
            $attribute = $element->attributes->item($i);

            if ($attribute === null) {
                continue;
            }

            if (! in_array(strtolower($attribute->nodeName), $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }

        if ($tag === 'a') {
            self::secureLink($element);
        }
    }

    private static function secureLink(DOMElement $element): void
    {
        $href = trim($element->getAttribute('href'));
        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

        // Relative links have no scheme and are fine; javascript: and data: are not.
        if ($scheme !== '' && ! in_array($scheme, self::SAFE_SCHEMES, true)) {
            $element->removeAttribute('href');

            return;
        }

        if ($element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }
}
