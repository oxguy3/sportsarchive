<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class KernelListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set("Content-Security-Policy", "default-src 'self'; script-src 'self' 'unsafe-inline' https://*.googletagmanager.com; img-src 'self' data: https://nyc3.digitaloceanspaces.com https://imgproxy.sportsarchive.net https://*.google-analytics.com https://*.googletagmanager.com; style-src 'self' 'unsafe-inline'; connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com; report-uri https://d23266040c21bd2a00e0e190e8a04a64.report-uri.com/r/d/csp/enforce");
        $response->headers->set("Strict-Transport-Security", "max-age=31536000");
        $response->headers->set("X-XSS-Protection", "1; mode=block");
        $response->headers->set("X-Frame-Options", "SAMEORIGIN");
        $response->headers->set("X-Content-Type-Options", "nosniff");
    }
}
