<?php

declare(strict_types=1);

$criticalCount = 0;
$warningCount = 0;
$missedCount = 0;
$totalSavings = 0.0;

foreach ($deadlineItems as $item) {
    $deadline =
        $item['deadline'];

    if (
        $deadline[
            'missed_current_deadline'
        ]
    ) {
        $missedCount++;
    }

    if (
        $deadline['urgency']
        === 'critical'
    ) {
        $criticalCount++;
    } elseif (
        in_array(
            $deadline['urgency'],
            ['warning', 'upcoming'],
            true
        )
    ) {
        $warningCount++;
    }

    $totalSavings +=
        (float) (
            $deadline[
                'saving_12_months'
            ] ?? 0
        );
}

?>

<section
    class="
        mx-auto
        max-w-7xl
        px-4
        py-7
        sm:px-6
        sm:py-10
    "
>

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

            <div
                class="
                    text-xs
                    font-semibold
                    uppercase
                    tracking-[0.18em]
                    text-blue-600
                    dark:text-blue-400
                "
            >
                Vertragslebenszyklus
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
                Kündigungs- & Fristen-Cockpit
            </h1>

            <p
                class="
                    mt-2
                    max-w-3xl
                    text-sm
                    leading-6
                    text-slate-500
                    sm:text-base
                    dark:text-slate-400
                "
            >
                Kündigungsfristen, nächste mögliche Vertragsenden und
                Verlängerungen auf einen Blick.
            </p>

        </div>


        <form
            method="get"
            action="/deadlines"
            class="
                w-full
                lg:w-72
            "
        >
            <label
                for="deadline-holder"
                class="
                    mb-2
                    block
                    text-xs
                    font-semibold
                    uppercase
                    tracking-wide
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Vertragsinhaber
            </label>

            <select
                id="deadline-holder"
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

                <?php foreach (
                    $contractHolders
                    as $holder
                ): ?>

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


    <div
        class="
            mb-6
            grid
            grid-cols-2
            gap-3
            lg:grid-cols-4
        "
    >

        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-4
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Verträge mit Frist
            </div>
            <div class="mt-2 text-2xl font-bold">
                <?= count($deadlineItems) ?>
            </div>
        </div>

        <div
            class="
                rounded-2xl
                border
                border-red-200
                bg-red-50
                p-4
                dark:border-red-900
                dark:bg-red-950/30
            "
        >
            <div class="text-xs font-semibold uppercase tracking-wide text-red-600 dark:text-red-400">
                ≤ 7 Tage
            </div>
            <div class="mt-2 text-2xl font-bold">
                <?= $criticalCount ?>
            </div>
        </div>

        <div
            class="
                rounded-2xl
                border
                border-amber-200
                bg-amber-50
                p-4
                dark:border-amber-900
                dark:bg-amber-950/30
            "
        >
            <div class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-400">
                Frist verpasst
            </div>
            <div class="mt-2 text-2xl font-bold">
                <?= $missedCount ?>
            </div>
        </div>

        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-4
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Einsparpotenzial 12 Monate
            </div>
            <div class="mt-2 text-2xl font-bold">
                <?= e(
                    contract_format_money(
                        $totalSavings
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

        <?php if (
            empty($deadlineItems)
        ): ?>

            <div class="px-6 py-16 text-center">
                <div class="font-semibold">
                    Keine Fristen vorhanden
                </div>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Für die gewählte Auswahl sind keine aktuell laufenden Verträge mit relevanter Frist oder vorgemerktem Vertragsende hinterlegt.
                </p>
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
                    lg:grid-cols-[1.3fr_0.8fr_0.9fr_0.9fr_0.9fr_0.8fr]
                    lg:gap-4
                    dark:border-slate-800
                    dark:bg-slate-800/70
                    dark:text-slate-400
                "
            >
                <div>Vertrag</div>
                <div>Inhaber</div>
                <div>Frist / Kündigung</div>
                <div>Nächstes Ende</div>
                <div>Status</div>
                <div class="text-right">Potenzial</div>
            </div>


            <div class="divide-y divide-slate-200 dark:divide-slate-800">

                <?php foreach (
                    $deadlineItems
                    as $item
                ): ?>

                    <?php
                    $deadline =
                        $item['deadline'];

                    $days =
                        (int) $deadline[
                            'days_until_deadline'
                        ];

                    $urgency =
                        $deadline['urgency'];

                    $statusClasses =
                        $urgency === 'critical'
                        || $urgency === 'overdue'
                            ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'
                            : (
                                $urgency === 'warning'
                                || $deadline[
                                    'missed_current_deadline'
                                ]
                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300'
                            );

                    if (
                        !empty(
                            $deadline[
                                'is_cancelled'
                            ]
                        )
                    ) {
                        $statusText =
                            $days < 0
                                ? 'Beendet'
                                : (
                                    $days === 0
                                        ? 'Endet heute'
                                        : 'Gekündigt · noch '
                                            . $days
                                            . (
                                                $days === 1
                                                    ? ' Tag'
                                                    : ' Tage'
                                            )
                                );
                    } elseif (
                        $deadline[
                            'missed_current_deadline'
                        ]
                    ) {
                        $statusText =
                            'Frist verpasst';
                    } elseif ($days < 0) {
                        $statusText =
                            'Überfällig';
                    } elseif ($days === 0) {
                        $statusText =
                            'Heute';
                    } else {
                        $statusText =
                            'Noch '
                            . $days
                            . (
                                $days === 1
                                    ? ' Tag'
                                    : ' Tage'
                            );
                    }
                    ?>

                    <a
                        href="/contracts/<?= (int) $item['id'] ?>"
                        class="
                            block
                            px-5
                            py-5
                            transition
                            hover:bg-slate-50
                            lg:grid
                            lg:grid-cols-[1.3fr_0.8fr_0.9fr_0.9fr_0.9fr_0.8fr]
                            lg:items-center
                            lg:gap-4
                            dark:hover:bg-slate-800/60
                        "
                    >

                        <div class="min-w-0">
                            <div class="truncate font-semibold">
                                <?= e($item['title']) ?>
                            </div>
                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                <?= e($item['provider']) ?>
                                ·
                                <?= e($item['contract_type']) ?>
                            </div>
                        </div>

                        <div class="mt-4 text-sm lg:mt-0">
                            <span class="text-slate-400 lg:hidden">Inhaber: </span>
                            <?= e($item['contract_holder_name']) ?>
                        </div>

                        <div class="mt-2 text-sm lg:mt-0">
                            <span class="text-slate-400 lg:hidden">Frist: </span>
                            <?= e(
                                contract_format_date(
                                    $deadline[
                                        'deadline_date'
                                    ]
                                )
                            ) ?>
                        </div>

                        <div class="mt-2 text-sm lg:mt-0">
                            <span class="text-slate-400 lg:hidden">Ende: </span>
                            <?= e(
                                contract_format_date(
                                    $deadline[
                                        'end_date'
                                    ]
                                )
                            ) ?>
                        </div>

                        <div class="mt-3 lg:mt-0">
                            <span
                                class="
                                    inline-flex
                                    rounded-full
                                    px-2.5
                                    py-1
                                    text-xs
                                    font-semibold
                                    <?= $statusClasses ?>
                                "
                            >
                                <?= e($statusText) ?>
                            </span>

                            <?php if (
                                $deadline[
                                    'automatic_renewal'
                                ]
                            ): ?>
                                <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                    automatische Verlängerung
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-3 font-semibold lg:mt-0 lg:text-right">
                            <?= e(
                                contract_format_money(
                                    (float) $deadline[
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

</section>
