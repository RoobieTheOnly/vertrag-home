<?php

declare(strict_types=1);

$formAction =
    '/admin/users/'
    . (int) $editingUser['id']
    . '/edit';

$submitLabel =
    'Änderungen speichern';

?>

<section class="mx-auto max-w-4xl px-6 py-10">

    <div class="mb-8">

        <a
            href="/admin/users"
            class="
                text-sm
                font-medium
                text-blue-600
                hover:text-blue-700
                dark:text-blue-400
                dark:hover:text-blue-300
            "
        >
            ← Benutzer
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
            Benutzer bearbeiten
        </h1>

        <p
            class="
                mt-2
                text-slate-500
                dark:text-slate-400
            "
        >
            <?= e($editingUser['display_name']) ?>
            ·
            <?= e($editingUser['username']) ?>
        </p>

    </div>

    <?php
    require BASE_PATH
        . '/templates/admin/users/_form.php';
    ?>

</section>
