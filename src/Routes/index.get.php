<?php
declare(strict_types=1);

return static fn ($request) => (new \App\Modules\Docs\SiteView())->response('home');
