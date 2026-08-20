<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(
        STDERR,
        "Dieses Skript darf nur über die Kommandozeile ausgeführt werden.\n"
    );
    exit(1);
}

define(
    'BASE_PATH',
    dirname(__DIR__)
);

require_once BASE_PATH
    . '/config/database.php';

function prompt(
    string $label,
    ?string $default = null
): string {
    $suffix =
        $default !== null
            ? ' [' . $default . ']'
            : '';

    fwrite(
        STDOUT,
        $label . $suffix . ': '
    );

    $value =
        trim(
            (string) fgets(STDIN)
        );

    if (
        $value === ''
        && $default !== null
    ) {
        return $default;
    }

    return $value;
}

function generateTemporaryPassword(): string
{
    return rtrim(
        strtr(
            base64_encode(
                random_bytes(24)
            ),
            '+/',
            '-_'
        ),
        '='
    );
}

$username =
    prompt(
        'Benutzername',
        'admin'
    );

$displayName =
    prompt(
        'Anzeigename',
        'Administrator'
    );

$email =
    prompt(
        'E-Mail-Adresse (optional)'
    );

if (
    !preg_match(
        '/^[A-Za-z0-9._-]{3,100}$/',
        $username
    )
) {
    fwrite(
        STDERR,
        "Der Benutzername muss 3 bis 100 Zeichen lang sein und darf nur Buchstaben, Zahlen, Punkt, Unterstrich und Bindestrich enthalten.\n"
    );
    exit(1);
}

if ($displayName === '') {
    fwrite(
        STDERR,
        "Der Anzeigename darf nicht leer sein.\n"
    );
    exit(1);
}

if (
    $email !== ''
    && filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ) === false
) {
    fwrite(
        STDERR,
        "Die E-Mail-Adresse ist ungültig.\n"
    );
    exit(1);
}

$pdo = db();
$pdo->beginTransaction();

try {
    $existing =
        $pdo->prepare(
            '
            SELECT id
            FROM users
            WHERE username = :username
               OR (
                    :email_check <> ""
                    AND email = :email_value
               )
            LIMIT 1
            '
        );

    $existing->execute([
        'username' => $username,
        'email_check' => $email,
        'email_value' => $email,
    ]);

    if ($existing->fetchColumn()) {
        throw new RuntimeException(
            'Ein Benutzer mit diesem Benutzernamen oder dieser E-Mail-Adresse existiert bereits.'
        );
    }

    $role =
        $pdo->query(
            '
            SELECT id
            FROM roles
            WHERE name = "admin"
              AND is_active = 1
            LIMIT 1
            '
        );

    $roleId =
        $role->fetchColumn();

    if (!$roleId) {
        throw new RuntimeException(
            'Die Administratorrolle wurde nicht gefunden. Bitte zuerst die Datenbankmigrationen ausführen.'
        );
    }

    $temporaryPassword =
        generateTemporaryPassword();

    $insert =
        $pdo->prepare(
            '
            INSERT INTO users (
                username,
                display_name,
                email,
                password_hash,
                is_active,
                must_change_password
            )
            VALUES (
                :username,
                :display_name,
                :email,
                :password_hash,
                1,
                1
            )
            '
        );

    $insert->execute([
        'username' => $username,
        'display_name' => $displayName,
        'email' => $email !== '' ? $email : null,
        'password_hash' =>
            password_hash(
                $temporaryPassword,
                PASSWORD_DEFAULT
            ),
    ]);

    $userId =
        (int) $pdo->lastInsertId();

    $assign =
        $pdo->prepare(
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

    $assign->execute([
        'user_id' => $userId,
        'role_id' => (int) $roleId,
    ]);

    $pdo->commit();

    echo PHP_EOL;
    echo "Administrator wurde angelegt.\n";
    echo "Benutzername: " . $username . "\n";
    echo "Temporäres Passwort: " . $temporaryPassword . "\n";
    echo "Beim ersten Login muss das Passwort geändert werden.\n";
    echo PHP_EOL;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(
        STDERR,
        'FEHLER: '
            . $e->getMessage()
            . PHP_EOL
    );

    exit(1);
}
