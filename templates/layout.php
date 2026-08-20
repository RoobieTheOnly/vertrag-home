<?php

declare(strict_types=1);

$user = current_user();

$pageTitle = isset($pageTitle)
    ? $pageTitle . ' – Vertrag Home'
    : 'Vertrag Home';

$contractNotifications = [];

if (
    $user !== null
    && (int) $user[
        'must_change_password'
    ] === 0
    && has_permission(
        'contracts.view'
    )
) {
    $contractNotifications =
        contract_notifications();
}

$contractNotificationCount =
    count(
        $contractNotifications
    );

$brandLogoUrl =
    brand_logo_url();

$brandFaviconUrl =
    brand_favicon_url();

$toastMessages = [];

if (
    isset($success)
    && is_string($success)
    && trim($success) !== ''
) {
    $toastMessages[] = [
        'type' => 'success',
        'message' => $success,
    ];
}

if (
    isset($warning)
    && is_string($warning)
    && trim($warning) !== ''
) {
    $toastMessages[] = [
        'type' => 'warning',
        'message' => $warning,
    ];
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta
        name="color-scheme"
        content="light dark"
    >
    <title><?= e($pageTitle) ?></title>

    <?php if ($brandFaviconUrl !== null): ?>
        <link
            rel="icon"
            type="image/png"
            href="<?= e($brandFaviconUrl) ?>"
        >
        <link
            rel="apple-touch-icon"
            href="<?= e($brandFaviconUrl) ?>"
        >
    <?php endif; ?>

    <link
        rel="stylesheet"
        href="/assets/css/app.css"
    >
    <script
        src="/assets/js/app.js"
        defer
    ></script>
</head>

<body
    class="
        min-h-screen
        bg-slate-100
        text-slate-900
        dark:bg-slate-950
        dark:text-slate-100
    "
>

<?php if ($user !== null): ?>

    <header
        class="
            print:hidden
            sticky
            top-0
            z-40
            border-b
            border-slate-200
            bg-white/95
            backdrop-blur
            dark:border-slate-800
            dark:bg-slate-900/95
        "
    >
        <div
            class="
                mx-auto
                flex
                max-w-7xl
                items-center
                justify-between
                gap-6
                px-4
                py-3
                sm:px-6
                sm:py-4
            "
        >
            <div class="flex min-w-0 items-center gap-8">

                <a
                    href="/dashboard"
                    class="
                        flex
                        shrink-0
                        items-center
                    "
                    aria-label="Vertrag Home – Übersicht"
                >
                    <?php if (
                        $brandLogoUrl !== null
                    ): ?>

                        <img
                            src="<?= e($brandLogoUrl) ?>"
                            alt="Vertrag Home"
                            class="
                                h-9
                                w-auto
                                max-w-[180px]
                                object-contain
                                sm:h-10
                                sm:max-w-[220px]
                            "
                        >

                    <?php else: ?>

                        <span
                            class="
                                truncate
                                text-xl
                                font-bold
                                tracking-tight
                                text-slate-900
                                dark:text-white
                            "
                        >
                            Vertrag Home
                        </span>

                    <?php endif; ?>
                </a>

                <?php if (
                    (int) $user['must_change_password'] === 0
                ): ?>

                    <nav
                        class="
                            hidden
                            items-center
                            gap-5
                            md:flex
                        "
                    >
                        <a
                            href="/dashboard"
                            class="
                                text-sm
                                font-medium
                                text-slate-600
                                transition
                                hover:text-slate-950
                                dark:text-slate-300
                                dark:hover:text-white
                            "
                        >
                            Übersicht
                        </a>

                        <?php if (
                            has_permission(
                                'contracts.view'
                            )
                        ): ?>

                            <a
                                href="/contracts"
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-600
                                    transition
                                    hover:text-slate-950
                                    dark:text-slate-300
                                    dark:hover:text-white
                                "
                            >
                                Verträge
                            </a>

                            <a
                                href="/deadlines"
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-600
                                    transition
                                    hover:text-slate-950
                                    dark:text-slate-300
                                    dark:hover:text-white
                                "
                            >
                                Fristen
                            </a>

                            <a
                                href="/reports/payment-planner"
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-600
                                    transition
                                    hover:text-slate-950
                                    dark:text-slate-300
                                    dark:hover:text-white
                                "
                            >
                                Ausgabenplanung
                            </a>

                            <a
                                href="/reports/cost-development"
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-600
                                    transition
                                    hover:text-slate-950
                                    dark:text-slate-300
                                    dark:hover:text-white
                                "
                            >
                                Kosten
                            </a>

                            <a
                                href="/reports/financial-overview"
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-600
                                    transition
                                    hover:text-slate-950
                                    dark:text-slate-300
                                    dark:hover:text-white
                                "
                            >
                                Finanzübersicht
                            </a>

                        <?php endif; ?>


                        <?php if (
                            can_access_admin()
                        ): ?>

                            <a
                                href="/admin"
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-600
                                    transition
                                    hover:text-slate-950
                                    dark:text-slate-300
                                    dark:hover:text-white
                                "
                            >
                                Administration
                            </a>

                        <?php endif; ?>

                    </nav>

                <?php endif; ?>

            </div>

            <div
                class="
                    flex
                    shrink-0
                    items-center
                    gap-2
                "
            >

                <?php if (
                    (int) $user[
                        'must_change_password'
                    ] === 0
                    && has_permission(
                        'contracts.view'
                    )
                ): ?>

                    <div class="relative">

                        <button
                            type="button"
                            data-notification-button
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-label="Vertragshinweise öffnen"
                            class="
                                relative
                                flex
                                h-[54px]
                                w-[54px]
                                items-center
                                justify-center
                                rounded-xl
                                border
                                border-slate-200
                                bg-white
                                text-slate-600
                                transition
                                hover:bg-slate-50
                                hover:text-slate-900
                                dark:border-slate-700
                                dark:bg-slate-800
                                dark:text-slate-300
                                dark:hover:bg-slate-700
                                dark:hover:text-white
                            "
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-5 w-5"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"
                                />
                            </svg>

                            <?php if (
                                $contractNotificationCount > 0
                            ): ?>

                                <span
                                    class="
                                        absolute
                                        -right-1
                                        -top-1
                                        flex
                                        min-h-5
                                        min-w-5
                                        items-center
                                        justify-center
                                        rounded-full
                                        bg-red-600
                                        px-1.5
                                        text-[10px]
                                        font-bold
                                        leading-5
                                        text-white
                                        ring-2
                                        ring-white
                                        dark:ring-slate-900
                                    "
                                >
                                    <?= $contractNotificationCount > 99
                                        ? '99+'
                                        : $contractNotificationCount ?>
                                </span>

                            <?php endif; ?>

                        </button>


                        <div
                            data-notification-menu
                            class="
                                fixed
                                left-4
                                right-4
                                top-20
                                z-50
                                hidden
                                max-w-sm
                                sm:absolute
                                sm:left-auto
                                sm:right-0
                                sm:top-auto
                                sm:mt-2
                                sm:w-96
                                overflow-hidden
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white
                                shadow-2xl
                                shadow-slate-300/40
                                dark:border-slate-700
                                dark:bg-slate-900
                                dark:shadow-black/30
                            "
                        >

                            <div
                                class="
                                    flex
                                    items-center
                                    justify-between
                                    gap-3
                                    border-b
                                    border-slate-200
                                    px-4
                                    py-3.5
                                    dark:border-slate-800
                                "
                            >
                                <div>
                                    <div
                                        class="
                                            text-sm
                                            font-semibold
                                            text-slate-900
                                            dark:text-white
                                        "
                                    >
                                        Vertragshinweise
                                    </div>

                                    <div
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-slate-500
                                            dark:text-slate-400
                                        "
                                    >
                                        Nur aktuelle und relevante Hinweise
                                    </div>
                                </div>

                                <?php if (
                                    $contractNotificationCount > 0
                                ): ?>
                                    <span
                                        class="
                                            rounded-full
                                            bg-slate-100
                                            px-2.5
                                            py-1
                                            text-xs
                                            font-semibold
                                            text-slate-600
                                            dark:bg-slate-800
                                            dark:text-slate-300
                                        "
                                    >
                                        <?= $contractNotificationCount ?>
                                    </span>
                                <?php endif; ?>
                            </div>


                            <?php if (
                                $contractNotificationCount === 0
                            ): ?>

                                <div
                                    class="
                                        px-5
                                        py-10
                                        text-center
                                    "
                                >
                                    <div
                                        class="
                                            text-sm
                                            font-semibold
                                            text-slate-700
                                            dark:text-slate-200
                                        "
                                    >
                                        Keine aktuellen Hinweise
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            text-xs
                                            text-slate-500
                                            dark:text-slate-400
                                        "
                                    >
                                        Laufzeiten und Verlängerungen sind aktuell unauffällig.
                                    </div>
                                </div>

                            <?php else: ?>

                                <div
                                    class="
                                        max-h-[65vh]
                                        divide-y
                                        divide-slate-200
                                        overflow-y-auto
                                        dark:divide-slate-800
                                    "
                                >

                                    <?php foreach (
                                        $contractNotifications
                                        as $notification
                                    ): ?>

                                        <a
                                            href="/contracts/<?= (int) $notification['contract_id'] ?>"
                                            class="
                                                block
                                                px-4
                                                py-4
                                                transition
                                                hover:bg-slate-50
                                                dark:hover:bg-slate-800/60
                                            "
                                        >
                                            <div
                                                class="
                                                    flex
                                                    items-start
                                                    gap-3
                                                "
                                            >
                                                <div
                                                    class="
                                                        mt-1
                                                        h-2.5
                                                        w-2.5
                                                        shrink-0
                                                        rounded-full
                                                        <?= $notification['severity'] === 'danger'
                                                            ? 'bg-red-500'
                                                            : (
                                                                $notification['severity'] === 'warning'
                                                                    ? 'bg-amber-500'
                                                                    : 'bg-blue-500'
                                                            ) ?>
                                                    "
                                                ></div>

                                                <div class="min-w-0">
                                                    <div
                                                        class="
                                                            truncate
                                                            text-sm
                                                            font-semibold
                                                            text-slate-900
                                                            dark:text-white
                                                        "
                                                    >
                                                        <?= e(
                                                            $notification[
                                                                'title'
                                                            ]
                                                        ) ?>
                                                    </div>

                                                    <div
                                                        class="
                                                            mt-1
                                                            text-xs
                                                            leading-5
                                                            text-slate-600
                                                            dark:text-slate-300
                                                        "
                                                    >
                                                        <?= e(
                                                            $notification[
                                                                'message'
                                                            ]
                                                        ) ?>
                                                    </div>

                                                    <div
                                                        class="
                                                            mt-1.5
                                                            text-[11px]
                                                            text-slate-400
                                                        "
                                                    >
                                                        <?= e(
                                                            $notification[
                                                                'holder'
                                                            ]
                                                        ) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endif; ?>


                <div class="relative shrink-0">

                <button
                    type="button"
                    data-user-menu-button
                    aria-haspopup="true"
                    aria-expanded="false"
                    class="
                        flex
                        items-center
                        gap-3
                        rounded-xl
                        border
                        border-slate-200
                        bg-white
                        px-3
                        py-2
                        text-left
                        transition
                        hover:bg-slate-50
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:hover:bg-slate-700
                    "
                >
                    <div
                        class="
                            flex
                            h-9
                            w-9
                            items-center
                            justify-center
                            rounded-full
                            bg-blue-600
                            text-sm
                            font-bold
                            text-white
                        "
                    >
                        <?= e(
                            strtoupper(
                                substr(
                                    $user['display_name'],
                                    0,
                                    1
                                )
                            )
                        ) ?>
                    </div>

                    <div
                        class="
                            hidden
                            min-w-0
                            sm:block
                        "
                    >
                        <div
                            class="
                                max-w-40
                                truncate
                                text-sm
                                font-semibold
                                text-slate-900
                                dark:text-white
                            "
                        >
                            <?= e($user['display_name']) ?>
                        </div>

                        <div
                            class="
                                max-w-40
                                truncate
                                text-xs
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            <?= e($user['username']) ?>
                        </div>
                    </div>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        class="
                            h-4
                            w-4
                            text-slate-500
                            dark:text-slate-400
                        "
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m7 10 5 5 5-5"
                        />
                    </svg>
                </button>

                <div
                    data-user-menu
                    class="
                        absolute
                        right-0
                        mt-2
                        hidden
                        w-64
                        overflow-hidden
                        rounded-2xl
                        border
                        border-slate-200
                        bg-white
                        shadow-xl
                        shadow-slate-300/40
                        dark:border-slate-700
                        dark:bg-slate-900
                        dark:shadow-black/30
                    "
                >
                    <div
                        class="
                            border-b
                            border-slate-200
                            px-4
                            py-3
                            dark:border-slate-800
                        "
                    >
                        <div
                            class="
                                text-sm
                                font-semibold
                                text-slate-900
                                dark:text-white
                            "
                        >
                            <?= e($user['display_name']) ?>
                        </div>

                        <div
                            class="
                                mt-0.5
                                text-xs
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            Angemeldet als
                            <?= e($user['username']) ?>
                        </div>
                    </div>

                    <div class="p-2">

                        <a
                            href="/dashboard"
                            class="
                                block
                                rounded-lg
                                px-3
                                py-2.5
                                text-sm
                                font-medium
                                text-slate-700
                                transition
                                hover:bg-slate-100
                                dark:text-slate-200
                                dark:hover:bg-slate-800
                            "
                        >
                            Übersicht
                        </a>

                        <?php if (
                            has_permission(
                                'contracts.view'
                            )
                        ): ?>

                            <a
                                href="/contracts"
                                class="
                                    block
                                    rounded-lg
                                    px-3
                                    py-2.5
                                    text-sm
                                    font-medium
                                    text-slate-700
                                    transition
                                    hover:bg-slate-100
                                    dark:text-slate-200
                                    dark:hover:bg-slate-800
                                "
                            >
                                Verträge
                            </a>

                        <?php endif; ?>

                        <?php if (
                            has_permission(
                                'contracts.create'
                            )
                        ): ?>

                            <a
                                href="/contracts/create"
                                class="
                                    block
                                    rounded-lg
                                    px-3
                                    py-2.5
                                    text-sm
                                    font-medium
                                    text-slate-700
                                    transition
                                    hover:bg-slate-100
                                    dark:text-slate-200
                                    dark:hover:bg-slate-800
                                "
                            >
                                Vertrag anlegen
                            </a>

                        <?php endif; ?>


                        <?php if (
                            has_permission(
                                'contracts.view'
                            )
                        ): ?>

                            <a
                                href="/deadlines"
                                class="
                                    block
                                    rounded-lg
                                    px-3
                                    py-2.5
                                    text-sm
                                    font-medium
                                    text-slate-700
                                    transition
                                    hover:bg-slate-100
                                    dark:text-slate-200
                                    dark:hover:bg-slate-800
                                "
                            >
                                Fristen-Cockpit
                            </a>

                            <a
                                href="/reports/payment-planner"
                                class="
                                    block
                                    rounded-lg
                                    px-3
                                    py-2.5
                                    text-sm
                                    font-medium
                                    text-slate-700
                                    transition
                                    hover:bg-slate-100
                                    dark:text-slate-200
                                    dark:hover:bg-slate-800
                                "
                            >
                                Ausgabenplanung
                            </a>

                            <a
                                href="/reports/cost-development"
                                class="
                                    block
                                    rounded-lg
                                    px-3
                                    py-2.5
                                    text-sm
                                    font-medium
                                    text-slate-700
                                    transition
                                    hover:bg-slate-100
                                    dark:text-slate-200
                                    dark:hover:bg-slate-800
                                "
                            >
                                Kostenentwicklung
                            </a>

                            <a
                                href="/reports/financial-overview"
                                class="
                                    block
                                    rounded-lg
                                    px-3
                                    py-2.5
                                    text-sm
                                    font-medium
                                    text-slate-700
                                    transition
                                    hover:bg-slate-100
                                    dark:text-slate-200
                                    dark:hover:bg-slate-800
                                "
                            >
                                Finanzübersicht
                            </a>

                        <?php endif; ?>


                        <?php if (
                            can_access_admin()
                        ): ?>

                            <a
                                href="/admin"
                                class="
                                    block
                                    rounded-lg
                                    px-3
                                    py-2.5
                                    text-sm
                                    font-medium
                                    text-slate-700
                                    transition
                                    hover:bg-slate-100
                                    dark:text-slate-200
                                    dark:hover:bg-slate-800
                                "
                            >
                                Administration
                            </a>

                        <?php endif; ?>

                        <a
                            href="/password/change"
                            class="
                                block
                                rounded-lg
                                px-3
                                py-2.5
                                text-sm
                                font-medium
                                text-slate-700
                                transition
                                hover:bg-slate-100
                                dark:text-slate-200
                                dark:hover:bg-slate-800
                            "
                        >
                            Passwort ändern
                        </a>

                    </div>

                    <div
                        class="
                            border-t
                            border-slate-200
                            p-2
                            dark:border-slate-800
                        "
                    >
                        <form
                            method="post"
                            action="/logout"
                        >
                            <?= csrf_field() ?>

                            <button
                                type="submit"
                                class="
                                    block
                                    w-full
                                    rounded-lg
                                    px-3
                                    py-2.5
                                    text-left
                                    text-sm
                                    font-semibold
                                    text-red-600
                                    transition
                                    hover:bg-red-50
                                    dark:text-red-400
                                    dark:hover:bg-red-950/60
                                "
                            >
                                Abmelden
                            </button>
                        </form>
                    </div>

                </div>

            </div>

            </div>
        </div>
    </header>

<?php endif; ?>

<main>
    <?= $content ?>
</main>



<?php if (!empty($toastMessages)): ?>

    <div
        data-toast-container
        class="
            pointer-events-none
            fixed
            inset-x-4
            bottom-4
            z-[140]
            flex
            flex-col
            items-stretch
            gap-3
            sm:inset-x-auto
            sm:right-5
            sm:bottom-5
            sm:w-full
            sm:max-w-sm
        "
        aria-live="polite"
        aria-atomic="false"
    >

        <?php foreach (
            $toastMessages
            as $toast
        ): ?>

            <?php
            $toastType =
                $toast['type']
                ?? 'success';

            $toastIsWarning =
                $toastType === 'warning';
            ?>

            <div
                data-toast
                data-toast-duration="3800"
                class="
                    pointer-events-auto
                    flex
                    translate-y-2
                    items-start
                    gap-3
                    rounded-2xl
                    border
                    bg-white
                    px-4
                    py-3.5
                    opacity-0
                    shadow-2xl
                    transition
                    duration-300
                    ease-out
                    <?= $toastIsWarning
                        ? 'border-amber-200 dark:border-amber-900'
                        : 'border-emerald-200 dark:border-emerald-900' ?>
                    dark:bg-slate-900
                "
                role="status"
            >

                <div
                    class="
                        mt-0.5
                        flex
                        h-8
                        w-8
                        shrink-0
                        items-center
                        justify-center
                        rounded-full
                        <?= $toastIsWarning
                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' ?>
                    "
                >

                    <?php if (
                        $toastIsWarning
                    ): ?>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 0 0 1.74 3h14.92A2 2 0 0 0 21.2 17L13.7 3.7a2 2 0 0 0-3.4 0Z"
                            />
                        </svg>

                    <?php else: ?>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>

                    <?php endif; ?>

                </div>


                <div class="min-w-0 flex-1">

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-slate-900
                            dark:text-white
                        "
                    >
                        <?= $toastIsWarning
                            ? 'Hinweis'
                            : 'Gespeichert' ?>
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-sm
                            leading-5
                            text-slate-600
                            dark:text-slate-300
                        "
                    >
                        <?= e(
                            $toast['message']
                            ?? ''
                        ) ?>
                    </div>

                </div>


                <button
                    type="button"
                    data-toast-close
                    aria-label="Meldung schließen"
                    class="
                        -mr-1
                        -mt-1
                        rounded-lg
                        p-1.5
                        text-slate-400
                        transition
                        hover:bg-slate-100
                        hover:text-slate-700
                        dark:hover:bg-slate-800
                        dark:hover:text-white
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-4 w-4"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>

            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>


<div
    data-confirm-modal
    class="
        fixed
        inset-0
        z-[100]
        hidden
        items-center
        justify-center
        bg-slate-950/70
        p-4
        backdrop-blur-sm
    "
    aria-hidden="true"
>
    <div
        data-confirm-dialog
        role="dialog"
        aria-modal="true"
        aria-labelledby="app-confirm-title"
        class="
            w-full
            max-w-lg
            rounded-2xl
            border
            border-slate-200
            bg-white
            p-5
            shadow-2xl
            sm:p-6
            dark:border-slate-700
            dark:bg-slate-900
        "
    >
        <div
            class="
                flex
                items-start
                justify-between
                gap-4
            "
        >
            <div>
                <div
                    data-confirm-eyebrow
                    class="
                        text-xs
                        font-semibold
                        uppercase
                        tracking-widest
                        text-blue-600
                        dark:text-blue-400
                    "
                >
                    Bestätigung
                </div>

                <h2
                    id="app-confirm-title"
                    data-confirm-title-output
                    class="
                        mt-2
                        text-xl
                        font-bold
                        text-slate-900
                        dark:text-white
                    "
                >
                    Aktion bestätigen
                </h2>
            </div>

            <button
                type="button"
                data-confirm-close
                aria-label="Popup schließen"
                class="
                    rounded-lg
                    p-2
                    text-slate-400
                    transition
                    hover:bg-slate-100
                    hover:text-slate-700
                    dark:hover:bg-slate-800
                    dark:hover:text-white
                "
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        d="M6 6l12 12M18 6 6 18"
                    />
                </svg>
            </button>
        </div>

        <p
            data-confirm-message-output
            class="
                mt-4
                text-sm
                leading-6
                text-slate-600
                dark:text-slate-300
            "
        ></p>

        <div
            class="
                mt-6
                flex
                flex-col-reverse
                gap-3
                sm:flex-row
                sm:justify-end
            "
        >
            <button
                type="button"
                data-confirm-cancel
                class="
                    rounded-xl
                    border
                    border-slate-300
                    px-4
                    py-2.5
                    text-sm
                    font-semibold
                    text-slate-700
                    transition
                    hover:bg-slate-50
                    dark:border-slate-700
                    dark:text-slate-200
                    dark:hover:bg-slate-800
                "
            >
                Abbrechen
            </button>

            <button
                type="button"
                data-confirm-accept
                class="
                    rounded-xl
                    bg-blue-600
                    px-4
                    py-2.5
                    text-sm
                    font-semibold
                    text-white
                    transition
                    hover:bg-blue-700
                    dark:hover:bg-blue-500
                "
            >
                Bestätigen
            </button>
        </div>
    </div>
</div>

</body>
</html>
