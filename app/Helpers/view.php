<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


function brand_asset_url(
    string $relativePath
): ?string {
    $relativePath =
        ltrim(
            str_replace(
                '\\',
                '/',
                $relativePath
            ),
            '/'
        );

    if (
        $relativePath === ''
        || str_contains(
            $relativePath,
            '..'
        )
    ) {
        return null;
    }

    $absolutePath =
        BASE_PATH
        . '/public/'
        . $relativePath;

    if (!is_file($absolutePath)) {
        return null;
    }

    $modified =
        @filemtime(
            $absolutePath
        );

    $url =
        '/'
        . $relativePath;

    if (
        is_int($modified)
        && $modified > 0
    ) {
        $url .=
            '?v='
            . $modified;
    }

    return $url;
}

function brand_logo_url(): ?string
{
    return brand_asset_url(
        'assets/images/vertrag-home-logo.png'
    );
}

function brand_favicon_url(): ?string
{
    return brand_asset_url(
        'assets/images/vertrag-home-favicon.png'
    );
}


function render(
    string $template,
    array $data = []
): never {
    extract($data, EXTR_SKIP);

    ob_start();

    require BASE_PATH
        . '/templates/'
        . $template
        . '.php';

    $content = ob_get_clean();

    require BASE_PATH
        . '/templates/layout.php';

    exit;
}
