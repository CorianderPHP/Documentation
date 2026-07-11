<?php
declare(strict_types=1);

namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;
use Modules\Docs\DocumentationRepository;
use Modules\Docs\DocumentationSearch;
use Modules\Docs\DocumentationPage;
use Modules\Docs\GuidedProject;
use Modules\Docs\GuidedProjectNavigation;
use Modules\Docs\GuidedProjectRegistry;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class ExamplesController
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
        $projects = [];
        foreach ($this->projectRegistry->all() as $project) {
            $navigation = new GuidedProjectNavigation($project);
            $projects[] = [
                'project' => $project,
                'pages' => $navigation->orderedPages($this->repository->byScope($project->key)),
            ];
        }

        $this->view->render('examples', [
            'projects' => $projects,
        ]);
    }

    public function forum(?string $slug = null): ?Response
    {
        return $this->showProject('forum', $slug);
    }

    public function forumSearch(ServerRequestInterface $request): void
    {
        $this->searchProject('forum', $request);
    }

    public function shelterApi(?string $slug = null): ?Response
    {
        return $this->showProject('shelter-api', $slug);
    }

    public function shelterApiSearch(ServerRequestInterface $request): void
    {
        $this->searchProject('shelter-api', $request);
    }

    private function showProject(string $key, ?string $slug = null): ?Response
    {
        $project = $this->projectRegistry->find($key);
        if ($project === null) {
            return new Response(404, [], 'Guided project not found.');
        }

        $page = $this->repository->find($project->docSlug($slug));
        if ($page === null) {
            return new Response(404, [], 'Guided project page not found.');
        }

        $navigation = new GuidedProjectNavigation($project);
        $pages = $this->repository->byScope($project->key);
        $this->view->render($project->view, $this->viewData($project, $navigation, $pages, [
            'mode' => 'show',
            'adjacent' => $navigation->adjacent($pages, $page->slug),
            'page' => $page,
            'query' => '',
            'results' => [],
        ]));

        return null;
    }

    private function searchProject(string $key, ServerRequestInterface $request): void
    {
        $project = $this->projectRegistry->find($key);
        if ($project === null) {
            return;
        }

        $queryParams = $request->getQueryParams();
        $query = is_string($queryParams['q'] ?? null) ? trim($queryParams['q']) : '';

        $navigation = new GuidedProjectNavigation($project);
        $pages = $this->repository->byScope($project->key);
        $this->view->render($project->view, $this->viewData($project, $navigation, $pages, [
            'mode' => 'search',
            'adjacent' => ['previous' => null, 'next' => null],
            'page' => null,
            'query' => $query,
            'results' => $this->search->search($query, $project->key),
        ]));
    }

    /**
     * @param DocumentationPage[] $pages
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function viewData(GuidedProject $project, GuidedProjectNavigation $navigation, array $pages, array $data): array
    {
        return $data + [
            'project' => $project,
            'pages' => $navigation->orderedPages($pages),
            'navigationGroups' => $navigation->grouped($pages),
        ];
    }
}
