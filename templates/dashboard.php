<?php

declare(strict_types=1);

$contractCount =
    (int) ($stats['contract_count'] ?? 0);

$monthlyCost =
    (float) ($stats['monthly_cost'] ?? 0);

$annualCost =
    (float) ($stats['annual_cost'] ?? 0);

$nextPaymentDate =
    $stats['next_payment_date'] ?? null;

$holderCount =
    count($holderStats ?? []);

$runningMonthlyTotal = 0.0;
$runningAnnualTotal = 0.0;
$paymentsNext30Count = 0;
$paymentsNext30Total = 0.0;
$today = new DateTimeImmutable('today');
$next30Days = $today->modify('+30 days');

foreach (
    $runningCosts ?? []
    as $runningCost
) {
    $runningMonthlyTotal +=
        (float) (
            $runningCost[
                'monthly_cost'
            ] ?? 0
        );

    $runningAnnualTotal +=
        (float) (
            $runningCost[
                'annual_cost'
            ] ?? 0
        );
}

foreach (
    $paymentPlannerEvents ?? []
    as $plannerEvent
) {
    $eventDateValue =
        $plannerEvent['date'] ?? null;

    if (!$eventDateValue) {
        continue;
    }

    $eventDate =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            (string) $eventDateValue
        );

    if (
        !$eventDate
        || $eventDate < $today
        || $eventDate > $next30Days
    ) {
        continue;
    }

    $paymentsNext30Count++;
    $paymentsNext30Total +=
        (float) (
            $plannerEvent['amount'] ?? 0
        );
}

?>

<section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.95fr)]">

        <div
            class="
                overflow-hidden
                rounded-3xl
                border
                border-slate-200
                bg-gradient-to-br
                from-white
                via-white
                to-blue-50
                p-6
                shadow-sm
                dark:border-slate-800
                dark:from-slate-900
                dark:via-slate-900
                dark:to-slate-900
            "
        >

            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

                <div class="min-w-0">

                    <div
                        class="
                            inline-flex
                            items-center
                            rounded-full
                            border
                            border-blue-200
                            bg-blue-50
                            px-3
                            py-1
                            text-xs
                            font-semibold
                            uppercase
                            tracking-[0.18em]
                            text-blue-700
                            dark:border-blue-900
                            dark:bg-blue-950/50
                            dark:text-blue-300
                        "
                    >
                        Startseite
                    </div>

                    <h1
                        class="
                            mt-4
                            text-3xl
                            font-bold
                            tracking-tight
                            text-slate-900
                            dark:text-white
                            sm:text-4xl
                        "
                    >
                        Willkommen zurück,
                        <?= e($user['display_name']) ?>.
                    </h1>

                    <p
                        class="
                            mt-3
                            max-w-3xl
                            text-sm
                            leading-7
                            text-slate-600
                            dark:text-slate-300
                            sm:text-base
                        "
                    >
                        Laufende Verträge, Kosten, geplante Abbuchungen und Fristen
                        in einer zentralen Übersicht.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-3">

                        <div
                            class="
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white/80
                                px-4
                                py-3
                                dark:border-slate-800
                                dark:bg-slate-950/50
                            "
                        >
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Verträge
                            </div>
                            <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                                <?= $contractCount ?> laufend
                            </div>
                        </div>

                        <div
                            class="
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white/80
                                px-4
                                py-3
                                dark:border-slate-800
                                dark:bg-slate-950/50
                            "
                        >
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Vertragsinhaber
                            </div>
                            <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                                <?= $holderCount ?> aktiv
                            </div>
                        </div>

                        <div
                            class="
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white/80
                                px-4
                                py-3
                                dark:border-slate-800
                                dark:bg-slate-950/50
                            "
                        >
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Nächste Abbuchung
                            </div>
                            <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                                <?= $nextPaymentDate !== null
                                    ? e(
                                        contract_format_date(
                                            $nextPaymentDate
                                        )
                                    )
                                    : 'Keine geplant' ?>
                            </div>
                        </div>

                    </div>

                </div>


                <div class="flex w-full shrink-0 flex-col gap-3 lg:w-64">

                    <?php if (
                        has_permission(
                            'contracts.create'
                        )
                    ): ?>

                        <a
                            href="/contracts/create"
                            class="
                                inline-flex
                                items-center
                                justify-center
                                rounded-2xl
                                bg-blue-600
                                px-4
                                py-3
                                text-sm
                                font-semibold
                                text-white
                                transition
                                hover:bg-blue-700
                                dark:hover:bg-blue-500
                            "
                        >
                            + Vertrag anlegen
                        </a>

                    <?php endif; ?>

                    <a
                        href="/contracts"
                        class="
                            inline-flex
                            items-center
                            justify-center
                            rounded-2xl
                            border
                            border-slate-300
                            bg-white/90
                            px-4
                            py-3
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
                        Vertragsübersicht öffnen
                    </a>

                    <a
                        href="/reports/financial-overview"
                        class="
                            inline-flex
                            items-center
                            justify-center
                            rounded-2xl
                            border
                            border-slate-300
                            bg-white/90
                            px-4
                            py-3
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
                        Finanzübersicht / Auszug
                    </a>

                </div>

            </div>

        </div>


        <div
            class="
                rounded-3xl
                border
                border-slate-200
                bg-white
                p-6
                shadow-sm
                dark:border-slate-800
                dark:bg-slate-900
            "
        >

            <div class="flex items-start justify-between gap-4">

                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Schnellzugriff
                    </div>
                    <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                        Schnellzugriff
                    </h2>
                </div>

                <div
                    class="
                        rounded-2xl
                        bg-slate-100
                        px-3
                        py-2
                        text-xs
                        font-semibold
                        text-slate-600
                        dark:bg-slate-800
                        dark:text-slate-300
                    "
                >
                    Navigation
                </div>

            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">

                <a
                    href="/deadlines"
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        p-4
                        transition
                        hover:border-blue-300
                        hover:bg-blue-50/60
                        dark:border-slate-800
                        dark:hover:border-blue-700
                        dark:hover:bg-slate-800/70
                    "
                >
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                        Kündigungs- & Fristen-Cockpit
                    </div>
                    <div class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Fristen, Verlängerungen und Handlungsbedarf prüfen.
                    </div>
                </a>

                <a
                    href="/reports/payment-planner"
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        p-4
                        transition
                        hover:border-blue-300
                        hover:bg-blue-50/60
                        dark:border-slate-800
                        dark:hover:border-blue-700
                        dark:hover:bg-slate-800/70
                    "
                >
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                        Eigene Ausgabenplanung
                    </div>
                    <div class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Diagramm, Zeiträume und zusätzliche Auswertungen in einer eigenen Übersicht.
                    </div>
                </a>

                <a
                    href="/reports/cost-development"
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        p-4
                        transition
                        hover:border-blue-300
                        hover:bg-blue-50/60
                        dark:border-slate-800
                        dark:hover:border-blue-700
                        dark:hover:bg-slate-800/70
                    "
                >
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                        Kostenentwicklung & Einsparpotenzial
                    </div>
                    <div class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Preisentwicklung verstehen und Sparpotenziale erkennen.
                    </div>
                </a>

                <a
                    href="/contracts"
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        p-4
                        transition
                        hover:border-blue-300
                        hover:bg-blue-50/60
                        dark:border-slate-800
                        dark:hover:border-blue-700
                        dark:hover:bg-slate-800/70
                    "
                >
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                        Verträge verwalten
                    </div>
                    <div class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Suchen, filtern, anpassen und Dokumente einsehen.
                    </div>
                </a>

                <a
                    href="/reports/financial-overview"
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        p-4
                        transition
                        hover:border-blue-300
                        hover:bg-blue-50/60
                        dark:border-slate-800
                        dark:hover:border-blue-700
                        dark:hover:bg-slate-800/70
                    "
                >
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                        Finanzübersicht erstellen
                    </div>
                    <div class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Übersicht als Auszug für Bank oder eigene Planung nutzen.
                    </div>
                </a>

            </div>

        </div>

    </div>


    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <a
            href="/contracts"
            class="
                group
                rounded-3xl
                border
                border-slate-200
                bg-white
                p-5
                shadow-sm
                transition
                hover:-translate-y-0.5
                hover:border-blue-300
                hover:shadow-md
                dark:border-slate-800
                dark:bg-slate-900
                dark:hover:border-blue-700
            "
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Laufende Verträge
                    </div>
                    <div class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">
                        <?= $contractCount ?>
                    </div>
                </div>

                <div class="rounded-2xl bg-blue-50 p-3 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3h10.5A2.25 2.25 0 0 1 19.5 5.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25A2.25 2.25 0 0 1 6.75 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5h7.5M8.25 11.25h7.5M8.25 15h4.5" />
                    </svg>
                </div>
            </div>

            <p class="mt-4 text-sm leading-6 text-slate-500 dark:text-slate-400">
                Alle derzeit laufenden Verträge inklusive vorgemerkter Kündigungen bis zum tatsächlichen Enddatum.
            </p>

            <div class="mt-4 text-sm font-semibold text-blue-600 dark:text-blue-400">
                Zur Übersicht →
            </div>
        </a>


        <div
            class="
                rounded-3xl
                border
                border-slate-200
                bg-white
                p-5
                shadow-sm
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Monatliche Kosten
                    </div>
                    <div class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">
                        <?= e(
                            contract_format_money(
                                $monthlyCost
                            )
                        ) ?>
                    </div>
                </div>

                <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-4-8.25h7.5a2.25 2.25 0 0 1 0 4.5H10.5a2.25 2.25 0 0 0 0 4.5H16" />
                    </svg>
                </div>
            </div>

            <p class="mt-4 text-sm leading-6 text-slate-500 dark:text-slate-400">
                Aktuelle laufende Kosten. Verträge mit aktiver Pause sind hier bewusst nicht eingerechnet.
            </p>
        </div>


        <div
            class="
                rounded-3xl
                border
                border-slate-200
                bg-white
                p-5
                shadow-sm
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Geplante Kosten 12 Monate
                    </div>
                    <div class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">
                        <?= e(
                            contract_format_money(
                                $annualCost
                            )
                        ) ?>
                    </div>
                </div>

                <div class="rounded-2xl bg-violet-50 p-3 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M7.5 3.75v6m9-6v6m-9 5.25h1.5m3 0h1.5m3 0H18m-12 3h12A2.25 2.25 0 0 0 20.25 15.75v-9A2.25 2.25 0 0 0 18 4.5H6A2.25 2.25 0 0 0 3.75 6.75v9A2.25 2.25 0 0 0 6 18.75Z" />
                    </svg>
                </div>
            </div>

            <p class="mt-4 text-sm leading-6 text-slate-500 dark:text-slate-400">
                Erwartete Abbuchungen der nächsten 12 Monate – inklusive Kündigungs-Enddaten und Pausen.
            </p>
        </div>


        <div
            class="
                rounded-3xl
                border
                border-slate-200
                bg-white
                p-5
                shadow-sm
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Nächste 30 Tage
                    </div>
                    <div class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">
                        <?= e(
                            contract_format_money(
                                $paymentsNext30Total
                            )
                        ) ?>
                    </div>
                </div>

                <div class="rounded-2xl bg-amber-50 p-3 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.75 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <p class="mt-4 text-sm leading-6 text-slate-500 dark:text-slate-400">
                <?= $paymentsNext30Count ?> geplante Abbuchung(en) im nächsten Monat ab heute.
            </p>
        </div>

    </div>


    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">

        <div
            class="
                rounded-3xl
                border
                border-slate-200
                bg-white
                shadow-sm
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            Kosten nach Vertragsinhaber
                        </div>
                        <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                            Laufende Kosten nach Vertragsinhaber
                        </h2>
                    </div>

                    <a
                        href="/contracts"
                        class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        Alle Verträge öffnen →
                    </a>
                </div>

                <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Aktuelle laufende Verträge nach Vertragsinhaber. Vorgemerkte Kündigungen laufen bis zum Enddatum weiter; aktive Pausen zählen nicht zu den laufenden Monatskosten.
                </p>
            </div>

            <div class="p-5 sm:p-6">

                <div class="grid gap-3">

                    <a
                        href="/contracts"
                        class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4 transition hover:border-blue-300 dark:border-blue-900 dark:bg-blue-950/20 dark:hover:border-blue-700"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-base font-semibold text-slate-900 dark:text-white">
                                    Alle Vertragsinhaber
                                </div>
                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Gesamtansicht aller aktuell laufenden Verträge
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 sm:min-w-[260px]">
                                <div class="rounded-xl bg-white/80 px-3 py-2 dark:bg-slate-900/80">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                        Monatlich
                                    </div>
                                    <div class="mt-1 font-bold text-slate-900 dark:text-white">
                                        <?= e(
                                            contract_format_money(
                                                $monthlyCost
                                            )
                                        ) ?>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-white/80 px-3 py-2 dark:bg-slate-900/80">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                        12 Monate
                                    </div>
                                    <div class="mt-1 font-bold text-slate-900 dark:text-white">
                                        <?= e(
                                            contract_format_money(
                                                $annualCost
                                            )
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>


                    <?php foreach ($holderStats as $holder): ?>

                        <a
                            href="/contracts?holder=<?= (int) $holder['id'] ?>"
                            class="rounded-2xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-slate-50 dark:border-slate-800 dark:hover:border-blue-700 dark:hover:bg-slate-800/60"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="text-base font-semibold text-slate-900 dark:text-white">
                                        <?= e($holder['name']) ?>
                                    </div>
                                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        <?= (int) $holder['contract_count'] ?> laufende(r) Vertrag/Verträge
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 sm:min-w-[260px]">
                                    <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/80">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                            Monatlich
                                        </div>
                                        <div class="mt-1 font-bold text-slate-900 dark:text-white">
                                            <?= e(
                                                contract_format_money(
                                                    (float) $holder['monthly_cost']
                                                )
                                            ) ?>
                                        </div>
                                    </div>

                                    <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/80">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                            12 Monate
                                        </div>
                                        <div class="mt-1 font-bold text-slate-900 dark:text-white">
                                            <?= e(
                                                contract_format_money(
                                                    (float) $holder['annual_cost']
                                                )
                                            ) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                    <?php endforeach; ?>

                </div>

            </div>
        </div>


        <div class="grid gap-6">

            <div
                class="
                    rounded-3xl
                    border
                    border-slate-200
                    bg-white
                    p-5
                    shadow-sm
                    dark:border-slate-800
                    dark:bg-slate-900
                "
            >
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                    Heute im Blick
                </div>

                <div class="mt-4 space-y-3">

                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">
                            Nächste Abbuchung
                        </div>
                        <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                            <?= $nextPaymentDate !== null
                                ? e(
                                    contract_format_date(
                                        $nextPaymentDate
                                    )
                                )
                                : 'Derzeit keine geplant' ?>
                        </div>
                        <div class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Der früheste anstehende Zahlungstermin aus allen aktuell laufenden Verträgen.
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">
                            Zahlungen in den nächsten 30 Tagen
                        </div>
                        <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                            <?= $paymentsNext30Count ?> Termin(e)
                        </div>
                        <div class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Erwartetes Gesamtvolumen:
                            <strong class="font-semibold text-slate-700 dark:text-slate-200">
                                <?= e(
                                    contract_format_money(
                                        $paymentsNext30Total
                                    )
                                ) ?>
                            </strong>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">
                            Finanzübersicht / Auszug
                        </div>
                        <div class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Zusammenfassung der laufenden Vertragsausgaben für Bank, Kredit oder Selbstauskunft.
                        </div>
                        <a
                            href="/reports/financial-overview"
                            class="mt-3 inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Zum Auszug →
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>


    <div
        data-payment-planner
        class="
            mt-6
            overflow-hidden
            rounded-3xl
            border
            border-slate-200
            bg-white
            shadow-sm
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <div class="border-b border-slate-200 px-4 py-5 dark:border-slate-800 sm:px-6">

            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">

                <div class="min-w-0">

                    <div class="flex flex-wrap items-center gap-3">

                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Ausgabenplanung
                            </div>
                            <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                                Geplante Abbuchungen im Blick
                            </h2>
                        </div>

                        <a
                            href="/reports/financial-overview"
                            class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            Finanzübersicht / Auszug
                        </a>

                    </div>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Erwartete Abbuchungen ab heute. Diagramm, Summen und Einzelabbuchungen reagieren direkt auf Zeitraum und Vertragsinhaber.
                    </p>

                </div>


                <div class="grid w-full gap-4 md:grid-cols-[minmax(0,1fr)_minmax(330px,420px)] md:items-end xl:w-auto">

                    <div class="min-w-0">

                        <label
                            for="payment-holder"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Vertragsinhaber
                        </label>

                        <select
                            id="payment-holder"
                            data-payment-holder
                            class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 md:min-w-56 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-blue-950"
                        >
                            <option value="">
                                Alle Vertragsinhaber
                            </option>

                            <?php foreach (
                                $holderStats
                                as $holder
                            ): ?>

                                <option value="<?= (int) $holder['id'] ?>">
                                    <?= e($holder['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="min-w-0">

                        <div class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Zeitraum
                        </div>

                        <div class="grid h-11 w-full grid-cols-3 gap-1 rounded-2xl bg-slate-100 p-1 dark:bg-slate-800">

                            <button
                                type="button"
                                data-payment-range="30"
                                class="flex h-9 items-center justify-center whitespace-nowrap rounded-xl bg-white px-2 text-xs font-semibold text-slate-900 shadow-sm transition sm:text-sm dark:bg-slate-700 dark:text-white"
                            >
                                1 Monat
                            </button>

                            <button
                                type="button"
                                data-payment-range="90"
                                class="flex h-9 items-center justify-center whitespace-nowrap rounded-xl px-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 sm:text-sm dark:text-slate-400 dark:hover:text-white"
                            >
                                3 Monate
                            </button>

                            <button
                                type="button"
                                data-payment-range="365"
                                class="flex h-9 items-center justify-center whitespace-nowrap rounded-xl px-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 sm:text-sm dark:text-slate-400 dark:hover:text-white"
                            >
                                1 Jahr
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="p-4 sm:p-6">

            <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3">

                <div class="col-span-2 rounded-2xl bg-slate-50 p-4 sm:col-span-1 dark:bg-slate-800/70">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Zeitraum
                    </div>
                    <div data-payment-range-label class="mt-1.5 font-semibold text-slate-900 dark:text-white">
                        Nächster Monat ab heute
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Ausgaben
                    </div>
                    <div data-payment-total class="mt-1.5 text-xl font-bold text-slate-900 dark:text-white">
                        0,00 €
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Abbuchungen
                    </div>
                    <div data-payment-count class="mt-1.5 text-xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>

            </div>


            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 px-2 pb-3 pt-4 dark:border-slate-800 dark:bg-slate-950/40 sm:px-4">

                <div data-payment-chart-shell class="flex min-w-0 gap-2">

                    <div
                        data-payment-chart-scale
                        class="relative h-52 w-12 shrink-0 sm:h-60 sm:w-16"
                        aria-hidden="true"
                    ></div>

                    <div class="relative min-w-0 flex-1">

                        <div
                            data-payment-chart-grid
                            class="pointer-events-none absolute inset-x-0 bottom-6 top-0"
                            aria-hidden="true"
                        ></div>

                        <div
                            data-payment-chart
                            class="relative z-10 grid h-52 w-full min-w-0 items-end gap-1.5 sm:h-60 sm:gap-2"
                            aria-label="Diagramm der erwarteten Abbuchungen"
                        ></div>

                    </div>

                </div>


                <div
                    data-payment-chart-empty
                    class="hidden py-16 text-center text-sm text-slate-500 dark:text-slate-400"
                >
                    Für diesen Zeitraum sind keine Abbuchungen geplant.
                </div>

            </div>


            <div class="mt-6">

                <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="font-semibold text-slate-900 dark:text-white">
                        Abbuchungen im Zeitraum
                    </h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Vertrag antippen oder anklicken, um die Details zu öffnen.
                    </span>
                </div>

                <div data-payment-event-list class="grid gap-3 lg:grid-cols-2"></div>

            </div>

        </div>


        <script type="application/json" data-payment-planner-data><?= json_encode(
            $paymentPlannerEvents ?? [],
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        ) ?></script>

    </div>


    <div
        class="
            mt-6
            overflow-hidden
            rounded-3xl
            border
            border-slate-200
            bg-white
            shadow-sm
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <div class="flex flex-col justify-between gap-3 border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:flex-row sm:items-center sm:px-6">

            <div>
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                    Detailansicht
                </div>
                <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                    Alle laufenden Kosten
                </h2>
                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Einzelansicht aller aktuell laufenden wiederkehrenden Verträge.
                </p>
            </div>

            <a href="/contracts" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                Vertragsübersicht öffnen →
            </a>

        </div>


        <?php if (empty($runningCosts)): ?>

            <div class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                Aktuell sind keine laufenden Kosten hinterlegt.
            </div>

        <?php else: ?>

            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/70 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-4">Vertrag</th>
                            <th class="px-6 py-4">Inhaber</th>
                            <th class="px-6 py-4">Anbieter</th>
                            <th class="px-6 py-4">Abrechnung</th>
                            <th class="px-6 py-4 text-right">Betrag</th>
                            <th class="px-6 py-4 text-right">Monatlich</th>
                            <th class="px-6 py-4 text-right">Jährlich</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

                        <?php foreach ($runningCosts as $row): ?>

                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">

                                <td class="px-6 py-4">
                                    <a
                                        href="/contracts/<?= (int) $row['id'] ?>"
                                        class="font-semibold text-slate-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                                    >
                                        <?= e($row['title']) ?>
                                    </a>
                                </td>

                                <td class="px-6 py-4">
                                    <?= e($row['contract_holder_name']) ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= e($row['provider']) ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= e(
                                        contract_billing_frequency_label(
                                            $row['billing_frequency']
                                        )
                                    ) ?>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <?= e(
                                        contract_format_money(
                                            (float) $row['amount']
                                        )
                                    ) ?>
                                </td>

                                <td class="px-6 py-4 text-right font-semibold">
                                    <?= e(
                                        contract_format_money(
                                            (float) $row['monthly_cost']
                                        )
                                    ) ?>
                                </td>

                                <td class="px-6 py-4 text-right font-semibold">
                                    <?= e(
                                        contract_format_money(
                                            (float) $row['annual_cost']
                                        )
                                    ) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                    <?php if (count($runningCosts) > 1): ?>

                        <tfoot class="border-t-2 border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/70">
                            <tr>
                                <td colspan="5" class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                    Gesamt
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">
                                    <?= e(
                                        contract_format_money(
                                            $runningMonthlyTotal
                                        )
                                    ) ?>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">
                                    <?= e(
                                        contract_format_money(
                                            $runningAnnualTotal
                                        )
                                    ) ?>
                                </td>
                            </tr>
                        </tfoot>

                    <?php endif; ?>

                </table>

            </div>

        <?php endif; ?>

    </div>

</section>
