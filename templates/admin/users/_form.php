<?php

declare(strict_types=1);

$isEdit =
    !empty($editingUser);

$isSelf =
    $isEdit
    && (int) $editingUser['id']
        === (int) $user['id'];

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

        <h2 class="text-lg font-semibold">
            Benutzerkonto
        </h2>


        <div
            class="
                mt-5
                grid
                gap-5
                md:grid-cols-2
            "
        >

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
                    Benutzername *
                </label>

                <input
                    id="username"
                    type="text"
                    name="username"
                    required
                    value="<?= e(
                        $values['username'] ?? ''
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
                    for="display_name"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Anzeigename *
                </label>

                <input
                    id="display_name"
                    type="text"
                    name="display_name"
                    required
                    value="<?= e(
                        $values[
                            'display_name'
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
                    for="email"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    E-Mail
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?= e(
                        $values['email'] ?? ''
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
                    for="role_id"
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                        text-slate-700
                        dark:text-slate-200
                    "
                >
                    Rolle *
                </label>

                <?php if ($isSelf): ?>

                    <input
                        type="hidden"
                        name="role_id"
                        value="<?= (int) $values['role_id'] ?>"
                    >

                    <select
                        id="role_id"
                        disabled
                        class="
                            w-full
                            cursor-not-allowed
                            rounded-xl
                            border
                            border-slate-300
                            bg-slate-100
                            px-4
                            py-3
                            text-slate-500
                            dark:border-slate-700
                            dark:bg-slate-800
                            dark:text-slate-400
                        "
                    >
                        <?php foreach ($roles as $role): ?>
                            <?php if (
                                (string) $role['id']
                                ===
                                (string) $values['role_id']
                            ): ?>
                                <option>
                                    <?= e($role['label']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>

                    <p
                        class="
                            mt-2
                            text-xs
                            text-slate-500
                            dark:text-slate-400
                        "
                    >
                        Die eigene Administratorrolle kann hier nicht entfernt werden.
                    </p>

                <?php else: ?>

                    <select
                        id="role_id"
                        name="role_id"
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

                        <?php foreach ($roles as $role): ?>

                            <option
                                value="<?= (int) $role['id'] ?>"
                                <?= (
                                    (string) (
                                        $values[
                                            'role_id'
                                        ] ?? ''
                                    )
                                    ===
                                    (string) $role['id']
                                ) ? 'selected' : '' ?>
                            >
                                <?= e($role['label']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                <?php endif; ?>

            </div>

        </div>


        <div
            class="
                mt-6
                flex
                flex-wrap
                gap-6
            "
        >

            <?php if ($isSelf): ?>

                <input
                    type="hidden"
                    name="is_active"
                    value="1"
                >

                <label
                    class="
                        flex
                        cursor-not-allowed
                        items-center
                        gap-3
                        text-sm
                        font-medium
                        text-slate-400
                    "
                >
                    <input
                        type="checkbox"
                        checked
                        disabled
                        class="h-4 w-4 rounded"
                    >
                    Konto aktiv
                </label>

            <?php else: ?>

                <label
                    class="
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
                    Konto aktiv
                </label>

            <?php endif; ?>


            <?php if (!$isEdit): ?>

                <label
                    class="
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
                        name="must_change_password"
                        value="1"
                        <?= !isset(
                            $values[
                                'must_change_password'
                            ]
                        ) || !empty(
                            $values[
                                'must_change_password'
                            ]
                        ) ? 'checked' : '' ?>
                        class="h-4 w-4 rounded"
                    >
                    Passwort beim ersten Login ändern
                </label>

            <?php endif; ?>

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

        <h2 class="text-lg font-semibold">
            <?= $isEdit
                ? 'Passwort zurücksetzen'
                : 'Startpasswort' ?>
        </h2>

        <p
            class="
                mt-1
                text-sm
                text-slate-500
                dark:text-slate-400
            "
        >
            <?php if ($isEdit): ?>
                Leer lassen, wenn das bestehende Passwort unverändert bleiben soll.
                Sobald ein neues Passwort gesetzt wird, muss der Benutzer es beim
                nächsten Login ändern.
            <?php else: ?>
                Mindestens 12 Zeichen. Dieses Passwort wird nur als Startpasswort verwendet.
            <?php endif; ?>
        </p>

        <div class="mt-5">

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
                <?= $isEdit
                    ? 'Neues temporäres Passwort'
                    : 'Startpasswort *' ?>
            </label>

            <input
                id="password"
                type="password"
                name="password"
                <?= $isEdit ? '' : 'required' ?>
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
                    dark:border-slate-700
                    dark:bg-slate-800
                    dark:text-white
                "
            >

        </div>

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
            href="/admin/users"
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
