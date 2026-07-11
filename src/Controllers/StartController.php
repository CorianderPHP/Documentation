<?php
declare(strict_types=1);

namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;
use Modules\Docs\DocumentationRepository;
use Nyholm\Psr7\Response;

final class StartController
{
    private ViewRenderer $view;
    private DocumentationRepository $repository;

    public function __construct()
    {
        $this->view = new ViewRenderer();
        $this->repository = new DocumentationRepository();
    }

    public function index(): ?Response
    {
        $page = $this->repository->find('start-project');
        if ($page === null) {
            return new Response(404, [], 'Start guide not found.');
        }

        $this->view->render('start', ['page' => $page]);
        return null;
    }
}
