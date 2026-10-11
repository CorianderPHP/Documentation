<?php
declare(strict_types=1);

return static fn ($request) => \CorianderCore\Core\Http\Responses::redirect('/documentation', 302);
