<?php

declare(strict_types=1);

$isRequired =
    (int) ($user['must_change_password'] ?? 0)
    === 1;

?>

<section
    class="
        mx-auto
        flex
        min-h-[calc(100vh-82px)]
        max-w-xl
        items-center
        px-6
        py-12
    "
>
    <div class="w-full">

        <div class="mb-8">

            <div
                class="
                    mb-3
                    text-sm
                    font-semibold
                    uppercase
                    tracking-widest
                    text-blue-600
                    dark:text-blue-400
                "
            >
                Sicherheit
            </div>

            <h1
                class="
                    text-3xl
                    font-bold
                    tracking-tight
                    text-slate-900
                    dark:text-white
                "
            >
                Passwort ändern
            </h1>

            <p
                class="
                    mt-2
                    leading-relaxed
                    text-slate-500
                    dark:text-slate-400
                "
            >
                <?php if ($isRequired): ?>
                    Bitte legen Sie vor der weiteren Nutzung
                    ein eigenes Passwort fest.
                <?php else: ?>
                    Hier können Sie Ihr aktuelles Passwort ändern.
                <?php endif; ?>
            </p>

        </div>

        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-8
                shadow-lg
                shadow-slate-200/60
                dark:border-slate-800
                dark:bg-slate-900
                dark:shadow-black/20
            "
        >

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
                        font-medium
                        text-red-800
                        dark:border-red-900/70
                        dark:bg-red-950/60
                        dark:text-red-300
                    "
                >
                    <?= e($error) ?>
                </div>

            <?php endif; ?>

            <form
                method="post"
                action="/password/change"
                class="space-y-6"
            >
                <?= csrf_field() ?>

                <div>
                    <label
                        for="current_password"
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-slate-700
                            dark:text-slate-200
                        "
                    >
                        Aktuelles Passwort
                    </label>

                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        required
                        autocomplete="current-password"
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
                            transition
                            hover:border-slate-400
                            focus:border-blue-500
                            focus:ring-4
                            focus:ring-blue-100
                            dark:border-slate-700
                            dark:bg-slate-800
                            dark:text-white
                            dark:hover:border-slate-600
                            dark:focus:border-blue-500
                            dark:focus:ring-blue-950
                        "
                    >
                </div>

                <div
                    class="
                        border-t
                        border-slate-200
                        dark:border-slate-800
                    "
                ></div>

                <div>
                    <label
                        for="new_password"
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-slate-700
                            dark:text-slate-200
                        "
                    >
                        Neues Passwort
                    </label>

                    <input
                        id="new_password"
                        type="password"
                        name="new_password"
                        required
                        minlength="12"
                        autocomplete="new-password"
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
                            transition
                            hover:border-slate-400
                            focus:border-blue-500
                            focus:ring-4
                            focus:ring-blue-100
                            dark:border-slate-700
                            dark:bg-slate-800
                            dark:text-white
                            dark:hover:border-slate-600
                            dark:focus:border-blue-500
                            dark:focus:ring-blue-950
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
                        Mindestens 12 Zeichen.
                    </p>
                </div>

                <div>
                    <label
                        for="new_password_confirmation"
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-slate-700
                            dark:text-slate-200
                        "
                    >
                        Neues Passwort wiederholen
                    </label>

                    <input
                        id="new_password_confirmation"
                        type="password"
                        name="new_password_confirmation"
                        required
                        minlength="12"
                        autocomplete="new-password"
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
                            transition
                            hover:border-slate-400
                            focus:border-blue-500
                            focus:ring-4
                            focus:ring-blue-100
                            dark:border-slate-700
                            dark:bg-slate-800
                            dark:text-white
                            dark:hover:border-slate-600
                            dark:focus:border-blue-500
                            dark:focus:ring-blue-950
                        "
                    >
                </div>

                <button
                    type="submit"
                    class="
                        flex
                        w-full
                        items-center
                        justify-center
                        rounded-xl
                        bg-blue-600
                        px-4
                        py-3
                        font-semibold
                        text-white
                        shadow-sm
                        transition
                        hover:bg-blue-700
                        focus:outline-none
                        focus:ring-4
                        focus:ring-blue-200
                        dark:hover:bg-blue-500
                        dark:focus:ring-blue-950
                    "
                >
                    Passwort speichern
                </button>
            </form>

        </div>
    </div>
</section>
