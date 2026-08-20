<?php

declare(strict_types=1);

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function request_path(): string
{
    $path = parse_url(
        $_SERVER['REQUEST_URI'] ?? '/',
        PHP_URL_PATH
    );

    if (!is_string($path) || $path === '') {
        return '/';
    }

    if ($path !== '/') {
        $path = rtrim($path, '/');
    }

    return $path;
}

function ip_matches_cidr(
    string $ip,
    string $cidr
): bool {
    $cidr = trim($cidr);

    if ($cidr === '') {
        return false;
    }

    if (!str_contains($cidr, '/')) {
        return hash_equals(
            $cidr,
            $ip
        );
    }

    [$network, $prefixValue] =
        array_pad(
            explode('/', $cidr, 2),
            2,
            null
        );

    if (
        $network === null
        || $prefixValue === null
        || !ctype_digit($prefixValue)
    ) {
        return false;
    }

    $ipBinary = inet_pton($ip);
    $networkBinary = inet_pton($network);

    if (
        $ipBinary === false
        || $networkBinary === false
        || strlen($ipBinary)
            !== strlen($networkBinary)
    ) {
        return false;
    }

    $maxBits =
        strlen($ipBinary) * 8;

    $prefix =
        (int) $prefixValue;

    if (
        $prefix < 0
        || $prefix > $maxBits
    ) {
        return false;
    }

    $fullBytes =
        intdiv($prefix, 8);

    $remainingBits =
        $prefix % 8;

    if (
        $fullBytes > 0
        && substr(
            $ipBinary,
            0,
            $fullBytes
        ) !== substr(
            $networkBinary,
            0,
            $fullBytes
        )
    ) {
        return false;
    }

    if ($remainingBits === 0) {
        return true;
    }

    $mask =
        (0xFF << (8 - $remainingBits))
        & 0xFF;

    return (
        ord($ipBinary[$fullBytes])
        & $mask
    ) === (
        ord($networkBinary[$fullBytes])
        & $mask
    );
}

function request_from_trusted_proxy(
    string $remoteIp
): bool {
    $configured =
        trim(
            (string) getenv(
                'TRUSTED_PROXIES'
            )
        );

    if ($configured === '') {
        return false;
    }

    foreach (
        explode(',', $configured)
        as $trustedProxy
    ) {
        if (
            ip_matches_cidr(
                $remoteIp,
                trim($trustedProxy)
            )
        ) {
            return true;
        }
    }

    return false;
}

function client_ip(): string
{
    $remoteIp =
        $_SERVER['REMOTE_ADDR']
        ?? '';

    if (
        !is_string($remoteIp)
        || filter_var(
            $remoteIp,
            FILTER_VALIDATE_IP
        ) === false
    ) {
        return '0.0.0.0';
    }

    if (
        request_from_trusted_proxy(
            $remoteIp
        )
    ) {
        $forwardedFor =
            $_SERVER[
                'HTTP_X_FORWARDED_FOR'
            ] ?? '';

        if (is_string($forwardedFor)) {
            foreach (
                explode(',', $forwardedFor)
                as $candidate
            ) {
                $candidate =
                    trim($candidate);

                if (
                    filter_var(
                        $candidate,
                        FILTER_VALIDATE_IP
                    ) !== false
                ) {
                    return $candidate;
                }
            }
        }
    }

    return $remoteIp;
}
