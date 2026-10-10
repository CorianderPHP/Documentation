<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\Docs\SiteView;
use Psr\Http\Message\ResponseInterface;
use App\Modules\Docs\DocumentationRepository;
use App\Modules\Docs\DocumentationSearch;
use App\Modules\Docs\DocumentationPage;
use App\Modules\Docs\GuidedProject;
use App\Modules\Docs\GuidedProjectNavigation;
use App\Modules\Docs\GuidedProjectRegistry;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class GuidedProjectActions
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
        $projects = [];
        foreach ($this->projectRegistry->all() as $project) {
            $navigation = new GuidedProjectNavigation($project);
            $projects[] = [
                'project' => $project,
                'pages' => $navigation->orderedPages($this->repository->byScope($project->key)),
            ];
        }

        return $this->view->response('examples', [
            'projects' => $projects,
        ]);
    }

    public function forum(?string $slug = null): ResponseInterface
    {
        return $this->showProject('forum', $slug);
    }

    public function forumSearch(ServerRequestInterface $request): ResponseInterface
    {
        return $this->searchProject('forum', $request);
    }

    public function shelterApi(?string $slug = null): ResponseInterface
    {
        return $this->showProject('shelter-api', $slug);
    }

    public function shelterApiSearch(ServerRequestInterface $request): ResponseInterface
    {
        return $this->searchProject('shelter-api', $request);
    }

    private function showProject(string $key, ?string $slug = null): ResponseInterface
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
        return $this->view->response($project->view, $this->viewData($project, $navigation, $pages, [
            'mode' => 'show',
            'adjacent' => $navigation->adjacent($pages, $page->slug),
            'page' => $page,
            'query' => '',
            'results' => [],
        ]));

    }

    private function searchProject(string $key, ServerRequestInterface $request): ResponseInterface
    {
        $project = $this->projectRegistry->find($key);
        if ($project === null) {
            return new Response(404, [], 'Guided project not found.');
        }

        $queryParams = $request->getQueryParams();
        $query = is_string($queryParams['q'] ?? null) ? trim($queryParams['q']) : '';

        $navigation = new GuidedProjectNavigation($project);
        $pages = $this->repository->byScope($project->key);
        return $this->view->response($project->view, $this->viewData($project, $navigation, $pages, [
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
