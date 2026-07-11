<?php
declare(strict_types=1);

namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;
use Modules\Docs\DocumentationRepository;
use Modules\Docs\DocumentationSearch;
use Modules\Docs\GuidedProjectRegistry;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class DocsController
{
    private ViewRenderer $view;
    private DocumentationRepository $repository;
    private DocumentationSearch $search;
    private GuidedProjectRegistry $projectRegistry;

    public function __construct()
    {
        $this->view = new ViewRenderer();
        $this->projectRegistry = new GuidedProjectRegistry();
        $this->repository = new DocumentationRepository();
        $this->search = new DocumentationSearch($this->repository);
    }

    public function index(): void
    {
        $this->view->render('docs', [
            'mode' => 'index',
            'pages' => $this->repository->byScope('reference'),
            'groups' => $this->repository->grouped('reference'),
            'activeSlug' => '',
            'query' => '',
            'scope' => 'reference',
            'results' => [],
            'page' => null,
        ]);
    }

    public function show(string $slug): ?Response
    {
        $page = $this->repository->find($slug);
        if ($page === null || $this->projectRegistry->projectForSlug($page->slug) !== null || in_array($page->slug, ['start-project', 'forum-project'], true)) {
            return new Response(404, [], 'Documentation page not found.');
        }

        $this->view->render('docs', [
            'mode' => 'show',
            'pages' => $this->repository->byScope('reference'),
            'groups' => $this->repository->grouped('reference'),
            'activeSlug' => $page->slug,
            'query' => '',
            'scope' => 'reference',
            'results' => [],
            'page' => $page,
        ]);

        return null;
    }

    public function search(ServerRequestInterface $request): void
    {
        $queryParams = $request->getQueryParams();
        $query = is_string($queryParams['q'] ?? null) ? trim($queryParams['q']) : '';
        $scope = 'reference';

        $this->view->render('docs', [
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
