<?php
declare(strict_types=1);

/**
 * Deliberately simple session-token CSRF check for the demo. A real
 * application should use a hardened, well-reviewed CSRF library instead
 * of hand-rolled token comparisons.
 */

function smooth_delete_csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function smooth_delete_csrf_valid(?string $submitted): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $expected = $_SESSION['csrf_token'] ?? null;

    return is_string($submitted) && is_string($expected) && hash_equals($expected, $submitted);
}
