<section class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">

    <div
        class="
            mb-8
            flex
            flex-col
            justify-between
            gap-5
            sm:flex-row
            sm:items-end
        "
    >

        <div>

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

            <h1
                class="
                    mt-4
                    text-3xl
                    font-bold
                    tracking-tight
                    text-slate-900
                    dark:text-white
                "
            >
                Benutzer
            </h1>

            <p
                class="
                    mt-2
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Zugänge, Rollen und Benutzerstatus verwalten.
            </p>

        </div>


        <a
            href="/admin/users/create"
            class="
                inline-flex
                items-center
                justify-center
                rounded-xl
                bg-blue-600
                px-4
                py-3
                text-sm
                font-semibold
                text-white
                hover:bg-blue-700
                dark:hover:bg-blue-500
            "
        >
            + Benutzer anlegen
        </a>

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

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead
                    class="
                        bg-slate-50
                        text-xs
                        uppercase
                        tracking-wide
                        text-slate-500
                        dark:bg-slate-800/70
                        dark:text-slate-400
                    "
                >
                    <tr>
                        <th class="px-6 py-4">Benutzer</th>
                        <th class="px-6 py-4">E-Mail</th>
                        <th class="px-6 py-4">Rolle</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Letzte Anmeldung</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>


                <tbody
                    class="
                        divide-y
                        divide-slate-200
                        dark:divide-slate-800
                    "
                >

                    <?php foreach ($users as $entry): ?>

                        <tr
                            class="
                                hover:bg-slate-50
                                dark:hover:bg-slate-800/60
                            "
                        >

                            <td class="px-6 py-4">

                                <div
                                    class="
                                        font-semibold
                                        text-slate-900
                                        dark:text-white
                                    "
                                >
                                    <?= e($entry['display_name']) ?>
                                </div>

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

                            </td>


                            <td class="px-6 py-4">
                                <?= e(
                                    $entry['email']
                                    ?: '–'
                                ) ?>
                            </td>


                            <td class="px-6 py-4">
                                <?= e(
                                    $entry['role_labels']
                                    ?: '–'
                                ) ?>
                            </td>


                            <td class="px-6 py-4">

                                <?php if (
                                    (int) $entry['is_active']
                                    === 1
                                ): ?>

                                    <span
                                        class="
                                            rounded-full
                                            bg-emerald-100
                                            px-2.5
                                            py-1
                                            text-xs
                                            font-semibold
                                            text-emerald-800
                                            dark:bg-emerald-950
                                            dark:text-emerald-300
                                        "
                                    >
                                        Aktiv
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="
                                            rounded-full
                                            bg-slate-100
                                            px-2.5
                                            py-1
                                            text-xs
                                            font-semibold
                                            text-slate-700
                                            dark:bg-slate-800
                                            dark:text-slate-300
                                        "
                                    >
                                        Deaktiviert
                                    </span>

                                <?php endif; ?>


                                <?php if (
                                    (int) $entry[
                                        'must_change_password'
                                    ] === 1
                                ): ?>

                                    <div
                                        class="
                                            mt-2
                                            text-xs
                                            font-medium
                                            text-amber-700
                                            dark:text-amber-300
                                        "
                                    >
                                        Passwortwechsel erforderlich
                                    </div>

                                <?php endif; ?>

                            </td>


                            <td class="px-6 py-4">
                                <?= e(
                                    contract_format_date(
                                        $entry[
                                            'last_login_at'
                                        ]
                                    )
                                ) ?>
                            </td>


                            <td
                                class="
                                    px-6
                                    py-4
                                    text-right
                                "
                            >

                                <a
                                    href="/admin/users/<?= (int) $entry['id'] ?>/edit"
                                    class="
                                        font-semibold
                                        text-blue-600
                                        hover:text-blue-700
                                        dark:text-blue-400
                                        dark:hover:text-blue-300
                                    "
                                >
                                    Bearbeiten
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>
