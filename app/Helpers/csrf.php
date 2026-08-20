<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (
        !isset($_SESSION['csrf_token'])
        || !is_string($_SESSION['csrf_token'])
    ) {
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return sprintf(
        '<input type="hidden" name="_token" value="%s">',
        htmlspecialchars(
            csrf_token(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

function csrf_verify(): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? null;
    $submittedToken = $_POST['_token'] ?? null;

    if (
        !is_string($sessionToken)
        || !is_string($submittedToken)
    ) {
        return false;
    }

    return hash_equals(
        $sessionToken,
        $submittedToken
    );
}
