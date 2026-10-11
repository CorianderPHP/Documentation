<?php
declare(strict_types=1);

namespace App\Modules\Docs;

final class DocumentationSearch
{
    private const STOP_WORDS = ['a', 'an', 'the', 'how', 'to', 'do', 'does', 'i', 'we', 'my', 'can', 'could', 'want', 'with', 'for', 'in', 'on', 'of', 'and', 'is', 'it', 'use', 'using', 'php', 'coriander'];
    private const ALIASES = [
        'page' => 'view', 'pages' => 'view', 'views' => 'view',
        'controller' => 'handler', 'controllers' => 'handler', 'handlers' => 'handler',
        'routes' => 'route', 'routing' => 'route', 'router' => 'route',
        'modules' => 'module', 'repositories' => 'repository',
        'images' => 'image', 'imagehandler' => 'image',
        'auth' => 'authentication', 'login' => 'authentication',
        'permissions' => 'permission', 'migrations' => 'migration',
        'transactions' => 'transaction', 'installing' => 'install', 'installation' => 'install',
        'paginate' => 'pagination',
        'nullable' => 'null', 'sqlscript' => 'sql',
    ];

    public function __construct(private readonly DocumentationRepository $repository)
    {
    }

    /**
     * @return array<int,array{page:DocumentationPage,score:int,excerpt:string,anchor:string,heading:string,snippet:?array{language:string,code:string}}>
     */
    public function search(string $query, string $scope = 'all'): array
    {
        $terms = array_slice($this->words(substr($query, 0, 200)), 0, 16);
        if ($terms === []) {
            return [];
        }

        $pages = $this->repository->byScope($scope);
        $vocabulary = [];
        foreach ($pages as $page) {
            $vocabulary = array_merge($vocabulary, $this->words($this->plain($page->html)));
        }
        $terms = $this->correctTypos($terms, array_unique(array_merge($vocabulary, array_keys(self::ALIASES))));
        $results = [];
        foreach ($pages as $page) {
            $titleWords = $this->words($page->title);
            $best = null;
            foreach ($this->sections($page) as $section) {
                $headingWords = $this->words($section['heading']);
                $plain = $this->plain($section['html']);
                $bodyWords = $this->words($plain);
                $score = 0;
                $matched = 0;
                foreach ($terms as $term) {
                    if (in_array($term, $titleWords, true) || in_array($term, $bodyWords, true)) {
                        $matched++;
                    }
                    $score += in_array($term, $titleWords, true) ? 12 : 0;
                    $score += $section['heading'] !== $page->title && in_array($term, $headingWords, true) ? 14 : 0;
                    $score += in_array($term, $bodyWords, true) ? 2 : 0;
                }
                if ($matched < (int) ceil(count($terms) * 0.6)) {
                    continue;
                }
                $score += $matched === count($terms) ? 12 : 0;
                if (in_array('create', $terms, true) && in_array('view', $terms, true)
                    && !in_array('dynamic', $terms, true) && $page->slug === 'static-views') {
                    $score += 20;
                }
                if ($best === null || $score > $best['score']) {
                    $prose = $this->plain((string) preg_replace('/<(pre|h[1-4])\b[^>]*>.*?<\/\1>/s', '', $section['html']));
                    $best = [
                        'page' => $page, 'score' => $score,
                        'excerpt' => $this->excerpt($prose !== '' ? $prose : $plain, $terms),
                        'anchor' => $section['anchor'], 'heading' => $section['heading'],
                        'snippet' => $this->snippet($section['html']),
                    ];
                }
            }
            if ($best !== null) {
                $results[] = $best;
            }
        }
        usort($results, static fn(array $left, array $right): int =>
            ($right['score'] <=> $left['score']) ?: strcmp($left['page']->slug, $right['page']->slug));
        return $results;
    }

    /** @return string[] */
    private function words(string $text): array
    {
        preg_match_all('/[a-z0-9_]+/', strtolower($text), $matches);
        return array_values(array_unique(array_map(
            static fn(string $word): string => self::ALIASES[$word] ?? $word,
            array_filter($matches[0], static fn(string $word): bool =>
                strlen($word) > 1 && !in_array($word, self::STOP_WORDS, true)),
        )));
    }

    /** Correct only an unambiguous nearby word when the original has no exact match. */
    private function correctTypos(array $terms, array $vocabulary): array
    {
        foreach ($terms as &$term) {
            if (strlen($term) < 5 || in_array($term, $vocabulary, true)) {
                continue;
            }
            $distance = strlen($term) >= 8 ? 2 : 1;
            $candidates = [];
            foreach ($vocabulary as $word) {
                if (abs(strlen($term) - strlen($word)) > $distance) {
                    continue;
                }
                $difference = levenshtein($term, $word);
                if ($difference < $distance) {
                    $distance = $difference;
                    $candidates = [$word];
                } elseif ($difference === $distance) {
                    $candidates[] = $word;
                }
            }
            if (count($candidates) === 1) {
                $term = self::ALIASES[$candidates[0]] ?? $candidates[0];
            }
        }
        unset($term);
        return array_values(array_unique($terms));
    }

    /** Split the HTML emitted by our renderer at its generated heading elements. */
    private function sections(DocumentationPage $page): array
    {
        preg_match_all('/<h[1-4] id="([^"]+)"[^>]*>(.*?)<\/h[1-4]>/s', $page->html, $matches, PREG_OFFSET_CAPTURE);
        $sections = [];
        foreach ($matches[0] as $index => [$headingHtml, $offset]) {
            $end = $matches[0][$index + 1][1] ?? strlen($page->html);
            $sections[] = [
                'heading' => $this->plain($matches[2][$index][0]),
                'anchor' => $matches[1][$index][0],
                'html' => substr($page->html, $offset, $end - $offset),
            ];
        }
        return $sections !== [] ? $sections : [['heading' => $page->title, 'anchor' => '', 'html' => $page->html]];
    }

    private function plain(string $html): string
    {
        $text = strip_tags((string) preg_replace('/<[^>]+>/', ' $0 ', $html));
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function excerpt(string $plain, array $terms): string
    {
        $bestPosition = 0;
        $bestMatches = -1;
        foreach (preg_split('/(?<=[.!?])\s+/', $plain, -1, PREG_SPLIT_OFFSET_CAPTURE) ?: [] as [$sentence, $offset]) {
            $matches = count(array_intersect($terms, $this->words($sentence)));
            if ($matches > $bestMatches) {
                $bestMatches = $matches;
                $bestPosition = $offset;
            }
        }
        preg_match('/\A.{0,240}/us', substr($plain, $bestPosition), $match);
        $excerpt = $match[0] ?? '';
        return ($bestPosition > 0 ? '...' : '') . $excerpt . (strlen($plain) > $bestPosition + strlen($excerpt) ? '...' : '');
    }

    private function snippet(string $html): ?array
    {
        if (preg_match('/<code code-lang="([a-z0-9-]+)">(.*?)<\/code>/s', $html, $match) !== 1
            || in_array($match[1], ['structure', 'workflow', 'choices', 'flow', 'responsibilities'], true)) {
            return null;
        }
        $code = html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Do not silently truncate a class or method into apparently runnable code.
        return strlen($code) <= 1200 && substr_count($code, "\n") < 25
            ? ['language' => $match[1], 'code' => $code] : null;
    }
}
