<?php

declare(strict_types=1);

$generatedDate =
    $generatedAt->format(
        'd.m.Y'
    );

$generatedTime =
    $generatedAt->format(
        'H:i'
    );

?>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    html,
    body {
        background: #fff !important;
        color: #000 !important;
    }

    .financial-print-area {
        max-width: none !important;
        padding: 0 !important;
    }

    .financial-print-area,
    .financial-print-area * {
        color: #000 !important;
    }

    .financial-print-card {
        break-inside: avoid;
        border-color: #cbd5e1 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .financial-print-table {
        font-size: 9pt !important;
    }

    .financial-print-table th,
    .financial-print-table td {
        padding: 6px 7px !important;
        border-color: #cbd5e1 !important;
        color: #000 !important;
    }

    .financial-print-muted {
        color: #475569 !important;
    }
}
</style>


<section
    class="
        financial-print-area
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
            sm:flex-row
            sm:items-end
            sm:justify-between
            print:mb-5
        "
    >

        <div>

            <div
                class="
                    print:hidden
                    text-sm
                    font-semibold
                    text-blue-600
                    dark:text-blue-400
                "
            >
                <a
                    href="/dashboard"
                    class="
                        hover:text-blue-700
                        dark:hover:text-blue-300
                    "
                >
                    ← Übersicht
                </a>
            </div>

            <div
                class="
                    mt-4
                    text-xs
                    font-semibold
                    uppercase
                    tracking-[0.18em]
                    text-blue-600
                    print:mt-0
                    print:text-slate-700
                    dark:text-blue-400
                "
            >
                Selbstauskunft
            </div>

            <h1
                class="
                    mt-2
                    text-2xl
                    font-bold
                    tracking-tight
                    text-slate-900
                    sm:text-3xl
                    print:text-2xl
                    dark:text-white
                "
            >
                Private Vertrags- und Ausgabenübersicht
            </h1>

            <p
                class="
                    mt-2
                    max-w-3xl
                    text-sm
                    leading-6
                    text-slate-500
                    print:text-slate-600
                    dark:text-slate-400
                "
            >
                Zusammenfassung der aktuell laufenden wiederkehrenden
                Vertragsverpflichtungen auf Basis der in Vertrag Home
                hinterlegten Daten.
            </p>

        </div>


        <div
            class="
                flex
                flex-col
                gap-3
                sm:flex-row
                print:hidden
            "
        >

            <a
                href="/contracts"
                class="
                    inline-flex
                    h-11
                    items-center
                    justify-center
                    rounded-xl
                    border
                    border-slate-300
                    px-4
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
                Verträge öffnen
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="
                    inline-flex
                    h-11
                    items-center
                    justify-center
                    rounded-xl
                    bg-blue-600
                    px-4
                    text-sm
                    font-semibold
                    text-white
                    transition
                    hover:bg-blue-700
                    dark:hover:bg-blue-500
                "
            >
                Drucken / als PDF speichern
            </button>

        </div>

    </div>


    <div
        class="
            financial-print-card
            mb-6
            rounded-2xl
            border
            border-slate-200
            bg-white
            p-4
            shadow-sm
            sm:p-5
            print:mb-4
            print:rounded-none
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <div
            class="
                grid
                gap-4
                md:grid-cols-[minmax(0,1fr)_auto]
                md:items-end
            "
        >

            <form
                method="get"
                action="/reports/financial-overview"
                class="print:hidden"
            >

                <label
                    for="financial-holder"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Vertragsinhaber
                </label>

                <select
                    id="financial-holder"
                    name="holder"
                    onchange="this.form.submit()"
                    class="
                        h-11
                        w-full
                        max-w-md
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
                        dark:focus:ring-blue-950
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


            <div
                class="
                    grid
                    grid-cols-2
                    gap-x-6
                    gap-y-2
                    text-sm
                    md:text-right
                    print:grid-cols-3
                    print:text-left
                "
            >
                <div>
                    <div
                        class="
                            text-xs
                            uppercase
                            tracking-wide
                            text-slate-500
                            dark:text-slate-400
                        "
                    >
                        Vertragsinhaber
                    </div>
                    <div
                        class="
                            mt-1
                            font-semibold
                            text-slate-900
                            dark:text-white
                        "
                    >
                        <?= e($selectedHolderName) ?>
                    </div>
                </div>

                <div>
                    <div
                        class="
                            text-xs
                            uppercase
                            tracking-wide
                            text-slate-500
                            dark:text-slate-400
                        "
                    >
                        Stand
                    </div>
                    <div
                        class="
                            mt-1
                            font-semibold
                            text-slate-900
                            dark:text-white
                        "
                    >
                        <?= e($generatedDate) ?>
                    </div>
                </div>

                <div class="hidden print:block">
                    <div
                        class="
                            text-xs
                            uppercase
                            tracking-wide
                            text-slate-500
                        "
                    >
                        Erstellt von
                    </div>
                    <div
                        class="
                            mt-1
                            font-semibold
                            text-slate-900
                        "
                    >
                        <?= e(
                            $user['display_name']
                            ?? $user['username']
                        ) ?>
                    </div>
                </div>
            </div>

        </div>

    </div>


    <div
        class="
            mb-6
            grid
            grid-cols-2
            gap-3
            lg:grid-cols-4
            print:mb-4
            print:grid-cols-4
        "
    >

        <div
            class="
                financial-print-card
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-4
                shadow-sm
                print:rounded-none
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div
                class="
                    text-[11px]
                    font-semibold
                    uppercase
                    tracking-wide
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Laufende Verträge
            </div>

            <div
                class="
                    mt-2
                    text-2xl
                    font-bold
                    text-slate-900
                    dark:text-white
                "
            >
                <?= count($financialContracts) ?>
            </div>
        </div>


        <div
            class="
                financial-print-card
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-4
                shadow-sm
                print:rounded-none
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div
                class="
                    text-[11px]
                    font-semibold
                    uppercase
                    tracking-wide
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Monatlicher Vergleich
            </div>

            <div
                class="
                    mt-2
                    text-2xl
                    font-bold
                    text-slate-900
                    dark:text-white
                "
            >
                <?= e(
                    contract_format_money(
                        $monthlyTotal
                    )
                ) ?>
            </div>
        </div>


        <div
            class="
                financial-print-card
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-4
                shadow-sm
                print:rounded-none
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div
                class="
                    text-[11px]
                    font-semibold
                    uppercase
                    tracking-wide
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Jährlicher Vergleich
            </div>

            <div
                class="
                    mt-2
                    text-2xl
                    font-bold
                    text-slate-900
                    dark:text-white
                "
            >
                <?= e(
                    contract_format_money(
                        $annualTotal
                    )
                ) ?>
            </div>
        </div>


        <div
            class="
                financial-print-card
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-4
                shadow-sm
                print:rounded-none
                dark:border-slate-800
                dark:bg-slate-900
            "
        >
            <div
                class="
                    text-[11px]
                    font-semibold
                    uppercase
                    tracking-wide
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Geplante Abbuchungen 12 Monate
            </div>

            <div
                class="
                    mt-2
                    text-2xl
                    font-bold
                    text-slate-900
                    dark:text-white
                "
            >
                <?= e(
                    contract_format_money(
                        $nextTwelveMonthsTotal
                    )
                ) ?>
            </div>
        </div>

    </div>


    <div
        class="
            financial-print-card
            overflow-hidden
            rounded-2xl
            border
            border-slate-200
            bg-white
            shadow-sm
            print:rounded-none
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <div
            class="
                border-b
                border-slate-200
                px-5
                py-4
                print:px-3
                print:py-3
                dark:border-slate-800
            "
        >
            <h2
                class="
                    font-semibold
                    text-slate-900
                    dark:text-white
                "
            >
                Einzelaufstellung der laufenden Vertragskosten
            </h2>

            <p
                class="
                    mt-1
                    text-xs
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Einmalige Verträge sind in dieser laufenden Kostenübersicht nicht enthalten.
            </p>
        </div>


        <?php if (
            empty($financialContracts)
        ): ?>

            <div
                class="
                    px-6
                    py-14
                    text-center
                    text-sm
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Für die gewählte Auswahl sind keine aktuell laufenden wiederkehrenden Verträge vorhanden.
            </div>

        <?php else: ?>

            <div
                class="
                    divide-y
                    divide-slate-200
                    lg:hidden
                    print:hidden
                    dark:divide-slate-800
                "
            >

                <?php foreach (
                    $financialContracts
                    as $contract
                ): ?>

                    <div class="p-5">

                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-4
                            "
                        >
                            <div class="min-w-0">
                                <div
                                    class="
                                        truncate
                                        font-semibold
                                        text-slate-900
                                        dark:text-white
                                    "
                                >
                                    <?= e($contract['title']) ?>
                                </div>

                                <div
                                    class="
                                        mt-1
                                        text-xs
                                        text-slate-500
                                        dark:text-slate-400
                                    "
                                >
                                    <?= e($contract['provider']) ?>
                                    ·
                                    <?= e($contract['contract_type']) ?>

                                    <?php if (
                                        !empty(
                                            $contract[
                                                'pause_state'
                                            ][
                                                'is_paused'
                                            ]
                                        )
                                    ): ?>
                                        · Pausiert bis
                                        <?= e(
                                            contract_format_date(
                                                $contract[
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

                            <div
                                class="
                                    shrink-0
                                    text-right
                                "
                            >
                                <div
                                    class="
                                        font-bold
                                        text-slate-900
                                        dark:text-white
                                    "
                                >
                                    <?= e(
                                        contract_format_money(
                                            (float) $contract[
                                                'monthly_cost'
                                            ]
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
                                    pro Monat
                                </div>
                            </div>
                        </div>


                        <div
                            class="
                                mt-4
                                grid
                                grid-cols-2
                                gap-x-4
                                gap-y-3
                                text-sm
                            "
                        >
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">
                                    Inhaber
                                </div>
                                <div class="mt-1">
                                    <?= e($contract['contract_holder_name']) ?>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">
                                    Abrechnung
                                </div>
                                <div class="mt-1">
                                    <?= e(
                                        contract_billing_frequency_label(
                                            $contract['billing_frequency']
                                        )
                                    ) ?>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">
                                    Vertragsbetrag
                                </div>
                                <div class="mt-1">
                                    <?= e(
                                        contract_format_money(
                                            (float) $contract['amount']
                                        )
                                    ) ?>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">
                                    Nächste Abbuchung
                                </div>
                                <div class="mt-1">
                                    <?= e(
                                        contract_format_date(
                                            $contract[
                                                'calculated_next_payment_date'
                                            ]
                                        )
                                    ) ?>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">
                                    Vertragsnummer
                                </div>
                                <div class="mt-1 break-all">
                                    <?= e(
                                        $contract['contract_number']
                                        ?: '–'
                                    ) ?>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-400">
                                    Kundennummer
                                </div>
                                <div class="mt-1 break-all">
                                    <?= e(
                                        $contract['customer_number']
                                        ?: '–'
                                    ) ?>
                                </div>
                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>


                <?php if (
                    count($financialContracts) > 1
                ): ?>

                    <div
                        class="
                            bg-slate-50
                            p-5
                            dark:bg-slate-800/70
                        "
                    >
                        <div class="font-bold">
                            Gesamt
                        </div>

                        <div
                            class="
                                mt-3
                                grid
                                grid-cols-2
                                gap-4
                            "
                        >
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Monatlich
                                </div>
                                <div class="mt-1 font-bold">
                                    <?= e(
                                        contract_format_money(
                                            $monthlyTotal
                                        )
                                    ) ?>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Jährlich
                                </div>
                                <div class="mt-1 font-bold">
                                    <?= e(
                                        contract_format_money(
                                            $annualTotal
                                        )
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>


            <div
                class="
                    hidden
                    overflow-x-auto
                    lg:block
                    print:block
                "
            >
                <table
                    class="
                        financial-print-table
                        w-full
                        text-left
                        text-xs
                    "
                >
                    <thead
                        class="
                            bg-slate-50
                            uppercase
                            tracking-wide
                            text-slate-500
                            dark:bg-slate-800/70
                            dark:text-slate-400
                        "
                    >
                        <tr>
                            <th class="px-4 py-3">Vertrag</th>
                            <th class="px-4 py-3">Inhaber</th>
                            <th class="px-4 py-3">Anbieter / Art</th>
                            <th class="px-4 py-3">Vertrags-/Kundennr.</th>
                            <th class="px-4 py-3">Abrechnung</th>
                            <th class="px-4 py-3 text-right">Betrag</th>
                            <th class="px-4 py-3 text-right">Monatlich</th>
                            <th class="px-4 py-3 text-right">Jährlich</th>
                            <th class="px-4 py-3">Nächste Abbuchung</th>
                        </tr>
                    </thead>

                    <tbody
                        class="
                            divide-y
                            divide-slate-200
                            dark:divide-slate-800
                        "
                    >

                        <?php foreach (
                            $financialContracts
                            as $contract
                        ): ?>

                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">
                                        <?= e($contract['title']) ?>
                                    </div>

                                    <?php if (
                                        !empty(
                                            $contract[
                                                'pause_state'
                                            ][
                                                'is_paused'
                                            ]
                                        )
                                    ): ?>
                                        <div class="mt-1 text-[10px] font-medium text-violet-700 dark:text-violet-300">
                                            Pausiert bis
                                            <?= e(
                                                contract_format_date(
                                                    $contract[
                                                        'pause_state'
                                                    ][
                                                        'current'
                                                    ][
                                                        'pause_to'
                                                    ] ?? null
                                                )
                                            ) ?>
                                        </div>
                                    <?php elseif (
                                        $contract['status']
                                        === 'cancelled'
                                        && !empty(
                                            $contract[
                                                'cancellation_effective_date'
                                            ]
                                        )
                                    ): ?>
                                        <div class="mt-1 text-[10px] font-medium text-amber-700 dark:text-amber-300">
                                            Gekündigt zum
                                            <?= e(
                                                contract_format_date(
                                                    $contract[
                                                        'cancellation_effective_date'
                                                    ]
                                                )
                                            ) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-3">
                                    <?= e($contract['contract_holder_name']) ?>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        <?= e($contract['provider']) ?>
                                    </div>
                                    <div class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                        <?= e($contract['contract_type']) ?>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div>
                                        V:
                                        <?= e(
                                            $contract['contract_number']
                                            ?: '–'
                                        ) ?>
                                    </div>
                                    <div class="mt-1">
                                        K:
                                        <?= e(
                                            $contract['customer_number']
                                            ?: '–'
                                        ) ?>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <?= e(
                                        contract_billing_frequency_label(
                                            $contract['billing_frequency']
                                        )
                                    ) ?>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <?= e(
                                        contract_format_money(
                                            (float) $contract['amount']
                                        )
                                    ) ?>
                                </td>

                                <td class="px-4 py-3 text-right font-semibold">
                                    <?= e(
                                        contract_format_money(
                                            (float) $contract['monthly_cost']
                                        )
                                    ) ?>
                                </td>

                                <td class="px-4 py-3 text-right font-semibold">
                                    <?= e(
                                        contract_format_money(
                                            (float) $contract['annual_cost']
                                        )
                                    ) ?>
                                </td>

                                <td class="px-4 py-3">
                                    <?= e(
                                        contract_format_date(
                                            $contract[
                                                'calculated_next_payment_date'
                                            ]
                                        )
                                    ) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>


                    <?php if (
                        count($financialContracts) > 1
                    ): ?>

                        <tfoot
                            class="
                                border-t-2
                                border-slate-300
                                bg-slate-50
                                dark:border-slate-700
                                dark:bg-slate-800/70
                            "
                        >
                            <tr>
                                <td
                                    colspan="6"
                                    class="
                                        px-4
                                        py-3
                                        font-bold
                                    "
                                >
                                    Gesamt
                                </td>

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-bold
                                    "
                                >
                                    <?= e(
                                        contract_format_money(
                                            $monthlyTotal
                                        )
                                    ) ?>
                                </td>

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-bold
                                    "
                                >
                                    <?= e(
                                        contract_format_money(
                                            $annualTotal
                                        )
                                    ) ?>
                                </td>

                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>

                    <?php endif; ?>

                </table>
            </div>

        <?php endif; ?>

    </div>


    <div
        class="
            financial-print-card
            mt-6
            rounded-2xl
            border
            border-slate-200
            bg-white
            p-5
            text-sm
            leading-6
            text-slate-600
            shadow-sm
            print:mt-4
            print:rounded-none
            print:text-xs
            dark:border-slate-800
            dark:bg-slate-900
            dark:text-slate-300
        "
    >
        <div
            class="
                font-semibold
                text-slate-900
                dark:text-white
            "
        >
            Hinweis zur Verwendung
        </div>

        <p class="mt-2">
            Diese Übersicht ist eine automatisch erzeugte Selbstauskunft auf
            Grundlage der in Vertrag Home hinterlegten Vertragsdaten. Sie
            ersetzt keine Originalrechnung, Vertragsurkunde oder
            Kontobestätigung. Für eine Bank oder andere Stelle können die
            zugehörigen Originaldokumente bei Bedarf ergänzend vorgelegt
            werden.
        </p>

        <div
            class="
                mt-4
                grid
                gap-3
                sm:grid-cols-3
                print:grid-cols-3
            "
        >
            <div>
                <span class="financial-print-muted text-slate-500 dark:text-slate-400">
                    Erstellt am:
                </span>
                <?= e($generatedDate) ?>
                um
                <?= e($generatedTime) ?>
                Uhr
            </div>

            <div>
                <span class="financial-print-muted text-slate-500 dark:text-slate-400">
                    Nächste Abbuchung:
                </span>
                <?= e(
                    contract_format_date(
                        $nextPaymentDate
                    )
                ) ?>
            </div>

            <div>
                <span class="financial-print-muted text-slate-500 dark:text-slate-400">
                    Auswahl:
                </span>
                <?= e($selectedHolderName) ?>
            </div>
        </div>
    </div>

</section>
