<?php
declare(strict_types=1);

return static fn ($request) => (new \App\Actions\GuidedProjectActions())->shelterApi((string) $request->getAttribute('slug'));
