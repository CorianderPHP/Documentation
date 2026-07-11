<?php
declare(strict_types=1);

namespace Modules\Docs;

final class DocumentationSearch
{
    public function __construct(private readonly DocumentationRepository $repository)
    {
    }

    /**
     * @return array<int,array{page:DocumentationPage,score:int,excerpt:string}>
     */
    public function search(string $query, string $scope = 'all'): array
    {
        $terms = array_values(array_filter(preg_split('/\s+/', strtolower(trim($query))) ?: []));
        if ($terms === []) {
            return [];
        }

        $results = [];
        foreach ($this->repository->byScope($scope) as $page) {
            $haystack = strtolower($page->title . ' ' . $page->description . ' ' . strip_tags($page->html));
            $score = 0;
            foreach ($terms as $term) {
                $score += substr_count(strtolower($page->title), $term) * 5;
                $score += substr_count($haystack, $term);
            }

            if ($score <= 0) {
                continue;
            }

            $results[] = [
                'page' => $page,
                'score' => $score,
                'excerpt' => $this->excerpt($page, $terms),
            ];
        }

        usort($results, static fn(array $left, array $right): int => $right['score'] <=> $left['score']);
        return $results;
    }

    /**
     * @param string[] $terms
     */
    private function excerpt(DocumentationPage $page, array $terms): string
    {
        $plain = trim((string) preg_replace('/\s+/', ' ', strip_tags($page->html)));
        $lower = strtolower($plain);
        $position = 0;
        foreach ($terms as $term) {
            $found = strpos($lower, $term);
            if ($found !== false) {
                $position = max(0, $found - 70);
                break;
            }
        }

        $excerpt = substr($plain, $position, 180);
        return ($position > 0 ? '...' : '') . $excerpt . (strlen($plain) > $position + 180 ? '...' : '');
    }
}
