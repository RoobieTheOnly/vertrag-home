<?php

declare(strict_types=1);

?>

<section class="mx-auto max-w-6xl px-4 py-7 sm:px-6 sm:py-10">

    <div class="mb-8">

        <a
            href="/admin"
            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
        >
            ← Administration
        </a>

        <h1 class="mt-5 text-2xl font-bold tracking-tight sm:text-3xl">
            Dokumentarten
        </h1>

        <p class="mt-2 text-sm text-slate-500 sm:text-base dark:text-slate-400">
            Kategorien für Vertragsdokumente zentral verwalten.
        </p>

    </div>


    <div
        class="
            mb-6
            rounded-2xl
            border
            border-slate-200
            bg-white
            p-5
            dark:border-slate-800
            dark:bg-slate-900
        "
    >

        <h2 class="font-semibold">
            Neue Dokumentart
        </h2>

        <form
            method="post"
            action="/admin/document-types/save"
            class="mt-4 grid gap-4 lg:grid-cols-[1fr_1.4fr_120px_auto]"
        >
            <?= csrf_field() ?>

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Name *
                </label>
                <input
                    type="text"
                    name="name"
                    required
                    class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 dark:border-slate-700 dark:bg-slate-800"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Beschreibung
                </label>
                <input
                    type="text"
                    name="description"
                    maxlength="500"
                    class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 dark:border-slate-700 dark:bg-slate-800"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Sortierung
                </label>
                <input
                    type="number"
                    name="sort_order"
                    value="100"
                    class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 dark:border-slate-700 dark:bg-slate-800"
                >
            </div>

            <div class="flex items-end">
                <button
                    type="submit"
                    class="h-11 w-full rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 lg:w-auto"
                >
                    + Anlegen
                </button>
            </div>
        </form>

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

        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <h2 class="font-semibold">
                Vorhandene Dokumentarten
            </h2>
        </div>

        <div class="divide-y divide-slate-200 dark:divide-slate-800">

            <?php foreach (
                $documentTypes
                as $type
            ): ?>

                <form
                    method="post"
                    action="/admin/document-types/save"
                    class="
                        grid
                        gap-3
                        px-5
                        py-5
                        md:grid-cols-[1fr_1.5fr_100px_110px_auto]
                        md:items-end
                    "
                >
                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $type['id'] ?>"
                    >

                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-slate-400">
                            Name
                        </label>
                        <input
                            type="text"
                            name="name"
                            required
                            value="<?= e($type['name']) ?>"
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-800"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-slate-400">
                            Beschreibung
                        </label>
                        <input
                            type="text"
                            name="description"
                            maxlength="500"
                            value="<?= e($type['description'] ?? '') ?>"
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-800"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-slate-400">
                            Sortierung
                        </label>
                        <input
                            type="number"
                            name="sort_order"
                            value="<?= (int) $type['sort_order'] ?>"
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-800"
                        >
                    </div>

                    <label class="flex h-10 items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            <?= (int) $type['is_active'] === 1
                                ? 'checked'
                                : '' ?>
                            class="h-4 w-4 rounded border-slate-300"
                        >
                        Aktiv
                    </label>

                    <button
                        type="submit"
                        class="h-10 rounded-lg border border-slate-300 px-4 text-sm font-semibold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                    >
                        Speichern
                    </button>

                </form>

            <?php endforeach; ?>

        </div>

    </div>

</section>
