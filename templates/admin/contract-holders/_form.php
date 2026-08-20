<?php

declare(strict_types=1);

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

        <div
            class="
                grid
                gap-5
                md:grid-cols-2
            "
        >

            <div>

                <label
                    for="name"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Name *
                </label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    required
                    value="<?= e(
                        $values['name'] ?? ''
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
                    for="sort_order"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Sortierung
                </label>

                <input
                    id="sort_order"
                    type="number"
                    name="sort_order"
                    value="<?= e(
                        $values[
                            'sort_order'
                        ] ?? '100'
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
                name="is_active"
                value="1"
                <?= !isset(
                    $values['is_active']
                ) || !empty(
                    $values['is_active']
                ) ? 'checked' : '' ?>
                class="h-4 w-4 rounded"
            >

            Vertragsinhaber aktiv

        </label>

        <p
            class="
                mt-2
                text-xs
                text-slate-500
                dark:text-slate-400
            "
        >
            Inaktive Vertragsinhaber bleiben bei bestehenden Verträgen erhalten,
            sind aber bei neuen Verträgen nicht mehr auswählbar.
        </p>

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
            href="/admin/contract-holders"
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
