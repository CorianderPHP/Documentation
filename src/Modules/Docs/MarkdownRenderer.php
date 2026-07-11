<?php
declare(strict_types=1);

namespace Modules\Docs;

final class MarkdownRenderer
{
    /**
     * @return array{html:string,headings:array<int,array{level:int,text:string,id:string}>}
     */
    public function render(string $markdown): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];
        $html = [];
        $headings = [];
        $paragraph = [];
        $list = [];
        $orderedList = [];
        $table = [];
        $inCode = false;
        $code = [];
        $codeLanguage = '';

        $flushParagraph = static function () use (&$paragraph, &$html): void {
            if ($paragraph === []) {
                return;
            }
            $text = implode(' ', array_map('trim', $paragraph));
            $html[] = '<p class="mt-4 leading-7 text-black/80 dark:text-white/80">' . self::inline($text) . '</p>';
            $paragraph = [];
        };

        $flushList = static function () use (&$list, &$html): void {
            if ($list === []) {
                return;
            }
            $items = array_map(static fn(string $item): string => '<li>' . self::inline($item) . '</li>', $list);
            $html[] = '<ul class="mt-4 list-disc space-y-2 pl-6 text-black/80 dark:text-white/80">' . implode('', $items) . '</ul>';
            $list = [];
        };

        $flushOrderedList = static function () use (&$orderedList, &$html): void {
            if ($orderedList === []) {
                return;
            }
            $items = array_map(static fn(string $item): string => '<li>' . self::inline($item) . '</li>', $orderedList);
            $html[] = '<ol class="mt-4 list-decimal space-y-2 pl-6 text-black/80 dark:text-white/80">' . implode('', $items) . '</ol>';
            $orderedList = [];
        };

        $flushTable = static function () use (&$table, &$html): void {
            if ($table === []) {
                return;
            }

            $header = array_shift($table);
            $headCells = array_map(
                static fn(string $cell): string => '<th class="border-b border-dark-green/15 px-3 py-2 text-left font-semibold text-dark-green dark:border-mint/20 dark:text-mint">' . self::inline($cell) . '</th>',
                $header
            );
            $bodyRows = array_map(static function (array $row): string {
                $cells = array_map(
                    static fn(string $cell): string => '<td class="border-b border-dark-green/10 px-3 py-2 align-top text-black/75 dark:border-mint/10 dark:text-white/75">' . self::inline($cell) . '</td>',
                    $row
                );

                return '<tr>' . implode('', $cells) . '</tr>';
            }, $table);

            $html[] = '<div class="mt-5 overflow-x-auto rounded-lg border border-dark-green/15 bg-true-white shadow-sm dark:border-mint/20 dark:bg-true-black"><table class="w-full min-w-max border-collapse text-sm"><thead><tr>' . implode('', $headCells) . '</tr></thead><tbody>' . implode('', $bodyRows) . '</tbody></table></div>';
            $table = [];
        };

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '```')) {
                if ($inCode) {
                    $languageClass = $codeLanguage !== '' ? ' data-language="' . self::escape($codeLanguage) . '"' : '';
                    $codeLanguageAttribute = $codeLanguage !== '' ? ' code-lang="' . self::escape($codeLanguage) . '"' : '';
                    $html[] = '<pre class="mt-5 overflow-x-auto rounded-lg border border-dark-green/15 bg-true-white p-4 text-sm text-black shadow-sm dark:border-mint/20 dark:bg-true-black dark:text-white"' . $languageClass . '><code' . $codeLanguageAttribute . '>' . self::escape(implode("\n", $code)) . '</code></pre>';
                    $inCode = false;
                    $code = [];
                    $codeLanguage = '';
                    continue;
                }

                $flushParagraph();
                $flushList();
                $flushOrderedList();
                $inCode = true;
                $codeLanguage = trim(substr(trim($line), 3));
                continue;
            }

            if ($inCode) {
                $code[] = $line;
                continue;
            }

            $trimmed = trim($line);
            if ($trimmed === '') {
                $flushParagraph();
                $flushList();
                $flushOrderedList();
                $flushTable();
                continue;
            }

            if (self::isTableSeparator($trimmed)) {
                continue;
            }

            if (self::isTableRow($trimmed)) {
                $flushParagraph();
                $flushList();
                $flushOrderedList();
                $table[] = self::parseTableRow($trimmed);
                continue;
            }

            $flushTable();

            if (preg_match('/^(#{1,4})\s+(.+)$/', $trimmed, $matches) === 1) {
                $flushParagraph();
                $flushList();
                $flushOrderedList();
                $level = strlen($matches[1]);
                $text = trim($matches[2]);
                $id = self::slugify($text);
                $headings[] = ['level' => $level, 'text' => $text, 'id' => $id];
                $class = match ($level) {
                    1 => 'mt-0 text-4xl md:text-5xl',
                    2 => 'mt-10 text-2xl md:text-3xl',
                    3 => 'mt-8 text-xl md:text-2xl',
                    default => 'mt-6 text-lg md:text-xl',
                };
                $html[] = '<h' . $level . ' id="' . self::escape($id) . '" class="' . $class . ' scroll-mt-28 font-concert-one text-dark-green dark:text-mint">' . self::inline($text) . '</h' . $level . '>';
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $trimmed, $matches) === 1) {
                $flushParagraph();
                $flushOrderedList();
                $list[] = $matches[1];
                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $matches) === 1) {
                $flushParagraph();
                $flushList();
                $orderedList[] = $matches[1];
                continue;
            }

            $flushList();
            $flushOrderedList();
            $paragraph[] = $trimmed;
        }

        $flushParagraph();
        $flushList();
        $flushOrderedList();
        $flushTable();

        return ['html' => implode("\n", $html), 'headings' => $headings];
    }

    private static function inline(string $value): string
    {
        $escaped = self::escape($value);
        $escaped = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a class="font-semibold text-dark-green underline decoration-dark-green/30 underline-offset-4 hover:decoration-dark-green dark:text-mint dark:decoration-mint/30 dark:hover:decoration-mint" href="$2">$1</a>', $escaped) ?? $escaped;
        $escaped = preg_replace('/`([^`]+)`/', '<code class="rounded bg-dark-green/10 px-1.5 py-0.5 text-sm text-dark-green dark:bg-mint/10 dark:text-mint">$1</code>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        return $escaped;
    }

    private static function slugify(string $value): string
    {
        $slug = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $value));
        return trim($slug, '-') !== '' ? trim($slug, '-') : 'section';
    }

    private static function isTableRow(string $line): bool
    {
        return str_contains($line, '|') && str_starts_with($line, '|') && str_ends_with($line, '|');
    }

    private static function isTableSeparator(string $line): bool
    {
        if (!self::isTableRow($line)) {
            return false;
        }

        $cells = self::parseTableRow($line);
        return $cells !== [] && array_reduce(
            $cells,
            static fn(bool $valid, string $cell): bool => $valid && preg_match('/^:?-{3,}:?$/', trim($cell)) === 1,
            true
        );
    }

    /**
     * @return array<int,string>
     */
    private static function parseTableRow(string $line): array
    {
        $line = trim($line, '|');
        return array_map(static fn(string $cell): string => trim($cell), explode('|', $line));
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
