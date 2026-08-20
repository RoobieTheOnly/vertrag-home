<?php

declare(strict_types=1);

$monthlyEquivalent =
    contract_monthly_equivalent(
        $contract
    );

$annualEquivalent =
    contract_annual_equivalent(
        $contract
    );

$nextPaymentDate =
    contract_next_payment_date(
        $contract
    );

$today =
    new DateTimeImmutable('today');

$isHistorical =
    contract_is_historical(
        $contract,
        $today
    );

$isRunning =
    contract_is_running_on(
        $contract,
        $today
    );

$isPaused =
    !empty(
        $pauseState[
            'is_paused'
        ]
    );

$currentPause =
    $pauseState[
        'current'
    ] ?? null;

$nextPause =
    $pauseState[
        'next'
    ] ?? null;

$cancellationDate =
    contract_cancellation_effective_date(
        $contract
    );

?>

<section class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">

    <div
        class="
            mb-8
            flex
            flex-col
            justify-between
            gap-5
            lg:flex-row
            lg:items-start
        "
    >

        <div>

            <a
                href="/contracts"
                class="
                    text-sm
                    font-medium
                    text-blue-600
                    hover:text-blue-700
                    dark:text-blue-400
                    dark:hover:text-blue-300
                "
            >
                ← Zurück zu Verträgen
            </a>


            <div
                class="
                    mt-4
                    flex
                    flex-wrap
                    items-center
                    gap-3
                "
            >

                <h1
                    class="
                        text-3xl
                        font-bold
                        tracking-tight
                        text-slate-900
                        dark:text-white
                    "
                >
                    <?= e($contract['title']) ?>
                </h1>


                <?php if ($isPaused): ?>

                    <span
                        class="
                            rounded-full
                            bg-violet-100
                            px-3
                            py-1
                            text-xs
                            font-semibold
                            text-violet-800
                            dark:bg-violet-950
                            dark:text-violet-300
                        "
                    >
                        Pausiert
                    </span>

                <?php elseif (
                    $contract['status']
                    === 'active'
                ): ?>

                    <span
                        class="
                            rounded-full
                            bg-emerald-100
                            px-3
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

                <?php elseif (
                    $contract['status']
                    === 'cancelled'
                ): ?>

                    <span
                        class="
                            rounded-full
                            bg-amber-100
                            px-3
                            py-1
                            text-xs
                            font-semibold
                            text-amber-800
                            dark:bg-amber-950
                            dark:text-amber-300
                        "
                    >
                        <?= $cancellationDate !== null
                            && !$isHistorical
                                ? 'Gekündigt zum '
                                    . e(
                                        $cancellationDate->format(
                                            'd.m.Y'
                                        )
                                    )
                                : 'Gekündigt' ?>
                    </span>

                <?php else: ?>

                    <span
                        class="
                            rounded-full
                            bg-slate-100
                            px-3
                            py-1
                            text-xs
                            font-semibold
                            text-slate-700
                            dark:bg-slate-800
                            dark:text-slate-300
                        "
                    >
                        <?= e(
                            contract_status_label(
                                $contract['status']
                            )
                        ) ?>
                    </span>

                <?php endif; ?>

                <?php if (
                    $isPaused
                    && $contract['status']
                        === 'cancelled'
                    && $cancellationDate !== null
                ): ?>

                    <span
                        class="
                            rounded-full
                            bg-amber-100
                            px-3
                            py-1
                            text-xs
                            font-semibold
                            text-amber-800
                            dark:bg-amber-950
                            dark:text-amber-300
                        "
                    >
                        Gekündigt zum
                        <?= e(
                            $cancellationDate->format(
                                'd.m.Y'
                            )
                        ) ?>
                    </span>

                <?php endif; ?>

            </div>


            <p
                class="
                    mt-2
                    text-slate-500
                    dark:text-slate-400
                "
            >
                <?= e($contract['provider']) ?>
                ·
                <?= e($contract['contract_type']) ?>
                ·
                <?= e($contract['contract_holder_name']) ?>

                <?php if (
                    (int) (
                        $contract[
                            'notifications_enabled'
                        ] ?? 1
                    ) !== 1
                ): ?>
                    · Benachrichtigungen aus
                <?php endif; ?>
            </p>

        </div>


        <div
            class="
                flex
                flex-wrap
                items-center
                gap-3
            "
        >

            <?php if (
                has_permission(
                    'contracts.edit'
                )
            ): ?>

                <a
                    href="/contracts/<?= (int) $contract['id'] ?>/edit"
                    class="
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-2.5
                        text-sm
                        font-semibold
                        text-slate-700
                        transition
                        hover:bg-slate-50
                        dark:border-slate-700
                        dark:bg-slate-900
                        dark:text-slate-200
                        dark:hover:bg-slate-800
                    "
                >
                    Bearbeiten
                </a>


                <form
                    method="post"
                    action="/contracts/<?= (int) $contract['id'] ?>/notifications"
                >
                    <?= csrf_field() ?>

                    <?php
                    $notificationsEnabled =
                        (int) (
                            $contract[
                                'notifications_enabled'
                            ] ?? 1
                        ) === 1;
                    ?>

                    <input
                        type="hidden"
                        name="enabled"
                        value="<?= $notificationsEnabled ? '0' : '1' ?>"
                    >

                    <button
                        type="submit"
                        class="
                            rounded-xl
                            border
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            transition
                            <?= $notificationsEnabled
                                ? 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800'
                                : 'border-slate-300 bg-slate-100 text-slate-600 hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' ?>
                        "
                    >
                        <?= $notificationsEnabled
                            ? 'Benachrichtigungen ausschalten'
                            : 'Benachrichtigungen einschalten' ?>
                    </button>
                </form>


                <?php if (
                    in_array(
                        $contract['status'],
                        ['active', 'planned'],
                        true
                    )
                ): ?>

                    <button
                        type="button"
                        data-contract-cancel-open
                        class="
                            rounded-xl
                            border
                            border-amber-300
                            bg-amber-50
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-amber-800
                            transition
                            hover:bg-amber-100
                            dark:border-amber-900
                            dark:bg-amber-950
                            dark:text-amber-300
                            dark:hover:bg-amber-900/60
                        "
                    >
                        Kündigung hinterlegen
                    </button>

                <?php elseif (
                    $contract['status']
                    === 'cancelled'
                ): ?>

                    <button
                        type="button"
                        data-contract-cancel-open
                        class="
                            rounded-xl
                            border
                            border-amber-300
                            bg-amber-50
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-amber-800
                            transition
                            hover:bg-amber-100
                            dark:border-amber-900
                            dark:bg-amber-950
                            dark:text-amber-300
                            dark:hover:bg-amber-900/60
                        "
                    >
                        Kündigungsdatum ändern
                    </button>

                    <form
                        method="post"
                        action="/contracts/<?= (int) $contract['id'] ?>/reactivate"
                        data-confirm
                        data-confirm-title="Kündigung aufheben?"
                        data-confirm-message="Die hinterlegte Kündigung wird entfernt und der Vertrag wieder als aktiv geführt."
                        data-confirm-label="Kündigung aufheben"
                        data-confirm-variant="success"
                    >

                        <?= csrf_field() ?>

                        <button
                            type="submit"
                            class="
                                rounded-xl
                                border
                                border-emerald-300
                                bg-emerald-50
                                px-4
                                py-2.5
                                text-sm
                                font-semibold
                                text-emerald-800
                                transition
                                hover:bg-emerald-100
                                dark:border-emerald-900
                                dark:bg-emerald-950
                                dark:text-emerald-300
                                dark:hover:bg-emerald-900/60
                            "
                        >
                            Kündigung aufheben
                        </button>

                    </form>

                <?php elseif (
                    $contract['status']
                    === 'expired'
                ): ?>

                    <form
                        method="post"
                        action="/contracts/<?= (int) $contract['id'] ?>/reactivate"
                        data-confirm
                        data-confirm-title="Vertrag wieder aktivieren?"
                        data-confirm-message="Der Vertrag wird wieder als aktiv geführt."
                        data-confirm-label="Wieder aktivieren"
                        data-confirm-variant="success"
                    >

                        <?= csrf_field() ?>

                        <button
                            type="submit"
                            class="
                                rounded-xl
                                border
                                border-emerald-300
                                bg-emerald-50
                                px-4
                                py-2.5
                                text-sm
                                font-semibold
                                text-emerald-800
                                dark:border-emerald-900
                                dark:bg-emerald-950
                                dark:text-emerald-300
                            "
                        >
                            Wieder aktivieren
                        </button>

                    </form>

                <?php endif; ?>


                <?php if ($isRunning): ?>

                    <button
                        type="button"
                        data-contract-pause-open
                        class="
                            rounded-xl
                            border
                            border-violet-300
                            bg-violet-50
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-violet-800
                            transition
                            hover:bg-violet-100
                            dark:border-violet-900
                            dark:bg-violet-950
                            dark:text-violet-300
                            dark:hover:bg-violet-900/60
                        "
                    >
                        + Pause
                    </button>

                <?php endif; ?>

            <?php endif; ?>


            <?php if (
                has_permission(
                    'contracts.delete'
                )
            ): ?>

                <form
                    method="post"
                    action="/contracts/<?= (int) $contract['id'] ?>/delete"
                    data-confirm
                    data-confirm-title="Vertrag endgültig löschen?"
                    data-confirm-message="Der Vertrag, seine Dokumenteinträge und die gespeicherten Vertragsdateien werden vollständig entfernt. Dieser Vorgang kann nicht rückgängig gemacht werden."
                    data-confirm-label="Endgültig löschen"
                    data-confirm-variant="danger"
                >

                    <?= csrf_field() ?>

                    <button
                        type="submit"
                        class="
                            rounded-xl
                            bg-red-600
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-white
                            transition
                            hover:bg-red-700
                            dark:hover:bg-red-500
                        "
                    >
                        Endgültig löschen
                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>

    <?php if ($isHistorical): ?>

        <div
            class="
                mb-6
                rounded-xl
                border
                border-amber-200
                bg-amber-50
                px-5
                py-4
                text-sm
                text-amber-900
                dark:border-amber-900
                dark:bg-amber-950/60
                dark:text-amber-200
            "
        >
            Dieser Vertrag ist historisch. Er bleibt vollständig mit seinen
            Vertragsdaten und Dokumenten erhalten, wird aber nicht mehr in den
            laufenden Kosten berücksichtigt.

            <?php if (
                $contract['status'] === 'cancelled'
                && $cancellationDate !== null
            ): ?>
                <div class="mt-2 font-semibold">
                    Vertragsende:
                    <?= e(
                        $cancellationDate->format(
                            'd.m.Y'
                        )
                    ) ?>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif (
        $contract['status'] === 'cancelled'
        && $cancellationDate !== null
    ): ?>

        <div
            class="
                mb-6
                rounded-xl
                border
                border-amber-200
                bg-amber-50
                px-5
                py-4
                text-sm
                text-amber-900
                dark:border-amber-900
                dark:bg-amber-950/50
                dark:text-amber-200
            "
        >
            <div class="font-semibold">
                Kündigung ist vorgemerkt.
            </div>

            <div class="mt-1">
                Der Vertrag läuft noch bis einschließlich
                <strong>
                    <?= e(
                        $cancellationDate->format(
                            'd.m.Y'
                        )
                    ) ?>
                </strong>
                und wird bis dahin weiterhin in den Kosten- und
                Abbuchungsplanungen berücksichtigt. Eine automatische
                Verlängerung wird nicht mehr berechnet.
            </div>

            <?php if (
                !empty(
                    $contract['cancelled_at']
                )
            ): ?>
                <div class="mt-2 text-xs opacity-80">
                    Kündigung hinterlegt am
                    <?= e(
                        date(
                            'd.m.Y H:i',
                            strtotime(
                                (string) $contract[
                                    'cancelled_at'
                                ]
                            )
                        )
                    ) ?>
                    Uhr.
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>


    <?php if (
        $currentPause !== null
    ): ?>

        <div
            class="
                mb-6
                rounded-xl
                border
                border-violet-200
                bg-violet-50
                px-5
                py-4
                text-sm
                text-violet-900
                dark:border-violet-900
                dark:bg-violet-950/50
                dark:text-violet-200
            "
        >
            <div class="font-semibold">
                Vertrag ist aktuell pausiert.
            </div>

            <div class="mt-1">
                Pause vom
                <strong>
                    <?= e(
                        contract_format_date(
                            $currentPause[
                                'pause_from'
                            ]
                        )
                    ) ?>
                </strong>
                bis einschließlich
                <strong>
                    <?= e(
                        contract_format_date(
                            $currentPause[
                                'pause_to'
                            ]
                        )
                    ) ?>
                </strong>.
                Abbuchungen innerhalb dieses Zeitraums werden in Vertrag Home
                nicht als erwartete Kosten eingeplant.
            </div>

            <?php if (
                !empty(
                    $currentPause['reason']
                )
            ): ?>
                <div class="mt-2 text-xs opacity-80">
                    Grund:
                    <?= e(
                        $currentPause['reason']
                    ) ?>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif (
        $nextPause !== null
    ): ?>

        <div
            class="
                mb-6
                rounded-xl
                border
                border-blue-200
                bg-blue-50
                px-5
                py-4
                text-sm
                text-blue-900
                dark:border-blue-900
                dark:bg-blue-950/40
                dark:text-blue-200
            "
        >
            <div class="font-semibold">
                Zukünftige Vertragspause hinterlegt.
            </div>

            <div class="mt-1">
                Vom
                <?= e(
                    contract_format_date(
                        $nextPause[
                            'pause_from'
                        ]
                    )
                ) ?>
                bis
                <?= e(
                    contract_format_date(
                        $nextPause[
                            'pause_to'
                        ]
                    )
                ) ?>
                werden planmäßige Abbuchungen übersprungen.
            </div>
        </div>

    <?php endif; ?>


    <div
        class="
            mb-6
            grid
            gap-5
            sm:grid-cols-2
            xl:grid-cols-4
        "
    >

        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-5
                dark:border-slate-800
                dark:bg-slate-900
            "
        >

            <div
                class="
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Vertragsbetrag
            </div>

            <div class="mt-2 text-2xl font-bold">
                <?= e(
                    contract_format_money(
                        (float) $contract['amount']
                    )
                ) ?>
            </div>

            <div
                class="
                    mt-1
                    text-xs
                    text-slate-500
                    dark:text-slate-400
                "
            >
                <?= e(
                    contract_billing_frequency_label(
                        $contract[
                            'billing_frequency'
                        ]
                    )
                ) ?>
            </div>

        </div>


        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-5
                dark:border-slate-800
                dark:bg-slate-900
            "
        >

            <div
                class="
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Monatlicher Vergleichswert
            </div>

            <div class="mt-2 text-2xl font-bold">
                <?= e(
                    contract_format_money(
                        $monthlyEquivalent
                    )
                ) ?>
            </div>

        </div>


        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-5
                dark:border-slate-800
                dark:bg-slate-900
            "
        >

            <div
                class="
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Jährlicher Vergleichswert
            </div>

            <div class="mt-2 text-2xl font-bold">
                <?= e(
                    contract_format_money(
                        $annualEquivalent
                    )
                ) ?>
            </div>

        </div>


        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-5
                dark:border-slate-800
                dark:bg-slate-900
            "
        >

            <div
                class="
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Nächste Abbuchung
            </div>

            <div class="mt-2 text-2xl font-bold">
                <?= e(
                    contract_format_date(
                        $nextPaymentDate
                    )
                ) ?>
            </div>

        </div>

    </div>


    <div
        class="
            mb-6
            rounded-2xl
            border
            border-slate-200
            bg-white
            p-5
            sm:p-6
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <div
            class="
                flex
                flex-col
                gap-4
                sm:flex-row
                sm:items-start
                sm:justify-between
            "
        >

            <div>

                <h2
                    class="
                        text-lg
                        font-semibold
                        text-slate-900
                        dark:text-white
                    "
                >
                    Bisherige Vertragskosten
                </h2>

                <p
                    class="
                        mt-1
                        text-sm
                        leading-6
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Rechnerisch bis heute anhand des hinterlegten
                    Abbuchungsplans. Es findet kein Abgleich mit einem
                    Bankkonto statt.
                </p>

            </div>


            <div
                class="
                    rounded-xl
                    bg-blue-50
                    px-4
                    py-3
                    text-right
                    dark:bg-blue-950/40
                "
            >
                <div
                    class="
                        text-xs
                        font-semibold
                        uppercase
                        tracking-wide
                        text-blue-600
                        dark:text-blue-400
                    "
                >
                    Bisher angefallen
                </div>

                <div
                    class="
                        mt-1
                        text-2xl
                        font-bold
                        text-slate-900
                        dark:text-white
                    "
                >
                    <?= e(
                        contract_format_money(
                            (float) (
                                $accumulatedCostSummary[
                                    'total_cost'
                                ] ?? 0
                            )
                        )
                    ) ?>
                </div>
            </div>

        </div>


        <div
            class="
                mt-5
                grid
                gap-3
                sm:grid-cols-3
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
                <div
                    class="
                        text-xs
                        font-semibold
                        uppercase
                        tracking-wide
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Berücksichtigte Abbuchungen
                </div>

                <div
                    class="
                        mt-2
                        text-xl
                        font-bold
                        text-slate-900
                        dark:text-white
                    "
                >
                    <?= (int) (
                        $accumulatedCostSummary[
                            'payment_count'
                        ] ?? 0
                    ) ?>
                </div>
            </div>


            <div
                class="
                    rounded-xl
                    bg-slate-50
                    p-4
                    dark:bg-slate-800/70
                "
            >
                <div
                    class="
                        text-xs
                        font-semibold
                        uppercase
                        tracking-wide
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Erste Abbuchung
                </div>

                <div
                    class="
                        mt-2
                        font-semibold
                        text-slate-900
                        dark:text-white
                    "
                >
                    <?= e(
                        contract_format_date(
                            $accumulatedCostSummary[
                                'first_payment_date'
                            ] ?? null
                        )
                    ) ?>
                </div>
            </div>


            <div
                class="
                    rounded-xl
                    bg-slate-50
                    p-4
                    dark:bg-slate-800/70
                "
            >
                <div
                    class="
                        text-xs
                        font-semibold
                        uppercase
                        tracking-wide
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Letzte berücksichtigte Abbuchung
                </div>

                <div
                    class="
                        mt-2
                        font-semibold
                        text-slate-900
                        dark:text-white
                    "
                >
                    <?= e(
                        contract_format_date(
                            $accumulatedCostSummary[
                                'last_payment_date'
                            ] ?? null
                        )
                    ) ?>
                </div>
            </div>

        </div>

    </div>


    <div class="space-y-6">

        <div class="space-y-6">

            <div
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-6
                    dark:border-slate-800
                    dark:bg-slate-900
                "
            >

                <h2 class="text-lg font-semibold">
                    Vertragsdaten
                </h2>


                <dl
                    class="
                        mt-6
                        grid
                        gap-x-8
                        gap-y-6
                        sm:grid-cols-2
                    "
                >

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Anbieter
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e($contract['provider']) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Vertragsart
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e($contract['contract_type']) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Vertragsinhaber
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e($contract['contract_holder_name']) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Status
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e(
                                contract_status_label(
                                    $contract['status']
                                )
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Vertragsnummer
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e(
                                $contract['contract_number']
                                ?: '–'
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Kundennummer
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e(
                                $contract['customer_number']
                                ?: '–'
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Erster Abbuchungstermin
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e(
                                contract_format_date(
                                    $contract[
                                        'first_payment_date'
                                    ]
                                    ?? $contract[
                                        'next_payment_date'
                                    ]
                                    ?? null
                                )
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Vertragsbeginn
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e(
                                contract_format_date(
                                    $contract['start_date']
                                )
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Vertragsende
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= e(
                                contract_format_date(
                                    $contract['end_date']
                                )
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Mindestlaufzeit
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?php if (
                                !empty(
                                    $contract[
                                        'minimum_term_months'
                                    ]
                                )
                            ): ?>
                                <?= (int) $contract[
                                    'minimum_term_months'
                                ] ?>
                                Monate
                            <?php else: ?>
                                –
                            <?php endif; ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Kündigungsfrist
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?php if (
                                !empty(
                                    $contract[
                                        'notice_period_value'
                                    ]
                                )
                            ): ?>
                                <?= (int) $contract[
                                    'notice_period_value'
                                ] ?>
                                <?= e(
                                    contract_notice_unit_label(
                                        $contract[
                                            'notice_period_unit'
                                        ]
                                    )
                                ) ?>
                            <?php else: ?>
                                –
                            <?php endif; ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Automatische Verlängerung
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?= (int) $contract['automatic_renewal'] === 1
                                ? 'Ja'
                                : 'Nein' ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">
                            Verlängerungszeitraum
                        </dt>
                        <dd class="mt-1 font-medium">
                            <?php if (
                                !empty(
                                    $contract[
                                        'renewal_period_months'
                                    ]
                                )
                            ): ?>
                                <?= (int) $contract[
                                    'renewal_period_months'
                                ] ?>
                                Monate
                            <?php else: ?>
                                –
                            <?php endif; ?>
                        </dd>
                    </div>

                </dl>


                <?php if (
                    !empty($contract['description'])
                ): ?>

                    <div
                        class="
                            mt-6
                            border-t
                            border-slate-200
                            pt-6
                            dark:border-slate-800
                        "
                    >

                        <div
                            class="
                                text-sm
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            Beschreibung
                        </div>

                        <div class="mt-2 whitespace-pre-line">
                            <?= e($contract['description']) ?>
                        </div>

                    </div>

                <?php endif; ?>


                <?php if (
                    !empty($contract['notes'])
                ): ?>

                    <div
                        class="
                            mt-6
                            border-t
                            border-slate-200
                            pt-6
                            dark:border-slate-800
                        "
                    >

                        <div
                            class="
                                text-sm
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            Notizen
                        </div>

                        <div class="mt-2 whitespace-pre-line">
                            <?= e($contract['notes']) ?>
                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>


    </div>


    <div
        class="
            mt-6
            grid
            gap-6
            lg:grid-cols-2
        "
    >

        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-5
                sm:p-6
                dark:border-slate-800
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
                    <h2 class="text-lg font-semibold">
                        Kündigung & Fristen
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Nächste relevante Frist für diesen Vertrag.
                    </p>
                </div>

                <a
                    href="/deadlines"
                    class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                >
                    Cockpit →
                </a>
            </div>


            <?php if (
                !empty(
                    $deadlineInfo[
                        'has_deadline'
                    ]
                )
            ): ?>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">

                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/70">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <?= !empty(
                                $deadlineInfo[
                                    'is_cancelled'
                                ]
                            )
                                ? 'Gekündigt zum'
                                : 'Kündigungsfrist' ?>
                        </div>
                        <div class="mt-2 text-xl font-bold">
                            <?= e(
                                contract_format_date(
                                    $deadlineInfo[
                                        'deadline_date'
                                    ]
                                )
                            ) ?>
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/70">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <?= !empty(
                                $deadlineInfo[
                                    'is_cancelled'
                                ]
                            )
                                ? 'Kündigung hinterlegt am'
                                : 'Nächstes mögliches Ende' ?>
                        </div>
                        <div class="mt-2 text-xl font-bold">
                            <?php if (
                                !empty(
                                    $deadlineInfo[
                                        'is_cancelled'
                                    ]
                                )
                            ): ?>
                                <?= e(
                                    contract_format_date(
                                        !empty(
                                            $contract[
                                                'cancelled_at'
                                            ]
                                        )
                                            ? substr(
                                                (string) $contract[
                                                    'cancelled_at'
                                                ],
                                                0,
                                                10
                                            )
                                            : null
                                    )
                                ) ?>
                            <?php else: ?>
                                <?= e(
                                    contract_format_date(
                                        $deadlineInfo[
                                            'end_date'
                                        ]
                                    )
                                ) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/70">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <?= !empty(
                                $deadlineInfo[
                                    'is_cancelled'
                                ]
                            )
                                ? 'Restlaufzeit'
                                : 'Zeit bis Frist' ?>
                        </div>
                        <div class="mt-2 font-semibold">
                            <?php
                            $deadlineDays =
                                (int) $deadlineInfo[
                                    'days_until_deadline'
                                ];
                            ?>

                            <?php if (
                                $deadlineInfo[
                                    'missed_current_deadline'
                                ]
                            ): ?>
                                <span class="text-amber-700 dark:text-amber-400">
                                    Aktuelle Frist verpasst
                                </span>
                            <?php elseif (
                                $deadlineDays < 0
                            ): ?>
                                <span class="text-red-700 dark:text-red-400">
                                    Überfällig
                                </span>
                            <?php elseif (
                                $deadlineDays === 0
                            ): ?>
                                Heute
                            <?php else: ?>
                                <?= $deadlineDays ?>
                                <?= $deadlineDays === 1
                                    ? 'Tag'
                                    : 'Tage' ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="rounded-xl bg-blue-50 p-4 dark:bg-blue-950/30">
                        <div class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-400">
                            Einsparpotenzial 12 Monate
                        </div>
                        <div class="mt-2 font-bold">
                            <?= e(
                                contract_format_money(
                                    (float) (
                                        $deadlineInfo[
                                            'saving_12_months'
                                        ] ?? 0
                                    )
                                )
                            ) ?>
                        </div>
                    </div>

                </div>

            <?php else: ?>

                <div class="mt-5 rounded-xl border border-dashed border-slate-300 p-5 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    Für diesen Vertrag kann aktuell keine Kündigungsfrist berechnet werden.
                    Hinterlege dafür ein Vertragsende und gegebenenfalls eine Kündigungsfrist.
                </div>

            <?php endif; ?>

        </div>


        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-5
                sm:p-6
                dark:border-slate-800
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
                    <h2 class="text-lg font-semibold">
                        Preis- & Kostenhistorie
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Historische Preisstände bleiben erhalten und werden für bisherige Kosten berücksichtigt.
                    </p>
                </div>

                <?php if (
                    has_permission(
                        'contracts.edit'
                    )
                ): ?>
                    <button
                        type="button"
                        data-price-history-open
                        class="
                            rounded-lg
                            border
                            border-slate-300
                            px-3
                            py-2
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
                        + Preisstand
                    </button>
                <?php endif; ?>
            </div>


            <?php if (empty($priceHistory)): ?>

                <div class="mt-5 rounded-xl border border-dashed border-slate-300 p-5 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    Noch keine Preis-Historie vorhanden.
                </div>

            <?php else: ?>

                <div class="mt-5 space-y-3">

                    <?php foreach (
                        $priceHistory
                        as $price
                    ): ?>

                        <div
                            class="
                                flex
                                flex-col
                                justify-between
                                gap-2
                                rounded-xl
                                bg-slate-50
                                p-4
                                sm:flex-row
                                sm:items-center
                                dark:bg-slate-800/70
                            "
                        >
                            <div>
                                <div class="font-semibold">
                                    <?= e(
                                        contract_format_money(
                                            (float) $price[
                                                'amount'
                                            ]
                                        )
                                    ) ?>
                                    ·
                                    <?= e(
                                        contract_billing_frequency_label(
                                            $price[
                                                'billing_frequency'
                                            ]
                                        )
                                    ) ?>
                                </div>

                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    ab
                                    <?= e(
                                        contract_format_date(
                                            $price[
                                                'valid_from'
                                            ]
                                        )
                                    ) ?>

                                    <?php if (
                                        !empty(
                                            $price[
                                                'valid_to'
                                            ]
                                        )
                                    ): ?>
                                        bis
                                        <?= e(
                                            contract_format_date(
                                                $price[
                                                    'valid_to'
                                                ]
                                            )
                                        ) ?>
                                    <?php else: ?>
                                        · aktuell
                                    <?php endif; ?>
                                </div>

                                <?php if (
                                    !empty(
                                        $price[
                                            'change_reason'
                                        ]
                                    )
                                ): ?>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        <?= e(
                                            $price[
                                                'change_reason'
                                            ]
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="text-xs text-slate-400">
                                <?= e(
                                    $price[
                                        'created_by_name'
                                    ] ?? ''
                                ) ?>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <div
        class="
            mt-6
            rounded-2xl
            border
            border-slate-200
            bg-white
            p-5
            sm:p-6
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <div
            class="
                flex
                flex-col
                gap-4
                sm:flex-row
                sm:items-start
                sm:justify-between
            "
        >
            <div>
                <h2 class="text-lg font-semibold">
                    Vertragspausen
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Hinterlegte Unterbrechungen des Vertrags. Innerhalb einer
                    Pause werden planmäßige Abbuchungen nicht als erwartete
                    Kosten berücksichtigt.
                </p>
            </div>

            <?php if (
                has_permission(
                    'contracts.edit'
                )
                && $isRunning
            ): ?>
                <button
                    type="button"
                    data-contract-pause-open
                    class="
                        shrink-0
                        rounded-lg
                        border
                        border-violet-300
                        bg-violet-50
                        px-3
                        py-2
                        text-sm
                        font-semibold
                        text-violet-800
                        transition
                        hover:bg-violet-100
                        dark:border-violet-900
                        dark:bg-violet-950
                        dark:text-violet-300
                        dark:hover:bg-violet-900/60
                    "
                >
                    + Pause hinzufügen
                </button>
            <?php endif; ?>
        </div>


        <?php if (empty($contractPauses)): ?>

            <div
                class="
                    mt-5
                    rounded-xl
                    border
                    border-dashed
                    border-slate-300
                    p-5
                    text-sm
                    text-slate-500
                    dark:border-slate-700
                    dark:text-slate-400
                "
            >
                Für diesen Vertrag sind noch keine Pausen hinterlegt.
            </div>

        <?php else: ?>

            <div class="mt-5 space-y-3">

                <?php foreach (
                    $contractPauses
                    as $pause
                ): ?>

                    <?php
                    $pauseFrom =
                        DateTimeImmutable::createFromFormat(
                            '!Y-m-d',
                            $pause['pause_from']
                        );

                    $pauseTo =
                        DateTimeImmutable::createFromFormat(
                            '!Y-m-d',
                            $pause['pause_to']
                        );

                    $pauseIsCurrent =
                        $pauseFrom
                        && $pauseTo
                        && $pauseFrom <= $today
                        && $pauseTo >= $today;

                    $pauseIsFuture =
                        $pauseFrom
                        && $pauseFrom > $today;
                    ?>

                    <div
                        class="
                            flex
                            flex-col
                            justify-between
                            gap-4
                            rounded-xl
                            border
                            p-4
                            sm:flex-row
                            sm:items-center
                            <?= $pauseIsCurrent
                                ? 'border-violet-200 bg-violet-50 dark:border-violet-900 dark:bg-violet-950/30'
                                : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/60' ?>
                        "
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="font-semibold">
                                    <?= e(
                                        contract_format_date(
                                            $pause['pause_from']
                                        )
                                    ) ?>
                                    –
                                    <?= e(
                                        contract_format_date(
                                            $pause['pause_to']
                                        )
                                    ) ?>
                                </div>

                                <span
                                    class="
                                        rounded-full
                                        px-2
                                        py-0.5
                                        text-[11px]
                                        font-semibold
                                        <?= $pauseIsCurrent
                                            ? 'bg-violet-100 text-violet-800 dark:bg-violet-950 dark:text-violet-300'
                                            : (
                                                $pauseIsFuture
                                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300'
                                                    : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
                                            ) ?>
                                    "
                                >
                                    <?= $pauseIsCurrent
                                        ? 'Aktuell'
                                        : (
                                            $pauseIsFuture
                                                ? 'Geplant'
                                                : 'Beendet'
                                        ) ?>
                                </span>
                            </div>

                            <?php if (
                                !empty(
                                    $pause['reason']
                                )
                            ): ?>
                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    <?= e($pause['reason']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-1 text-xs text-slate-400">
                                Hinterlegt
                                <?= e(
                                    contract_format_date(
                                        $pause['created_at']
                                    )
                                ) ?>

                                <?php if (
                                    !empty(
                                        $pause[
                                            'created_by_name'
                                        ]
                                    )
                                ): ?>
                                    ·
                                    <?= e(
                                        $pause[
                                            'created_by_name'
                                        ]
                                    ) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (
                            has_permission(
                                'contracts.edit'
                            )
                        ): ?>
                            <form
                                method="post"
                                action="/contracts/<?= (int) $contract['id'] ?>/pauses/<?= (int) $pause['id'] ?>/delete"
                                data-confirm
                                data-confirm-title="Vertragspause entfernen?"
                                data-confirm-message="Der Pausezeitraum wird aus der Planung entfernt. Bereits berechnete Ansichten werden danach wieder anhand des normalen Abbuchungsplans erstellt."
                                data-confirm-label="Pause entfernen"
                                data-confirm-variant="warning"
                            >
                                <?= csrf_field() ?>

                                <button
                                    type="submit"
                                    class="
                                        rounded-lg
                                        border
                                        border-slate-300
                                        px-3
                                        py-2
                                        text-sm
                                        font-semibold
                                        text-slate-700
                                        hover:bg-white
                                        dark:border-slate-700
                                        dark:text-slate-200
                                        dark:hover:bg-slate-800
                                    "
                                >
                                    Entfernen
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>


    <div
        class="
            mt-6
            overflow-hidden
            rounded-2xl
            border
            border-slate-200
            bg-white
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <div
            class="
                flex
                items-start
                justify-between
                gap-4
                border-b
                border-slate-200
                px-5
                py-5
                sm:px-6
                dark:border-slate-800
            "
        >

            <div class="min-w-0">

                <h2 class="text-lg font-semibold">
                    Dokumente
                </h2>

                <p
                    class="
                        mt-1
                        text-sm
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    <?= count($documents) ?>
                    Dokument(e) hinterlegt.

                    <?php if (
                        !empty($documents)
                        && has_permission(
                            'documents.view'
                        )
                    ): ?>
                        Dokumente können direkt im Browser angesehen werden.
                    <?php endif; ?>
                </p>

            </div>


            <?php if (
                has_permission(
                    'documents.upload'
                )
            ): ?>

                <button
                    type="button"
                    data-document-upload-open
                    aria-label="Dokument hinzufügen"
                    title="Dokument hinzufügen"
                    class="
                        flex
                        h-10
                        w-10
                        shrink-0
                        items-center
                        justify-center
                        rounded-xl
                        bg-blue-600
                        text-white
                        shadow-sm
                        transition
                        hover:bg-blue-700
                        focus:outline-none
                        focus:ring-4
                        focus:ring-blue-200
                        dark:hover:bg-blue-500
                        dark:focus:ring-blue-950
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
                            d="M12 5v14M5 12h14"
                        />
                    </svg>
                </button>

            <?php endif; ?>

        </div>


        <?php if (empty($documents)): ?>

            <div
                class="
                    px-6
                    py-12
                    text-center
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Für diesen Vertrag wurden noch keine Dokumente hinterlegt.
            </div>

        <?php else: ?>

            <div
                class="
                    divide-y
                    divide-slate-200
                    dark:divide-slate-800
                "
            >

                <?php foreach ($documents as $document): ?>

                    <?php
                    $documentDisplayName =
                        $document[
                            'document_name'
                        ]
                        ?: $document[
                            'original_filename'
                        ];

                    $previewMode =
                        document_preview_mode(
                            $document
                        );

                    $previewable =
                        document_is_previewable(
                            $document
                        );

                    $canViewDocument =
                        has_permission(
                            'documents.view'
                        );
                    ?>

                    <div
                        <?php if (
                            $canViewDocument
                        ): ?>
                            data-document-open
                            data-document-id="<?= (int) $document['id'] ?>"
                            data-document-name="<?= e($documentDisplayName) ?>"
                            data-document-filename="<?= e($document['original_filename']) ?>"
                            data-document-extension="<?= e(strtoupper($document['file_extension'])) ?>"
                            data-document-size="<?= e(
                                format_file_size(
                                    (int) $document['file_size']
                                )
                            ) ?>"
                            data-document-preview-mode="<?= e($previewMode) ?>"
                            data-document-preview-url="/documents/<?= (int) $document['id'] ?>/preview"
                            data-document-preview-info-url="/documents/<?= (int) $document['id'] ?>/preview-info"
                            data-document-preview-page-url="/documents/<?= (int) $document['id'] ?>/preview-page"
                            data-document-download-url="/documents/<?= (int) $document['id'] ?>/download"
                            role="button"
                            tabindex="0"
                            aria-label="<?= e(
                                'Dokument '
                                . $documentDisplayName
                                . ' öffnen'
                            ) ?>"
                        <?php endif; ?>

                        class="
                            flex
                            flex-col
                            justify-between
                            gap-4
                            px-5
                            py-5
                            sm:flex-row
                            sm:items-center
                            sm:px-6
                            <?= $canViewDocument
                                ? 'cursor-pointer transition hover:bg-slate-50 focus:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 dark:hover:bg-slate-800/60 dark:focus:bg-blue-950/40'
                                : '' ?>
                        "
                    >

                        <div
                            class="
                                flex
                                min-w-0
                                items-start
                                gap-3
                            "
                        >

                            <div
                                class="
                                    flex
                                    h-11
                                    w-11
                                    shrink-0
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-slate-100
                                    text-xs
                                    font-bold
                                    text-slate-600
                                    dark:bg-slate-800
                                    dark:text-slate-300
                                "
                            >
                                <?= e(
                                    strtoupper(
                                        $document[
                                            'file_extension'
                                        ]
                                    )
                                ) ?>
                            </div>


                            <div class="min-w-0">

                                <div
                                    class="
                                        truncate
                                        font-semibold
                                        text-slate-900
                                        dark:text-white
                                    "
                                >
                                    <?= e($documentDisplayName) ?>
                                </div>

                                <div
                                    class="
                                        mt-1
                                        flex
                                        flex-wrap
                                        items-center
                                        gap-1.5
                                        text-xs
                                        text-slate-500
                                        dark:text-slate-400
                                    "
                                >
                                    <?php if (
                                        !empty(
                                            $document[
                                                'document_type_name'
                                            ]
                                        )
                                    ): ?>
                                        <span
                                            class="
                                                rounded-full
                                                bg-blue-50
                                                px-2
                                                py-0.5
                                                font-medium
                                                text-blue-700
                                                dark:bg-blue-950/40
                                                dark:text-blue-300
                                            "
                                        >
                                            <?= e(
                                                $document[
                                                    'document_type_name'
                                                ]
                                            ) ?>
                                        </span>
                                    <?php endif; ?>

                                    <span>
                                        Version
                                        <?= (int) (
                                            $document[
                                                'version_no'
                                            ] ?? 1
                                        ) ?>
                                    </span>

                                    <span>·</span>

                                    <span>
                                        <?= e(
                                            $document[
                                                'original_filename'
                                            ]
                                        ) ?>
                                    </span>

                                    <span>·</span>

                                    <span>
                                        <?= e(
                                            format_file_size(
                                                (int) $document[
                                                    'file_size'
                                                ]
                                            )
                                        ) ?>
                                    </span>

                                    <span>·</span>

                                    <span>
                                        <?= e(
                                            contract_format_date(
                                                $document[
                                                    'document_date'
                                                ]
                                                ?? $document[
                                                    'created_at'
                                                ]
                                            )
                                        ) ?>
                                    </span>

                                    <?php if (
                                        !empty(
                                            $document[
                                                'uploaded_by_name'
                                            ]
                                        )
                                    ): ?>
                                        <span>·</span>
                                        <span>
                                            <?= e(
                                                $document[
                                                    'uploaded_by_name'
                                                ]
                                            ) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>


                                <?php if (
                                    $canViewDocument
                                ): ?>

                                    <div
                                        class="
                                            mt-2
                                            text-xs
                                            font-medium
                                            <?= $previewable
                                                ? 'text-blue-600 dark:text-blue-400'
                                                : 'text-slate-400 dark:text-slate-500' ?>
                                        "
                                    >
                                        <?= $previewable
                                            ? (
                                                $previewMode === 'office'
                                                    ? 'Lokale Dokumentvorschau verfügbar'
                                                    : 'Direkte Browser-Vorschau verfügbar'
                                            )
                                            : 'Für diesen Dateityp ist keine direkte Browser-Vorschau verfügbar' ?>
                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>


                        <div
                            class="
                                flex
                                shrink-0
                                flex-wrap
                                items-center
                                gap-2
                            "
                        >

                            <?php if (
                                $canViewDocument
                            ): ?>

                                <button
                                    type="button"
                                    data-document-open-button
                                    data-document-id="<?= (int) $document['id'] ?>"
                                    class="
                                        rounded-lg
                                        bg-blue-600
                                        px-3
                                        py-2
                                        text-sm
                                        font-semibold
                                        text-white
                                        transition
                                        hover:bg-blue-700
                                        dark:hover:bg-blue-500
                                    "
                                >
                                    Ansehen
                                </button>


                                <a
                                    href="/documents/<?= (int) $document['id'] ?>/download"
                                    class="
                                        rounded-lg
                                        border
                                        border-slate-300
                                        px-3
                                        py-2
                                        text-sm
                                        font-semibold
                                        text-slate-700
                                        hover:bg-slate-50
                                        dark:border-slate-700
                                        dark:text-slate-200
                                        dark:hover:bg-slate-800
                                    "
                                >
                                    Herunterladen
                                </a>

                            <?php endif; ?>


                            <?php if (
                                has_permission(
                                    'documents.delete'
                                )
                            ): ?>

                                <form
                                    method="post"
                                    action="/documents/<?= (int) $document['id'] ?>/delete"
                                    data-confirm
                                    data-confirm-title="Dokument entfernen?"
                                    data-confirm-message="Das Dokument wird aus dem Vertrag entfernt."
                                    data-confirm-label="Dokument entfernen"
                                    data-confirm-variant="danger"
                                >

                                    <?= csrf_field() ?>

                                    <button
                                        type="submit"
                                        class="
                                            rounded-lg
                                            border
                                            border-red-200
                                            px-3
                                            py-2
                                            text-sm
                                            font-semibold
                                            text-red-700
                                            hover:bg-red-50
                                            dark:border-red-900
                                            dark:text-red-400
                                            dark:hover:bg-red-950
                                        "
                                    >
                                        Entfernen
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <?php if (
            !empty($documentHistory)
        ): ?>

            <div
                class="
                    border-t
                    border-slate-200
                    px-5
                    py-5
                    sm:px-6
                    dark:border-slate-800
                "
            >

                <details class="group">

                    <summary
                        class="
                            flex
                            cursor-pointer
                            list-none
                            items-center
                            justify-between
                            gap-4
                        "
                    >
                        <div>
                            <div class="font-semibold">
                                Dokumenthistorie
                            </div>
                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Alle Versionen und auch entfernte Dokumenteinträge nachvollziehen.
                            </div>
                        </div>

                        <span
                            class="
                                text-sm
                                font-semibold
                                text-blue-600
                                transition
                                group-open:rotate-180
                                dark:text-blue-400
                            "
                        >
                            ⌄
                        </span>
                    </summary>


                    <div class="mt-5 space-y-3">

                        <?php foreach (
                            $documentHistory
                            as $historyDocument
                        ): ?>

                            <?php
                            $historyCanOpen =
                                empty(
                                    $historyDocument[
                                        'deleted_at'
                                    ]
                                )
                                && has_permission(
                                    'documents.view'
                                );

                            $historyPreviewMode =
                                document_preview_mode(
                                    $historyDocument
                                );

                            $historyDisplayName =
                                $historyDocument[
                                    'document_name'
                                ]
                                ?: $historyDocument[
                                    'original_filename'
                                ];
                            ?>

                            <div
                                <?php if (
                                    $historyCanOpen
                                ): ?>
                                    data-document-open
                                    data-document-id="<?= (int) $historyDocument['id'] ?>"
                                    data-document-name="<?= e($historyDisplayName) ?>"
                                    data-document-filename="<?= e($historyDocument['original_filename']) ?>"
                                    data-document-extension="<?= e(strtoupper($historyDocument['file_extension'])) ?>"
                                    data-document-size="<?= e(
                                        format_file_size(
                                            (int) $historyDocument[
                                                'file_size'
                                            ]
                                        )
                                    ) ?>"
                                    data-document-preview-mode="<?= e($historyPreviewMode) ?>"
                                    data-document-preview-url="/documents/<?= (int) $historyDocument['id'] ?>/preview"
                                    data-document-preview-info-url="/documents/<?= (int) $historyDocument['id'] ?>/preview-info"
                                    data-document-preview-page-url="/documents/<?= (int) $historyDocument['id'] ?>/preview-page"
                                    data-document-download-url="/documents/<?= (int) $historyDocument['id'] ?>/download"
                                    role="button"
                                    tabindex="0"
                                <?php endif; ?>

                                class="
                                    flex
                                    flex-col
                                    justify-between
                                    gap-3
                                    rounded-xl
                                    border
                                    border-slate-200
                                    p-4
                                    sm:flex-row
                                    sm:items-center
                                    dark:border-slate-800
                                    <?= $historyCanOpen
                                        ? 'cursor-pointer transition hover:border-blue-300 hover:bg-slate-50 dark:hover:border-blue-700 dark:hover:bg-slate-800/50'
                                        : '' ?>
                                "
                            >

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold">
                                            <?= e(
                                                $historyDocument[
                                                    'document_name'
                                                ]
                                                ?: $historyDocument[
                                                    'original_filename'
                                                ]
                                            ) ?>
                                        </span>

                                        <span
                                            class="
                                                rounded-full
                                                bg-slate-100
                                                px-2
                                                py-0.5
                                                text-[11px]
                                                font-semibold
                                                text-slate-600
                                                dark:bg-slate-800
                                                dark:text-slate-300
                                            "
                                        >
                                            <?= e(
                                                $historyDocument[
                                                    'document_type_name'
                                                ] ?? 'Sonstiges'
                                            ) ?>
                                        </span>

                                        <span
                                            class="
                                                rounded-full
                                                px-2
                                                py-0.5
                                                text-[11px]
                                                font-semibold
                                                <?= !empty(
                                                    $historyDocument[
                                                        'deleted_at'
                                                    ]
                                                )
                                                    ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                                    : (
                                                        (int) $historyDocument[
                                                            'is_current'
                                                        ] === 1
                                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                            : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                                    ) ?>
                                            "
                                        >
                                            <?= !empty(
                                                $historyDocument[
                                                    'deleted_at'
                                                ]
                                            )
                                                ? 'Entfernt'
                                                : (
                                                    (int) $historyDocument[
                                                        'is_current'
                                                    ] === 1
                                                        ? 'Aktuell'
                                                        : 'Ersetzt'
                                                ) ?>
                                        </span>
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Version
                                        <?= (int) (
                                            $historyDocument[
                                                'version_no'
                                            ] ?? 1
                                        ) ?>
                                        ·
                                        <?= e(
                                            contract_format_date(
                                                $historyDocument[
                                                    'document_date'
                                                ]
                                                ?? $historyDocument[
                                                    'created_at'
                                                ]
                                            )
                                        ) ?>
                                        ·
                                        <?= e(
                                            $historyDocument[
                                                'original_filename'
                                            ]
                                        ) ?>
                                    </div>
                                </div>

                                <div class="text-xs text-slate-400">
                                    <?= e(
                                        $historyDocument[
                                            'uploaded_by_name'
                                        ] ?? ''
                                    ) ?>
                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </details>

            </div>

        <?php endif; ?>

    </div>

</section>


<?php if (
    has_permission(
        'contracts.edit'
    )
): ?>

    <div
        data-contract-cancel-modal
        data-contract-cancel-open-on-load="<?= !empty(
            $openCancelModal
        ) ? '1' : '0' ?>"
        class="
            fixed
            inset-0
            z-[112]
            hidden
            items-center
            justify-center
            bg-slate-950/75
            p-4
            backdrop-blur-sm
        "
        aria-hidden="true"
    >
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="contract-cancel-title"
            class="
                max-h-[92vh]
                w-full
                max-w-lg
                overflow-y-auto
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-2xl
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
                    border-b
                    border-slate-200
                    px-5
                    py-5
                    dark:border-slate-800
                "
            >
                <div>
                    <div
                        class="
                            text-xs
                            font-semibold
                            uppercase
                            tracking-widest
                            text-amber-600
                            dark:text-amber-400
                        "
                    >
                        Vertragsende
                    </div>

                    <h2
                        id="contract-cancel-title"
                        class="mt-1 text-xl font-bold"
                    >
                        Kündigung hinterlegen
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Der Vertrag bleibt bis zum angegebenen Datum aktiv in
                        den Planungen und wird erst danach historisch.
                    </p>
                </div>

                <button
                    type="button"
                    data-contract-cancel-close
                    aria-label="Kündigungsfenster schließen"
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
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>
            </div>


            <form
                method="post"
                action="/contracts/<?= (int) $contract['id'] ?>/cancel"
                class="p-5"
            >
                <?= csrf_field() ?>

                <?php if (!empty($cancelError)): ?>
                    <div
                        class="
                            mb-5
                            rounded-xl
                            border
                            border-red-200
                            bg-red-50
                            px-4
                            py-3
                            text-sm
                            text-red-800
                            dark:border-red-900
                            dark:bg-red-950
                            dark:text-red-300
                        "
                    >
                        <?= e($cancelError) ?>
                    </div>
                <?php endif; ?>

                <label
                    for="cancellation_effective_date"
                    class="mb-2 block text-sm font-medium"
                >
                    Vertrag gekündigt zum *
                </label>

                <input
                    id="cancellation_effective_date"
                    type="date"
                    name="cancellation_effective_date"
                    required
                    value="<?= e(
                        $cancelFormValues[
                            'cancellation_effective_date'
                        ]
                        ?? $contract[
                            'cancellation_effective_date'
                        ]
                        ?? $deadlineInfo[
                            'end_date'
                        ]
                        ?? $contract[
                            'end_date'
                        ]
                        ?? $today->format(
                            'Y-m-d'
                        )
                    ) ?>"
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

                <div
                    class="
                        mt-4
                        rounded-xl
                        bg-slate-50
                        p-4
                        text-sm
                        leading-6
                        text-slate-600
                        dark:bg-slate-800/70
                        dark:text-slate-300
                    "
                >
                    Beispiel: Wird heute eine Kündigung zum
                    <strong>31.12.2026</strong> hinterlegt, bleiben
                    Abbuchungen bis einschließlich 31.12.2026 in der
                    Ausgabenplanung enthalten. Danach wird der Vertrag nicht
                    mehr als laufend behandelt.
                </div>

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
                        data-contract-cancel-close
                        class="
                            rounded-xl
                            border
                            border-slate-300
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-slate-700
                            dark:border-slate-700
                            dark:text-slate-200
                        "
                    >
                        Abbrechen
                    </button>

                    <button
                        type="submit"
                        class="
                            rounded-xl
                            bg-amber-600
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-white
                            hover:bg-amber-700
                            dark:hover:bg-amber-500
                        "
                    >
                        Kündigung speichern
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div
        data-contract-pause-modal
        data-contract-pause-open-on-load="<?= !empty(
            $openPauseModal
        ) ? '1' : '0' ?>"
        class="
            fixed
            inset-0
            z-[113]
            hidden
            items-center
            justify-center
            bg-slate-950/75
            p-4
            backdrop-blur-sm
        "
        aria-hidden="true"
    >
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="contract-pause-title"
            class="
                max-h-[92vh]
                w-full
                max-w-lg
                overflow-y-auto
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-2xl
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
                    border-b
                    border-slate-200
                    px-5
                    py-5
                    dark:border-slate-800
                "
            >
                <div>
                    <div
                        class="
                            text-xs
                            font-semibold
                            uppercase
                            tracking-widest
                            text-violet-600
                            dark:text-violet-400
                        "
                    >
                        Unterbrechung
                    </div>

                    <h2
                        id="contract-pause-title"
                        class="mt-1 text-xl font-bold"
                    >
                        Vertrag pausieren
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Von- und Bis-Datum sind Pflicht. Planmäßige
                        Abbuchungen in diesem Zeitraum werden übersprungen.
                    </p>
                </div>

                <button
                    type="button"
                    data-contract-pause-close
                    aria-label="Pausefenster schließen"
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
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>
            </div>


            <form
                method="post"
                action="/contracts/<?= (int) $contract['id'] ?>/pause"
                class="p-5"
            >
                <?= csrf_field() ?>

                <?php if (!empty($pauseError)): ?>
                    <div
                        class="
                            mb-5
                            rounded-xl
                            border
                            border-red-200
                            bg-red-50
                            px-4
                            py-3
                            text-sm
                            text-red-800
                            dark:border-red-900
                            dark:bg-red-950
                            dark:text-red-300
                        "
                    >
                        <?= e($pauseError) ?>
                    </div>
                <?php endif; ?>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            for="contract_pause_from"
                            class="mb-2 block text-sm font-medium"
                        >
                            Von *
                        </label>

                        <input
                            id="contract_pause_from"
                            type="date"
                            name="pause_from"
                            required
                            value="<?= e(
                                $pauseFormValues[
                                    'pause_from'
                                ]
                                ?? $today->format(
                                    'Y-m-d'
                                )
                            ) ?>"
                            class="
                                h-11
                                w-full
                                rounded-xl
                                border
                                border-slate-300
                                bg-white
                                px-4
                                dark:border-slate-700
                                dark:bg-slate-800
                            "
                        >
                    </div>

                    <div>
                        <label
                            for="contract_pause_to"
                            class="mb-2 block text-sm font-medium"
                        >
                            Bis *
                        </label>

                        <input
                            id="contract_pause_to"
                            type="date"
                            name="pause_to"
                            required
                            value="<?= e(
                                $pauseFormValues[
                                    'pause_to'
                                ] ?? ''
                            ) ?>"
                            class="
                                h-11
                                w-full
                                rounded-xl
                                border
                                border-slate-300
                                bg-white
                                px-4
                                dark:border-slate-700
                                dark:bg-slate-800
                            "
                        >
                    </div>
                </div>

                <div class="mt-5">
                    <label
                        for="contract_pause_reason"
                        class="mb-2 block text-sm font-medium"
                    >
                        Grund / Notiz
                    </label>

                    <input
                        id="contract_pause_reason"
                        type="text"
                        name="reason"
                        maxlength="500"
                        value="<?= e(
                            $pauseFormValues[
                                'reason'
                            ] ?? ''
                        ) ?>"
                        placeholder="z. B. saisonale Stilllegung"
                        class="
                            h-11
                            w-full
                            rounded-xl
                            border
                            border-slate-300
                            bg-white
                            px-4
                            dark:border-slate-700
                            dark:bg-slate-800
                            dark:placeholder:text-slate-500
                        "
                    >
                </div>

                <div
                    class="
                        mt-4
                        rounded-xl
                        bg-violet-50
                        p-4
                        text-sm
                        leading-6
                        text-violet-900
                        dark:bg-violet-950/40
                        dark:text-violet-200
                    "
                >
                    Die Pause verändert den Vertragspreis nicht. Sie sorgt
                    ausschließlich dafür, dass planmäßige Abbuchungen innerhalb
                    dieses Zeitraums in Kostenplanung und bisheriger
                    Kostenberechnung nicht angesetzt werden.
                </div>

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
                        data-contract-pause-close
                        class="
                            rounded-xl
                            border
                            border-slate-300
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-slate-700
                            dark:border-slate-700
                            dark:text-slate-200
                        "
                    >
                        Abbrechen
                    </button>

                    <button
                        type="submit"
                        class="
                            rounded-xl
                            bg-violet-600
                            px-4
                            py-2.5
                            text-sm
                            font-semibold
                            text-white
                            hover:bg-violet-700
                            dark:hover:bg-violet-500
                        "
                    >
                        Pause speichern
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        data-price-history-modal
        data-price-history-open-on-load="<?= !empty(
            $openPriceHistoryModal
        ) ? '1' : '0' ?>"
        class="
            fixed
            inset-0
            z-[114]
            hidden
            items-center
            justify-center
            bg-slate-950/75
            p-4
            backdrop-blur-sm
        "
        aria-hidden="true"
    >

        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="price-history-title"
            class="
                max-h-[92vh]
                w-full
                max-w-lg
                overflow-y-auto
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-2xl
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
                    border-b
                    border-slate-200
                    px-5
                    py-5
                    dark:border-slate-800
                "
            >
                <div>
                    <div class="text-xs font-semibold uppercase tracking-widest text-blue-600 dark:text-blue-400">
                        Kostenhistorie
                    </div>

                    <h2
                        id="price-history-title"
                        class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        Preisstand hinzufügen
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Historische oder aktuelle Preisänderungen nachvollziehbar speichern.
                    </p>
                </div>

                <button
                    type="button"
                    data-price-history-close
                    aria-label="Preisfenster schließen"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white"
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


            <form
                method="post"
                action="/contracts/<?= (int) $contract['id'] ?>/prices"
                class="p-5"
            >
                <?= csrf_field() ?>

                <?php if (!empty($priceError)): ?>
                    <div
                        class="
                            mb-5
                            rounded-xl
                            border
                            border-red-200
                            bg-red-50
                            px-4
                            py-3
                            text-sm
                            text-red-800
                            dark:border-red-900
                            dark:bg-red-950
                            dark:text-red-300
                        "
                    >
                        <?= e($priceError) ?>
                    </div>
                <?php endif; ?>

                <div class="grid gap-4 sm:grid-cols-2">

                    <div>
                        <label
                            for="price_history_amount"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                        >
                            Betrag *
                        </label>

                        <input
                            id="price_history_amount"
                            type="number"
                            name="amount"
                            required
                            min="0"
                            step="0.01"
                            value="<?= e(
                                (string) $contract[
                                    'amount'
                                ]
                            ) ?>"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 dark:border-slate-700 dark:bg-slate-800"
                        >
                    </div>

                    <div>
                        <label
                            for="price_history_valid_from"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                        >
                            Gültig ab *
                        </label>

                        <input
                            id="price_history_valid_from"
                            type="date"
                            name="valid_from"
                            required
                            value="<?= e(
                                (
                                    new DateTimeImmutable(
                                        'today'
                                    )
                                )->format(
                                    'Y-m-d'
                                )
                            ) ?>"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 dark:border-slate-700 dark:bg-slate-800"
                        >
                    </div>

                </div>


                <div class="mt-5">

                    <label
                        for="price_history_frequency"
                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        Abrechnungsintervall *
                    </label>

                    <select
                        id="price_history_frequency"
                        name="billing_frequency"
                        required
                        class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <?php foreach (
                            [
                                'monthly' =>
                                    'Monatlich',
                                'quarterly' =>
                                    'Vierteljährlich',
                                'semiannual' =>
                                    'Halbjährlich',
                                'annual' =>
                                    'Jährlich',
                                'one_time' =>
                                    'Einmalig',
                                'custom' =>
                                    'Individuell',
                            ]
                            as $frequencyValue
                            => $frequencyLabel
                        ): ?>
                            <option
                                value="<?= e(
                                    $frequencyValue
                                ) ?>"
                                <?= $contract[
                                    'billing_frequency'
                                ] === $frequencyValue
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= e(
                                    $frequencyLabel
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="mt-5">

                    <label
                        for="price_history_custom_months"
                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        Individuelles Intervall in Monaten
                    </label>

                    <input
                        id="price_history_custom_months"
                        type="number"
                        name="custom_billing_months"
                        min="1"
                        value="<?= e(
                            (string) (
                                $contract[
                                    'custom_billing_months'
                                ] ?? ''
                            )
                        ) ?>"
                        class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>


                <div class="mt-5">

                    <label
                        for="price_history_reason"
                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        Grund / Notiz
                    </label>

                    <input
                        id="price_history_reason"
                        type="text"
                        name="change_reason"
                        maxlength="500"
                        placeholder="z. B. Preiserhöhung ab 01.10.2026"
                        class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 dark:border-slate-700 dark:bg-slate-800 dark:placeholder:text-slate-500"
                    >
                </div>


                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        data-price-history-close
                        class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Abbrechen
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 dark:hover:bg-blue-500"
                    >
                        Preisstand speichern
                    </button>
                </div>

            </form>

        </div>

    </div>

<?php endif; ?>


<?php if (
    has_permission(
        'documents.upload'
    )
): ?>

    <div
        data-document-upload-modal
        data-document-upload-open-on-load="<?= !empty($error)
            ? '1'
            : '0' ?>"
        class="
            fixed
            inset-0
            z-[115]
            hidden
            items-center
            justify-center
            bg-slate-950/75
            p-4
            backdrop-blur-sm
        "
        aria-hidden="true"
    >

        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="document-upload-title"
            class="
                max-h-[92vh]
                w-full
                max-w-lg
                overflow-y-auto
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-2xl
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
                    border-b
                    border-slate-200
                    px-5
                    py-5
                    dark:border-slate-800
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
                        Dokumente
                    </div>

                    <h2
                        id="document-upload-title"
                        class="
                            mt-1
                            text-xl
                            font-bold
                            text-slate-900
                            dark:text-white
                        "
                    >
                        Neues Dokument hinzufügen
                    </h2>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-slate-500
                            dark:text-slate-400
                        "
                    >
                        PDF, Word, Excel, JPG, PNG oder TXT · maximal 20 MB.
                    </p>

                </div>


                <button
                    type="button"
                    data-document-upload-close
                    aria-label="Upload schließen"
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


            <form
                method="post"
                action="/contracts/<?= (int) $contract['id'] ?>/documents"
                enctype="multipart/form-data"
                class="p-5"
                data-document-upload-form
            >

                <?= csrf_field() ?>


                <?php if (!empty($error)): ?>

                    <div
                        class="
                            mb-5
                            rounded-xl
                            border
                            border-red-200
                            bg-red-50
                            px-4
                            py-3
                            text-sm
                            text-red-800
                            dark:border-red-900
                            dark:bg-red-950
                            dark:text-red-300
                        "
                    >
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <div class="grid gap-4 sm:grid-cols-2">

                    <div>
                        <label
                            for="document_upload_type"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                        >
                            Dokumentart *
                        </label>

                        <select
                            id="document_upload_type"
                            name="document_type_id"
                            required
                            class="
                                h-11
                                w-full
                                rounded-xl
                                border
                                border-slate-300
                                bg-white
                                px-4
                                text-sm
                                text-slate-900
                                dark:border-slate-700
                                dark:bg-slate-800
                                dark:text-white
                            "
                        >
                            <?php foreach (
                                $documentTypes
                                as $documentType
                            ): ?>
                                <option
                                    value="<?= (int) $documentType['id'] ?>"
                                    <?= $documentType['name'] === 'Sonstiges'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= e($documentType['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label
                            for="document_upload_date"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                        >
                            Dokumentdatum
                        </label>

                        <input
                            id="document_upload_date"
                            type="date"
                            name="document_date"
                            value="<?= e(
                                (
                                    new DateTimeImmutable(
                                        'today'
                                    )
                                )->format(
                                    'Y-m-d'
                                )
                            ) ?>"
                            class="
                                h-11
                                w-full
                                rounded-xl
                                border
                                border-slate-300
                                bg-white
                                px-4
                                text-sm
                                text-slate-900
                                dark:border-slate-700
                                dark:bg-slate-800
                                dark:text-white
                            "
                        >
                    </div>

                </div>


                <div class="mt-5">

                    <label
                        for="document_upload_replaces"
                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        Neue Version von
                    </label>

                    <select
                        id="document_upload_replaces"
                        name="replaces_document_id"
                        class="
                            h-11
                            w-full
                            rounded-xl
                            border
                            border-slate-300
                            bg-white
                            px-4
                            text-sm
                            text-slate-900
                            dark:border-slate-700
                            dark:bg-slate-800
                            dark:text-white
                        "
                    >
                        <option value="">
                            Neues eigenständiges Dokument
                        </option>

                        <?php foreach (
                            $documents
                            as $existingDocument
                        ): ?>
                            <option
                                value="<?= (int) $existingDocument['id'] ?>"
                            >
                                <?= e(
                                    $existingDocument[
                                        'document_name'
                                    ]
                                    ?: $existingDocument[
                                        'original_filename'
                                    ]
                                ) ?>
                                · Version
                                <?= (int) (
                                    $existingDocument[
                                        'version_no'
                                    ] ?? 1
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        Wird ein bestehendes Dokument gewählt, bleibt die alte Version
                        in der Dokumenthistorie erhalten.
                    </p>

                </div>


                <div class="mt-5">

                    <label
                        for="document_upload_name"
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-slate-700
                            dark:text-slate-200
                        "
                    >
                        Bezeichnung
                    </label>

                    <input
                        id="document_upload_name"
                        type="text"
                        name="document_name"
                        placeholder="z. B. Versicherungsschein"
                        autocomplete="off"
                        class="
                            h-11
                            w-full
                            rounded-xl
                            border
                            border-slate-300
                            bg-white
                            px-4
                            text-sm
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

                    <p
                        class="
                            mt-2
                            text-xs
                            text-slate-500
                            dark:text-slate-400
                        "
                    >
                        Optional. Ohne Bezeichnung wird der Dateiname verwendet.
                    </p>

                </div>


                <div class="mt-5">

                    <label
                        for="document_upload_file"
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-slate-700
                            dark:text-slate-200
                        "
                    >
                        Datei *
                    </label>

                    <label
                        for="document_upload_file"
                        class="
                            flex
                            cursor-pointer
                            flex-col
                            items-center
                            justify-center
                            rounded-2xl
                            border-2
                            border-dashed
                            border-slate-300
                            bg-slate-50
                            px-5
                            py-8
                            text-center
                            transition
                            hover:border-blue-400
                            hover:bg-blue-50
                            dark:border-slate-700
                            dark:bg-slate-800/60
                            dark:hover:border-blue-600
                            dark:hover:bg-blue-950/30
                        "
                    >

                        <div
                            class="
                                flex
                                h-11
                                w-11
                                items-center
                                justify-center
                                rounded-xl
                                bg-white
                                text-blue-600
                                shadow-sm
                                dark:bg-slate-900
                                dark:text-blue-400
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
                                    d="M12 16V4m0 0L8 8m4-4 4 4M5 13v5a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5"
                                />
                            </svg>
                        </div>

                        <div
                            class="
                                mt-3
                                text-sm
                                font-semibold
                                text-slate-900
                                dark:text-white
                            "
                        >
                            Datei auswählen
                        </div>

                        <div
                            data-document-upload-file-name
                            class="
                                mt-1
                                max-w-full
                                truncate
                                text-xs
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            Noch keine Datei ausgewählt
                        </div>

                    </label>

                    <input
                        id="document_upload_file"
                        type="file"
                        name="document"
                        required
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt"
                        class="sr-only"
                        data-document-upload-file
                    >

                </div>


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
                        data-document-upload-close
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
                        type="submit"
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
                            focus:outline-none
                            focus:ring-4
                            focus:ring-blue-200
                            dark:hover:bg-blue-500
                            dark:focus:ring-blue-950
                        "
                    >
                        Dokument hochladen
                    </button>

                </div>

            </form>

        </div>

    </div>

<?php endif; ?>


<?php if (
    !empty($documents)
    && has_permission(
        'documents.view'
    )
): ?>

    <div
        data-document-preview-modal
        class="
            fixed
            inset-0
            z-[120]
            hidden
            items-center
            justify-center
            bg-slate-950/80
            backdrop-blur-sm
            sm:p-4
        "
        aria-hidden="true"
    >

        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="document-preview-title"
            class="
                flex
                h-[100dvh]
                w-full
                flex-col
                overflow-hidden
                bg-white
                shadow-2xl
                sm:h-[88vh]
                sm:max-w-6xl
                sm:rounded-2xl
                sm:border
                sm:border-slate-200
                dark:bg-slate-900
                sm:dark:border-slate-700
            "
        >

            <div
                class="
                    flex
                    shrink-0
                    items-start
                    justify-between
                    gap-4
                    border-b
                    border-slate-200
                    px-4
                    py-4
                    sm:px-5
                    dark:border-slate-800
                "
            >

                <div class="min-w-0">

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
                        Dokumentvorschau
                    </div>

                    <h2
                        id="document-preview-title"
                        data-document-preview-title
                        class="
                            mt-1
                            truncate
                            text-lg
                            font-bold
                            text-slate-900
                            sm:text-xl
                            dark:text-white
                        "
                    >
                        Dokument
                    </h2>

                    <div
                        data-document-preview-meta
                        class="
                            mt-1
                            truncate
                            text-xs
                            text-slate-500
                            sm:text-sm
                            dark:text-slate-400
                        "
                    ></div>

                </div>


                <button
                    type="button"
                    data-document-preview-close
                    aria-label="Dokumentvorschau schließen"
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


            <div
                class="
                    relative
                    min-h-0
                    flex-1
                    overflow-hidden
                    bg-slate-100
                    dark:bg-slate-950
                "
            >

                <div
                    data-document-preview-loading
                    class="
                        absolute
                        inset-0
                        z-10
                        flex
                        items-center
                        justify-center
                        bg-slate-100
                        text-sm
                        text-slate-500
                        dark:bg-slate-950
                        dark:text-slate-400
                    "
                >
                    Vorschau wird geladen …
                </div>


                <div
                    data-document-preview-pdf-wrap
                    class="
                        hidden
                        h-full
                        min-h-0
                        flex-col
                    "
                >

                    <div
                        class="
                            flex
                            shrink-0
                            flex-wrap
                            items-center
                            justify-between
                            gap-2
                            border-b
                            border-slate-200
                            bg-white
                            px-3
                            py-2
                            sm:px-4
                            dark:border-slate-800
                            dark:bg-slate-900
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <button
                                type="button"
                                data-document-pdf-prev
                                class="
                                    rounded-lg
                                    border
                                    border-slate-300
                                    px-3
                                    py-2
                                    text-xs
                                    font-semibold
                                    text-slate-700
                                    disabled:cursor-not-allowed
                                    disabled:opacity-40
                                    dark:border-slate-700
                                    dark:text-slate-200
                                "
                            >
                                ←
                            </button>

                            <div
                                data-document-pdf-page-label
                                class="
                                    min-w-24
                                    text-center
                                    text-xs
                                    font-semibold
                                    text-slate-600
                                    sm:text-sm
                                    dark:text-slate-300
                                "
                            >
                                Seite 1 / 1
                            </div>

                            <button
                                type="button"
                                data-document-pdf-next
                                class="
                                    rounded-lg
                                    border
                                    border-slate-300
                                    px-3
                                    py-2
                                    text-xs
                                    font-semibold
                                    text-slate-700
                                    disabled:cursor-not-allowed
                                    disabled:opacity-40
                                    dark:border-slate-700
                                    dark:text-slate-200
                                "
                            >
                                →
                            </button>
                        </div>


                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >
                            <button
                                type="button"
                                data-document-pdf-zoom-out
                                class="
                                    rounded-lg
                                    border
                                    border-slate-300
                                    px-3
                                    py-2
                                    text-xs
                                    font-semibold
                                    text-slate-700
                                    dark:border-slate-700
                                    dark:text-slate-200
                                "
                            >
                                −
                            </button>

                            <div
                                data-document-pdf-zoom-label
                                class="
                                    w-12
                                    text-center
                                    text-xs
                                    font-semibold
                                    text-slate-600
                                    dark:text-slate-300
                                "
                            >
                                100 %
                            </div>

                            <button
                                type="button"
                                data-document-pdf-zoom-in
                                class="
                                    rounded-lg
                                    border
                                    border-slate-300
                                    px-3
                                    py-2
                                    text-xs
                                    font-semibold
                                    text-slate-700
                                    dark:border-slate-700
                                    dark:text-slate-200
                                "
                            >
                                +
                            </button>
                        </div>

                    </div>


                    <div
                        data-document-pdf-scroll
                        class="
                            min-h-0
                            flex-1
                            overflow-auto
                            p-2
                            sm:p-4
                        "
                    >
                        <div
                            class="
                                flex
                                min-h-full
                                items-start
                                justify-center
                            "
                        >
                            <img
                                data-document-pdf-image
                                alt="Dokumentseite"
                                class="
                                    h-auto
                                    w-full
                                    max-w-none
                                    bg-white
                                    object-contain
                                    shadow-xl
                                "
                            >
                        </div>
                    </div>

                </div>


                <div
                    data-document-preview-image-wrap
                    class="
                        hidden
                        h-full
                        w-full
                        items-center
                        justify-center
                        overflow-auto
                        p-4
                        sm:p-6
                    "
                >
                    <img
                        data-document-preview-image
                        alt="Dokumentvorschau"
                        class="
                            max-h-full
                            max-w-full
                            object-contain
                            shadow-xl
                        "
                    >
                </div>


                <div
                    data-document-preview-text-wrap
                    class="
                        hidden
                        h-full
                        overflow-auto
                        p-4
                        sm:p-6
                    "
                >
                    <pre
                        data-document-preview-text
                        class="
                            whitespace-pre-wrap
                            break-words
                            rounded-xl
                            bg-white
                            p-4
                            font-mono
                            text-sm
                            leading-6
                            text-slate-800
                            shadow-sm
                            sm:p-6
                            dark:bg-slate-900
                            dark:text-slate-200
                        "
                    ></pre>
                </div>


                <div
                    data-document-preview-unsupported
                    class="
                        hidden
                        h-full
                        items-center
                        justify-center
                        p-6
                    "
                >
                    <div
                        class="
                            max-w-lg
                            rounded-2xl
                            border
                            border-slate-200
                            bg-white
                            p-6
                            text-center
                            shadow-sm
                            dark:border-slate-800
                            dark:bg-slate-900
                        "
                    >
                        <div
                            class="
                                mx-auto
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-xl
                                bg-slate-100
                                text-sm
                                font-bold
                                text-slate-600
                                dark:bg-slate-800
                                dark:text-slate-300
                            "
                            data-document-preview-extension
                        >
                            DOC
                        </div>

                        <h3
                            class="
                                mt-4
                                font-semibold
                                text-slate-900
                                dark:text-white
                            "
                        >
                            Keine direkte Browser-Vorschau
                        </h3>

                        <p
                            class="
                                mt-2
                                text-sm
                                leading-6
                                text-slate-500
                                dark:text-slate-400
                            "
                        >
                            Für diesen Dateityp steht aktuell keine direkte
                            Vorschau zur Verfügung. Die Datei bleibt geschützt
                            gespeichert und kann bei Bedarf heruntergeladen werden.
                        </p>
                    </div>
                </div>

            </div>


            <div
                class="
                    flex
                    shrink-0
                    flex-col-reverse
                    gap-2
                    border-t
                    border-slate-200
                    bg-white
                    px-4
                    py-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-end
                    sm:px-5
                    dark:border-slate-800
                    dark:bg-slate-900
                "
            >

                <button
                    type="button"
                    data-document-preview-close
                    class="
                        rounded-xl
                        border
                        border-slate-300
                        px-4
                        py-2.5
                        text-sm
                        font-semibold
                        text-slate-700
                        hover:bg-slate-50
                        dark:border-slate-700
                        dark:text-slate-200
                        dark:hover:bg-slate-800
                    "
                >
                    Schließen
                </button>

                <a
                    data-document-preview-download
                    href="#"
                    class="
                        rounded-xl
                        bg-blue-600
                        px-4
                        py-2.5
                        text-center
                        text-sm
                        font-semibold
                        text-white
                        transition
                        hover:bg-blue-700
                        dark:hover:bg-blue-500
                    "
                >
                    Herunterladen
                </a>

            </div>

        </div>

    </div>

<?php endif; ?>

