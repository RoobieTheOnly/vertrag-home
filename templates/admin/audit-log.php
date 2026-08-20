<section class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">

    <div class="mb-8">

        <a
            href="/admin"
            class="
                text-sm
                font-medium
                text-blue-600
                hover:text-blue-700
                dark:text-blue-400
                dark:hover:text-blue-300
            "
        >
            ← Administration
        </a>

        <div
            class="
                mt-5
                text-sm
                font-semibold
                uppercase
                tracking-widest
                text-blue-600
                dark:text-blue-400
            "
        >
            Nur Administrator
        </div>

        <h1
            class="
                mt-2
                text-2xl
                font-bold
                tracking-tight
                text-slate-900
                sm:text-3xl
                dark:text-white
            "
        >
            Auditlog
        </h1>

        <p
            class="
                mt-2
                max-w-3xl
                text-sm
                text-slate-500
                sm:text-base
                dark:text-slate-400
            "
        >
            Nachvollziehbare Übersicht darüber, welcher Benutzer welche
            Aktion zu welchem Zeitpunkt ausgeführt hat. Ein Klick auf einen
            Eintrag zeigt die vorhandenen Detailinformationen.
        </p>

    </div>


    <div
        data-audit-filters
        class="
            mb-5
            grid
            gap-4
            rounded-2xl
            border
            border-slate-200
            bg-white
            p-4
            md:grid-cols-2
            xl:grid-cols-[1.6fr_1fr_1fr_0.9fr_0.9fr_auto]
            xl:items-end
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <div>

            <label
                for="audit-search"
                class="
                    mb-2
                    block
                    text-sm
                    font-medium
                    text-slate-700
                    dark:text-slate-200
                "
            >
                Suche
            </label>

            <input
                id="audit-search"
                type="search"
                data-audit-search
                placeholder="Benutzer, Aktion, Beschreibung, IP …"
                autocomplete="off"
                class="
                    h-11
                    w-full
                    rounded-xl
                    border
                    border-slate-300
                    bg-white
                    px-4
                    text-slate-900
                    outline-none
                    focus:border-blue-500
                    focus:ring-4
                    focus:ring-blue-100
                    dark:border-slate-700
                    dark:bg-slate-800
                    dark:text-white
                    dark:placeholder:text-slate-500
                    dark:focus:ring-blue-950
                "
            >

        </div>


        <div>

            <label
                for="audit-user"
                class="
                    mb-2
                    block
                    text-sm
                    font-medium
                    text-slate-700
                    dark:text-slate-200
                "
            >
                Benutzer
            </label>

            <select
                id="audit-user"
                data-audit-user
                class="
                    h-11
                    w-full
                    rounded-xl
                    border
                    border-slate-300
                    bg-white
                    px-4
                    text-slate-900
                    dark:border-slate-700
                    dark:bg-slate-800
                    dark:text-white
                "
            >

                <option value="">
                    Alle Benutzer
                </option>

                <option value="system">
                    System / unbekannt
                </option>

                <?php foreach ($auditUsers as $auditUser): ?>

                    <option
                        value="<?= (int) $auditUser['id'] ?>"
                    >
                        <?= e($auditUser['display_name']) ?>
                        (<?= e($auditUser['username']) ?>)
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div>

            <label
                for="audit-action"
                class="
                    mb-2
                    block
                    text-sm
                    font-medium
                    text-slate-700
                    dark:text-slate-200
                "
            >
                Aktion
            </label>

            <select
                id="audit-action"
                data-audit-action
                class="
                    h-11
                    w-full
                    rounded-xl
                    border
                    border-slate-300
                    bg-white
                    px-4
                    text-slate-900
                    dark:border-slate-700
                    dark:bg-slate-800
                    dark:text-white
                "
            >

                <option value="">
                    Alle Aktionen
                </option>

                <?php foreach ($auditActions as $auditAction): ?>

                    <option
                        value="<?= e($auditAction['action']) ?>"
                    >
                        <?= e(
                            audit_action_label(
                                $auditAction['action']
                            )
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div>

            <label
                for="audit-date-from"
                class="
                    mb-2
                    block
                    text-sm
                    font-medium
                    text-slate-700
                    dark:text-slate-200
                "
            >
                Von
            </label>

            <input
                id="audit-date-from"
                type="date"
                data-audit-date-from
                class="
                    h-11
                    w-full
                    rounded-xl
                    border
                    border-slate-300
                    bg-white
                    px-4
                    text-slate-900
                    dark:border-slate-700
                    dark:bg-slate-800
                    dark:text-white
                "
            >

        </div>


        <div>

            <label
                for="audit-date-to"
                class="
                    mb-2
                    block
                    text-sm
                    font-medium
                    text-slate-700
                    dark:text-slate-200
                "
            >
                Bis
            </label>

            <input
                id="audit-date-to"
                type="date"
                data-audit-date-to
                class="
                    h-11
                    w-full
                    rounded-xl
                    border
                    border-slate-300
                    bg-white
                    px-4
                    text-slate-900
                    dark:border-slate-700
                    dark:bg-slate-800
                    dark:text-white
                "
            >

        </div>


        <button
            type="button"
            data-audit-reset
            class="
                hidden
                h-11
                self-end
                rounded-xl
                border
                border-slate-300
                px-5
                text-center
                text-sm
                font-semibold
                text-slate-700
                hover:bg-slate-50
                dark:border-slate-700
                dark:text-slate-200
                dark:hover:bg-slate-800
            "
        >
            Zurücksetzen
        </button>

    </div>


    <div
        class="
            mb-3
            flex
            flex-col
            justify-between
            gap-3
            sm:flex-row
            sm:items-center
        "
    >

        <div
            data-audit-count
            class="
                text-sm
                text-slate-500
                dark:text-slate-400
            "
        >
            <?= count($auditEntries) ?>
            Einträge geladen
        </div>


        <form
            method="get"
            action="/admin/audit-log"
            class="
                flex
                items-center
                gap-2
            "
        >

            <label
                for="audit-limit"
                class="
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Letzte
            </label>

            <select
                id="audit-limit"
                name="limit"
                onchange="this.form.submit()"
                class="
                    rounded-lg
                    border
                    border-slate-300
                    bg-white
                    px-3
                    py-2
                    text-sm
                    text-slate-900
                    dark:border-slate-700
                    dark:bg-slate-800
                    dark:text-white
                "
            >
                <?php foreach ([50, 100, 250, 500] as $option): ?>
                    <option
                        value="<?= $option ?>"
                        <?= $auditLimit === $option
                            ? 'selected'
                            : '' ?>
                    >
                        <?= $option ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <span
                class="
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Einträge
            </span>

        </form>

    </div>


    <div
        class="
            overflow-hidden
            rounded-2xl
            border
            border-slate-200
            bg-white
            shadow-sm
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <?php if (empty($auditEntries)): ?>

            <div
                class="
                    px-6
                    py-16
                    text-center
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Es sind noch keine Auditlog-Einträge vorhanden.
            </div>

        <?php else: ?>

            <div
                class="
                    hidden
                    border-b
                    border-slate-200
                    bg-slate-50
                    px-5
                    py-3
                    text-xs
                    font-semibold
                    uppercase
                    tracking-wide
                    text-slate-500
                    lg:grid
                    lg:grid-cols-[1fr_1fr_1.15fr_0.8fr_2fr_0.85fr]
                    lg:gap-4
                    dark:border-slate-800
                    dark:bg-slate-800/70
                    dark:text-slate-400
                "
            >
                <div>Zeitpunkt</div>
                <div>Benutzer</div>
                <div>Aktion</div>
                <div>Objekt</div>
                <div>Beschreibung</div>
                <div>IP-Adresse</div>
            </div>


            <div
                class="
                    max-h-[68vh]
                    divide-y
                    divide-slate-200
                    overflow-y-auto
                    dark:divide-slate-800
                "
            >

                <?php foreach ($auditEntries as $entry): ?>

                    <?php
                    $searchText = implode(
                        ' ',
                        [
                            $entry['display_name'] ?? '',
                            $entry['username'] ?? '',
                            audit_action_label(
                                $entry['action']
                            ),
                            $entry['action'],
                            audit_entity_label(
                                $entry['entity_type']
                            ),
                            (string) (
                                $entry['entity_id']
                                ?? ''
                            ),
                            $entry['description'] ?? '',
                            $entry['ip_address'] ?? '',
                            audit_format_datetime(
                                $entry['created_at']
                            ),
                        ]
                    );

                    $entryDate =
                        substr(
                            (string) $entry['created_at'],
                            0,
                            10
                        );

                    $detailsJson =
                        (string) (
                            $entry['details_json']
                            ?? ''
                        );
                    ?>

                    <div
                        data-audit-row
                        data-user="<?= $entry['user_id'] !== null
                            ? (int) $entry['user_id']
                            : 'system' ?>"
                        data-action="<?= e($entry['action']) ?>"
                        data-date="<?= e($entryDate) ?>"
                        data-search="<?= e($searchText) ?>"
                        data-audit-time="<?= e(
                            audit_format_datetime(
                                $entry['created_at']
                            )
                        ) ?>"
                        data-audit-user-label="<?= e(
                            $entry['user_id'] !== null
                                ? (
                                    $entry['display_name']
                                    ?: $entry['username']
                                    ?: 'Unbekannt'
                                )
                                : 'System / unbekannt'
                        ) ?>"
                        data-audit-username="<?= e(
                            $entry['username']
                            ?? ''
                        ) ?>"
                        data-audit-action-label="<?= e(
                            audit_action_label(
                                $entry['action']
                            )
                        ) ?>"
                        data-audit-action-key="<?= e(
                            $entry['action']
                        ) ?>"
                        data-audit-object="<?= e(
                            audit_entity_label(
                                $entry['entity_type']
                            )
                        ) ?>"
                        data-audit-object-id="<?= e(
                            $entry['entity_id'] !== null
                                ? (string) $entry['entity_id']
                                : ''
                        ) ?>"
                        data-audit-description="<?= e(
                            $entry['description']
                            ?? ''
                        ) ?>"
                        data-audit-ip="<?= e(
                            $entry['ip_address']
                            ?? ''
                        ) ?>"
                        data-audit-details="<?= e(
                            $detailsJson
                        ) ?>"
                        role="button"
                        tabindex="0"
                        class="
                            cursor-pointer
                            px-5
                            py-5
                            outline-none
                            transition
                            hover:bg-slate-50
                            focus:bg-blue-50
                            focus:ring-2
                            focus:ring-inset
                            focus:ring-blue-500
                            lg:grid
                            lg:grid-cols-[1fr_1fr_1.15fr_0.8fr_2fr_0.85fr]
                            lg:items-start
                            lg:gap-4
                            dark:hover:bg-slate-800/60
                            dark:focus:bg-blue-950/40
                        "
                    >

                        <div
                            class="
                                text-sm
                                text-slate-600
                                dark:text-slate-300
                            "
                        >
                            <?= e(
                                audit_format_datetime(
                                    $entry['created_at']
                                )
                            ) ?>
                        </div>


                        <div class="mt-3 lg:mt-0">

                            <div
                                class="
                                    text-xs
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                    lg:hidden
                                "
                            >
                                Benutzer
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-semibold
                                    text-slate-900
                                    lg:mt-0
                                    dark:text-white
                                "
                            >
                                <?= e(
                                    $entry['user_id'] !== null
                                        ? (
                                            $entry['display_name']
                                            ?: $entry['username']
                                            ?: 'Unbekannt'
                                        )
                                        : 'System / unbekannt'
                                ) ?>
                            </div>

                            <?php if (
                                !empty($entry['username'])
                            ): ?>
                                <div
                                    class="
                                        mt-1
                                        text-xs
                                        text-slate-500
                                        dark:text-slate-400
                                    "
                                >
                                    <?= e($entry['username']) ?>
                                </div>
                            <?php endif; ?>

                        </div>


                        <div class="mt-3 lg:mt-0">

                            <div
                                class="
                                    text-xs
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                    lg:hidden
                                "
                            >
                                Aktion
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-semibold
                                    text-slate-900
                                    lg:mt-0
                                    dark:text-white
                                "
                            >
                                <?= e(
                                    audit_action_label(
                                        $entry['action']
                                    )
                                ) ?>
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-mono
                                    text-xs
                                    text-slate-500
                                    dark:text-slate-400
                                "
                            >
                                <?= e($entry['action']) ?>
                            </div>

                        </div>


                        <div class="mt-3 lg:mt-0">

                            <div
                                class="
                                    text-xs
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                    lg:hidden
                                "
                            >
                                Objekt
                            </div>

                            <div class="mt-1 lg:mt-0">
                                <?= e(
                                    audit_entity_label(
                                        $entry['entity_type']
                                    )
                                ) ?>
                            </div>

                            <?php if (
                                $entry['entity_id'] !== null
                            ): ?>
                                <div
                                    class="
                                        mt-1
                                        text-xs
                                        text-slate-500
                                        dark:text-slate-400
                                    "
                                >
                                    ID
                                    <?= (int) $entry['entity_id'] ?>
                                </div>
                            <?php endif; ?>

                        </div>


                        <div class="mt-3 lg:mt-0">

                            <div
                                class="
                                    text-xs
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                    lg:hidden
                                "
                            >
                                Beschreibung
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-slate-700
                                    lg:mt-0
                                    dark:text-slate-200
                                "
                            >
                                <?= e(
                                    $entry['description']
                                    ?: '–'
                                ) ?>
                            </div>

                        </div>


                        <div class="mt-3 lg:mt-0">

                            <div
                                class="
                                    text-xs
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                    lg:hidden
                                "
                            >
                                IP-Adresse
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-mono
                                    text-xs
                                    text-slate-500
                                    lg:mt-0
                                    dark:text-slate-400
                                "
                            >
                                <?= e(
                                    $entry['ip_address']
                                    ?: '–'
                                ) ?>
                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>


                <div
                    data-audit-empty
                    class="
                        hidden
                        px-6
                        py-14
                        text-center
                        text-sm
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Für die gewählten Filter wurden keine Auditlog-Einträge gefunden.
                </div>

            </div>

        <?php endif; ?>

    </div>

</section>


<div
    data-audit-detail-modal
    class="
        fixed
        inset-0
        z-[110]
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
        role="dialog"
        aria-modal="true"
        aria-labelledby="audit-detail-title"
        class="
            max-h-[90vh]
            w-full
            max-w-2xl
            overflow-y-auto
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
                    class="
                        text-xs
                        font-semibold
                        uppercase
                        tracking-widest
                        text-blue-600
                        dark:text-blue-400
                    "
                >
                    Auditlog-Details
                </div>

                <h2
                    id="audit-detail-title"
                    data-audit-detail-action
                    class="
                        mt-2
                        text-xl
                        font-bold
                        text-slate-900
                        dark:text-white
                    "
                >
                    Aktion
                </h2>

            </div>


            <button
                type="button"
                data-audit-detail-close
                aria-label="Popup schließen"
                class="
                    rounded-lg
                    p-2
                    text-slate-400
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
                >
                    <path
                        stroke-linecap="round"
                        d="M6 6l12 12M18 6 6 18"
                    />
                </svg>
            </button>

        </div>


        <div
            class="
                mt-6
                grid
                gap-4
                sm:grid-cols-2
            "
        >

            <div
                class="
                    rounded-xl
                    bg-slate-50
                    p-4
                    dark:bg-slate-800/70
                "
            >
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Zeitpunkt
                </div>
                <div data-audit-detail-time class="mt-1 font-medium"></div>
            </div>

            <div
                class="
                    rounded-xl
                    bg-slate-50
                    p-4
                    dark:bg-slate-800/70
                "
            >
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Benutzer
                </div>
                <div data-audit-detail-user class="mt-1 font-medium"></div>
            </div>

            <div
                class="
                    rounded-xl
                    bg-slate-50
                    p-4
                    dark:bg-slate-800/70
                "
            >
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Objekt
                </div>
                <div data-audit-detail-object class="mt-1 font-medium"></div>
            </div>

            <div
                class="
                    rounded-xl
                    bg-slate-50
                    p-4
                    dark:bg-slate-800/70
                "
            >
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    IP-Adresse
                </div>
                <div data-audit-detail-ip class="mt-1 font-mono text-sm"></div>
            </div>

        </div>


        <div
            class="
                mt-4
                rounded-xl
                bg-slate-50
                p-4
                dark:bg-slate-800/70
            "
        >
            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Beschreibung
            </div>
            <div
                data-audit-detail-description
                class="
                    mt-2
                    text-sm
                    leading-6
                    text-slate-700
                    dark:text-slate-200
                "
            ></div>
        </div>


        <div class="mt-6">

            <h3
                class="
                    text-sm
                    font-semibold
                    uppercase
                    tracking-wide
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Änderungsdetails
            </h3>

            <div
                data-audit-detail-structured
                class="mt-3 space-y-4"
            ></div>

        </div>


        <div
            class="
                mt-6
                flex
                justify-end
            "
        >
            <button
                type="button"
                data-audit-detail-close
                class="
                    rounded-xl
                    bg-slate-900
                    px-4
                    py-2.5
                    text-sm
                    font-semibold
                    text-white
                    hover:bg-slate-700
                    dark:bg-slate-100
                    dark:text-slate-900
                    dark:hover:bg-white
                "
            >
                Schließen
            </button>
        </div>

    </div>
</div>
