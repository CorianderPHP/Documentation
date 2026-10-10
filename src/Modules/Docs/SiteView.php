<?php
declare(strict_types=1);

namespace App\Modules\Docs;

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;

final class SiteView
{
    public function response(string $view, array $data = [], int $status = 200): ResponseInterface
    {
        $title = match (true) {
            $view === 'documentation' => 'Documentation',
            $view === 'examples/forum' => 'Forum Guided Project',
            $view === 'examples/shelter-api' => 'Shelter API Guided Project',
            $view === 'examples' => 'Guided Projects',
            str_starts_with($view, 'forum-demo') => 'Forum Demo',
            $view === 'notfound' => 'Page Not Found',
            default => 'CorianderPHP',
        };
        return Responses::view($view, $data + [
            'requestedView' => $view,
            'title' => $title,
            'description' => 'CorianderPHP documentation and guided projects.',
        ], $status);
    }
}
