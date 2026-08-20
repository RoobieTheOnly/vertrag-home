<?php

declare(strict_types=1);

$formAction =
    $formAction ?? '/contracts/create';

$submitLabel =
    $submitLabel ?? 'Vertrag speichern';

$showStatus =
    $showStatus ?? false;

$selectedStatus =
    $values['status']
    ?? 'active';

$selectedFrequency =
    $values['billing_frequency']
    ?? 'monthly';

?>

<?php if (!empty($error)): ?>

    <div
        class="
            mb-6
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


<form
    method="post"
    action="<?= e($formAction) ?>"
    class="space-y-6"
>

    <?= csrf_field() ?>


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

        <h2 class="mb-5 text-lg font-semibold">
            Vertrag
        </h2>


        <div
            class="
                grid
                gap-5
                md:grid-cols-2
            "
        >

            <div>

                <label
                    for="title"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Bezeichnung *
                </label>

                <input
                    id="title"
                    type="text"
                    name="title"
                    required
                    value="<?= e($values['title'] ?? '') ?>"
                    placeholder="z. B. Vodafone Kabel"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
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

            </div>


            <div>

                <label
                    for="contract_type_id"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Vertragsart *
                </label>

                <select
                    id="contract_type_id"
                    name="contract_type_id"
                    required
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

                    <option value="">
                        Bitte auswählen
                    </option>

                    <?php foreach ($contractTypes as $type): ?>

                        <option
                            value="<?= (int) $type['id'] ?>"
                            <?= (
                                (string) (
                                    $values[
                                        'contract_type_id'
                                    ] ?? ''
                                )
                                ===
                                (string) $type['id']
                            ) ? 'selected' : '' ?>
                        >
                            <?= e($type['name']) ?><?= (int) ($type['is_active'] ?? 1) === 0 ? ' (inaktiv)' : '' ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div>

                <label
                    for="provider"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Anbieter *
                </label>

                <input
                    id="provider"
                    type="text"
                    name="provider"
                    required
                    value="<?= e($values['provider'] ?? '') ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
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

            </div>


            <div>

                <label
                    for="contract_holder_id"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Vertragsinhaber *
                </label>

                <select
                    id="contract_holder_id"
                    name="contract_holder_id"
                    required
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

                    <option value="">
                        Bitte auswählen
                    </option>

                    <?php foreach ($contractHolders as $holder): ?>

                        <option
                            value="<?= (int) $holder['id'] ?>"
                            <?= (
                                (string) (
                                    $values[
                                        'contract_holder_id'
                                    ] ?? ''
                                )
                                ===
                                (string) $holder['id']
                            ) ? 'selected' : '' ?>
                        >
                            <?= e($holder['name']) ?><?= (int) ($holder['is_active'] ?? 1) === 0 ? ' (inaktiv)' : '' ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div>

                <label
                    for="contract_number"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Vertragsnummer
                </label>

                <input
                    id="contract_number"
                    type="text"
                    name="contract_number"
                    value="<?= e(
                        $values[
                            'contract_number'
                        ] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

            </div>


            <div>

                <label
                    for="customer_number"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Kundennummer
                </label>

                <input
                    id="customer_number"
                    type="text"
                    name="customer_number"
                    value="<?= e(
                        $values[
                            'customer_number'
                        ] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

            </div>


            <?php if ($showStatus): ?>

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
                        Status *
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                        class="
                            w-full
                            rounded-xl
                            border
                            border-slate-300
                            bg-white
                            px-4
                            py-3
                            text-slate-900
                            dark:border-slate-700
                            dark:bg-slate-800
                            dark:text-white
                        "
                    >

                        <option
                            value="active"
                            <?= $selectedStatus === 'active'
                                ? 'selected'
                                : '' ?>
                        >
                            Aktiv
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

                    <p
                        class="
                            mt-2
                            text-xs
                            text-slate-500
                            dark:text-slate-400
                        "
                    >
                        Nur aktive Verträge werden als laufende Kosten berücksichtigt.
                    </p>

                </div>

            <?php else: ?>

                <input
                    type="hidden"
                    name="status"
                    value="active"
                >

            <?php endif; ?>

        </div>


        <div class="mt-5">

            <label
                for="description"
                class="
                    mb-2
                    block
                    text-sm
                    font-medium
                    text-slate-700
                    dark:text-slate-200
                "
            >
                Beschreibung
            </label>

            <textarea
                id="description"
                name="description"
                rows="3"
                class="
                    w-full
                    rounded-xl
                    border
                    border-slate-300
                    bg-white
                    px-4
                    py-3
                    text-slate-900
                    dark:border-slate-700
                    dark:bg-slate-800
                    dark:text-white
                "
            ><?= e($values['description'] ?? '') ?></textarea>

        </div>

    </div>


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

        <h2 class="mb-5 text-lg font-semibold">
            Laufzeit und Kündigung
        </h2>


        <div
            class="
                grid
                gap-5
                md:grid-cols-3
            "
        >

            <div>

                <label
                    for="start_date"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Vertragsbeginn
                </label>

                <input
                    id="start_date"
                    type="date"
                    name="start_date"
                    value="<?= e(
                        $values['start_date'] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

            </div>


            <div>

                <label
                    for="end_date"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Vertragsende
                </label>

                <input
                    id="end_date"
                    type="date"
                    name="end_date"
                    value="<?= e(
                        $values['end_date'] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

            </div>


            <div>

                <label
                    for="minimum_term_months"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Mindestlaufzeit in Monaten
                </label>

                <input
                    id="minimum_term_months"
                    type="number"
                    name="minimum_term_months"
                    min="0"
                    value="<?= e(
                        $values[
                            'minimum_term_months'
                        ] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

            </div>


            <div>

                <label
                    for="notice_period_value"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Kündigungsfrist
                </label>

                <input
                    id="notice_period_value"
                    type="number"
                    name="notice_period_value"
                    min="0"
                    value="<?= e(
                        $values[
                            'notice_period_value'
                        ] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

            </div>


            <div>

                <label
                    for="notice_period_unit"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Einheit
                </label>

                <select
                    id="notice_period_unit"
                    name="notice_period_unit"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >
                    <option value="">–</option>

                    <option
                        value="days"
                        <?= (
                            ($values[
                                'notice_period_unit'
                            ] ?? '')
                            === 'days'
                        ) ? 'selected' : '' ?>
                    >
                        Tage
                    </option>

                    <option
                        value="weeks"
                        <?= (
                            ($values[
                                'notice_period_unit'
                            ] ?? '')
                            === 'weeks'
                        ) ? 'selected' : '' ?>
                    >
                        Wochen
                    </option>

                    <option
                        value="months"
                        <?= (
                            ($values[
                                'notice_period_unit'
                            ] ?? '')
                            === 'months'
                        ) ? 'selected' : '' ?>
                    >
                        Monate
                    </option>

                </select>

            </div>


            <div>

                <label
                    for="renewal_period_months"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Verlängerung um Monate
                </label>

                <input
                    id="renewal_period_months"
                    type="number"
                    name="renewal_period_months"
                    min="0"
                    value="<?= e(
                        $values[
                            'renewal_period_months'
                        ] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

            </div>

        </div>


        <label
            class="
                mt-5
                flex
                items-center
                gap-3
                text-sm
                font-medium
                text-slate-700
                dark:text-slate-200
            "
        >

            <input
                type="checkbox"
                name="automatic_renewal"
                value="1"
                <?= !empty(
                    $values[
                        'automatic_renewal'
                    ]
                ) ? 'checked' : '' ?>
                class="
                    h-4
                    w-4
                    rounded
                    border-slate-300
                "
            >

            Vertrag verlängert sich automatisch

        </label>



        <label
            class="
                mt-4
                flex
                items-start
                gap-3
                rounded-xl
                border
                border-slate-200
                bg-slate-50
                p-4
                dark:border-slate-800
                dark:bg-slate-800/50
            "
        >

            <input
                type="checkbox"
                name="notifications_enabled"
                value="1"
                <?= (
                    $values[
                        'notifications_enabled'
                    ] ?? '1'
                ) === '1' ? 'checked' : '' ?>
                class="
                    mt-0.5
                    h-4
                    w-4
                    rounded
                    border-slate-300
                "
            >

            <span>
                <span
                    class="
                        block
                        text-sm
                        font-semibold
                        text-slate-800
                        dark:text-slate-100
                    "
                >
                    Benachrichtigungen zu diesem Vertrag anzeigen
                </span>

                <span
                    class="
                        mt-1
                        block
                        text-xs
                        leading-5
                        text-slate-500
                        dark:text-slate-400
                    "
                >
                    Betrifft Hinweise zu Vertragsende, Verlängerung und hinterlegter Kündigung.
                    Fristen, Kosten und Ausgabenplanung bleiben unabhängig davon aktiv.
                </span>
            </span>

        </label>

    </div>


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

        <h2 class="mb-5 text-lg font-semibold">
            Kosten und Abbuchung
        </h2>


        <div
            class="
                grid
                gap-5
                md:grid-cols-2
                xl:grid-cols-3
            "
        >

            <div>

                <label
                    for="amount"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Betrag *
                </label>

                <input
                    id="amount"
                    type="number"
                    name="amount"
                    required
                    min="0"
                    step="0.01"
                    value="<?= e(
                        $values['amount'] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

            </div>


            <div>

                <label
                    for="price_valid_from"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Preis gültig ab
                </label>

                <input
                    id="price_valid_from"
                    type="date"
                    name="price_valid_from"
                    value="<?= e(
                        $values[
                            'price_valid_from'
                        ]
                        ?? (
                            new DateTimeImmutable(
                                'today'
                            )
                        )->format(
                            'Y-m-d'
                        )
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
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
                    Bei einer Preisänderung wird ab diesem Datum ein neuer Eintrag
                    in der Kostenhistorie angelegt.
                </p>

            </div>


            <?php if (
                isset($contract)
            ): ?>

            <div>

                <label
                    for="price_change_reason"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Grund der Preisänderung
                </label>

                <input
                    id="price_change_reason"
                    type="text"
                    name="price_change_reason"
                    maxlength="500"
                    value="<?= e(
                        $values[
                            'price_change_reason'
                        ] ?? ''
                    ) ?>"
                    placeholder="z. B. Tarifanpassung"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                        dark:placeholder:text-slate-500
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
                    Wird nur verwendet, wenn sich Betrag oder Abrechnungsintervall ändern.
                </p>

            </div>


            <?php endif; ?>


            <div>

                <label
                    for="billing_frequency"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Abrechnung *
                </label>

                <select
                    id="billing_frequency"
                    name="billing_frequency"
                    required
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
                    "
                >

                    <option
                        value="monthly"
                        <?= $selectedFrequency === 'monthly'
                            ? 'selected'
                            : '' ?>
                    >
                        Monatlich
                    </option>

                    <option
                        value="quarterly"
                        <?= $selectedFrequency === 'quarterly'
                            ? 'selected'
                            : '' ?>
                    >
                        Vierteljährlich
                    </option>

                    <option
                        value="semiannual"
                        <?= $selectedFrequency === 'semiannual'
                            ? 'selected'
                            : '' ?>
                    >
                        Halbjährlich
                    </option>

                    <option
                        value="annual"
                        <?= $selectedFrequency === 'annual'
                            ? 'selected'
                            : '' ?>
                    >
                        Jährlich
                    </option>

                    <option
                        value="one_time"
                        <?= $selectedFrequency === 'one_time'
                            ? 'selected'
                            : '' ?>
                    >
                        Einmalig
                    </option>

                    <option
                        value="custom"
                        <?= $selectedFrequency === 'custom'
                            ? 'selected'
                            : '' ?>
                    >
                        Individuell
                    </option>

                </select>

            </div>


            <div>

                <label
                    for="custom_billing_months"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Individuelles Intervall
                </label>

                <input
                    id="custom_billing_months"
                    type="number"
                    name="custom_billing_months"
                    min="1"
                    value="<?= e(
                        $values[
                            'custom_billing_months'
                        ] ?? ''
                    ) ?>"
                    placeholder="Monate"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
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
                    Nur bei „Individuell“, z. B. 2 für alle zwei Monate.
                </p>

            </div>


            <div>

                <label
                    for="first_payment_date"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Erster Abbuchungstermin *
                </label>

                <input
                    id="first_payment_date"
                    type="date"
                    name="first_payment_date"
                    required
                    value="<?= e(
                        $values[
                            'first_payment_date'
                        ] ?? ''
                    ) ?>"
                    class="
                        w-full
                        rounded-xl
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-3
                        text-slate-900
                        dark:border-slate-700
                        dark:bg-slate-800
                        dark:text-white
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
                    Der nächste Abbuchungstermin wird danach automatisch anhand
                    des Abrechnungsintervalls und des aktuellen Datums berechnet.
                </p>

            </div>

        </div>

    </div>


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

        <h2 class="mb-5 text-lg font-semibold">
            Notizen
        </h2>

        <textarea
            name="notes"
            rows="5"
            placeholder="Zusätzliche Hinweise zum Vertrag"
            class="
                w-full
                rounded-xl
                border
                border-slate-300
                bg-white
                px-4
                py-3
                text-slate-900
                dark:border-slate-700
                dark:bg-slate-800
                dark:text-white
            "
        ><?= e($values['notes'] ?? '') ?></textarea>

    </div>


    <div
        class="
            flex
            flex-col-reverse
            justify-end
            gap-3
            sm:flex-row
        "
    >

        <a
            href="<?= e($cancelUrl ?? '/contracts') ?>"
            class="
                rounded-xl
                border
                border-slate-300
                px-5
                py-3
                text-center
                font-semibold
                text-slate-700
                hover:bg-slate-50
                dark:border-slate-700
                dark:text-slate-200
                dark:hover:bg-slate-800
            "
        >
            Abbrechen
        </a>

        <button
            type="submit"
            class="
                rounded-xl
                bg-blue-600
                px-5
                py-3
                font-semibold
                text-white
                hover:bg-blue-700
                dark:hover:bg-blue-500
            "
        >
            <?= e($submitLabel) ?>
        </button>

    </div>

</form>
