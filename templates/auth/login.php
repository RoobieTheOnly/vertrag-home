<section
    class="
        flex
        min-h-screen
        items-center
        justify-center
        px-6
        py-16
    "
>
    <div class="w-full max-w-md">

        <div class="mb-8 text-center">

            <?php if (
                brand_logo_url() !== null
            ): ?>

                <img
                    src="<?= e(
                        brand_logo_url()
                    ) ?>"
                    alt="Vertrag Home"
                    class="
                        mx-auto
                        mb-5
                        h-20
                        w-auto
                        max-w-[280px]
                        object-contain
                        sm:h-24
                    "
                >

                <h1 class="sr-only">
                    Vertrag Home
                </h1>

            <?php else: ?>

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
                    Self-hosted Vertragsverwaltung
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
                    Vertrag Home
                </h1>

            <?php endif; ?>

            <p
                class="
                    mt-2
                    text-slate-500
                    dark:text-slate-400
                "
            >
                Anmeldung zur Vertragsverwaltung
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
                action="/login"
                class="space-y-6"
            >
                <?= csrf_field() ?>

                <div>
                    <label
                        for="username"
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-slate-700
                            dark:text-slate-200
                        "
                    >
                        Benutzername
                    </label>

                    <input
                        id="username"
                        name="username"
                        type="text"
                        required
                        autofocus
                        autocomplete="username"
                        value="<?= e($username ?? '') ?>"
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

                <div>
                    <label
                        for="password"
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-slate-700
                            dark:text-slate-200
                        "
                    >
                        Passwort
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
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
                    Anmelden
                </button>
            </form>

        </div>
    </div>
</section>
