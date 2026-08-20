<?php

declare(strict_types=1);

$activeFilterCount = 0;

if (
    !empty($selectedSearch)
) {
    $activeFilterCount++;
}

if (
    $selectedHolderId !== null
) {
    $activeFilterCount++;
}

if (
    $selectedStatus !== null
) {
    $activeFilterCount++;
}

?>

<section class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">

    <div
        class="
            mb-6
            flex
            flex-col
            justify-between
            gap-4
            sm:mb-8
            sm:flex-row
            sm:items-end
        "
    >

        <div>

            <h1
                class="
                    text-2xl
                    font-bold
                    tracking-tight
                    text-slate-900
                    sm:text-3xl
                    dark:text-white
                "
            >
                Verträge
            </h1>

            <p
                class="
                    mt-2
                    text-sm
                    text-slate-500
                    sm:text-base
                    dark:text-slate-400
                "
            >
                Aktive, geplante und historische Verträge.
            </p>

        </div>


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
                    rounded-xl
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

    </div>

<div
        data-contract-filters
        class="
            mb-5
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

        <button
            type="button"
            data-contract-filter-toggle
            aria-expanded="false"
            class="
                flex
                w-full
                items-center
                justify-between
                gap-4
                px-4
                py-4
                text-left
                transition
                hover:bg-slate-50
                sm:px-5
                dark:hover:bg-slate-800/60
            "
        >

            <div class="min-w-0">

                <div
                    class="
                        flex
                        flex-wrap
                        items-center
                        gap-2
                    "
                >

                    <span
                        class="
                            font-semibold
                            text-slate-900
                            dark:text-white
                        "
                    >
                        Filter & Suche
                    </span>

                    <span
                        data-contract-filter-badge
                        class="
                            <?= $activeFilterCount > 0
                                ? ''
                                : 'hidden' ?>
                            rounded-full
                            bg-blue-100
                            px-2.5
                            py-1
                            text-xs
                            font-semibold
                            text-blue-700
                            dark:bg-blue-950
                            dark:text-blue-300
                        "
                    >
                        <?= $activeFilterCount ?>
                        aktiv
                    </span>

                </div>

                <div
                    data-contract-filter-summary
                    class="
                        mt-1
                        truncate
                        text-xs
                        text-slate-500
                        sm:text-sm
                        dark:text-slate-400
                    "
                >
                    Zum Suchen und Filtern aufklappen
                </div>

            </div>


            <svg
                data-contract-filter-chevron
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="
                    h-5
                    w-5
                    shrink-0
                    text-slate-500
                    transition-transform
                    dark:text-slate-400
                "
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m6 9 6 6 6-6"
                />
            </svg>

        </button>


        <div
            data-contract-filter-panel
            class="
                hidden
                border-t
                border-slate-200
                p-4
                sm:p-5
                dark:border-slate-800
            "
        >

            <div
                class="
                    grid
                    gap-4
                    md:grid-cols-2
                    xl:grid-cols-[1.5fr_1fr_1fr_auto]
                    xl:items-end
                "
            >

                <div>

                    <label
                        for="contract-search"
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
                        id="contract-search"
                        type="search"
                        data-contract-search
                        value="<?= e($selectedSearch ?? '') ?>"
                        placeholder="Vertrag, Anbieter, Nummer, Vertragsart …"
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
                        for="holder"
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
                        id="holder"
                        data-contract-holder-filter
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
                                <?= (
                                    $selectedHolderId
                                    === (int) $holder['id']
                                ) ? 'selected' : '' ?>
                            >
                                <?= e($holder['name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div>

                    <label
                        for="status"
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-slate-700
                            dark:text-slate-200
                        "
                    >
                        Status
                    </label>

                    <select
                        id="status"
                        data-contract-status-filter
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
                            dark:focus:ring-blue-950
                        "
                    >

                        <option
                            value=""
                            <?= $selectedStatus === null
                                ? 'selected'
                                : '' ?>
                        >
                            Alle Status
                        </option>

                        <option
                            value="active"
                            <?= $selectedStatus === 'active'
                                ? 'selected'
                                : '' ?>
                        >
                            Aktiv
                        </option>

                        <option
                            value="paused"
                            <?= $selectedStatus === 'paused'
                                ? 'selected'
                                : '' ?>
                        >
                            Pausiert
                        </option>

                        <option
                            value="planned"
                            <?= $selectedStatus === 'planned'
                                ? 'selected'
                                : '' ?>
                        >
                            Geplant
                        </option>

                        <option
                            value="cancelled"
                            <?= $selectedStatus === 'cancelled'
                                ? 'selected'
                                : '' ?>
                        >
                            Gekündigt
                        </option>

                        <option
                            value="expired"
                            <?= $selectedStatus === 'expired'
                                ? 'selected'
                                : '' ?>
                        >
                            Beendet
                        </option>

                    </select>

                </div>


                <button
                    type="button"
                    data-contract-filter-reset
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

        </div>

    </div>


    <div
        data-contract-filter-count
        class="
            mb-3
            text-sm
            text-slate-500
            dark:text-slate-400
        "
    >
        <?= count($contracts) ?>
        von
        <?= count($contracts) ?>
        Verträgen
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

        <?php if (empty($contracts)): ?>

            <div
                class="
                    px-6
                    py-16
                    text-center
                "
            >

                <div
                    class="
                        text-lg
                        font-semibold
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Noch keine Verträge vorhanden
                </div>

                <p
                    class="
                        mt-2
                        text-sm
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Lege den ersten Vertrag an, um mit der Vertragsübersicht zu beginnen.
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
                    md:grid
                    md:grid-cols-[1.45fr_0.9fr_0.9fr_0.9fr_0.7fr_0.75fr_0.9fr]
                    md:gap-4
                    dark:border-slate-800
                    dark:bg-slate-800/70
                    dark:text-slate-400
                "
            >
                <div>Vertrag</div>
                <div>Inhaber</div>
                <div>Vertragsart</div>
                <div>Anbieter</div>
                <div>Status</div>
                <div class="text-right">Betrag</div>
                <div>Nächste Abbuchung</div>
            </div>


            <div
                class="
                    divide-y
                    divide-slate-200
                    dark:divide-slate-800
                "
            >

                <?php foreach (
                    $contracts
                    as $contract
                ): ?>

                    <?php
                    $rowPaused =
                        !empty(
                            $contract[
                                'pause_state'
                            ][
                                'is_paused'
                            ]
                        );

                    $rowHistorical =
                        contract_is_historical(
                            $contract
                        );

                    $rowCancellationDate =
                        contract_cancellation_effective_date(
                            $contract
                        );
                    ?>

                    <div
                        data-contract-row
                        data-href="/contracts/<?= (int) $contract['id'] ?>"
                        data-holder="<?= (int) (
                            $contract[
                                'contract_holder_id'
                            ]
                            ?? 0
                        ) ?>"
                        data-status="<?= e(
                            $contract['status']
                        ) ?>"
                        data-statuses="<?= e(
                            trim(
                                $contract['status']
                                . (
                                    $rowPaused
                                        ? ' paused'
                                        : ''
                                )
                            )
                        ) ?>"
                        data-monthly-cost="<?= e(
                            (string) (
                                $contract[
                                    'calculated_monthly_cost'
                                ] ?? 0
                            )
                        ) ?>"
                        data-annual-cost="<?= e(
                            (string) (
                                $contract[
                                    'calculated_annual_cost'
                                ] ?? 0
                            )
                        ) ?>"
                        data-search="<?= e(
                            implode(
                                ' ',
                                [
                                    $contract['title'] ?? '',
                                    $contract['provider'] ?? '',
                                    $contract['contract_number'] ?? '',
                                    $contract['customer_number'] ?? '',
                                    $contract['contract_type'] ?? '',
                                    $contract['contract_holder_name'] ?? '',
                                    contract_status_label(
                                        $contract['status']
                                    ),
                                    $rowPaused
                                        ? 'Pausiert Pause'
                                        : '',
                                    $rowCancellationDate
                                        ? 'gekündigt zum '
                                            . $rowCancellationDate->format(
                                                'd.m.Y'
                                            )
                                        : '',
                                    contract_billing_frequency_label(
                                        $contract['billing_frequency']
                                    ),
                                    (string) ($contract['amount'] ?? ''),
                                ]
                            )
                        ) ?>"
                        role="link"
                        tabindex="0"
                        aria-label="<?= e(
                            'Vertrag '
                            . $contract['title']
                            . ' öffnen'
                        ) ?>"
                        class="
                            group
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
                            md:grid
                            md:grid-cols-[1.45fr_0.9fr_0.9fr_0.9fr_0.7fr_0.75fr_0.9fr]
                            md:items-center
                            md:gap-4
                            dark:hover:bg-slate-800/60
                            dark:focus:bg-blue-950/40
                        "
                    >

                        <div class="min-w-0">

                            <div
                                class="
                                    truncate
                                    font-semibold
                                    text-slate-900
                                    transition
                                    group-hover:text-blue-600
                                    dark:text-white
                                    dark:group-hover:text-blue-400
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
                                <?php if (
                                    !empty(
                                        $contract[
                                            'contract_number'
                                        ]
                                    )
                                ): ?>
                                    Vertragsnr.
                                    <?= e(
                                        $contract[
                                            'contract_number'
                                        ]
                                    ) ?>
                                <?php else: ?>
                                    <?= e(
                                        contract_billing_frequency_label(
                                            $contract[
                                                'billing_frequency'
                                            ]
                                        )
                                    ) ?>
                                <?php endif; ?>
                            </div>

                        </div>


                        <div
                            class="
                                mt-4
                                grid
                                grid-cols-2
                                gap-x-4
                                gap-y-3
                                md:contents
                            "
                        >

                            <div>
                                <div
                                    class="
                                        text-xs
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                        md:hidden
                                    "
                                >
                                    Inhaber
                                </div>
                                <div class="mt-1 md:mt-0">
                                    <?= e(
                                        $contract[
                                            'contract_holder_name'
                                        ]
                                    ) ?>
                                </div>
                            </div>


                            <div>
                                <div
                                    class="
                                        text-xs
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                        md:hidden
                                    "
                                >
                                    Vertragsart
                                </div>
                                <div class="mt-1 md:mt-0">
                                    <?= e(
                                        $contract[
                                            'contract_type'
                                        ]
                                    ) ?>
                                </div>
                            </div>


                            <div>
                                <div
                                    class="
                                        text-xs
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                        md:hidden
                                    "
                                >
                                    Anbieter
                                </div>
                                <div class="mt-1 truncate md:mt-0">
                                    <?= e(
                                        $contract[
                                            'provider'
                                        ]
                                    ) ?>
                                </div>
                            </div>


                            <div>
                                <div
                                    class="
                                        text-xs
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                        md:hidden
                                    "
                                >
                                    Status
                                </div>
                                <div class="mt-1 md:mt-0">

                                    <?php if ($rowPaused): ?>

                                        <span
                                            class="
                                                inline-flex
                                                rounded-full
                                                bg-violet-100
                                                px-2.5
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
                                                inline-flex
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

                                    <?php elseif (
                                        $contract['status']
                                        === 'cancelled'
                                    ): ?>

                                        <span
                                            class="
                                                inline-flex
                                                rounded-full
                                                bg-amber-100
                                                px-2.5
                                                py-1
                                                text-xs
                                                font-semibold
                                                text-amber-800
                                                dark:bg-amber-950
                                                dark:text-amber-300
                                            "
                                        >
                                            <?= $rowCancellationDate !== null
                                                && !$rowHistorical
                                                    ? 'Gekündigt zum '
                                                        . e(
                                                            $rowCancellationDate->format(
                                                                'd.m.Y'
                                                            )
                                                        )
                                                    : 'Gekündigt' ?>
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="
                                                inline-flex
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
                                            <?= e(
                                                contract_status_label(
                                                    $contract[
                                                        'status'
                                                    ]
                                                )
                                            ) ?>
                                        </span>

                                    <?php endif; ?>

                                    <?php if (
                                        $rowPaused
                                        && $contract['status']
                                            === 'cancelled'
                                        && $rowCancellationDate
                                            !== null
                                    ): ?>

                                        <span
                                            class="
                                                ml-1
                                                inline-flex
                                                rounded-full
                                                bg-amber-100
                                                px-2.5
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
                                                $rowCancellationDate->format(
                                                    'd.m.Y'
                                                )
                                            ) ?>
                                        </span>

                                    <?php endif; ?>

                                </div>
                            </div>


                            <div>
                                <div
                                    class="
                                        text-xs
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                        md:hidden
                                    "
                                >
                                    Betrag
                                </div>
                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        md:mt-0
                                        md:text-right
                                    "
                                >
                                    <?= e(
                                        contract_format_money(
                                            (float) $contract[
                                                'amount'
                                            ]
                                        )
                                    ) ?>
                                </div>
                            </div>


                            <div>
                                <div
                                    class="
                                        text-xs
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                        md:hidden
                                    "
                                >
                                    Nächste Abbuchung
                                </div>
                                <div class="mt-1 md:mt-0">
                                    <?= e(
                                        contract_format_date(
                                            $contract[
                                                'calculated_next_payment_date'
                                            ]
                                            ?? null
                                        )
                                    ) ?>
                                </div>
                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>


                <div
                    data-contract-filter-empty
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
                    Für die gewählte Suche und Filterkombination wurden keine Verträge gefunden.
                </div>

                <div
                    data-contract-total-row
                    class="
                        hidden
                        border-t
                        border-slate-200
                        bg-slate-50
                        px-5
                        py-4
                        dark:border-slate-800
                        dark:bg-slate-800/70
                    "
                >
                    <div
                        class="
                            flex
                            flex-col
                            gap-3
                            sm:flex-row
                            sm:items-center
                            sm:justify-between
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
                                Gesamt der sichtbaren Verträge
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-xs
                                    text-slate-500
                                    dark:text-slate-400
                                "
                            >
                                Vergleichswerte werden auf Monat und Jahr normiert.
                            </div>
                        </div>

                        <div
                            class="
                                grid
                                grid-cols-2
                                gap-5
                                sm:text-right
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
                                    Monatlich
                                </div>
                                <div
                                    data-contract-total-monthly
                                    class="
                                        mt-1
                                        font-bold
                                        text-slate-900
                                        dark:text-white
                                    "
                                >
                                    0,00 €
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
                                    Jährlich
                                </div>
                                <div
                                    data-contract-total-annual
                                    class="
                                        mt-1
                                        font-bold
                                        text-slate-900
                                        dark:text-white
                                    "
                                >
                                    0,00 €
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        <?php endif; ?>

    </div>

</section>
