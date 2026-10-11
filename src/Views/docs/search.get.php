<?php
declare(strict_types=1);

return static fn ($request) => \CorianderCore\Core\Http\Responses::redirect('/documentation/search?' . http_build_query($request->getQueryParams()), 302);
