<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\Docs\SiteView;
use Psr\Http\Message\ResponseInterface;
use App\Modules\Docs\DocumentationRepository;
use App\Modules\Docs\DocumentationSearch;
use App\Modules\Docs\GuidedProjectRegistry;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class DocumentationActions
{
    private SiteView $view;
    private DocumentationRepository $repository;
    private DocumentationSearch $search;
    private GuidedProjectRegistry $projectRegistry;

    public function __construct()
    {
        $this->view = new SiteView();
        $this->projectRegistry = new GuidedProjectRegistry();
        $this->repository = new DocumentationRepository();
        $this->search = new DocumentationSearch($this->repository);
    }

    public function index(): ResponseInterface
    {
        $page = $this->repository->find('index');

        return $this->view->response('documentation', [
            'mode' => $page !== null ? 'show' : 'index',
            'pages' => $this->repository->byScope('reference'),
            'groups' => $this->repository->grouped('reference'),
            'activeSlug' => 'index',
            'query' => '',
            'scope' => 'reference',
            'results' => [],
            'page' => $page,
        ]);
    }

    public function show(string $slug): ResponseInterface
    {
        $page = $this->repository->find($slug);
        if ($page === null || $this->projectRegistry->projectForSlug($page->slug) !== null || in_array($page->slug, ['index', 'forum-project'], true)) {
            return new Response(404, [], 'Documentation page not found.');
        }

        return $this->view->response('documentation', [
            'mode' => 'show',
            'pages' => $this->repository->byScope('reference'),
            'groups' => $this->repository->grouped('reference'),
            'activeSlug' => $page->slug,
            'query' => '',
            'scope' => 'reference',
            'results' => [],
            'page' => $page,
        ]);

    }

    public function search(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $query = is_string($queryParams['q'] ?? null) ? trim($queryParams['q']) : '';
        $scope = 'reference';

        return $this->view->response('documentation', [
            'mode' => 'search',
            'pages' => $this->repository->byScope('reference'),
            'groups' => $this->repository->grouped('reference'),
            'activeSlug' => 'search',
            'query' => $query,
            'scope' => $scope,
            'results' => $this->search->search($query, $scope),
            'page' => null,
        ]);
    }
}
