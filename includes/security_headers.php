<?php
declare(strict_types=1);

function csp_nonce(): string
{
    static $nonce = null;

    if ($nonce === null) {
        $nonce = bin2hex(random_bytes(16));
    }

    return $nonce;
}

function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    $nonce = csp_nonce();

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com data:; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
}
