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

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '```')) {
                if ($inCode) {
                    $languageClass = $codeLanguage !== '' ? ' data-language="' . self::escape($codeLanguage) . '"' : '';
                    $codeLanguageAttribute = $codeLanguage !== '' ? ' code-lang="' . self::escape($codeLanguage) . '"' : '';
                    $html[] = '<pre class="mt-5 overflow-x-auto rounded-lg border border-dark-green/15 bg-true-white p-4 text-sm text-black shadow-sm dark:border-peach/20 dark:bg-true-black dark:text-white"' . $languageClass . '><code' . $codeLanguageAttribute . '>' . self::escape(implode("\n", $code)) . '</code></pre>';
                    $inCode = false;
                    $code = [];
                    $codeLanguage = '';
                    continue;
                }

                $flushParagraph();
                $flushList();
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
                continue;
            }

            if (preg_match('/^(#{1,4})\s+(.+)$/', $trimmed, $matches) === 1) {
                $flushParagraph();
                $flushList();
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
                $html[] = '<h' . $level . ' id="' . self::escape($id) . '" class="' . $class . ' scroll-mt-28 font-concert-one text-dark-green dark:text-peach">' . self::inline($text) . '</h' . $level . '>';
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $trimmed, $matches) === 1) {
                $flushParagraph();
                $list[] = $matches[1];
                continue;
            }

            $flushList();
            $paragraph[] = $trimmed;
        }

        $flushParagraph();
        $flushList();

        return ['html' => implode("\n", $html), 'headings' => $headings];
    }

    private static function inline(string $value): string
    {
        $escaped = self::escape($value);
        $escaped = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a class="font-semibold text-dark-green underline decoration-dark-green/30 underline-offset-4 hover:decoration-dark-green dark:text-peach dark:decoration-peach/30 dark:hover:decoration-peach" href="$2">$1</a>', $escaped) ?? $escaped;
        $escaped = preg_replace('/`([^`]+)`/', '<code class="rounded bg-dark-green/10 px-1.5 py-0.5 text-sm text-dark-green dark:bg-peach/10 dark:text-peach">$1</code>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        return $escaped;
    }

    private static function slugify(string $value): string
    {
        $slug = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $value));
        return trim($slug, '-') !== '' ? trim($slug, '-') : 'section';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
