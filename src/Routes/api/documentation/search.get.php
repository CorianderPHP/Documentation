<?php
declare(strict_types=1);

return static fn ($request) => (new \App\Actions\DocumentationSearchAction())->get_search($request);
