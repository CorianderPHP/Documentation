<?php
declare(strict_types=1);

return static fn ($request) => (new \App\Actions\ForumActions())->storeReply($request, (string) $request->getAttribute('id'));
