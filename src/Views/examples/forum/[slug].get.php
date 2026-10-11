<?php
declare(strict_types=1);

return static fn ($request) => \CorianderCore\Core\Http\Responses::redirect('/guided-projects/forum/' . $request->getAttribute('slug'), 301);
