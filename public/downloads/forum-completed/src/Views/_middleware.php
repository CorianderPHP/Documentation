<?php
use CorianderCore\Core\Security\ApiRequestLimitsMiddleware;
use CorianderCore\Core\Security\CsrfMiddleware;
use CorianderCore\Core\Security\SecurityHeadersMiddleware;

// Fresh-starter policy. Merge into existing apps instead of removing their gates.
return [
    new SecurityHeadersMiddleware(),
    new ApiRequestLimitsMiddleware(apiPrefixes: []),
    new CsrfMiddleware(),
];
