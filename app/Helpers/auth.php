<?php

declare(strict_types=1);

function current_user(): ?array
{
    if (
        empty($_SESSION['user_id'])
        || !is_numeric($_SESSION['user_id'])
    ) {
        return null;
    }

    $stmt = db()->prepare(
        '
        SELECT
            id,
            username,
            display_name,
            email,
            is_active,
            must_change_password,
            last_login_at
        FROM users
        WHERE id = :id
          AND is_active = 1
          AND deleted_at IS NULL
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => (int) $_SESSION['user_id'],
    ]);

    $user = $stmt->fetch();

    return $user ?: null;
}

function login_user(int $userId): void
{
    session_regenerate_id(false);

    $_SESSION['user_id'] = $userId;
    $_SESSION['authenticated_at'] = time();

    unset($_SESSION['csrf_token']);
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]
        );
    }

    session_destroy();
}

function require_login(): array
{
    $user = current_user();

    if ($user === null) {
        redirect('/login');
    }

    return $user;
}

function require_completed_password_change(): array
{
    $user = require_login();

    if ((int) $user['must_change_password'] === 1) {
        redirect('/password/change');
    }

    return $user;
}

function user_permissions(int $userId): array
{
    $stmt = db()->prepare(
        '
        SELECT DISTINCT
            p.permission_key
        FROM permissions p
        INNER JOIN role_permissions rp
            ON rp.permission_id = p.id
        INNER JOIN roles r
            ON r.id = rp.role_id
           AND r.is_active = 1
        INNER JOIN user_roles ur
            ON ur.role_id = r.id
        WHERE ur.user_id = :user_id
        ORDER BY p.permission_key
        '
    );

    $stmt->execute([
        'user_id' => $userId,
    ]);

    return array_column(
        $stmt->fetchAll(),
        'permission_key'
    );
}

function has_permission(string $permission): bool
{
    $user = current_user();

    if ($user === null) {
        return false;
    }

    return in_array(
        $permission,
        user_permissions((int) $user['id']),
        true
    );
}

function require_permission(string $permission): array
{
    $user = require_completed_password_change();

    if (!has_permission($permission)) {
        http_response_code(403);

        render(
            'errors/403',
            [
                'pageTitle' => 'Kein Zugriff',
                'user' => $user,
            ]
        );
    }

    return $user;
}

function login_is_blocked(
    string $username,
    string $ip
): bool {
    $maxAttempts = max(
        1,
        (int) (getenv('LOGIN_MAX_ATTEMPTS') ?: 5)
    );

    $windowMinutes = max(
        1,
        (int) (getenv('LOGIN_WINDOW_MINUTES') ?: 15)
    );

    $since = date(
        'Y-m-d H:i:s',
        time() - ($windowMinutes * 60)
    );

    $stmt = db()->prepare(
        '
        SELECT COUNT(*)
        FROM login_attempts
        WHERE username = :username
          AND ip_address = :ip
          AND was_successful = 0
          AND attempted_at >= :since
        '
    );

    $stmt->execute([
        'username' => $username,
        'ip' => $ip,
        'since' => $since,
    ]);

    return (int) $stmt->fetchColumn() >= $maxAttempts;
}

function record_login_attempt(
    string $username,
    string $ip,
    bool $successful
): void {
    $stmt = db()->prepare(
        '
        INSERT INTO login_attempts (
            username,
            ip_address,
            was_successful
        )
        VALUES (
            :username,
            :ip,
            :successful
        )
        '
    );

    $stmt->execute([
        'username' => $username,
        'ip' => $ip,
        'successful' => $successful ? 1 : 0,
    ]);

    if ($successful) {
        $cleanup = db()->prepare(
            '
            DELETE FROM login_attempts
            WHERE username = :username
              AND ip_address = :ip
              AND was_successful = 0
            '
        );

        $cleanup->execute([
            'username' => $username,
            'ip' => $ip,
        ]);
    }
}

function audit_log(
    ?int $userId,
    string $action,
    ?string $description = null,
    ?string $entityType = 'auth',
    ?int $entityId = null,
    ?array $details = null
): void {
    $detailsJson = null;

    if ($details !== null) {
        $encoded = json_encode(
            $details,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        if ($encoded !== false) {
            $detailsJson = $encoded;
        }
    }

    $stmt = db()->prepare(
        '
        INSERT INTO audit_log (
            user_id,
            action,
            entity_type,
            entity_id,
            description,
            details_json,
            ip_address
        )
        VALUES (
            :user_id,
            :action,
            :entity_type,
            :entity_id,
            :description,
            :details_json,
            :ip_address
        )
        '
    );

    $stmt->execute([
        'user_id' => $userId,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'description' => $description,
        'details_json' => $detailsJson,
        'ip_address' => client_ip(),
    ]);
}
