<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\Docs\DocumentationRepository;
use App\Modules\Docs\DocumentationSearch;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

class DocumentationSearchAction
{
    public function get_search(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $query = is_string($params['q'] ?? null) ? trim($params['q']) : '';
        $scope = is_string($params['scope'] ?? null) ? trim($params['scope']) : 'all';
        $search = new DocumentationSearch(new DocumentationRepository());

        $payload = [
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
        return new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
