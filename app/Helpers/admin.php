<?php

declare(strict_types=1);

function can_access_admin(): bool
{
    return has_permission('users.manage')
        || has_permission('contract_types.manage')
        || has_permission('settings.manage')
        || has_permission('audit.view');
}

function require_admin_access(): array
{
    $user = require_completed_password_change();

    if (!can_access_admin()) {
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

function admin_roles(): array
{
    return db()->query(
        '
        SELECT
            id,
            name,
            label,
            description
        FROM roles
        WHERE is_active = 1
        ORDER BY
            CASE name
                WHEN "admin" THEN 1
                WHEN "user" THEN 2
                ELSE 10
            END,
            label
        '
    )->fetchAll();
}

function admin_users(): array
{
    return db()->query(
        '
        SELECT
            u.id,
            u.username,
            u.display_name,
            u.email,
            u.is_active,
            u.must_change_password,
            u.last_login_at,
            u.created_at,
            GROUP_CONCAT(
                DISTINCT r.label
                ORDER BY r.label
                SEPARATOR ", "
            ) AS role_labels
        FROM users u
        LEFT JOIN user_roles ur
            ON ur.user_id = u.id
        LEFT JOIN roles r
            ON r.id = ur.role_id
        WHERE u.deleted_at IS NULL
        GROUP BY
            u.id,
            u.username,
            u.display_name,
            u.email,
            u.is_active,
            u.must_change_password,
            u.last_login_at,
            u.created_at
        ORDER BY
            u.is_active DESC,
            u.display_name,
            u.username
        '
    )->fetchAll();
}

function admin_find_user(
    int $userId
): ?array {
    $stmt = db()->prepare(
        '
        SELECT
            u.id,
            u.username,
            u.display_name,
            u.email,
            u.is_active,
            u.must_change_password,
            u.last_login_at,
            u.created_at,
            r.id AS role_id,
            r.name AS role_name,
            r.label AS role_label
        FROM users u
        LEFT JOIN user_roles ur
            ON ur.user_id = u.id
        LEFT JOIN roles r
            ON r.id = ur.role_id
        WHERE u.id = :id
          AND u.deleted_at IS NULL
        ORDER BY r.id
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $userId,
    ]);

    $user = $stmt->fetch();

    return $user ?: null;
}

function admin_validate_user_values(
    array $values,
    ?int $userId = null,
    bool $passwordRequired = false
): ?string {
    if (
        trim((string) ($values['username'] ?? '')) === ''
        || trim((string) ($values['display_name'] ?? '')) === ''
        || trim((string) ($values['role_id'] ?? '')) === ''
    ) {
        return 'Bitte Benutzername, Anzeigename und Rolle ausfüllen.';
    }

    if (
        !preg_match(
            '/^[A-Za-z0-9._-]{2,80}$/',
            (string) $values['username']
        )
    ) {
        return 'Der Benutzername darf nur Buchstaben, Zahlen, Punkt, Unterstrich und Bindestrich enthalten.';
    }

    if (
        !ctype_digit(
            (string) $values['role_id']
        )
    ) {
        return 'Die ausgewählte Rolle ist ungültig.';
    }

    $roleCheck = db()->prepare(
        '
        SELECT COUNT(*)
        FROM roles
        WHERE id = :id
          AND is_active = 1
        '
    );

    $roleCheck->execute([
        'id' => (int) $values['role_id'],
    ]);

    if (
        (int) $roleCheck->fetchColumn()
        !== 1
    ) {
        return 'Die ausgewählte Rolle ist nicht verfügbar.';
    }

    $email =
        trim((string) ($values['email'] ?? ''));

    if (
        $email !== ''
        && !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        return 'Bitte eine gültige E-Mail-Adresse eingeben.';
    }

    $uniqueSql = '
        SELECT COUNT(*)
        FROM users
        WHERE username = :username
          AND deleted_at IS NULL
    ';

    $params = [
        'username' =>
            trim((string) $values['username']),
    ];

    if ($userId !== null) {
        $uniqueSql .= '
            AND id <> :id
        ';

        $params['id'] = $userId;
    }

    $uniqueStmt = db()->prepare(
        $uniqueSql
    );

    $uniqueStmt->execute($params);

    if (
        (int) $uniqueStmt->fetchColumn()
        > 0
    ) {
        return 'Dieser Benutzername ist bereits vergeben.';
    }

    if ($email !== '') {
        $emailSql = '
            SELECT COUNT(*)
            FROM users
            WHERE email = :email
              AND deleted_at IS NULL
        ';

        $emailParams = [
            'email' => $email,
        ];

        if ($userId !== null) {
            $emailSql .= '
                AND id <> :id
            ';

            $emailParams['id'] =
                $userId;
        }

        $emailStmt = db()->prepare(
            $emailSql
        );

        $emailStmt->execute(
            $emailParams
        );

        if (
            (int) $emailStmt->fetchColumn()
            > 0
        ) {
            return 'Diese E-Mail-Adresse wird bereits verwendet.';
        }
    }

    $password =
        (string) ($values['password'] ?? '');

    if (
        $passwordRequired
        && $password === ''
    ) {
        return 'Bitte ein Startpasswort festlegen.';
    }

    if (
        $password !== ''
        && strlen($password) < 12
    ) {
        return 'Das Passwort muss mindestens 12 Zeichen lang sein.';
    }

    return null;
}

function admin_assign_user_role(
    int $userId,
    int $roleId
): void {
    $pdo = db();

    $delete = $pdo->prepare(
        '
        DELETE FROM user_roles
        WHERE user_id = :user_id
        '
    );

    $delete->execute([
        'user_id' => $userId,
    ]);

    $insert = $pdo->prepare(
        '
        INSERT INTO user_roles (
            user_id,
            role_id
        )
        VALUES (
            :user_id,
            :role_id
        )
        '
    );

    $insert->execute([
        'user_id' => $userId,
        'role_id' => $roleId,
    ]);
}

function admin_contract_type_stats(): array
{
    return db()->query(
        '
        SELECT
            COUNT(*) AS total_count,
            SUM(is_active = 1) AS active_count
        FROM contract_types
        '
    )->fetch();
}

function admin_user_stats(): array
{
    return db()->query(
        '
        SELECT
            COUNT(*) AS total_count,
            SUM(is_active = 1) AS active_count
        FROM users
        WHERE deleted_at IS NULL
        '
    )->fetch();
}

function admin_holder_stats(): array
{
    return db()->query(
        '
        SELECT
            COUNT(*) AS total_count,
            SUM(is_active = 1) AS active_count
        FROM contract_holders
        '
    )->fetch();
}

function admin_find_contract_type(
    int $typeId
): ?array {
    $stmt = db()->prepare(
        '
        SELECT
            id,
            name,
            description,
            icon,
            sort_order,
            is_active
        FROM contract_types
        WHERE id = :id
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $typeId,
    ]);

    $type = $stmt->fetch();

    return $type ?: null;
}

function admin_find_contract_holder(
    int $holderId
): ?array {
    $stmt = db()->prepare(
        '
        SELECT
            id,
            name,
            sort_order,
            is_active
        FROM contract_holders
        WHERE id = :id
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $holderId,
    ]);

    $holder = $stmt->fetch();

    return $holder ?: null;
}


function admin_audit_entries(
    int $limit = 100
): array {
    $allowedLimits = [
        50,
        100,
        250,
        500,
    ];

    if (
        !in_array(
            $limit,
            $allowedLimits,
            true
        )
    ) {
        $limit = 100;
    }

    $sql = '
        SELECT
            a.id,
            a.user_id,
            a.action,
            a.entity_type,
            a.entity_id,
            a.description,
            a.details_json,
            a.ip_address,
            a.created_at,
            u.username,
            u.display_name
        FROM audit_log a
        LEFT JOIN users u
            ON u.id = a.user_id
        ORDER BY
            a.created_at DESC,
            a.id DESC
        LIMIT ' . $limit;

    return db()
        ->query($sql)
        ->fetchAll();
}

function admin_audit_users(): array
{
    return db()->query(
        '
        SELECT DISTINCT
            u.id,
            u.username,
            u.display_name
        FROM audit_log a
        INNER JOIN users u
            ON u.id = a.user_id
        ORDER BY
            u.display_name,
            u.username
        '
    )->fetchAll();
}

function admin_audit_actions(): array
{
    return db()->query(
        '
        SELECT DISTINCT
            action
        FROM audit_log
        WHERE action IS NOT NULL
          AND action <> ""
        ORDER BY action
        '
    )->fetchAll();
}

function audit_action_label(
    string $action
): string {
    return match ($action) {
        'login_success' =>
            'Anmeldung',
        'login_failed' =>
            'Anmeldung fehlgeschlagen',
        'logout' =>
            'Abmeldung',
        'password_changed' =>
            'Passwort geändert',

        'contract_created' =>
            'Vertrag angelegt',
        'contract_updated' =>
            'Vertrag bearbeitet',
        'contract_cancelled' =>
            'Vertrag gekündigt',
        'contract_reactivated' =>
            'Vertrag reaktiviert',
        'contract_purged' =>
            'Vertrag endgültig gelöscht',

        'document_uploaded' =>
            'Dokument hochgeladen',
        'document_downloaded' =>
            'Dokument heruntergeladen',
        'document_previewed' =>
            'Dokument angesehen',
        'document_type_created' =>
            'Dokumentart angelegt',
        'document_type_updated' =>
            'Dokumentart bearbeitet',
        'contract_price_changed' =>
            'Vertragspreis geändert',
        'contract_paused' =>
            'Vertrag pausiert',
        'contract_pause_removed' =>
            'Vertragspause entfernt',
        'contract_notifications_changed' =>
            'Vertragsbenachrichtigungen geändert',
        'document_deleted' =>
            'Dokument entfernt',

        'user_created' =>
            'Benutzer angelegt',
        'user_updated' =>
            'Benutzer bearbeitet',

        'contract_type_created' =>
            'Vertragsart angelegt',
        'contract_type_updated' =>
            'Vertragsart bearbeitet',

        'contract_holder_created' =>
            'Vertragsinhaber angelegt',
        'contract_holder_updated' =>
            'Vertragsinhaber bearbeitet',

        default =>
            ucwords(
                str_replace(
                    '_',
                    ' ',
                    $action
                )
            ),
    };
}

function audit_entity_label(
    ?string $entityType
): string {
    return match ($entityType) {
        'auth' =>
            'Anmeldung',
        'contract' =>
            'Vertrag',
        'document' =>
            'Dokument',
        'document_type' =>
            'Dokumentart',
        'user' =>
            'Benutzer',
        'contract_type' =>
            'Vertragsart',
        'contract_holder' =>
            'Vertragsinhaber',
        'system' =>
            'System',
        null, '' =>
            '–',
        default =>
            $entityType,
    };
}

function audit_format_datetime(
    ?string $dateTime
): string {
    if (
        $dateTime === null
        || $dateTime === ''
    ) {
        return '–';
    }

    $timestamp =
        strtotime($dateTime);

    if ($timestamp === false) {
        return '–';
    }

    return date(
        'd.m.Y H:i:s',
        $timestamp
    );
}
