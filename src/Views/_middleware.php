<?php
declare(strict_types=1);

use App\Middleware\NotFoundMiddleware;
use CorianderCore\Core\Security\ApiRequestLimitsMiddleware;
use CorianderCore\Core\Security\CsrfMiddleware;
use CorianderCore\Core\Security\SecurityHeadersMiddleware;

return [
    new SecurityHeadersMiddleware([
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' https://analytics.corianderphp.com; connect-src 'self' https://analytics.corianderphp.com; base-uri 'self'; frame-ancestors 'none'; object-src 'none'",
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ]),
    new NotFoundMiddleware(),
    new ApiRequestLimitsMiddleware(API_MAX_BODY_BYTES, API_TIMEOUT_SECONDS, apiPrefixes: []),
    // The shelter playground is stateless, read-only, and uses no login cookies.
    new CsrfMiddleware(apiPrefixes: ['api/playground/shelter']),
];
