<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\Docs\DocumentationRepository;
use Modules\Docs\DocumentationSearch;

final class DocsController
{
    public function get_search(): array
    {
        $query = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
        $scope = is_string($_GET['scope'] ?? null) ? trim($_GET['scope']) : 'all';
        $search = new DocumentationSearch(new DocumentationRepository());

        return [
            'ok' => true,
            'query' => $query,
            'scope' => $scope,
            'results' => array_map(static fn(array $result): array => [
                'title' => $result['page']->title,
                'slug' => $result['page']->slug,
                'section' => $result['page']->section,
                'excerpt' => $result['excerpt'],
            ], $search->search($query, $scope)),
        ];
    }
}
