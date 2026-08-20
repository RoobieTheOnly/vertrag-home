<?php

declare(strict_types=1);

$formAction =
    '/contracts/create';

$submitLabel =
    'Vertrag speichern';

$showStatus =
    false;

$cancelUrl =
    '/contracts';

?>

<section class="mx-auto max-w-5xl px-4 py-7 sm:px-6 sm:py-10">

    <div class="mb-8">

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
            Vertrag anlegen
        </h1>

        <p
            class="
                mt-2
                text-slate-500
                dark:text-slate-400
            "
        >
            Erfasse die Stammdaten, Laufzeit und laufenden Kosten des Vertrags.
        </p>

    </div>

    <?php
    require BASE_PATH
        . '/templates/contracts/_form.php';
    ?>

</section>
