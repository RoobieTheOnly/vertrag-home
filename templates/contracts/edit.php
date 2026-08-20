<?php

declare(strict_types=1);

$formAction =
    '/contracts/'
    . (int) $contract['id']
    . '/edit';

$submitLabel =
    'Änderungen speichern';

$showStatus =
    true;

$cancelUrl =
    '/contracts/'
    . (int) $contract['id'];

?>

<section class="mx-auto max-w-5xl px-4 py-7 sm:px-6 sm:py-10">

    <div class="mb-8">

        <a
            href="/contracts/<?= (int) $contract['id'] ?>"
            class="
                text-sm
                font-medium
                text-blue-600
                hover:text-blue-700
                dark:text-blue-400
                dark:hover:text-blue-300
            "
        >
            ← Zurück zum Vertrag
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
            Vertrag bearbeiten
        </h1>

        <p
            class="
                mt-2
                text-slate-500
                dark:text-slate-400
            "
        >
            Änderungen an Status, Laufzeit, Vertragsinhaber und Kosten werden direkt übernommen.
        </p>

    </div>

    <?php
    require BASE_PATH
        . '/templates/contracts/_form.php';
    ?>

</section>
