<?php
declare(strict_types=1);

return static fn ($request) => \CorianderCore\Core\Http\Responses::redirect('/forum-demo/topics/' . $request->getAttribute('id'), 302);
