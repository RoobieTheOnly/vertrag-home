<?php

declare(strict_types=1);

$selectedHolderLabel =
    $selectedHolderName
    ?? 'Alle Vertragsinhaber';

$contractCount =
    (int) (
        $planningStats['contract_count']
        ?? 0
    );

$holderCount =
    (int) (
        $planningStats['holder_count']
        ?? 0
    );

$eventCount30 =
    (int) (
        $planningStats['event_count_30']
        ?? 0
    );

$total30 =
    (float) (
        $planningStats['total_30']
        ?? 0
    );

$total90 =
    (float) (
        $planningStats['total_90']
        ?? 0
    );

$total365 =
    (float) (
        $planningStats['total_365']
        ?? 0
    );

$nextPaymentDate =
    $planningStats['next_payment_date']
    ?? null;

?>

<section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.95fr)]">

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-white via-white to-cyan-50 p-6 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900">

            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

                <div class="min-w-0">

                    <div class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700 dark:border-cyan-900 dark:bg-cyan-950/40 dark:text-cyan-300">
                        Eigene Ausgabenplanung
                    </div>

                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                        Ausgaben vorausschauend planen
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 dark:text-slate-300 sm:text-base">
                        Geplante Abbuchungen nach Zeitraum und Vertragsinhaber.
                        Zusätzlich werden Durchschnitt, Spitzenzeiträume und größte
                        Einzelbelastungen ausgewiesen.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-3">

                        <div class="rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Aktueller Fokus
                            </div>
                            <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                                <?= e($selectedHolderLabel) ?>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Laufende Verträge
                            </div>
                            <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                                <?= $contractCount ?>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
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

                    <a href="/dashboard" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        Zur Übersicht
                    </a>

                    <a href="/reports/financial-overview" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        Finanzübersicht / Auszug
                    </a>

                    <a href="/contracts" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700 dark:hover:bg-cyan-500">
                        Verträge prüfen
                    </a>

                </div>

            </div>

        </div>


        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Auswertungen
                    </div>
                    <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                        Zusätzliche Kennzahlen
                    </h2>
                </div>
            </div>

            <div class="mt-5 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="font-semibold text-slate-900 dark:text-white">Spitzen früh erkennen</div>
                    <p class="mt-1">Zeiträume mit besonders hohen Abbuchungen werden hervorgehoben.</p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="font-semibold text-slate-900 dark:text-white">Belastung je Vertrag bewerten</div>
                    <p class="mt-1">Verträge werden anhand ihrer Belastung im gewählten Zeitraum ausgewertet.</p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="font-semibold text-slate-900 dark:text-white">Nach Inhaber filtern</div>
                    <p class="mt-1">Die geplanten Ausgaben können getrennt nach Vertragsinhaber ausgewertet werden.</p>
                </div>
            </div>

        </div>

    </div>


    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Nächste 30 Tage</div>
            <div class="mt-3 text-3xl font-bold text-slate-900 dark:text-white"><?= e(contract_format_money($total30)) ?></div>
            <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"><?= $eventCount30 ?> geplante Abbuchung(en) innerhalb des nächsten Monats ab heute.</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Nächste 3 Monate</div>
            <div class="mt-3 text-3xl font-bold text-slate-900 dark:text-white"><?= e(contract_format_money($total90)) ?></div>
            <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">Kurzfristige Belastungen und saisonale Spitzen im Betrachtungszeitraum.</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Nächste 12 Monate</div>
            <div class="mt-3 text-3xl font-bold text-slate-900 dark:text-white"><?= e(contract_format_money($total365)) ?></div>
            <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">Erwartete Gesamtabbuchungen im nächsten Jahr auf Basis der aktuellen Verträge.</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Vertragsinhaber</div>
            <div class="mt-3 text-3xl font-bold text-slate-900 dark:text-white"><?= $holderCount ?></div>
            <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">Anzahl der berücksichtigten Vertragsinhaber im aktuell gewählten Betrachtungsumfang.</p>
        </div>

    </div>


    <div data-payment-planner class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

        <div class="border-b border-slate-200 px-4 py-5 dark:border-slate-800 sm:px-6">

            <div class="flex flex-col gap-5 2xl:flex-row 2xl:items-end 2xl:justify-between">

                <div class="min-w-0">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Interaktive Ausgabenplanung</div>
                    <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Diagramm und Zusatzinformationen in einer Ansicht</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Zeitraum und Vertragsinhaber lassen sich direkt umschalten. Angezeigt werden Gesamtsumme, Durchschnitt, Spitzenzeitraum und größte Kostentreiber.
                    </p>
                </div>

                <div class="grid w-full gap-4 lg:grid-cols-2 lg:items-end 2xl:w-[48rem]">

                    <div class="min-w-0 lg:min-w-[18rem]">
                        <label for="payment-holder" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Vertragsinhaber</label>
                        <select id="payment-holder" data-payment-holder class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-cyan-950">
                            <option value=""<?= $selectedHolderId === null ? ' selected' : '' ?>>Alle Vertragsinhaber</option>
                            <?php foreach ($contractHolders as $holder): ?>
                                <option value="<?= (int) $holder['id'] ?>"<?= $selectedHolderId !== null && (int) $selectedHolderId === (int) $holder['id'] ? ' selected' : '' ?>>
                                    <?= e($holder['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="min-w-0 lg:min-w-[21rem]">
                        <div class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Zeitraum</div>
                        <div class="grid h-11 w-full grid-cols-3 gap-1 rounded-2xl bg-slate-100 p-1 dark:bg-slate-800">
                            <button type="button" data-payment-range="30" class="flex h-9 items-center justify-center whitespace-nowrap rounded-xl bg-white px-2 text-xs font-semibold text-slate-900 shadow-sm transition sm:text-sm dark:bg-slate-700 dark:text-white">1 Monat</button>
                            <button type="button" data-payment-range="90" class="flex h-9 items-center justify-center whitespace-nowrap rounded-xl px-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 sm:text-sm dark:text-slate-400 dark:hover:text-white">3 Monate</button>
                            <button type="button" data-payment-range="365" class="flex h-9 items-center justify-center whitespace-nowrap rounded-xl px-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 sm:text-sm dark:text-slate-400 dark:hover:text-white">1 Jahr</button>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="p-4 sm:p-6">

            <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Zeitraum</div>
                    <div data-payment-range-label class="mt-1.5 font-semibold text-slate-900 dark:text-white">Nächster Monat ab heute</div>
                    <div data-payment-active-holder class="mt-1 text-xs text-slate-500 dark:text-slate-400">Alle Vertragsinhaber</div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Ausgaben</div>
                    <div data-payment-total class="mt-1.5 text-xl font-bold text-slate-900 dark:text-white">0,00 €</div>
                    <div data-payment-average class="mt-1 text-xs text-slate-500 dark:text-slate-400">Ø pro Monat: 0,00 €</div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Abbuchungen</div>
                    <div data-payment-count class="mt-1.5 text-xl font-bold text-slate-900 dark:text-white">0</div>
                    <div data-payment-covered-contracts class="mt-1 text-xs text-slate-500 dark:text-slate-400">0 Verträge betroffen</div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nächste Abbuchung</div>
                    <div data-payment-next class="mt-1.5 text-xl font-bold text-slate-900 dark:text-white">–</div>
                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Frühester Termin im aktuellen Filter</div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Größte Belastung</div>
                    <div data-payment-largest class="mt-1.5 text-xl font-bold text-slate-900 dark:text-white">–</div>
                    <div data-payment-largest-meta class="mt-1 text-xs text-slate-500 dark:text-slate-400">Noch keine Abbuchung im Zeitraum</div>
                </div>

            </div>

            <div class="mb-5 grid gap-3 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Höchste Phase im Zeitraum</div>
                    <div data-payment-peak class="mt-1 text-lg font-bold text-slate-900 dark:text-white">–</div>
                    <div data-payment-peak-meta class="mt-1 text-sm text-slate-500 dark:text-slate-400">Wird anhand des aktuell gewählten Zeitraums berechnet.</div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Interpretation</div>
                    <div class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Berücksichtigt werden Spitzenzeiträume, die Anzahl betroffener Verträge und der Anteil größerer Einzelabbuchungen an der Gesamtsumme.
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 px-2 pb-3 pt-4 dark:border-slate-800 dark:bg-slate-950/40 sm:px-4">
                <div data-payment-chart-shell class="flex min-w-0 gap-2">
                    <div data-payment-chart-scale class="relative h-52 w-12 shrink-0 sm:h-60 sm:w-16" aria-hidden="true"></div>
                    <div class="relative min-w-0 flex-1">
                        <div data-payment-chart-grid class="pointer-events-none absolute inset-x-0 bottom-6 top-0" aria-hidden="true"></div>
                        <div data-payment-chart class="relative z-10 grid h-52 w-full min-w-0 items-end gap-1.5 sm:h-60 sm:gap-2" aria-label="Diagramm der erwarteten Abbuchungen"></div>
                    </div>
                </div>

                <div data-payment-chart-empty class="hidden py-16 text-center text-sm text-slate-500 dark:text-slate-400">
                    Für diesen Zeitraum sind keine Abbuchungen geplant.
                </div>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(320px,0.95fr)]">

                <div>
                    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Abbuchungen im Zeitraum</h3>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Vertrag antippen oder anklicken, um die Details zu öffnen.</span>
                    </div>
                    <div data-payment-event-list class="grid gap-3 lg:grid-cols-2"></div>
                </div>

                <div class="grid gap-6">

                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">Top-Verträge im Zeitraum</div>
                        <div class="mt-3 grid gap-3" data-payment-top-contracts>
                            <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                Wird nach dem Laden berechnet.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">Verteilung im Zeitraum</div>
                        <div class="mt-3 grid gap-2" data-payment-breakdown>
                            <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                Wird nach dem Laden berechnet.
                            </div>
                        </div>
                    </div>

                </div>

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


    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Ausgaben nach Vertragsinhaber</div>
                <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">12-Monats-Belastung im Vergleich</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Zeigt, wie stark die geplanten Abbuchungen im nächsten Jahr je Vertragsinhaber ausfallen.</p>
            </div>

            <div class="p-5 sm:p-6">
                <?php if (empty($planningByHolder)): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Derzeit liegen keine geplanten Abbuchungen vor.
                    </div>
                <?php else: ?>
                    <div class="grid gap-3">
                        <?php foreach ($planningByHolder as $holderItem): ?>
                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="text-base font-semibold text-slate-900 dark:text-white"><?= e($holderItem['name']) ?></div>
                                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= (int) $holderItem['event_count'] ?> geplante Abbuchung(en) im nächsten Jahr</div>
                                    </div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white">
                                        <?= e(contract_format_money((float) $holderItem['total_365'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Hinweise zur Ausgabenplanung</div>
            <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Planungshinweise</h2>

            <div class="mt-5 space-y-4 text-sm leading-6 text-slate-600 dark:text-slate-300">
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="font-semibold text-slate-900 dark:text-white">1. Große Einzelbelastungen</div>
                    <p class="mt-1">Einzelne hohe Abbuchungen können die Belastung eines Monats deutlich erhöhen.</p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="font-semibold text-slate-900 dark:text-white">2. Monatliche Spitzen</div>
                    <p class="mt-1">Jährliche oder quartalsweise Abbuchungen können zu konzentrierten Belastungen in einzelnen Monaten führen.</p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <div class="font-semibold text-slate-900 dark:text-white">3. Vertragsinhaber-Vergleich</div>
                    <p class="mt-1">Die getrennte Darstellung nach Vertragsinhaber zeigt die jeweilige Belastung innerhalb des Haushalts.</p>
                </div>
            </div>
        </div>

    </div>

</section>
