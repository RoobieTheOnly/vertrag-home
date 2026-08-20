<?php

declare(strict_types=1);

$formAction =
    '/admin/contract-types/create';

$submitLabel =
    'Vertragsart anlegen';

?>

<section class="mx-auto max-w-3xl px-6 py-10">

    <div class="mb-8">

        <a
            href="/admin/contract-types"
            class="
                text-sm
                font-medium
                text-blue-600
                hover:text-blue-700
                dark:text-blue-400
                dark:hover:text-blue-300
            "
        >
            ← Vertragsarten
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
            Vertragsart anlegen
        </h1>

    </div>

    <?php
    require BASE_PATH
        . '/templates/admin/contract-types/_form.php';
    ?>

</section>
