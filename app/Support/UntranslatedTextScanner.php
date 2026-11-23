<?php

namespace App\Support;

/**
 * Finds user-visible text in Blade templates that is not passed through the translator.
 *
 * All UI texts must live in lang/sk (docs/brief.md, section 4.4). The scanner strips Blade echoes,
 * directives, comments, scripts and markup and reports any remaining words, plus literal values
 * of user-visible attributes (alt, title, placeholder, aria-label, label).
 */
final class UntranslatedTextScanner
{
    private const VISIBLE_ATTRIBUTES = ['alt', 'title', 'placeholder', 'aria-label', 'label', 'description'];

    /**
     * @return list<array{line: int, text: string}>
     */
    public function scan(string $blade): array
    {
        $source = $this->blankOut($blade, [
            '/\{\{--.*?--\}\}/s',
            '/<!--.*?-->/s',
            '/@php\b.*?@endphp/s',
            '/<script\b.*?<\/script>/is',
            '/<style\b.*?<\/style>/is',
            '/\{!!.*?!!\}/s',
            '/\{\{.*?\}\}/s',
        ]);

        $findings = $this->attributeFindings($source);

        // Directives first (their arguments may contain "=>" or "->"), then quote-aware tags.
        $text = $this->blankOut($source, [
            '/@[a-zA-Z]+(\s*(\((?:[^()]++|(?2))*\)))?/',
            '/<(?:[^>"\']|"[^"]*"|\'[^\']*\')*>/s',
            '/&[a-zA-Z#0-9]+;/',
        ]);

        foreach (preg_split('/\R/', $text) ?: [] as $index => $line) {
            $line = trim($line);

            if ($line !== '' && preg_match('/\p{L}{2,}/u', $line) === 1) {
                $findings[] = ['line' => $index + 1, 'text' => $line];
            }
        }

        usort($findings, fn (array $a, array $b): int => $a['line'] <=> $b['line']);

        return $findings;
    }

    /**
     * @return list<array{line: int, text: string}>
     */
    private function attributeFindings(string $source): array
    {
        $pattern = '/(?<![:\w-])('.implode('|', self::VISIBLE_ATTRIBUTES).')\s*=\s*"([^"]*)"/i';
        $findings = [];

        if (preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false) {
            return [];
        }

        foreach ($matches as $match) {
            if (preg_match('/\p{L}{2,}/u', $match[2][0]) === 1) {
                $findings[] = [
                    'line' => substr_count(substr($source, 0, $match[0][1]), "\n") + 1,
                    'text' => $match[0][0],
                ];
            }
        }

        return $findings;
    }

    /**
     * Replaces every match with blank space, keeping line breaks so line numbers stay correct.
     *
     * @param  list<string>  $patterns
     */
    private function blankOut(string $source, array $patterns): string
    {
        foreach ($patterns as $pattern) {
            $source = (string) preg_replace_callback(
                $pattern,
                fn (array $match): string => str_repeat("\n", substr_count($match[0], "\n")).' ',
                $source,
            );
        }

        return $source;
    }
}
