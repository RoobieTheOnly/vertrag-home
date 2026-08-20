<section class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">

    <div class="mb-8">

        <div
            class="
                text-sm
                font-semibold
                uppercase
                tracking-widest
                text-blue-600
                dark:text-blue-400
            "
        >
            Administration
        </div>

        <h1
            class="
                mt-2
                text-3xl
                font-bold
                tracking-tight
                text-slate-900
                dark:text-white
            "
        >
            Systemeinstellungen
        </h1>

        <p
            class="
                mt-2
                text-slate-500
                dark:text-slate-400
            "
        >
            Benutzer, Vertragsarten, Vertragsinhaber und Dokumentarten zentral verwalten.
        </p>

    </div>

<div
        class="
            grid
            gap-5
            md:grid-cols-2
            xl:grid-cols-4
        "
    >

        <?php if (
            has_permission(
                'users.manage'
            )
        ): ?>

            <a
                href="/admin/users"
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-6
                    shadow-sm
                    transition
                    hover:border-blue-300
                    hover:shadow-md
                    dark:border-slate-800
                    dark:bg-slate-900
                    dark:hover:border-blue-700
                "
            >

                <div
                    class="
                        text-lg
                        font-bold
                        text-slate-900
                        dark:text-white
                    "
                >
                    Benutzer
                </div>

                <p
                    class="
                        mt-2
                        text-sm
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Benutzer anlegen, bearbeiten, deaktivieren und Passwörter zurücksetzen.
                </p>

                <div
                    class="
                        mt-6
                        flex
                        items-end
                        justify-between
                        gap-4
                    "
                >

                    <div>

                        <div
                            class="
                                text-3xl
                                font-bold
                                text-slate-900
                                dark:text-white
                            "
                        >
                            <?= (int) ($userStats['active_count'] ?? 0) ?>
                        </div>

                        <div
                            class="
                                mt-1
                                text-xs
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            aktiv von
                            <?= (int) ($userStats['total_count'] ?? 0) ?>
                        </div>

                    </div>

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-blue-600
                            dark:text-blue-400
                        "
                    >
                        Verwalten →
                    </div>

                </div>

            </a>

        <?php endif; ?>


        <?php if (
            has_permission(
                'contract_types.manage'
            )
        ): ?>

            <a
                href="/admin/contract-types"
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-6
                    shadow-sm
                    transition
                    hover:border-blue-300
                    hover:shadow-md
                    dark:border-slate-800
                    dark:bg-slate-900
                    dark:hover:border-blue-700
                "
            >

                <div
                    class="
                        text-lg
                        font-bold
                        text-slate-900
                        dark:text-white
                    "
                >
                    Vertragsarten
                </div>

                <p
                    class="
                        mt-2
                        text-sm
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Vertragsarten ergänzen, umbenennen, sortieren oder deaktivieren.
                </p>

                <div
                    class="
                        mt-6
                        flex
                        items-end
                        justify-between
                        gap-4
                    "
                >

                    <div>

                        <div
                            class="
                                text-3xl
                                font-bold
                                text-slate-900
                                dark:text-white
                            "
                        >
                            <?= (int) ($typeStats['active_count'] ?? 0) ?>
                        </div>

                        <div
                            class="
                                mt-1
                                text-xs
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            aktiv von
                            <?= (int) ($typeStats['total_count'] ?? 0) ?>
                        </div>

                    </div>

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-blue-600
                            dark:text-blue-400
                        "
                    >
                        Verwalten →
                    </div>

                </div>

            </a>

        <?php endif; ?>


        <?php if (
            has_permission(
                'settings.manage'
            )
        ): ?>

            <a
                href="/admin/contract-holders"
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-6
                    shadow-sm
                    transition
                    hover:border-blue-300
                    hover:shadow-md
                    dark:border-slate-800
                    dark:bg-slate-900
                    dark:hover:border-blue-700
                "
            >

                <div
                    class="
                        text-lg
                        font-bold
                        text-slate-900
                        dark:text-white
                    "
                >
                    Vertragsinhaber
                </div>

                <p
                    class="
                        mt-2
                        text-sm
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Personen oder gemeinsame Vertragsinhaber flexibel verwalten.
                </p>

                <div
                    class="
                        mt-6
                        flex
                        items-end
                        justify-between
                        gap-4
                    "
                >

                    <div>

                        <div
                            class="
                                text-3xl
                                font-bold
                                text-slate-900
                                dark:text-white
                            "
                        >
                            <?= (int) ($holderStats['active_count'] ?? 0) ?>
                        </div>

                        <div
                            class="
                                mt-1
                                text-xs
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            aktiv von
                            <?= (int) ($holderStats['total_count'] ?? 0) ?>
                        </div>

                    </div>

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-blue-600
                            dark:text-blue-400
                        "
                    >
                        Verwalten →
                    </div>

                </div>

            </a>

        <?php endif; ?>


        <?php if (
            has_permission(
                'settings.manage'
            )
        ): ?>

            <?php
            $documentTypeStats =
                get_document_types(false);

            $activeDocumentTypes =
                count(
                    array_filter(
                        $documentTypeStats,
                        static fn (
                            array $type
                        ): bool =>
                            (int) $type[
                                'is_active'
                            ] === 1
                    )
                );
            ?>

            <a
                href="/admin/document-types"
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-6
                    shadow-sm
                    transition
                    hover:border-blue-300
                    hover:shadow-md
                    dark:border-slate-800
                    dark:bg-slate-900
                    dark:hover:border-blue-700
                "
            >

                <div class="text-lg font-bold text-slate-900 dark:text-white">
                    Dokumentarten
                </div>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Dokumentkategorien für Vertrag, Rechnung, Kündigung,
                    Bestätigung oder eigene Arten verwalten.
                </p>

                <div class="mt-6 flex items-end justify-between gap-4">

                    <div>
                        <div class="text-3xl font-bold text-slate-900 dark:text-white">
                            <?= $activeDocumentTypes ?>
                        </div>

                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            aktiv von
                            <?= count($documentTypeStats) ?>
                        </div>
                    </div>

                    <div class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                        Verwalten →
                    </div>

                </div>

            </a>

        <?php endif; ?>


        <?php if (
            has_permission(
                'audit.view'
            )
        ): ?>

            <a
                href="/admin/audit-log"
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-6
                    shadow-sm
                    transition
                    hover:border-blue-300
                    hover:shadow-md
                    dark:border-slate-800
                    dark:bg-slate-900
                    dark:hover:border-blue-700
                "
            >

                <div
                    class="
                        text-lg
                        font-bold
                        text-slate-900
                        dark:text-white
                    "
                >
                    Auditlog
                </div>

                <p
                    class="
                        mt-2
                        text-sm
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Nachvollziehen, welcher Benutzer welche Aktion wann ausgeführt hat.
                </p>

                <div
                    class="
                        mt-6
                        text-sm
                        font-semibold
                        text-blue-600
                        dark:text-blue-400
                    "
                >
                    Auditlog öffnen →
                </div>

            </a>

        <?php endif; ?>

    </div>

</section>
