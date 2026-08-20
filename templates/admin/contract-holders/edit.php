<?php

declare(strict_types=1);

$formAction =
    '/admin/contract-holders/'
    . (int) $contractHolder['id']
    . '/edit';

$submitLabel =
    'Änderungen speichern';

?>

<section class="mx-auto max-w-3xl px-6 py-10">

    <div class="mb-8">

        <a
            href="/admin/contract-holders"
            class="
                text-sm
                font-medium
                text-blue-600
                hover:text-blue-700
                dark:text-blue-400
                dark:hover:text-blue-300
            "
        >
            ← Vertragsinhaber
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
            Vertragsinhaber bearbeiten
        </h1>

    </div>

    <?php
    require BASE_PATH
        . '/templates/admin/contract-holders/_form.php';
    ?>

</section>
