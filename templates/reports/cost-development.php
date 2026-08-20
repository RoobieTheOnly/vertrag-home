<?php

declare(strict_types=1);

$changePercent =
    abs(
        (float) $costDevelopment[
            'previous_monthly'
        ]
    ) > 0.00001
        ? (
            (
                (float) $costDevelopment[
                    'monthly_change'
                ]
                / (float) $costDevelopment[
                    'previous_monthly'
                ]
            ) * 100
        )
        : null;

?>

<section class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">

    <div
        class="
            mb-7
            flex
            flex-col
            gap-5
            lg:flex-row
            lg:items-end
            lg:justify-between
        "
    >

        <div>
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400">
                Finanzen
            </div>

            <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">
                Kostenentwicklung & Einsparpotenzial
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500 sm:text-base dark:text-slate-400">
                Entwicklung der laufenden Vertragskosten im Vergleich zu vor einem Jahr
                und rechnerisch vermeidbare Zahlungen bei Kündigung zum nächsten möglichen Termin.
            </p>
        </div>

        <form
            method="get"
            action="/reports/cost-development"
            class="w-full lg:w-72"
        >
            <label
                for="cost-holder"
                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
            >
                Vertragsinhaber
            </label>

            <select
                id="cost-holder"
                name="holder"
                onchange="this.form.submit()"
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
                    Alle Vertragsinhaber
                </option>

                <?php foreach ($contractHolders as $holder): ?>
                    <option
                        value="<?= (int) $holder['id'] ?>"
                        <?= $selectedHolderId
                            === (int) $holder['id']
                                ? 'selected'
                                : '' ?>
                    >
                        <?= e($holder['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

    </div>


    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Aktuell monatlich
            </div>
            <div class="mt-2 text-2xl font-bold">
                <?= e(
                    contract_format_money(
                        (float) $costDevelopment[
                            'current_monthly'
                        ]
                    )
                ) ?>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Aktuell jährlich
            </div>
            <div class="mt-2 text-2xl font-bold">
                <?= e(
                    contract_format_money(
                        (float) $costDevelopment[
                            'current_annual'
                        ]
                    )
                ) ?>
            </div>
        </div>

        <div
            class="
                rounded-2xl
                border
                <?= (float) $costDevelopment['annual_change'] > 0
                    ? 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/30'
                    : 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/30' ?>
                p-4
            "
        >
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Veränderung / Jahr
            </div>
            <div class="mt-2 text-2xl font-bold">
                <?= (float) $costDevelopment[
                    'annual_change'
                ] >= 0 ? '+' : '' ?>
                <?= e(
                    contract_format_money(
                        (float) $costDevelopment[
                            'annual_change'
                        ]
                    )
                ) ?>
            </div>

            <?php if ($changePercent !== null): ?>
                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    <?= $changePercent >= 0 ? '+' : '' ?>
                    <?= e(
                        number_format(
                            $changePercent,
                            1,
                            ',',
                            '.'
                        )
                    ) ?>
                    %
                </div>
            <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
            <div class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-400">
                Einsparpotenzial 12 Monate
            </div>
            <div class="mt-2 text-2xl font-bold">
                <?= e(
                    contract_format_money(
                        (float) $costDevelopment[
                            'saving_potential_12_months'
                        ]
                    )
                ) ?>
            </div>
        </div>

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

        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <h2 class="font-semibold">
                Entwicklung je Vertrag
            </h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Vergleich mit dem Preisstand am
                <?= e(
                    contract_format_date(
                        $costDevelopment[
                            'comparison_date'
                        ]
                    )
                ) ?>.
            </p>
        </div>

        <?php if (empty($costDevelopment['rows'])): ?>

            <div class="px-6 py-14 text-center text-sm text-slate-500 dark:text-slate-400">
                Keine aktuell laufenden wiederkehrenden Verträge vorhanden.
            </div>

        <?php else: ?>

            <div class="hidden border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 lg:grid lg:grid-cols-[1.4fr_0.9fr_0.9fr_0.9fr_0.9fr] lg:gap-4 dark:border-slate-800 dark:bg-slate-800/70 dark:text-slate-400">
                <div>Vertrag</div>
                <div class="text-right">Vorjahr / Monat</div>
                <div class="text-right">Aktuell / Monat</div>
                <div class="text-right">Mehr-/Minderkosten Jahr</div>
                <div class="text-right">Einsparpotenzial</div>
            </div>

            <div class="divide-y divide-slate-200 dark:divide-slate-800">

                <?php foreach (
                    $costDevelopment['rows']
                    as $row
                ): ?>

                    <?php $contract = $row['contract']; ?>

                    <a
                        href="/contracts/<?= (int) $contract['id'] ?>"
                        class="
                            block
                            px-5
                            py-5
                            transition
                            hover:bg-slate-50
                            lg:grid
                            lg:grid-cols-[1.4fr_0.9fr_0.9fr_0.9fr_0.9fr]
                            lg:items-center
                            lg:gap-4
                            dark:hover:bg-slate-800/60
                        "
                    >

                        <div class="min-w-0">
                            <div class="truncate font-semibold">
                                <?= e($contract['title']) ?>
                            </div>
                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                <?= e($contract['contract_holder_name']) ?>
                                ·
                                <?= e($contract['provider']) ?>

                                <?php if (
                                    !empty(
                                        $row[
                                            'pause_state'
                                        ][
                                            'is_paused'
                                        ]
                                    )
                                ): ?>
                                    · Pausiert bis
                                    <?= e(
                                        contract_format_date(
                                            $row[
                                                'pause_state'
                                            ][
                                                'current'
                                            ][
                                                'pause_to'
                                            ] ?? null
                                        )
                                    ) ?>
                                <?php elseif (
                                    $contract['status']
                                    === 'cancelled'
                                    && !empty(
                                        $contract[
                                            'cancellation_effective_date'
                                        ]
                                    )
                                ): ?>
                                    · Gekündigt zum
                                    <?= e(
                                        contract_format_date(
                                            $contract[
                                                'cancellation_effective_date'
                                            ]
                                        )
                                    ) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4 text-sm lg:mt-0 lg:text-right">
                            <span class="text-slate-400 lg:hidden">Vorjahr: </span>
                            <?= e(
                                contract_format_money(
                                    (float) $row[
                                        'previous_monthly'
                                    ]
                                )
                            ) ?>
                        </div>

                        <div class="mt-2 font-semibold lg:mt-0 lg:text-right">
                            <span class="text-slate-400 lg:hidden">Aktuell: </span>
                            <?= e(
                                contract_format_money(
                                    (float) $row[
                                        'current_monthly'
                                    ]
                                )
                            ) ?>
                        </div>

                        <div
                            class="
                                mt-2
                                font-semibold
                                lg:mt-0
                                lg:text-right
                                <?= (float) $row['annual_change'] > 0
                                    ? 'text-amber-700 dark:text-amber-400'
                                    : 'text-emerald-700 dark:text-emerald-400' ?>
                            "
                        >
                            <span class="text-slate-400 lg:hidden">Jahresänderung: </span>
                            <?= (float) $row[
                                'annual_change'
                            ] >= 0 ? '+' : '' ?>
                            <?= e(
                                contract_format_money(
                                    (float) $row[
                                        'annual_change'
                                    ]
                                )
                            ) ?>
                        </div>

                        <div class="mt-2 font-semibold lg:mt-0 lg:text-right">
                            <span class="text-slate-400 lg:hidden">Potenzial: </span>
                            <?= e(
                                contract_format_money(
                                    (float) $row[
                                        'saving_12_months'
                                    ]
                                )
                            ) ?>
                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>


    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 text-sm leading-6 text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
        <strong class="text-slate-900 dark:text-white">Berechnungslogik:</strong>
        Das Einsparpotenzial berücksichtigt nur Zahlungen innerhalb der nächsten
        zwölf Monate, die nach dem rechnerisch nächsten möglichen Vertragsende
        liegen würden. Es ist eine Planungsgröße und kein garantiertes Angebot
        eines Anbieters.
    </div>

</section>
