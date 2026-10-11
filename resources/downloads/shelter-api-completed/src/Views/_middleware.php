<?php
declare(strict_types=1);

use CorianderCore\Core\Security\ApiRequestLimitsMiddleware;
use CorianderCore\Core\Security\CsrfMiddleware;
use CorianderCore\Core\Security\SecurityHeadersMiddleware;

// Fresh local starter only: merge this narrow stateless exception into existing apps.
// CSRF exemption is not authentication; protect real public writes before deployment.
return [
    new SecurityHeadersMiddleware(),
    new ApiRequestLimitsMiddleware(apiPrefixes: []),
    new CsrfMiddleware(apiPrefixes: ['api/shelter']),
];
