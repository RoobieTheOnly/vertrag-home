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
                Vertragsinhaber
            </h1>

            <p
                class="
                    mt-2
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Vertragsinhaber für Kostenübersichten und Vertragszuordnungen verwalten.
            </p>

        </div>


        <a
            href="/admin/contract-holders/create"
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
            + Vertragsinhaber anlegen
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
                        <th class="px-6 py-4">Sortierung</th>
                        <th class="px-6 py-4">Vertragsinhaber</th>
                        <th class="px-6 py-4">Status</th>
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

                    <?php foreach ($contractHolders as $holder): ?>

                        <tr
                            class="
                                hover:bg-slate-50
                                dark:hover:bg-slate-800/60
                            "
                        >

                            <td class="px-6 py-4">
                                <?= (int) $holder['sort_order'] ?>
                            </td>

                            <td
                                class="
                                    px-6
                                    py-4
                                    font-semibold
                                    text-slate-900
                                    dark:text-white
                                "
                            >
                                <?= e($holder['name']) ?>
                            </td>

                            <td class="px-6 py-4">

                                <?php if (
                                    (int) $holder['is_active']
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
                                        Inaktiv
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td
                                class="
                                    px-6
                                    py-4
                                    text-right
                                "
                            >
                                <a
                                    href="/admin/contract-holders/<?= (int) $holder['id'] ?>/edit"
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
