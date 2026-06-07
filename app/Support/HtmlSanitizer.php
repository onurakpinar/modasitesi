<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    /** @var array<string, array<int, string>> */
    private const ALLOWED_TAGS = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        'h2' => [],
        'h3' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'blockquote' => [],
        'div' => [],
        'a' => ['href', 'title', 'rel', 'target'],
    ];

    public function sanitize(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<h1\b[^>]*>(.*?)<\/h1>/is', '<h2>$1</h2>', $html) ?? $html;

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="sanitize-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('sanitize-root');

        if (! $root) {
            return '';
        }

        $this->sanitizeNode($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private function sanitizeNode(DOMNode $node): void
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        /** @var DOMElement $element */
        $element = $node;

        if ($element->tagName === 'script' || $element->tagName === 'iframe') {
            $element->parentNode?->removeChild($element);

            return;
        }

        if (! array_key_exists($element->tagName, self::ALLOWED_TAGS)) {
            $this->unwrapElement($element);

            return;
        }

        if ($element->hasAttributes()) {
            $allowed = self::ALLOWED_TAGS[$element->tagName];

            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = $attribute->value;

                if (str_starts_with($name, 'on') || ! in_array($name, $allowed, true)) {
                    $element->removeAttributeNode($attribute);

                    continue;
                }

                if ($name === 'href' && ! $this->isSafeUrl($value)) {
                    $element->removeAttributeNode($attribute);
                }
            }
        }

        foreach (iterator_to_array($element->childNodes) as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $this->sanitizeNode($child);
            }
        }
    }

    private function unwrapElement(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || str_starts_with(strtolower($url), 'javascript:')) {
            return false;
        }

        return preg_match('/^(https?:\/\/|mailto:|#|\/)/i', $url) === 1;
    }
}
