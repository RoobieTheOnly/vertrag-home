<?php

declare(strict_types=1);

require_once dirname(__DIR__)
    . '/config/bootstrap.php';

$path = request_path();

$method = strtoupper(
    $_SERVER['REQUEST_METHOD']
    ?? 'GET'
);


/*
|--------------------------------------------------------------------------
| Startseite
|--------------------------------------------------------------------------
*/

if ($path === '/') {
    if (current_user() !== null) {
        redirect('/dashboard');
    }

    redirect('/login');
}


/*
|--------------------------------------------------------------------------
| Login anzeigen
|--------------------------------------------------------------------------
*/

if (
    $path === '/login'
    && $method === 'GET'
) {
    if (current_user() !== null) {
        redirect('/dashboard');
    }

    render(
        'auth/login',
        [
            'pageTitle' => 'Anmelden',
            'error' => null,
            'username' => '',
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Login durchführen
|--------------------------------------------------------------------------
*/

if (
    $path === '/login'
    && $method === 'POST'
) {
    if (!csrf_verify()) {
        http_response_code(419);

        render(
            'auth/login',
            [
                'pageTitle' => 'Anmelden',
                'error' =>
                    'Die Sitzung ist abgelaufen. Bitte versuchen Sie es erneut.',
                'username' => '',
            ]
        );
    }

    $username = trim(
        (string) (
            $_POST['username']
            ?? ''
        )
    );

    $password =
        (string) (
            $_POST['password']
            ?? ''
        );

    $ip = client_ip();

    if (
        $username === ''
        || $password === ''
    ) {
        render(
            'auth/login',
            [
                'pageTitle' => 'Anmelden',
                'error' =>
                    'Bitte Benutzername und Passwort eingeben.',
                'username' => $username,
            ]
        );
    }

    if (
        login_is_blocked(
            $username,
            $ip
        )
    ) {
        http_response_code(429);

        render(
            'auth/login',
            [
                'pageTitle' => 'Anmelden',
                'error' =>
                    'Zu viele fehlgeschlagene Anmeldeversuche. Bitte versuchen Sie es später erneut.',
                'username' => $username,
            ]
        );
    }

    $stmt = db()->prepare(
        '
        SELECT
            id,
            username,
            display_name,
            password_hash,
            must_change_password
        FROM users
        WHERE username = :username
          AND is_active = 1
          AND deleted_at IS NULL
        LIMIT 1
        '
    );

    $stmt->execute([
        'username' => $username,
    ]);

    $user = $stmt->fetch();

    $validLogin =
        $user
        && password_verify(
            $password,
            $user['password_hash']
        );

    record_login_attempt(
        $username,
        $ip,
        (bool) $validLogin
    );

    if (!$validLogin) {
        audit_log(
            null,
            'login_failed',
            'Fehlgeschlagene Anmeldung',
            'auth',
            null,
            [
                'username' =>
                    $username,
            ]
        );

        render(
            'auth/login',
            [
                'pageTitle' => 'Anmelden',
                'error' =>
                    'Benutzername oder Passwort ist nicht korrekt.',
                'username' => $username,
            ]
        );
    }

    if (
        password_needs_rehash(
            $user['password_hash'],
            PASSWORD_DEFAULT
        )
    ) {
        $newHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        $rehash = db()->prepare(
            '
            UPDATE users
            SET password_hash = :hash
            WHERE id = :id
            '
        );

        $rehash->execute([
            'hash' => $newHash,
            'id' => $user['id'],
        ]);
    }

    login_user(
        (int) $user['id']
    );

    $updateLogin = db()->prepare(
        '
        UPDATE users
        SET last_login_at = CURRENT_TIMESTAMP
        WHERE id = :id
        '
    );

    $updateLogin->execute([
        'id' => $user['id'],
    ]);

    audit_log(
        (int) $user['id'],
        'login_success',
        'Erfolgreiche Anmeldung',
        'auth',
        null,
        [
            'username' =>
                $user['username'],
        ]
    );

    if (
        (int) $user['must_change_password']
        === 1
    ) {
        redirect('/password/change');
    }

    redirect('/dashboard');
}


/*
|--------------------------------------------------------------------------
| Passwort ändern
|--------------------------------------------------------------------------
*/

if (
    $path === '/password/change'
    && $method === 'GET'
) {
    $user = require_login();

    render(
        'auth/change-password',
        [
            'pageTitle' =>
                'Passwort ändern',
            'user' =>
                $user,
            'error' =>
                null,
        ]
    );
}

if (
    $path === '/password/change'
    && $method === 'POST'
) {
    $user = require_login();

    if (!csrf_verify()) {
        http_response_code(419);

        render(
            'auth/change-password',
            [
                'pageTitle' =>
                    'Passwort ändern',
                'user' =>
                    $user,
                'error' =>
                    'Die Sitzung ist abgelaufen. Bitte versuchen Sie es erneut.',
            ]
        );
    }

    $currentPassword =
        (string) (
            $_POST['current_password']
            ?? ''
        );

    $newPassword =
        (string) (
            $_POST['new_password']
            ?? ''
        );

    $confirmation =
        (string) (
            $_POST[
                'new_password_confirmation'
            ]
            ?? ''
        );

    $stmt = db()->prepare(
        '
        SELECT password_hash
        FROM users
        WHERE id = :id
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $user['id'],
    ]);

    $passwordHash =
        (string) $stmt->fetchColumn();

    if (
        !password_verify(
            $currentPassword,
            $passwordHash
        )
    ) {
        render(
            'auth/change-password',
            [
                'pageTitle' =>
                    'Passwort ändern',
                'user' =>
                    $user,
                'error' =>
                    'Das aktuelle Passwort ist nicht korrekt.',
            ]
        );
    }

    if (strlen($newPassword) < 12) {
        render(
            'auth/change-password',
            [
                'pageTitle' =>
                    'Passwort ändern',
                'user' =>
                    $user,
                'error' =>
                    'Das neue Passwort muss mindestens 12 Zeichen lang sein.',
            ]
        );
    }

    if ($newPassword !== $confirmation) {
        render(
            'auth/change-password',
            [
                'pageTitle' =>
                    'Passwort ändern',
                'user' =>
                    $user,
                'error' =>
                    'Die neuen Passwörter stimmen nicht überein.',
            ]
        );
    }

    if (
        password_verify(
            $newPassword,
            $passwordHash
        )
    ) {
        render(
            'auth/change-password',
            [
                'pageTitle' =>
                    'Passwort ändern',
                'user' =>
                    $user,
                'error' =>
                    'Das neue Passwort darf nicht dem bisherigen Passwort entsprechen.',
            ]
        );
    }

    $newHash =
        password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

    $update = db()->prepare(
        '
        UPDATE users
        SET
            password_hash = :password_hash,
            must_change_password = 0
        WHERE id = :id
        '
    );

    $update->execute([
        'password_hash' => $newHash,
        'id' => $user['id'],
    ]);

    audit_log(
        (int) $user['id'],
        'password_changed',
        'Passwort geändert'
    );

    $_SESSION['flash_success'] =
        'Ihr Passwort wurde erfolgreich geändert.';

    redirect('/dashboard');
}


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

if (
    $path === '/dashboard'
    && $method === 'GET'
) {
    $user =
        require_completed_password_change();

    $today =
        new DateTimeImmutable('today');

    $dashboardContracts =
        db()->query(
            '
            SELECT
                c.*,

                COALESCE(
                    NULLIF(h.name, ""),
                    NULLIF(c.contract_holder, ""),
                    "–"
                ) AS contract_holder_name

            FROM contracts c

            LEFT JOIN contract_holders h
                ON h.id = c.contract_holder_id

            WHERE c.deleted_at IS NULL
              AND (
                  c.status = "active"
                  OR (
                      c.status = "cancelled"
                      AND c.cancellation_effective_date IS NOT NULL
                      AND c.cancellation_effective_date >= CURRENT_DATE
                  )
              )

            ORDER BY c.title ASC
            '
        )->fetchAll();

    $stats = [
        'contract_count' => 0,
        'monthly_cost' => 0.0,
        'annual_cost' => 0.0,
        'next_payment_date' => null,
    ];

    $holderStats = [];

    foreach (
        get_contract_holders()
        as $holder
    ) {
        $holderStats[
            (int) $holder['id']
        ] = [
            'id' =>
                (int) $holder['id'],
            'name' =>
                $holder['name'],
            'sort_order' =>
                (int) (
                    $holder[
                        'sort_order'
                    ] ?? 0
                ),
            'contract_count' => 0,
            'monthly_cost' => 0.0,
            'annual_cost' => 0.0,
        ];
    }

    $paymentContracts = [];
    $runningCosts = [];
    $nextPaymentDate = null;

    foreach (
        $dashboardContracts
        as $dashboardContract
    ) {
        if (
            !contract_is_running_on(
                $dashboardContract,
                $today
            )
        ) {
            continue;
        }

        $stats['contract_count']++;

        $holderId =
            (int) (
                $dashboardContract[
                    'contract_holder_id'
                ] ?? 0
            );

        if (
            isset(
                $holderStats[
                    $holderId
                ]
            )
        ) {
            $holderStats[
                $holderId
            ][
                'contract_count'
            ]++;
        }

        $paymentContracts[] =
            $dashboardContract;

        $isPausedToday =
            contract_is_paused_on(
                $dashboardContract,
                $today
            );

        if (
            !$isPausedToday
            && (
                $dashboardContract[
                    'billing_frequency'
                ] ?? ''
            ) !== 'one_time'
        ) {
            $monthly =
                contract_monthly_equivalent(
                    $dashboardContract
                );

            $annual =
                contract_annual_equivalent(
                    $dashboardContract
                );

            $stats['monthly_cost'] +=
                $monthly;

            $stats['annual_cost'] +=
                $annual;

            if (
                isset(
                    $holderStats[
                        $holderId
                    ]
                )
            ) {
                $holderStats[
                    $holderId
                ][
                    'monthly_cost'
                ] += $monthly;

                $holderStats[
                    $holderId
                ][
                    'annual_cost'
                ] += $annual;
            }

            $dashboardContract[
                'monthly_cost'
            ] = $monthly;

            $dashboardContract[
                'annual_cost'
            ] = $annual;

            $runningCosts[] =
                $dashboardContract;
        }

        $candidate =
            contract_next_payment_date(
                $dashboardContract,
                $today
            );

        if (
            $candidate !== null
            && (
                $nextPaymentDate === null
                || $candidate
                    < $nextPaymentDate
            )
        ) {
            $nextPaymentDate =
                $candidate;
        }
    }

    $holderStats =
        array_values(
            $holderStats
        );

    usort(
        $holderStats,
        static function (
            array $a,
            array $b
        ): int {
            $sortCompare =
                $a['sort_order']
                <=>
                $b['sort_order'];

            if ($sortCompare !== 0) {
                return $sortCompare;
            }

            return strcasecmp(
                $a['name'],
                $b['name']
            );
        }
    );

    $stats['next_payment_date'] =
        $nextPaymentDate;

    foreach (
        $runningCosts
        as &$runningCost
    ) {
        $runningCost[
            'calculated_next_payment_date'
        ] = contract_next_payment_date(
            $runningCost,
            $today
        );
    }

    unset($runningCost);

    $paymentPlannerEvents =
        contract_payment_planner_events(
            $paymentContracts,
            $today,
            $today
                ->modify('+1 year')
                ->modify('-1 day')
        );

    $stats['annual_cost'] = 0.0;

    foreach ($holderStats as &$holderStat) {
        $holderStat['annual_cost'] =
            0.0;
    }

    unset($holderStat);

    foreach (
        $paymentPlannerEvents
        as $plannerEvent
    ) {
        $eventAmount =
            (float) (
                $plannerEvent[
                    'amount'
                ] ?? 0
            );

        $stats['annual_cost'] +=
            $eventAmount;

        $eventHolderId =
            (int) (
                $plannerEvent[
                    'holder_id'
                ] ?? 0
            );

        foreach (
            $holderStats
            as &$holderStat
        ) {
            if (
                (int) $holderStat['id']
                === $eventHolderId
            ) {
                $holderStat[
                    'annual_cost'
                ] += $eventAmount;

                break;
            }
        }

        unset($holderStat);
    }

    $success =
        $_SESSION['flash_success']
        ?? null;

    unset(
        $_SESSION['flash_success']
    );

    render(
        'dashboard',
        [
            'pageTitle' =>
                'Übersicht',
            'user' =>
                $user,
            'stats' =>
                $stats,
            'holderStats' =>
                $holderStats,
            'runningCosts' =>
                $runningCosts,
            'paymentPlannerEvents' =>
                $paymentPlannerEvents,
            'success' =>
                $success,
        ]
    );
}



/*
|--------------------------------------------------------------------------
| Eigene Ausgabenplanung
|--------------------------------------------------------------------------
*/

if (
    $path === '/reports/payment-planner'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'contracts.view'
        );

    $today =
        new DateTimeImmutable('today');

    $contractHolders =
        get_contract_holders();

    $selectedHolderId = null;

    if (
        isset($_GET['holder'])
        && $_GET['holder'] !== ''
        && ctype_digit(
            (string) $_GET['holder']
        )
    ) {
        $selectedHolderId =
            (int) $_GET['holder'];
    }

    $planningContracts =
        db()->query(
            '
            SELECT
                c.*,

                COALESCE(
                    NULLIF(h.name, ""),
                    NULLIF(c.contract_holder, ""),
                    "–"
                ) AS contract_holder_name,

                ct.name AS contract_type_name

            FROM contracts c

            INNER JOIN contract_types ct
                ON ct.id = c.contract_type_id

            LEFT JOIN contract_holders h
                ON h.id = c.contract_holder_id

            WHERE c.deleted_at IS NULL
              AND (
                  c.status = "active"
                  OR (
                      c.status = "cancelled"
                      AND c.cancellation_effective_date IS NOT NULL
                      AND c.cancellation_effective_date >= CURRENT_DATE
                  )
              )

            ORDER BY
                contract_holder_name ASC,
                c.title ASC
            '
        )->fetchAll();

    $runningPlanningContracts = [];
    $nextPaymentDate = null;
    $selectedHolderName =
        'Alle Vertragsinhaber';

    foreach (
        $contractHolders
        as $holder
    ) {
        if (
            $selectedHolderId !== null
            && (int) $holder['id']
                === $selectedHolderId
        ) {
            $selectedHolderName =
                $holder['name'];

            break;
        }
    }

    foreach (
        $planningContracts
        as $planningContract
    ) {
        if (
            !contract_is_running_on(
                $planningContract,
                $today
            )
        ) {
            continue;
        }

        $runningPlanningContracts[] =
            $planningContract;

        $candidate =
            contract_next_payment_date(
                $planningContract,
                $today
            );

        if (
            $candidate !== null
            && (
                $nextPaymentDate === null
                || $candidate < $nextPaymentDate
            )
        ) {
            $nextPaymentDate =
                $candidate;
        }
    }

    $plannerEvents =
        contract_payment_planner_events(
            $runningPlanningContracts,
            $today,
            $today
                ->modify('+1 year')
                ->modify('-1 day')
        );

    $planningStats = [
        'contract_count' => 0,
        'holder_count' => 0,
        'event_count_30' => 0,
        'total_30' => 0.0,
        'total_90' => 0.0,
        'total_365' => 0.0,
        'next_payment_date' =>
            $nextPaymentDate,
    ];

    $planningByHolder = [];
    $holderIds = [];

    foreach (
        $runningPlanningContracts
        as $planningContract
    ) {
        $holderId =
            (int) (
                $planningContract[
                    'contract_holder_id'
                ] ?? 0
            );

        if (
            $selectedHolderId !== null
            && $holderId !== $selectedHolderId
        ) {
            continue;
        }

        $planningStats['contract_count']++;

        if ($holderId > 0) {
            $holderIds[$holderId] = true;
        }
    }

    $planningStats['holder_count'] =
        count($holderIds);

    $end30 =
        $today->modify('+1 month');
    $end90 =
        $today->modify('+3 months');
    $end365 =
        $today->modify('+1 year');

    foreach (
        $plannerEvents
        as $plannerEvent
    ) {
        $eventHolderId =
            (int) (
                $plannerEvent['holder_id'] ?? 0
            );

        if (
            $selectedHolderId !== null
            && $eventHolderId !== $selectedHolderId
        ) {
            continue;
        }

        $eventDate =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                (string) $plannerEvent['date']
            );

        if (!$eventDate) {
            continue;
        }

        $eventAmount =
            (float) (
                $plannerEvent['amount'] ?? 0
            );

        if ($eventDate >= $today && $eventDate < $end30) {
            $planningStats['event_count_30']++;
            $planningStats['total_30'] += $eventAmount;
        }

        if ($eventDate >= $today && $eventDate < $end90) {
            $planningStats['total_90'] += $eventAmount;
        }

        if ($eventDate >= $today && $eventDate < $end365) {
            $planningStats['total_365'] += $eventAmount;
        }

        if (
            !isset(
                $planningByHolder[
                    $eventHolderId
                ]
            )
        ) {
            $planningByHolder[
                $eventHolderId
            ] = [
                'id' => $eventHolderId,
                'name' =>
                    $plannerEvent['holder'] ?? '–',
                'total_365' => 0.0,
                'event_count' => 0,
            ];
        }

        if ($eventDate >= $today && $eventDate < $end365) {
            $planningByHolder[
                $eventHolderId
            ]['total_365'] += $eventAmount;

            $planningByHolder[
                $eventHolderId
            ]['event_count']++;
        }
    }

    $planningByHolder =
        array_values($planningByHolder);

    usort(
        $planningByHolder,
        static function (
            array $a,
            array $b
        ): int {
            $totalCompare =
                $b['total_365']
                <=>
                $a['total_365'];

            if ($totalCompare !== 0) {
                return $totalCompare;
            }

            return strcasecmp(
                $a['name'],
                $b['name']
            );
        }
    );

    render(
        'reports/payment-planner',
        [
            'pageTitle' =>
                'Ausgabenplanung',
            'user' =>
                $user,
            'contractHolders' =>
                $contractHolders,
            'selectedHolderId' =>
                $selectedHolderId,
            'selectedHolderName' =>
                $selectedHolderName,
            'planningStats' =>
                $planningStats,
            'planningByHolder' =>
                $planningByHolder,
            'paymentPlannerEvents' =>
                $plannerEvents,
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Finanzübersicht / Auszug
|--------------------------------------------------------------------------
*/

if (
    $path === '/reports/financial-overview'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'contracts.view'
        );

    $contractHolders =
        get_contract_holders();

    $selectedHolderId = null;

    if (
        isset($_GET['holder'])
        && $_GET['holder'] !== ''
        && ctype_digit(
            (string) $_GET['holder']
        )
    ) {
        $selectedHolderId =
            (int) $_GET['holder'];
    }

    $sql = '
        SELECT
            c.id,
            c.contract_holder_id,
            c.title,
            c.provider,
            c.contract_number,
            c.customer_number,
            c.status,
            c.cancelled_at,
            c.cancellation_effective_date,
            c.start_date,
            c.end_date,
            c.amount,
            c.billing_frequency,
            c.custom_billing_months,
            c.first_payment_date,
            c.next_payment_date,
            c.automatic_renewal,
            c.renewal_period_months,

            ct.name
                AS contract_type,

            COALESCE(
                NULLIF(h.name, ""),
                NULLIF(c.contract_holder, ""),
                "–"
            ) AS contract_holder_name

        FROM contracts c

        INNER JOIN contract_types ct
            ON ct.id = c.contract_type_id

        LEFT JOIN contract_holders h
            ON h.id = c.contract_holder_id

        WHERE c.deleted_at IS NULL
          AND (
              c.status = "active"
              OR (
                  c.status = "cancelled"
                  AND c.cancellation_effective_date IS NOT NULL
                  AND c.cancellation_effective_date >= CURRENT_DATE
              )
          )
          AND c.billing_frequency <> "one_time"
    ';

    $params = [];

    if ($selectedHolderId !== null) {
        $sql .= '
            AND c.contract_holder_id = :holder_id
        ';

        $params['holder_id'] =
            $selectedHolderId;
    }

    $sql .= '
        ORDER BY
            contract_holder_name,
            c.title
    ';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    $financialContracts =
        $stmt->fetchAll();

    $monthlyTotal = 0.0;
    $annualTotal = 0.0;
    $nextTwelveMonthsTotal = 0.0;
    $nextPaymentDate = null;

    $today =
        new DateTimeImmutable('today');

    $oneYearFromToday =
        $today
            ->modify('+1 year')
            ->modify('-1 day');

    foreach (
        $financialContracts
        as &$financialContract
    ) {
        $financialContract[
            'monthly_cost'
        ] = contract_monthly_equivalent(
            $financialContract
        );

        $financialContract[
            'annual_cost'
        ] = contract_annual_equivalent(
            $financialContract
        );

        $financialContract[
            'calculated_next_payment_date'
        ] = contract_next_payment_date(
            $financialContract
        );

        $financialContract[
            'pause_state'
        ] = contract_pause_state(
            $financialContract,
            $today
        );

        $monthlyTotal +=
            (float) $financialContract[
                'monthly_cost'
            ];

        $annualTotal +=
            (float) $financialContract[
                'annual_cost'
            ];

        $candidate =
            $financialContract[
                'calculated_next_payment_date'
            ];

        if (
            $candidate !== null
            && (
                $nextPaymentDate === null
                || $candidate < $nextPaymentDate
            )
        ) {
            $nextPaymentDate =
                $candidate;
        }

        $occurrences =
            contract_payment_occurrences(
                $financialContract,
                $today,
                $oneYearFromToday
            );

        $nextTwelveMonthsTotal +=
            count($occurrences)
            * (float) $financialContract[
                'amount'
            ];
    }

    unset($financialContract);

    $selectedHolderName =
        'Alle Vertragsinhaber';

    if ($selectedHolderId !== null) {
        foreach (
            $contractHolders
            as $holder
        ) {
            if (
                (int) $holder['id']
                === $selectedHolderId
            ) {
                $selectedHolderName =
                    $holder['name'];

                break;
            }
        }
    }

    render(
        'reports/financial-overview',
        [
            'pageTitle' =>
                'Finanzübersicht',
            'user' =>
                $user,
            'contractHolders' =>
                $contractHolders,
            'selectedHolderId' =>
                $selectedHolderId,
            'selectedHolderName' =>
                $selectedHolderName,
            'financialContracts' =>
                $financialContracts,
            'monthlyTotal' =>
                $monthlyTotal,
            'annualTotal' =>
                $annualTotal,
            'nextTwelveMonthsTotal' =>
                $nextTwelveMonthsTotal,
            'nextPaymentDate' =>
                $nextPaymentDate,
            'generatedAt' =>
                new DateTimeImmutable(),
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Kündigungs- & Fristen-Cockpit
|--------------------------------------------------------------------------
*/

if (
    $path === '/deadlines'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'contracts.view'
        );

    $selectedHolderId = null;

    if (
        isset($_GET['holder'])
        && $_GET['holder'] !== ''
        && ctype_digit(
            (string) $_GET['holder']
        )
    ) {
        $selectedHolderId =
            (int) $_GET['holder'];
    }

    render(
        'deadlines/index',
        [
            'pageTitle' =>
                'Kündigungs- & Fristen-Cockpit',
            'user' =>
                $user,
            'contractHolders' =>
                get_contract_holders(),
            'selectedHolderId' =>
                $selectedHolderId,
            'deadlineItems' =>
                contract_deadline_cockpit(
                    $selectedHolderId
                ),
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Kostenentwicklung & Einsparpotenzial
|--------------------------------------------------------------------------
*/

if (
    $path === '/reports/cost-development'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'contracts.view'
        );

    $selectedHolderId = null;

    if (
        isset($_GET['holder'])
        && $_GET['holder'] !== ''
        && ctype_digit(
            (string) $_GET['holder']
        )
    ) {
        $selectedHolderId =
            (int) $_GET['holder'];
    }

    render(
        'reports/cost-development',
        [
            'pageTitle' =>
                'Kostenentwicklung',
            'user' =>
                $user,
            'contractHolders' =>
                get_contract_holders(),
            'selectedHolderId' =>
                $selectedHolderId,
            'costDevelopment' =>
                contract_cost_development(
                    $selectedHolderId
                ),
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Vertragsübersicht
|--------------------------------------------------------------------------
*/

if (
    $path === '/contracts'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'contracts.view'
        );

    $contractHolders =
        get_contract_holders();

    $selectedHolderId = null;

    if (
        isset($_GET['holder'])
        && $_GET['holder'] !== ''
        && ctype_digit(
            (string) $_GET['holder']
        )
    ) {
        $selectedHolderId =
            (int) $_GET['holder'];
    }

    $allowedStatuses = [
        'active',
        'paused',
        'planned',
        'cancelled',
        'expired',
    ];

    $selectedStatus = null;

    if (
        isset($_GET['status'])
        && in_array(
            (string) $_GET['status'],
            $allowedStatuses,
            true
        )
    ) {
        $selectedStatus =
            (string) $_GET['status'];
    }

    $selectedSearch =
        trim(
            (string) (
                $_GET['q']
                ?? ''
            )
        );

    /*
     * Für den Live-Filter werden bewusst alle Verträge geladen.
     * Das Filtern nach Inhaber und Status erfolgt anschließend
     * direkt im Browser, ohne Seitenreload.
     */
    $contracts = db()->query(
        '
        SELECT
            c.id,
            c.contract_holder_id,
            c.title,
            c.provider,
            c.contract_number,
            c.customer_number,
            c.amount,
            c.billing_frequency,
            c.custom_billing_months,
            c.first_payment_date,
            c.next_payment_date,
            c.start_date,
            c.end_date,
            c.status,
            c.cancelled_at,
            c.cancellation_effective_date,
            c.automatic_renewal,
            c.renewal_period_months,

            ct.name
                AS contract_type,

            COALESCE(
                NULLIF(h.name, ""),
                NULLIF(c.contract_holder, ""),
                "–"
            ) AS contract_holder_name

        FROM contracts c

        INNER JOIN contract_types ct
            ON ct.id = c.contract_type_id

        LEFT JOIN contract_holders h
            ON h.id = c.contract_holder_id

        WHERE c.deleted_at IS NULL

        ORDER BY
            CASE c.status
                WHEN "active" THEN 1
                WHEN "planned" THEN 2
                WHEN "cancelled" THEN 3
                WHEN "expired" THEN 4
                ELSE 5
            END,
            c.title ASC
        '
    )->fetchAll();

    $contractListToday =
        new DateTimeImmutable('today');

    foreach ($contracts as &$contractRow) {
        $contractRow[
            'calculated_next_payment_date'
        ] = contract_next_payment_date(
            $contractRow,
            $contractListToday
        );

        $contractRow[
            'pause_state'
        ] = contract_pause_state(
            $contractRow,
            $contractListToday
        );

        $hasCurrentCost =
            contract_is_running_on(
                $contractRow,
                $contractListToday
            )
            && empty(
                $contractRow[
                    'pause_state'
                ][
                    'is_paused'
                ]
            );

        $contractRow[
            'calculated_monthly_cost'
        ] = $hasCurrentCost
            ? contract_monthly_equivalent(
                $contractRow
            )
            : 0.0;

        $contractRow[
            'calculated_annual_cost'
        ] = $hasCurrentCost
            ? contract_annual_equivalent(
                $contractRow
            )
            : 0.0;
    }

    unset($contractRow);

    $success =
        $_SESSION['flash_success']
        ?? null;

    $warning =
        $_SESSION['flash_warning']
        ?? null;

    unset(
        $_SESSION['flash_success'],
        $_SESSION['flash_warning']
    );

    render(
        'contracts/index',
        [
            'pageTitle' =>
                'Verträge',
            'user' =>
                $user,
            'contracts' =>
                $contracts,
            'contractHolders' =>
                $contractHolders,
            'selectedHolderId' =>
                $selectedHolderId,
            'selectedStatus' =>
                $selectedStatus,
            'selectedSearch' =>
                $selectedSearch,
            'success' =>
                $success,
            'warning' =>
                $warning,
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Vertrag anlegen
|--------------------------------------------------------------------------
*/

if (
    $path === '/contracts/create'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'contracts.create'
        );

    render(
        'contracts/create',
        [
            'pageTitle' =>
                'Vertrag anlegen',
            'user' =>
                $user,
            'contractTypes' =>
                get_contract_types(),
            'contractHolders' =>
                get_contract_holders(),
            'values' =>
                [
                    'status' => 'active',
                    'billing_frequency' =>
                        'monthly',
                    'price_valid_from' =>
                        (
                            new DateTimeImmutable(
                                'today'
                            )
                        )->format(
                            'Y-m-d'
                        ),
                    'price_change_reason' =>
                        '',
                    'notifications_enabled' =>
                        '1',
                ],
            'error' =>
                null,
        ]
    );
}

if (
    $path === '/contracts/create'
    && $method === 'POST'
) {
    $user =
        require_permission(
            'contracts.create'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $values =
        contract_form_values_from_request();

    $error =
        validate_contract_form_values(
            $values
        );

    if ($error !== null) {
        render(
            'contracts/create',
            [
                'pageTitle' =>
                    'Vertrag anlegen',
                'user' =>
                    $user,
                'contractTypes' =>
                    get_contract_types(),
                'contractHolders' =>
                    get_contract_holders(),
                'values' =>
                    $values,
                'error' =>
                    $error,
            ]
        );
    }

    $calculatedNextPaymentDate =
        contract_next_payment_date(
            $values
        );

    $stmt = db()->prepare(
        '
        INSERT INTO contracts (
            contract_type_id,
            contract_holder_id,
            title,
            provider,
            contract_number,
            customer_number,
            description,
            status,
            start_date,
            end_date,
            minimum_term_months,
            notice_period_value,
            notice_period_unit,
            automatic_renewal,
            notifications_enabled,
            renewal_period_months,
            amount,
            billing_frequency,
            custom_billing_months,
            first_payment_date,
            next_payment_date,
            notes,
            created_by,
            updated_by
        )
        VALUES (
            :contract_type_id,
            :contract_holder_id,
            :title,
            :provider,
            :contract_number,
            :customer_number,
            :description,
            :status,
            :start_date,
            :end_date,
            :minimum_term_months,
            :notice_period_value,
            :notice_period_unit,
            :automatic_renewal,
            :notifications_enabled,
            :renewal_period_months,
            :amount,
            :billing_frequency,
            :custom_billing_months,
            :first_payment_date,
            :next_payment_date,
            :notes,
            :created_by,
            :updated_by
        )
        '
    );

    $stmt->execute([
        'contract_type_id' =>
            (int) $values['contract_type_id'],
        'contract_holder_id' =>
            (int) $values['contract_holder_id'],
        'title' =>
            $values['title'],
        'provider' =>
            $values['provider'],
        'contract_number' =>
            $values['contract_number'] ?: null,
        'customer_number' =>
            $values['customer_number'] ?: null,
        'description' =>
            $values['description'] ?: null,
        'status' =>
            $values['status'],
        'start_date' =>
            $values['start_date'] ?: null,
        'end_date' =>
            $values['end_date'] ?: null,
        'minimum_term_months' =>
            $values['minimum_term_months'] !== ''
                ? (int) $values['minimum_term_months']
                : null,
        'notice_period_value' =>
            $values['notice_period_value'] !== ''
                ? (int) $values['notice_period_value']
                : null,
        'notice_period_unit' =>
            $values['notice_period_unit'] ?: null,
        'automatic_renewal' =>
            $values['automatic_renewal'] === '1'
                ? 1
                : 0,
        'notifications_enabled' =>
            $values['notifications_enabled'] === '1'
                ? 1
                : 0,
        'renewal_period_months' =>
            $values['renewal_period_months'] !== ''
                ? (int) $values['renewal_period_months']
                : null,
        'amount' =>
            (float) $values['amount'],
        'billing_frequency' =>
            $values['billing_frequency'],
        'custom_billing_months' =>
            $values['billing_frequency'] === 'custom'
                ? (int) $values['custom_billing_months']
                : null,
        'first_payment_date' =>
            $values['first_payment_date'],
        'next_payment_date' =>
            $calculatedNextPaymentDate,
        'notes' =>
            $values['notes'] ?: null,
        'created_by' =>
            (int) $user['id'],
        'updated_by' =>
            (int) $user['id'],
    ]);

    $contractId =
        (int) db()->lastInsertId();

    $initialPriceValidFrom =
        $values['price_valid_from']
        ?: (
            $values['start_date']
            ?: (
                $values['first_payment_date']
                ?: (
                    new DateTimeImmutable(
                        'today'
                    )
                )->format(
                    'Y-m-d'
                )
            )
        );

    record_contract_price_change(
        $contractId,
        (float) $values['amount'],
        $values['billing_frequency'],
        $values['billing_frequency'] === 'custom'
            ? (int) $values['custom_billing_months']
            : null,
        $initialPriceValidFrom,
        $values['price_change_reason']
            !== ''
                ? $values['price_change_reason']
                : 'Initialer Vertragspreis',
        (int) $user['id']
    );

    audit_log(
        (int) $user['id'],
        'contract_created',
        'Vertrag angelegt: '
            . $values['title'],
        'contract',
        $contractId,
        [
            'title' =>
                $values['title'],
            'provider' =>
                $values['provider'],
            'contract_holder_id' =>
                (int) $values['contract_holder_id'],
            'contract_type_id' =>
                (int) $values['contract_type_id'],
            'status' =>
                $values['status'],
            'amount' =>
                (float) $values['amount'],
            'billing_frequency' =>
                $values['billing_frequency'],
            'first_payment_date' =>
                $values['first_payment_date'],
            'notifications_enabled' =>
                $values['notifications_enabled'] === '1',
        ]
    );

    $_SESSION['flash_success'] =
        'Der Vertrag wurde erfolgreich angelegt.';

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Vertrag bearbeiten
|--------------------------------------------------------------------------
*/

if (
    $method === 'GET'
    && preg_match(
        '#^/contracts/(\d+)/edit$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.edit'
        );

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    render(
        'contracts/edit',
        [
            'pageTitle' =>
                'Vertrag bearbeiten',
            'user' =>
                $user,
            'contract' =>
                $contract,
            'contractTypes' =>
                get_contract_types(false),
            'contractHolders' =>
                get_contract_holders(false),
            'values' =>
                contract_to_form_values(
                    $contract
                ),
            'error' =>
                null,
        ]
    );
}

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/edit$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.edit'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    $values =
        contract_form_values_from_request();

    $error =
        validate_contract_form_values(
            $values,
            $contract
        );

    if ($error !== null) {
        render(
            'contracts/edit',
            [
                'pageTitle' =>
                    'Vertrag bearbeiten',
                'user' =>
                    $user,
                'contract' =>
                    $contract,
                'contractTypes' =>
                    get_contract_types(false),
                'contractHolders' =>
                    get_contract_holders(false),
                'values' =>
                    $values,
                'error' =>
                    $error,
            ]
        );
    }

    $calculatedNextPaymentDate =
        contract_next_payment_date(
            $values
        );

    $priceChanged =
        (float) $contract['amount']
            !== (float) $values['amount']
        || (string) $contract[
            'billing_frequency'
        ] !== (string) $values[
            'billing_frequency'
        ]
        || (
            (int) (
                $contract[
                    'custom_billing_months'
                ] ?? 0
            )
            !== (
                $values[
                    'billing_frequency'
                ] === 'custom'
                    ? (int) $values[
                        'custom_billing_months'
                    ]
                    : 0
            )
        );

    $stmt = db()->prepare(
        '
        UPDATE contracts
        SET
            contract_type_id =
                :contract_type_id,

            contract_holder_id =
                :contract_holder_id,

            contract_holder = NULL,

            title =
                :title,

            provider =
                :provider,

            contract_number =
                :contract_number,

            customer_number =
                :customer_number,

            description =
                :description,

            status =
                :status,

            start_date =
                :start_date,

            end_date =
                :end_date,

            minimum_term_months =
                :minimum_term_months,

            notice_period_value =
                :notice_period_value,

            notice_period_unit =
                :notice_period_unit,

            automatic_renewal =
                :automatic_renewal,

            notifications_enabled =
                :notifications_enabled,

            renewal_period_months =
                :renewal_period_months,

            amount =
                :amount,

            billing_frequency =
                :billing_frequency,

            custom_billing_months =
                :custom_billing_months,

            first_payment_date =
                :first_payment_date,

            next_payment_date =
                :next_payment_date,

            notes =
                :notes,

            updated_by =
                :updated_by

        WHERE id = :id
          AND deleted_at IS NULL
        '
    );

    $stmt->execute([
        'contract_type_id' =>
            (int) $values['contract_type_id'],
        'contract_holder_id' =>
            (int) $values['contract_holder_id'],
        'title' =>
            $values['title'],
        'provider' =>
            $values['provider'],
        'contract_number' =>
            $values['contract_number'] ?: null,
        'customer_number' =>
            $values['customer_number'] ?: null,
        'description' =>
            $values['description'] ?: null,
        'status' =>
            $values['status'],
        'start_date' =>
            $values['start_date'] ?: null,
        'end_date' =>
            $values['end_date'] ?: null,
        'minimum_term_months' =>
            $values['minimum_term_months'] !== ''
                ? (int) $values['minimum_term_months']
                : null,
        'notice_period_value' =>
            $values['notice_period_value'] !== ''
                ? (int) $values['notice_period_value']
                : null,
        'notice_period_unit' =>
            $values['notice_period_unit'] ?: null,
        'automatic_renewal' =>
            $values['automatic_renewal'] === '1'
                ? 1
                : 0,
        'notifications_enabled' =>
            $values['notifications_enabled'] === '1'
                ? 1
                : 0,
        'renewal_period_months' =>
            $values['renewal_period_months'] !== ''
                ? (int) $values['renewal_period_months']
                : null,
        'amount' =>
            (float) $values['amount'],
        'billing_frequency' =>
            $values['billing_frequency'],
        'custom_billing_months' =>
            $values['billing_frequency'] === 'custom'
                ? (int) $values['custom_billing_months']
                : null,
        'first_payment_date' =>
            $values['first_payment_date'],
        'next_payment_date' =>
            $calculatedNextPaymentDate,
        'notes' =>
            $values['notes'] ?: null,
        'updated_by' =>
            (int) $user['id'],
        'id' =>
            $contractId,
    ]);

    if ($priceChanged) {
        record_contract_price_change(
            $contractId,
            (float) $values['amount'],
            $values['billing_frequency'],
            $values['billing_frequency'] === 'custom'
                ? (int) $values[
                    'custom_billing_months'
                ]
                : null,
            $values['price_valid_from']
                ?: (
                    new DateTimeImmutable(
                        'today'
                    )
                )->format(
                    'Y-m-d'
                ),
            $values['price_change_reason']
                !== ''
                    ? $values[
                        'price_change_reason'
                    ]
                    : 'Preisänderung',
            (int) $user['id']
        );

        audit_log(
            (int) $user['id'],
            'contract_price_changed',
            'Preis geändert: '
                . $values['title'],
            'contract',
            $contractId,
            [
                'before_amount' =>
                    (float) $contract['amount'],
                'after_amount' =>
                    (float) $values['amount'],
                'valid_from' =>
                    $values['price_valid_from'],
                'reason' =>
                    $values['price_change_reason'],
            ]
        );
    }

    audit_log(
        (int) $user['id'],
        'contract_updated',
        'Vertrag bearbeitet: '
            . $values['title'],
        'contract',
        $contractId,
        [
            'before' => [
                'title' =>
                    $contract['title'],
                'provider' =>
                    $contract['provider'],
                'contract_holder_id' =>
                    (int) $contract['contract_holder_id'],
                'contract_type_id' =>
                    (int) $contract['contract_type_id'],
                'status' =>
                    $contract['status'],
                'amount' =>
                    (float) $contract['amount'],
                'billing_frequency' =>
                    $contract['billing_frequency'],
                'first_payment_date' =>
                    $contract['first_payment_date']
                    ?? $contract['next_payment_date']
                    ?? null,
                'notifications_enabled' =>
                    (int) (
                        $contract['notifications_enabled']
                        ?? 1
                    ) === 1,
            ],
            'after' => [
                'title' =>
                    $values['title'],
                'provider' =>
                    $values['provider'],
                'contract_holder_id' =>
                    (int) $values['contract_holder_id'],
                'contract_type_id' =>
                    (int) $values['contract_type_id'],
                'status' =>
                    $values['status'],
                'amount' =>
                    (float) $values['amount'],
                'billing_frequency' =>
                    $values['billing_frequency'],
                'first_payment_date' =>
                    $values['first_payment_date'],
                'notifications_enabled' =>
                    $values['notifications_enabled'] === '1',
            ],
        ]
    );

    $_SESSION['flash_success'] =
        'Der Vertrag wurde erfolgreich aktualisiert.';

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Vertragsbenachrichtigungen ein-/ausschalten
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/notifications$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.edit'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    $enabled =
        isset($_POST['enabled'])
        && (string) $_POST['enabled'] === '1';

    $stmt = db()->prepare(
        '
        UPDATE contracts
        SET
            notifications_enabled = :enabled,
            updated_by = :updated_by
        WHERE id = :id
          AND deleted_at IS NULL
        '
    );

    $stmt->execute([
        'enabled' =>
            $enabled ? 1 : 0,
        'updated_by' =>
            (int) $user['id'],
        'id' =>
            $contractId,
    ]);

    audit_log(
        (int) $user['id'],
        'contract_notifications_changed',
        'Vertragsbenachrichtigungen '
            . ($enabled ? 'aktiviert: ' : 'deaktiviert: ')
            . $contract['title'],
        'contract',
        $contractId,
        [
            'before' =>
                (int) (
                    $contract['notifications_enabled']
                    ?? 1
                ) === 1,
            'after' =>
                $enabled,
        ]
    );

    $_SESSION['flash_success'] =
        $enabled
            ? 'Benachrichtigungen für diesen Vertrag sind aktiviert.'
            : 'Benachrichtigungen für diesen Vertrag sind deaktiviert.';

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Vertrag als gekündigt markieren
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/cancel$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.edit'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    $effectiveDateValue =
        trim(
            (string) (
                $_POST[
                    'cancellation_effective_date'
                ] ?? ''
            )
        );

    try {
        if ($effectiveDateValue === '') {
            throw new RuntimeException(
                'Bitte das Datum angeben, zu dem der Vertrag endet.'
            );
        }

        $effectiveDate =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $effectiveDateValue
            );

        if (!$effectiveDate) {
            throw new RuntimeException(
                'Das Kündigungsdatum ist ungültig.'
            );
        }

        $startDateValue =
            trim(
                (string) (
                    $contract[
                        'start_date'
                    ] ?? ''
                )
            );

        if ($startDateValue !== '') {
            $startDate =
                DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $startDateValue
                );

            if (
                $startDate
                && $effectiveDate < $startDate
            ) {
                throw new RuntimeException(
                    'Das Vertragsende darf nicht vor dem Vertragsbeginn liegen.'
                );
            }
        }

        $stmt = db()->prepare(
            '
            UPDATE contracts
            SET
                status = "cancelled",
                cancelled_at = COALESCE(
                    cancelled_at,
                    CURRENT_TIMESTAMP
                ),
                cancellation_effective_date = :effective_date,
                updated_by = :updated_by
            WHERE id = :id
              AND deleted_at IS NULL
            '
        );

        $stmt->execute([
            'effective_date' =>
                $effectiveDateValue,
            'updated_by' =>
                (int) $user['id'],
            'id' =>
                $contractId,
        ]);

        audit_log(
            (int) $user['id'],
            'contract_cancelled',
            'Kündigung vorgemerkt: '
                . $contract['title'],
            'contract',
            $contractId,
            [
                'before' => [
                    'status' =>
                        $contract['status'],
                    'cancellation_effective_date' =>
                        $contract[
                            'cancellation_effective_date'
                        ] ?? null,
                ],
                'after' => [
                    'status' =>
                        'cancelled',
                    'cancellation_effective_date' =>
                        $effectiveDateValue,
                ],
                'title' =>
                    $contract['title'],
            ]
        );

        $today =
            new DateTimeImmutable('today');

        if ($effectiveDate >= $today) {
            $_SESSION['flash_success'] =
                'Die Kündigung wurde zum '
                . $effectiveDate->format(
                    'd.m.Y'
                )
                . ' hinterlegt. Der Vertrag bleibt bis einschließlich dieses Datums in den laufenden Planungen berücksichtigt.';
        } else {
            $_SESSION['flash_success'] =
                'Die Kündigung wurde mit Vertragsende '
                . $effectiveDate->format(
                    'd.m.Y'
                )
                . ' hinterlegt. Der Vertrag wird als historisch geführt.';
        }
    } catch (Throwable $e) {
        $_SESSION['flash_cancel_error'] =
            $e->getMessage();

        $_SESSION[
            'cancel_form_values'
        ] = [
            'cancellation_effective_date' =>
                $effectiveDateValue,
        ];

        $_SESSION['open_cancel_modal'] =
            true;
    }

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Vertrag pausieren
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/pause$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.edit'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    $pauseFromValue =
        trim(
            (string) (
                $_POST['pause_from']
                ?? ''
            )
        );

    $pauseToValue =
        trim(
            (string) (
                $_POST['pause_to']
                ?? ''
            )
        );

    $reason =
        trim(
            (string) (
                $_POST['reason']
                ?? ''
            )
        );

    try {
        if (
            $pauseFromValue === ''
            || $pauseToValue === ''
        ) {
            throw new RuntimeException(
                'Bitte für die Pause ein Von- und Bis-Datum angeben.'
            );
        }

        $pauseFrom =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $pauseFromValue
            );

        $pauseTo =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $pauseToValue
            );

        if (
            !$pauseFrom
            || !$pauseTo
        ) {
            throw new RuntimeException(
                'Der Pausezeitraum enthält ein ungültiges Datum.'
            );
        }

        if ($pauseTo < $pauseFrom) {
            throw new RuntimeException(
                'Das Bis-Datum der Pause darf nicht vor dem Von-Datum liegen.'
            );
        }

        if (
            !in_array(
                $contract['status'],
                [
                    'active',
                    'cancelled',
                ],
                true
            )
            || contract_is_historical(
                $contract
            )
        ) {
            throw new RuntimeException(
                'Für einen nicht mehr laufenden Vertrag kann keine neue Pause hinterlegt werden.'
            );
        }

        $contractStartValue =
            trim(
                (string) (
                    $contract[
                        'start_date'
                    ] ?? ''
                )
            );

        if ($contractStartValue !== '') {
            $contractStart =
                DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $contractStartValue
                );

            if (
                $contractStart
                && $pauseFrom < $contractStart
            ) {
                throw new RuntimeException(
                    'Die Pause darf nicht vor dem Vertragsbeginn starten.'
                );
            }
        }

        $cancellationDate =
            contract_cancellation_effective_date(
                $contract
            );

        if (
            $cancellationDate !== null
            && $pauseTo > $cancellationDate
        ) {
            throw new RuntimeException(
                'Die Pause darf nicht über das vorgemerkte Vertragsende hinausgehen.'
            );
        }

        if ($cancellationDate === null) {
            $effectiveEnd =
                contract_effective_end_date(
                    $contract,
                    $pauseFrom
                );

            if (
                $effectiveEnd !== null
                && $pauseTo > $effectiveEnd
                && (int) (
                    $contract[
                        'automatic_renewal'
                    ] ?? 0
                ) !== 1
            ) {
                throw new RuntimeException(
                    'Die Pause darf nicht über das Vertragsende hinausgehen.'
                );
            }
        }

        $overlap = db()->prepare(
            '
            SELECT COUNT(*)
            FROM contract_pauses
            WHERE contract_id = :contract_id
              AND deleted_at IS NULL
              AND pause_from <= :pause_to
              AND pause_to >= :pause_from
            '
        );

        $overlap->execute([
            'contract_id' =>
                $contractId,
            'pause_to' =>
                $pauseToValue,
            'pause_from' =>
                $pauseFromValue,
        ]);

        if (
            (int) $overlap->fetchColumn()
            > 0
        ) {
            throw new RuntimeException(
                'Der angegebene Zeitraum überschneidet sich mit einer bereits hinterlegten Pause.'
            );
        }

        $stmt = db()->prepare(
            '
            INSERT INTO contract_pauses (
                contract_id,
                pause_from,
                pause_to,
                reason,
                created_by
            )
            VALUES (
                :contract_id,
                :pause_from,
                :pause_to,
                :reason,
                :created_by
            )
            '
        );

        $stmt->execute([
            'contract_id' =>
                $contractId,
            'pause_from' =>
                $pauseFromValue,
            'pause_to' =>
                $pauseToValue,
            'reason' =>
                $reason !== ''
                    ? $reason
                    : null,
            'created_by' =>
                (int) $user['id'],
        ]);

        $pauseId =
            (int) db()->lastInsertId();

        audit_log(
            (int) $user['id'],
            'contract_paused',
            'Vertragspause hinterlegt: '
                . $contract['title'],
            'contract',
            $contractId,
            [
                'pause_id' =>
                    $pauseId,
                'pause_from' =>
                    $pauseFromValue,
                'pause_to' =>
                    $pauseToValue,
                'reason' =>
                    $reason,
            ]
        );

        $_SESSION['flash_success'] =
            'Die Vertragspause vom '
            . $pauseFrom->format(
                'd.m.Y'
            )
            . ' bis '
            . $pauseTo->format(
                'd.m.Y'
            )
            . ' wurde hinterlegt.';
    } catch (Throwable $e) {
        $_SESSION['flash_pause_error'] =
            $e->getMessage();

        $_SESSION[
            'pause_form_values'
        ] = [
            'pause_from' =>
                $pauseFromValue,
            'pause_to' =>
                $pauseToValue,
            'reason' =>
                $reason,
        ];

        $_SESSION['open_pause_modal'] =
            true;
    }

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Vertragspause entfernen
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/pauses/(\d+)/delete$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.edit'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractId =
        (int) $matches[1];

    $pauseId =
        (int) $matches[2];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    $pauseStmt = db()->prepare(
        '
        SELECT
            id,
            pause_from,
            pause_to,
            reason
        FROM contract_pauses
        WHERE id = :id
          AND contract_id = :contract_id
          AND deleted_at IS NULL
        LIMIT 1
        '
    );

    $pauseStmt->execute([
        'id' =>
            $pauseId,
        'contract_id' =>
            $contractId,
    ]);

    $pause =
        $pauseStmt->fetch();

    if (!$pause) {
        $_SESSION['flash_warning'] =
            'Die Vertragspause wurde nicht gefunden.';

        redirect(
            '/contracts/'
            . $contractId
        );
    }

    $delete = db()->prepare(
        '
        UPDATE contract_pauses
        SET deleted_at = CURRENT_TIMESTAMP
        WHERE id = :id
          AND contract_id = :contract_id
        '
    );

    $delete->execute([
        'id' =>
            $pauseId,
        'contract_id' =>
            $contractId,
    ]);

    audit_log(
        (int) $user['id'],
        'contract_pause_removed',
        'Vertragspause entfernt: '
            . $contract['title'],
        'contract',
        $contractId,
        [
            'pause_id' =>
                $pauseId,
            'pause_from' =>
                $pause['pause_from'],
            'pause_to' =>
                $pause['pause_to'],
            'reason' =>
                $pause['reason'],
        ]
    );

    $_SESSION['flash_success'] =
        'Die Vertragspause wurde entfernt.';

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Historischen Vertrag wieder aktivieren
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/reactivate$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.edit'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    $stmt = db()->prepare(
        '
        UPDATE contracts
        SET
            status = "active",
            cancelled_at = NULL,
            cancellation_effective_date = NULL,
            updated_by = :updated_by
        WHERE id = :id
          AND deleted_at IS NULL
        '
    );

    $stmt->execute([
        'updated_by' =>
            (int) $user['id'],
        'id' =>
            $contractId,
    ]);

    audit_log(
        (int) $user['id'],
        'contract_reactivated',
        'Vertrag wieder aktiviert: '
            . $contract['title'],
        'contract',
        $contractId,
        [
            'before' => [
                'status' =>
                    $contract['status'],
            ],
            'after' => [
                'status' =>
                    'active',
            ],
            'title' =>
                $contract['title'],
        ]
    );

    $_SESSION['flash_success'] =
        'Der Vertrag ist wieder aktiv und wird erneut in den laufenden Kosten berücksichtigt.';

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Vertrag endgültig löschen
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/delete$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.delete'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractId =
        (int) $matches[1];

    try {
        $result =
            delete_contract_permanently(
                $contractId
            );

        audit_log(
            (int) $user['id'],
            'contract_purged',
            'Ein Vertrag wurde endgültig gelöscht.',
            'system',
            null
        );

        $_SESSION['flash_success'] =
            'Der Vertrag „'
            . $result['title']
            . '“ wurde endgültig gelöscht.';

        if (
            !$result[
                'file_cleanup_complete'
            ]
        ) {
            $_SESSION['flash_warning'] =
                'Der Vertrag wurde aus der Datenbank entfernt. Mindestens eine zugehörige Datei konnte jedoch nicht automatisch von der Festplatte gelöscht werden.';
        }
    } catch (Throwable $e) {
        $_SESSION['flash_warning'] =
            'Der Vertrag konnte nicht endgültig gelöscht werden: '
            . $e->getMessage();
    }

    redirect('/contracts');
}



/*
|--------------------------------------------------------------------------
| Administration – Übersicht
|--------------------------------------------------------------------------
*/

if (
    $path === '/admin'
    && $method === 'GET'
) {
    $user =
        require_admin_access();

    $success =
        $_SESSION['flash_success']
        ?? null;

    unset(
        $_SESSION['flash_success']
    );

    render(
        'admin/index',
        [
            'pageTitle' =>
                'Administration',
            'user' =>
                $user,
            'userStats' =>
                has_permission(
                    'users.manage'
                )
                    ? admin_user_stats()
                    : [],
            'typeStats' =>
                has_permission(
                    'contract_types.manage'
                )
                    ? admin_contract_type_stats()
                    : [],
            'holderStats' =>
                has_permission(
                    'settings.manage'
                )
                    ? admin_holder_stats()
                    : [],
            'success' =>
                $success,
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Administration – Benutzer
|--------------------------------------------------------------------------
*/

if (
    $path === '/admin/users'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'users.manage'
        );

    $success =
        $_SESSION['flash_success']
        ?? null;

    unset(
        $_SESSION['flash_success']
    );

    render(
        'admin/users/index',
        [
            'pageTitle' =>
                'Benutzer',
            'user' =>
                $user,
            'users' =>
                admin_users(),
            'success' =>
                $success,
        ]
    );
}


if (
    $path === '/admin/users/create'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'users.manage'
        );

    $defaultRoleId = '';

    foreach (
        admin_roles()
        as $role
    ) {
        if ($role['name'] === 'user') {
            $defaultRoleId =
                (string) $role['id'];

            break;
        }
    }

    render(
        'admin/users/create',
        [
            'pageTitle' =>
                'Benutzer anlegen',
            'user' =>
                $user,
            'roles' =>
                admin_roles(),
            'values' =>
                [
                    'username' => '',
                    'display_name' => '',
                    'email' => '',
                    'role_id' =>
                        $defaultRoleId,
                    'is_active' => '1',
                    'must_change_password' =>
                        '1',
                ],
            'error' =>
                null,
        ]
    );
}


if (
    $path === '/admin/users/create'
    && $method === 'POST'
) {
    $user =
        require_permission(
            'users.manage'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $values = [
        'username' =>
            trim(
                (string) (
                    $_POST['username']
                    ?? ''
                )
            ),
        'display_name' =>
            trim(
                (string) (
                    $_POST['display_name']
                    ?? ''
                )
            ),
        'email' =>
            trim(
                (string) (
                    $_POST['email']
                    ?? ''
                )
            ),
        'role_id' =>
            trim(
                (string) (
                    $_POST['role_id']
                    ?? ''
                )
            ),
        'is_active' =>
            isset($_POST['is_active'])
                ? '1'
                : '0',
        'must_change_password' =>
            isset(
                $_POST[
                    'must_change_password'
                ]
            )
                ? '1'
                : '0',
        'password' =>
            (string) (
                $_POST['password']
                ?? ''
            ),
    ];

    $error =
        admin_validate_user_values(
            $values,
            null,
            true
        );

    if ($error !== null) {
        render(
            'admin/users/create',
            [
                'pageTitle' =>
                    'Benutzer anlegen',
                'user' =>
                    $user,
                'roles' =>
                    admin_roles(),
                'values' =>
                    $values,
                'error' =>
                    $error,
            ]
        );
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            '
            INSERT INTO users (
                username,
                display_name,
                email,
                password_hash,
                is_active,
                must_change_password
            )
            VALUES (
                :username,
                :display_name,
                :email,
                :password_hash,
                :is_active,
                :must_change_password
            )
            '
        );

        $stmt->execute([
            'username' =>
                $values['username'],
            'display_name' =>
                $values['display_name'],
            'email' =>
                $values['email'] !== ''
                    ? $values['email']
                    : null,
            'password_hash' =>
                password_hash(
                    $values['password'],
                    PASSWORD_DEFAULT
                ),
            'is_active' =>
                $values['is_active']
                === '1'
                    ? 1
                    : 0,
            'must_change_password' =>
                $values[
                    'must_change_password'
                ] === '1'
                    ? 1
                    : 0,
        ]);

        $newUserId =
            (int) $pdo->lastInsertId();

        admin_assign_user_role(
            $newUserId,
            (int) $values['role_id']
        );

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

    audit_log(
        (int) $user['id'],
        'user_created',
        'Benutzer angelegt: '
            . $values['username'],
        'user',
        $newUserId,
        [
            'username' =>
                $values['username'],
            'display_name' =>
                $values['display_name'],
            'email' =>
                $values['email'] !== ''
                    ? $values['email']
                    : null,
            'role_id' =>
                (int) $values['role_id'],
            'is_active' =>
                $values['is_active'] === '1',
            'must_change_password' =>
                $values['must_change_password'] === '1',
        ]
    );

    $_SESSION['flash_success'] =
        'Der Benutzer wurde erfolgreich angelegt.';

    redirect('/admin/users');
}


if (
    $method === 'GET'
    && preg_match(
        '#^/admin/users/(\d+)/edit$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'users.manage'
        );

    $editingUser =
        admin_find_user(
            (int) $matches[1]
        );

    if ($editingUser === null) {
        http_response_code(404);
        exit('Benutzer nicht gefunden.');
    }

    render(
        'admin/users/edit',
        [
            'pageTitle' =>
                'Benutzer bearbeiten',
            'user' =>
                $user,
            'editingUser' =>
                $editingUser,
            'roles' =>
                admin_roles(),
            'values' =>
                [
                    'username' =>
                        $editingUser[
                            'username'
                        ],
                    'display_name' =>
                        $editingUser[
                            'display_name'
                        ],
                    'email' =>
                        $editingUser[
                            'email'
                        ] ?? '',
                    'role_id' =>
                        (string) (
                            $editingUser[
                                'role_id'
                            ] ?? ''
                        ),
                    'is_active' =>
                        (string) (
                            $editingUser[
                                'is_active'
                            ] ?? 0
                        ),
                    'password' => '',
                ],
            'error' =>
                null,
        ]
    );
}


if (
    $method === 'POST'
    && preg_match(
        '#^/admin/users/(\d+)/edit$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'users.manage'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $editingUser =
        admin_find_user(
            (int) $matches[1]
        );

    if ($editingUser === null) {
        http_response_code(404);
        exit('Benutzer nicht gefunden.');
    }

    $isSelf =
        (int) $editingUser['id']
        ===
        (int) $user['id'];

    $values = [
        'username' =>
            trim(
                (string) (
                    $_POST['username']
                    ?? ''
                )
            ),
        'display_name' =>
            trim(
                (string) (
                    $_POST['display_name']
                    ?? ''
                )
            ),
        'email' =>
            trim(
                (string) (
                    $_POST['email']
                    ?? ''
                )
            ),
        'role_id' =>
            $isSelf
                ? (string) (
                    $editingUser[
                        'role_id'
                    ] ?? ''
                )
                : trim(
                    (string) (
                        $_POST['role_id']
                        ?? ''
                    )
                ),
        'is_active' =>
            $isSelf
                ? '1'
                : (
                    isset(
                        $_POST['is_active']
                    )
                        ? '1'
                        : '0'
                ),
        'password' =>
            (string) (
                $_POST['password']
                ?? ''
            ),
    ];

    $error =
        admin_validate_user_values(
            $values,
            (int) $editingUser['id'],
            false
        );

    if ($error !== null) {
        render(
            'admin/users/edit',
            [
                'pageTitle' =>
                    'Benutzer bearbeiten',
                'user' =>
                    $user,
                'editingUser' =>
                    $editingUser,
                'roles' =>
                    admin_roles(),
                'values' =>
                    $values,
                'error' =>
                    $error,
            ]
        );
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        if (
            $values['password']
            !== ''
        ) {
            $stmt = $pdo->prepare(
                '
                UPDATE users
                SET
                    username =
                        :username,

                    display_name =
                        :display_name,

                    email =
                        :email,

                    is_active =
                        :is_active,

                    password_hash =
                        :password_hash,

                    must_change_password = 1

                WHERE id = :id
                  AND deleted_at IS NULL
                '
            );

            $stmt->execute([
                'username' =>
                    $values['username'],
                'display_name' =>
                    $values['display_name'],
                'email' =>
                    $values['email'] !== ''
                        ? $values['email']
                        : null,
                'is_active' =>
                    $values['is_active']
                    === '1'
                        ? 1
                        : 0,
                'password_hash' =>
                    password_hash(
                        $values['password'],
                        PASSWORD_DEFAULT
                    ),
                'id' =>
                    (int) $editingUser['id'],
            ]);
        } else {
            $stmt = $pdo->prepare(
                '
                UPDATE users
                SET
                    username =
                        :username,

                    display_name =
                        :display_name,

                    email =
                        :email,

                    is_active =
                        :is_active

                WHERE id = :id
                  AND deleted_at IS NULL
                '
            );

            $stmt->execute([
                'username' =>
                    $values['username'],
                'display_name' =>
                    $values['display_name'],
                'email' =>
                    $values['email'] !== ''
                        ? $values['email']
                        : null,
                'is_active' =>
                    $values['is_active']
                    === '1'
                        ? 1
                        : 0,
                'id' =>
                    (int) $editingUser['id'],
            ]);
        }

        if (!$isSelf) {
            admin_assign_user_role(
                (int) $editingUser['id'],
                (int) $values['role_id']
            );
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

    audit_log(
        (int) $user['id'],
        'user_updated',
        'Benutzer bearbeitet: '
            . $values['username'],
        'user',
        (int) $editingUser['id'],
        [
            'before' => [
                'username' =>
                    $editingUser['username'],
                'display_name' =>
                    $editingUser['display_name'],
                'email' =>
                    $editingUser['email'],
                'role_id' =>
                    (int) (
                        $editingUser['role_id']
                        ?? 0
                    ),
                'is_active' =>
                    (int) $editingUser['is_active']
                    === 1,
            ],
            'after' => [
                'username' =>
                    $values['username'],
                'display_name' =>
                    $values['display_name'],
                'email' =>
                    $values['email'] !== ''
                        ? $values['email']
                        : null,
                'role_id' =>
                    (int) $values['role_id'],
                'is_active' =>
                    $values['is_active'] === '1',
                'password_reset' =>
                    $values['password'] !== '',
            ],
        ]
    );

    $_SESSION['flash_success'] =
        $values['password'] !== ''
            ? 'Der Benutzer wurde aktualisiert und das Passwort zurückgesetzt.'
            : 'Der Benutzer wurde erfolgreich aktualisiert.';

    redirect('/admin/users');
}


/*
|--------------------------------------------------------------------------
| Administration – Vertragsarten
|--------------------------------------------------------------------------
*/

if (
    $path === '/admin/contract-types'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'contract_types.manage'
        );

    $success =
        $_SESSION['flash_success']
        ?? null;

    unset(
        $_SESSION['flash_success']
    );

    render(
        'admin/contract-types/index',
        [
            'pageTitle' =>
                'Vertragsarten',
            'user' =>
                $user,
            'contractTypes' =>
                get_contract_types(false),
            'success' =>
                $success,
        ]
    );
}


if (
    $path === '/admin/contract-types/create'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'contract_types.manage'
        );

    render(
        'admin/contract-types/create',
        [
            'pageTitle' =>
                'Vertragsart anlegen',
            'user' =>
                $user,
            'values' =>
                [
                    'name' => '',
                    'description' => '',
                    'sort_order' => '100',
                    'is_active' => '1',
                ],
            'error' =>
                null,
        ]
    );
}


if (
    $path === '/admin/contract-types/create'
    && $method === 'POST'
) {
    $user =
        require_permission(
            'contract_types.manage'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $values = [
        'name' =>
            trim(
                (string) (
                    $_POST['name']
                    ?? ''
                )
            ),
        'description' =>
            trim(
                (string) (
                    $_POST['description']
                    ?? ''
                )
            ),
        'sort_order' =>
            trim(
                (string) (
                    $_POST['sort_order']
                    ?? '100'
                )
            ),
        'is_active' =>
            isset($_POST['is_active'])
                ? '1'
                : '0',
    ];

    $error = null;

    if ($values['name'] === '') {
        $error =
            'Bitte einen Namen für die Vertragsart eingeben.';
    }

    if (
        $error === null
        && !preg_match(
            '/^-?\d+$/',
            $values['sort_order']
        )
    ) {
        $error =
            'Die Sortierung muss eine ganze Zahl sein.';
    }

    if ($error === null) {
        $unique = db()->prepare(
            '
            SELECT COUNT(*)
            FROM contract_types
            WHERE name = :name
            '
        );

        $unique->execute([
            'name' =>
                $values['name'],
        ]);

        if (
            (int) $unique->fetchColumn()
            > 0
        ) {
            $error =
                'Diese Vertragsart ist bereits vorhanden.';
        }
    }

    if ($error !== null) {
        render(
            'admin/contract-types/create',
            [
                'pageTitle' =>
                    'Vertragsart anlegen',
                'user' =>
                    $user,
                'values' =>
                    $values,
                'error' =>
                    $error,
            ]
        );
    }

    $stmt = db()->prepare(
        '
        INSERT INTO contract_types (
            name,
            description,
            sort_order,
            is_active
        )
        VALUES (
            :name,
            :description,
            :sort_order,
            :is_active
        )
        '
    );

    $stmt->execute([
        'name' =>
            $values['name'],
        'description' =>
            $values['description'] !== ''
                ? $values['description']
                : null,
        'sort_order' =>
            (int) $values['sort_order'],
        'is_active' =>
            $values['is_active']
            === '1'
                ? 1
                : 0,
    ]);

    $typeId =
        (int) db()->lastInsertId();

    audit_log(
        (int) $user['id'],
        'contract_type_created',
        'Vertragsart angelegt: '
            . $values['name'],
        'contract_type',
        $typeId,
        [
            'name' =>
                $values['name'],
            'description' =>
                $values['description'],
            'sort_order' =>
                (int) $values['sort_order'],
            'is_active' =>
                $values['is_active'] === '1',
        ]
    );

    $_SESSION['flash_success'] =
        'Die Vertragsart wurde erfolgreich angelegt.';

    redirect('/admin/contract-types');
}


if (
    $method === 'GET'
    && preg_match(
        '#^/admin/contract-types/(\d+)/edit$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contract_types.manage'
        );

    $contractType =
        admin_find_contract_type(
            (int) $matches[1]
        );

    if ($contractType === null) {
        http_response_code(404);
        exit('Vertragsart nicht gefunden.');
    }

    render(
        'admin/contract-types/edit',
        [
            'pageTitle' =>
                'Vertragsart bearbeiten',
            'user' =>
                $user,
            'contractType' =>
                $contractType,
            'values' =>
                [
                    'name' =>
                        $contractType['name'],
                    'description' =>
                        $contractType[
                            'description'
                        ] ?? '',
                    'sort_order' =>
                        (string) $contractType[
                            'sort_order'
                        ],
                    'is_active' =>
                        (string) $contractType[
                            'is_active'
                        ],
                ],
            'error' =>
                null,
        ]
    );
}


if (
    $method === 'POST'
    && preg_match(
        '#^/admin/contract-types/(\d+)/edit$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contract_types.manage'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractType =
        admin_find_contract_type(
            (int) $matches[1]
        );

    if ($contractType === null) {
        http_response_code(404);
        exit('Vertragsart nicht gefunden.');
    }

    $values = [
        'name' =>
            trim(
                (string) (
                    $_POST['name']
                    ?? ''
                )
            ),
        'description' =>
            trim(
                (string) (
                    $_POST['description']
                    ?? ''
                )
            ),
        'sort_order' =>
            trim(
                (string) (
                    $_POST['sort_order']
                    ?? '100'
                )
            ),
        'is_active' =>
            isset($_POST['is_active'])
                ? '1'
                : '0',
    ];

    $error = null;

    if ($values['name'] === '') {
        $error =
            'Bitte einen Namen für die Vertragsart eingeben.';
    }

    if (
        $error === null
        && !preg_match(
            '/^-?\d+$/',
            $values['sort_order']
        )
    ) {
        $error =
            'Die Sortierung muss eine ganze Zahl sein.';
    }

    if ($error === null) {
        $unique = db()->prepare(
            '
            SELECT COUNT(*)
            FROM contract_types
            WHERE name = :name
              AND id <> :id
            '
        );

        $unique->execute([
            'name' =>
                $values['name'],
            'id' =>
                (int) $contractType['id'],
        ]);

        if (
            (int) $unique->fetchColumn()
            > 0
        ) {
            $error =
                'Diese Vertragsart ist bereits vorhanden.';
        }
    }

    if ($error !== null) {
        render(
            'admin/contract-types/edit',
            [
                'pageTitle' =>
                    'Vertragsart bearbeiten',
                'user' =>
                    $user,
                'contractType' =>
                    $contractType,
                'values' =>
                    $values,
                'error' =>
                    $error,
            ]
        );
    }

    $stmt = db()->prepare(
        '
        UPDATE contract_types
        SET
            name = :name,
            description = :description,
            sort_order = :sort_order,
            is_active = :is_active
        WHERE id = :id
        '
    );

    $stmt->execute([
        'name' =>
            $values['name'],
        'description' =>
            $values['description'] !== ''
                ? $values['description']
                : null,
        'sort_order' =>
            (int) $values['sort_order'],
        'is_active' =>
            $values['is_active']
            === '1'
                ? 1
                : 0,
        'id' =>
            (int) $contractType['id'],
    ]);

    audit_log(
        (int) $user['id'],
        'contract_type_updated',
        'Vertragsart bearbeitet: '
            . $values['name'],
        'contract_type',
        (int) $contractType['id'],
        [
            'before' => [
                'name' =>
                    $contractType['name'],
                'description' =>
                    $contractType['description'],
                'sort_order' =>
                    (int) $contractType['sort_order'],
                'is_active' =>
                    (int) $contractType['is_active']
                    === 1,
            ],
            'after' => [
                'name' =>
                    $values['name'],
                'description' =>
                    $values['description'],
                'sort_order' =>
                    (int) $values['sort_order'],
                'is_active' =>
                    $values['is_active'] === '1',
            ],
        ]
    );

    $_SESSION['flash_success'] =
        'Die Vertragsart wurde erfolgreich aktualisiert.';

    redirect('/admin/contract-types');
}


/*
|--------------------------------------------------------------------------
| Administration – Vertragsinhaber
|--------------------------------------------------------------------------
*/

if (
    $path === '/admin/contract-holders'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'settings.manage'
        );

    $success =
        $_SESSION['flash_success']
        ?? null;

    unset(
        $_SESSION['flash_success']
    );

    render(
        'admin/contract-holders/index',
        [
            'pageTitle' =>
                'Vertragsinhaber',
            'user' =>
                $user,
            'contractHolders' =>
                get_contract_holders(false),
            'success' =>
                $success,
        ]
    );
}


if (
    $path === '/admin/contract-holders/create'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'settings.manage'
        );

    render(
        'admin/contract-holders/create',
        [
            'pageTitle' =>
                'Vertragsinhaber anlegen',
            'user' =>
                $user,
            'values' =>
                [
                    'name' => '',
                    'sort_order' => '100',
                    'is_active' => '1',
                ],
            'error' =>
                null,
        ]
    );
}


if (
    $path === '/admin/contract-holders/create'
    && $method === 'POST'
) {
    $user =
        require_permission(
            'settings.manage'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $values = [
        'name' =>
            trim(
                (string) (
                    $_POST['name']
                    ?? ''
                )
            ),
        'sort_order' =>
            trim(
                (string) (
                    $_POST['sort_order']
                    ?? '100'
                )
            ),
        'is_active' =>
            isset($_POST['is_active'])
                ? '1'
                : '0',
    ];

    $error = null;

    if ($values['name'] === '') {
        $error =
            'Bitte einen Namen für den Vertragsinhaber eingeben.';
    }

    if (
        $error === null
        && !preg_match(
            '/^-?\d+$/',
            $values['sort_order']
        )
    ) {
        $error =
            'Die Sortierung muss eine ganze Zahl sein.';
    }

    if ($error === null) {
        $unique = db()->prepare(
            '
            SELECT COUNT(*)
            FROM contract_holders
            WHERE name = :name
            '
        );

        $unique->execute([
            'name' =>
                $values['name'],
        ]);

        if (
            (int) $unique->fetchColumn()
            > 0
        ) {
            $error =
                'Dieser Vertragsinhaber ist bereits vorhanden.';
        }
    }

    if ($error !== null) {
        render(
            'admin/contract-holders/create',
            [
                'pageTitle' =>
                    'Vertragsinhaber anlegen',
                'user' =>
                    $user,
                'values' =>
                    $values,
                'error' =>
                    $error,
            ]
        );
    }

    $stmt = db()->prepare(
        '
        INSERT INTO contract_holders (
            name,
            sort_order,
            is_active
        )
        VALUES (
            :name,
            :sort_order,
            :is_active
        )
        '
    );

    $stmt->execute([
        'name' =>
            $values['name'],
        'sort_order' =>
            (int) $values['sort_order'],
        'is_active' =>
            $values['is_active']
            === '1'
                ? 1
                : 0,
    ]);

    $holderId =
        (int) db()->lastInsertId();

    audit_log(
        (int) $user['id'],
        'contract_holder_created',
        'Vertragsinhaber angelegt: '
            . $values['name'],
        'contract_holder',
        $holderId,
        [
            'name' =>
                $values['name'],
            'sort_order' =>
                (int) $values['sort_order'],
            'is_active' =>
                $values['is_active'] === '1',
        ]
    );

    $_SESSION['flash_success'] =
        'Der Vertragsinhaber wurde erfolgreich angelegt.';

    redirect('/admin/contract-holders');
}


if (
    $method === 'GET'
    && preg_match(
        '#^/admin/contract-holders/(\d+)/edit$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'settings.manage'
        );

    $contractHolder =
        admin_find_contract_holder(
            (int) $matches[1]
        );

    if ($contractHolder === null) {
        http_response_code(404);
        exit('Vertragsinhaber nicht gefunden.');
    }

    render(
        'admin/contract-holders/edit',
        [
            'pageTitle' =>
                'Vertragsinhaber bearbeiten',
            'user' =>
                $user,
            'contractHolder' =>
                $contractHolder,
            'values' =>
                [
                    'name' =>
                        $contractHolder['name'],
                    'sort_order' =>
                        (string) $contractHolder[
                            'sort_order'
                        ],
                    'is_active' =>
                        (string) $contractHolder[
                            'is_active'
                        ],
                ],
            'error' =>
                null,
        ]
    );
}


if (
    $method === 'POST'
    && preg_match(
        '#^/admin/contract-holders/(\d+)/edit$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'settings.manage'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractHolder =
        admin_find_contract_holder(
            (int) $matches[1]
        );

    if ($contractHolder === null) {
        http_response_code(404);
        exit('Vertragsinhaber nicht gefunden.');
    }

    $values = [
        'name' =>
            trim(
                (string) (
                    $_POST['name']
                    ?? ''
                )
            ),
        'sort_order' =>
            trim(
                (string) (
                    $_POST['sort_order']
                    ?? '100'
                )
            ),
        'is_active' =>
            isset($_POST['is_active'])
                ? '1'
                : '0',
    ];

    $error = null;

    if ($values['name'] === '') {
        $error =
            'Bitte einen Namen für den Vertragsinhaber eingeben.';
    }

    if (
        $error === null
        && !preg_match(
            '/^-?\d+$/',
            $values['sort_order']
        )
    ) {
        $error =
            'Die Sortierung muss eine ganze Zahl sein.';
    }

    if ($error === null) {
        $unique = db()->prepare(
            '
            SELECT COUNT(*)
            FROM contract_holders
            WHERE name = :name
              AND id <> :id
            '
        );

        $unique->execute([
            'name' =>
                $values['name'],
            'id' =>
                (int) $contractHolder['id'],
        ]);

        if (
            (int) $unique->fetchColumn()
            > 0
        ) {
            $error =
                'Dieser Vertragsinhaber ist bereits vorhanden.';
        }
    }

    if ($error !== null) {
        render(
            'admin/contract-holders/edit',
            [
                'pageTitle' =>
                    'Vertragsinhaber bearbeiten',
                'user' =>
                    $user,
                'contractHolder' =>
                    $contractHolder,
                'values' =>
                    $values,
                'error' =>
                    $error,
            ]
        );
    }

    $stmt = db()->prepare(
        '
        UPDATE contract_holders
        SET
            name = :name,
            sort_order = :sort_order,
            is_active = :is_active
        WHERE id = :id
        '
    );

    $stmt->execute([
        'name' =>
            $values['name'],
        'sort_order' =>
            (int) $values['sort_order'],
        'is_active' =>
            $values['is_active']
            === '1'
                ? 1
                : 0,
        'id' =>
            (int) $contractHolder['id'],
    ]);

    audit_log(
        (int) $user['id'],
        'contract_holder_updated',
        'Vertragsinhaber bearbeitet: '
            . $values['name'],
        'contract_holder',
        (int) $contractHolder['id'],
        [
            'before' => [
                'name' =>
                    $contractHolder['name'],
                'sort_order' =>
                    (int) $contractHolder['sort_order'],
                'is_active' =>
                    (int) $contractHolder['is_active']
                    === 1,
            ],
            'after' => [
                'name' =>
                    $values['name'],
                'sort_order' =>
                    (int) $values['sort_order'],
                'is_active' =>
                    $values['is_active'] === '1',
            ],
        ]
    );

    $_SESSION['flash_success'] =
        'Der Vertragsinhaber wurde erfolgreich aktualisiert.';

    redirect('/admin/contract-holders');
}


/*
|--------------------------------------------------------------------------
| Administration – Dokumentarten
|--------------------------------------------------------------------------
*/

if (
    $path === '/admin/document-types'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'settings.manage'
        );

    $success =
        $_SESSION['flash_success']
        ?? null;

    $warning =
        $_SESSION['flash_warning']
        ?? null;

    unset(
        $_SESSION['flash_success'],
        $_SESSION['flash_warning']
    );

    render(
        'admin/document-types/index',
        [
            'pageTitle' =>
                'Dokumentarten',
            'user' =>
                $user,
            'documentTypes' =>
                get_document_types(false),
            'success' =>
                $success,
            'warning' =>
                $warning,
        ]
    );
}


if (
    $path === '/admin/document-types/save'
    && $method === 'POST'
) {
    $user =
        require_permission(
            'settings.manage'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $id =
        isset($_POST['id'])
        && ctype_digit(
            (string) $_POST['id']
        )
            ? (int) $_POST['id']
            : null;

    $name =
        trim(
            (string) (
                $_POST['name']
                ?? ''
            )
        );

    $description =
        trim(
            (string) (
                $_POST['description']
                ?? ''
            )
        );

    $sortOrder =
        isset($_POST['sort_order'])
        && preg_match(
            '/^-?\d+$/',
            (string) $_POST[
                'sort_order'
            ]
        )
            ? (int) $_POST[
                'sort_order'
            ]
            : 100;

    $isActive =
        isset($_POST['is_active'])
            ? 1
            : (
                $id === null
                    ? 1
                    : 0
            );

    if ($name === '') {
        $_SESSION['flash_warning'] =
            'Bitte einen Namen für die Dokumentart eingeben.';

        redirect(
            '/admin/document-types'
        );
    }

    if ($id === null) {
        $duplicate = db()->prepare(
            '
            SELECT COUNT(*)
            FROM document_types
            WHERE name = :name
            '
        );

        $duplicate->execute([
            'name' =>
                $name,
        ]);
    } else {
        $duplicate = db()->prepare(
            '
            SELECT COUNT(*)
            FROM document_types
            WHERE name = :name
              AND id <> :id
            '
        );

        $duplicate->execute([
            'name' =>
                $name,
            'id' =>
                $id,
        ]);
    }

    if (
        (int) $duplicate->fetchColumn()
        > 0
    ) {
        $_SESSION['flash_warning'] =
            'Diese Dokumentart ist bereits vorhanden.';

        redirect(
            '/admin/document-types'
        );
    }

    if ($id === null) {
        $stmt = db()->prepare(
            '
            INSERT INTO document_types (
                name,
                description,
                sort_order,
                is_active
            )
            VALUES (
                :name,
                :description,
                :sort_order,
                :is_active
            )
            '
        );

        $stmt->execute([
            'name' =>
                $name,
            'description' =>
                $description !== ''
                    ? $description
                    : null,
            'sort_order' =>
                $sortOrder,
            'is_active' =>
                $isActive,
        ]);

        $id =
            (int) db()->lastInsertId();

        audit_log(
            (int) $user['id'],
            'document_type_created',
            'Dokumentart angelegt: '
                . $name,
            'document_type',
            $id,
            [
                'name' =>
                    $name,
                'description' =>
                    $description,
                'sort_order' =>
                    $sortOrder,
                'is_active' =>
                    true,
            ]
        );

        $_SESSION['flash_success'] =
            'Die Dokumentart wurde angelegt.';
    } else {
        $stmt = db()->prepare(
            '
            UPDATE document_types
            SET
                name = :name,
                description = :description,
                sort_order = :sort_order,
                is_active = :is_active
            WHERE id = :id
            '
        );

        $stmt->execute([
            'name' =>
                $name,
            'description' =>
                $description !== ''
                    ? $description
                    : null,
            'sort_order' =>
                $sortOrder,
            'is_active' =>
                $isActive,
            'id' =>
                $id,
        ]);

        audit_log(
            (int) $user['id'],
            'document_type_updated',
            'Dokumentart bearbeitet: '
                . $name,
            'document_type',
            $id,
            [
                'name' =>
                    $name,
                'description' =>
                    $description,
                'sort_order' =>
                    $sortOrder,
                'is_active' =>
                    $isActive === 1,
            ]
        );

        $_SESSION['flash_success'] =
            'Die Dokumentart wurde gespeichert.';
    }

    redirect(
        '/admin/document-types'
    );
}


/*
|--------------------------------------------------------------------------
| Administration – Auditlog
|--------------------------------------------------------------------------
*/

if (
    $path === '/admin/audit-log'
    && $method === 'GET'
) {
    $user =
        require_permission(
            'audit.view'
        );

    $auditLimit =
        isset($_GET['limit'])
        && ctype_digit(
            (string) $_GET['limit']
        )
            ? (int) $_GET['limit']
            : 100;

    if (
        !in_array(
            $auditLimit,
            [50, 100, 250, 500],
            true
        )
    ) {
        $auditLimit = 100;
    }

    render(
        'admin/audit-log',
        [
            'pageTitle' =>
                'Auditlog',
            'user' =>
                $user,
            'auditEntries' =>
                admin_audit_entries(
                    $auditLimit
                ),
            'auditUsers' =>
                admin_audit_users(),
            'auditActions' =>
                admin_audit_actions(),
            'auditLimit' =>
                $auditLimit,
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Preisstand / Kostenhistorie ergänzen
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/prices$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.edit'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract(
            $contractId
        );

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    $amountValue =
        trim(
            (string) (
                $_POST['amount']
                ?? ''
            )
        );

    $frequency =
        trim(
            (string) (
                $_POST[
                    'billing_frequency'
                ]
                ?? 'monthly'
            )
        );

    $customMonths =
        isset(
            $_POST[
                'custom_billing_months'
            ]
        )
        && ctype_digit(
            (string) $_POST[
                'custom_billing_months'
            ]
        )
            ? (int) $_POST[
                'custom_billing_months'
            ]
            : null;

    $validFrom =
        trim(
            (string) (
                $_POST['valid_from']
                ?? ''
            )
        );

    $reason =
        trim(
            (string) (
                $_POST['change_reason']
                ?? ''
            )
        );

    try {
        if (
            $amountValue === ''
            || !is_numeric(
                $amountValue
            )
        ) {
            throw new RuntimeException(
                'Bitte einen gültigen Betrag eingeben.'
            );
        }

        if ($validFrom === '') {
            throw new RuntimeException(
                'Bitte ein Gültig-ab-Datum angeben.'
            );
        }

        $historyId =
            record_contract_price_change(
                $contractId,
                (float) $amountValue,
                $frequency,
                $customMonths,
                $validFrom,
                $reason !== ''
                    ? $reason
                    : 'Preisstand ergänzt',
                (int) $user['id']
            );

        audit_log(
            (int) $user['id'],
            'contract_price_changed',
            'Preisstand ergänzt: '
                . $contract['title'],
            'contract',
            $contractId,
            [
                'price_history_id' =>
                    $historyId,
                'amount' =>
                    (float) $amountValue,
                'billing_frequency' =>
                    $frequency,
                'valid_from' =>
                    $validFrom,
                'reason' =>
                    $reason,
            ]
        );

        $_SESSION['flash_success'] =
            'Der Preisstand wurde in der Kostenhistorie gespeichert.';
    } catch (Throwable $e) {
        $_SESSION['flash_price_error'] =
            $e->getMessage();

        $_SESSION[
            'open_price_history_modal'
        ] = true;
    }

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Dokument hochladen
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/contracts/(\d+)/documents$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'documents.upload'
        );

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    try {
        $documentId =
            store_uploaded_document(
                $_FILES['document']
                ?? [],
                $contractId,
                (int) $user['id'],
                trim(
                    (string) (
                        $_POST['document_name']
                        ?? ''
                    )
                ),
                isset(
                    $_POST['document_type_id']
                )
                && ctype_digit(
                    (string) $_POST[
                        'document_type_id'
                    ]
                )
                    ? (int) $_POST[
                        'document_type_id'
                    ]
                    : null,
                trim(
                    (string) (
                        $_POST['document_date']
                        ?? ''
                    )
                ) ?: null,
                isset(
                    $_POST['replaces_document_id']
                )
                && ctype_digit(
                    (string) $_POST[
                        'replaces_document_id'
                    ]
                )
                    ? (int) $_POST[
                        'replaces_document_id'
                    ]
                    : null
            );

        audit_log(
            (int) $user['id'],
            'document_uploaded',
            'Dokument hochgeladen',
            'document',
            $documentId,
            [
                'contract_id' =>
                    $contractId,
                'document_name' =>
                    trim(
                        (string) (
                            $_POST[
                                'document_name'
                            ]
                            ?? ''
                        )
                    ),
                'original_filename' =>
                    (string) (
                        $_FILES['document']['name']
                        ?? ''
                    ),
                'document_type_id' =>
                    isset(
                        $_POST[
                            'document_type_id'
                        ]
                    )
                    && ctype_digit(
                        (string) $_POST[
                            'document_type_id'
                        ]
                    )
                        ? (int) $_POST[
                            'document_type_id'
                        ]
                        : null,
                'document_date' =>
                    trim(
                        (string) (
                            $_POST[
                                'document_date'
                            ]
                            ?? ''
                        )
                    ) ?: null,
                'replaces_document_id' =>
                    isset(
                        $_POST[
                            'replaces_document_id'
                        ]
                    )
                    && ctype_digit(
                        (string) $_POST[
                            'replaces_document_id'
                        ]
                    )
                        ? (int) $_POST[
                            'replaces_document_id'
                        ]
                        : null,
            ]
        );

        $_SESSION['flash_success'] =
            'Das Dokument wurde erfolgreich hochgeladen.';
    } catch (Throwable $e) {
        $_SESSION['flash_document_error'] =
            $e->getMessage();
    }

    redirect(
        '/contracts/'
        . $contractId
    );
}


/*
|--------------------------------------------------------------------------
| Dokumentvorschau – PDF/Office Metadaten
|--------------------------------------------------------------------------
*/

if (
    $method === 'GET'
    && preg_match(
        '#^/documents/(\d+)/preview-info$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'documents.view'
        );

    $documentId =
        (int) $matches[1];

    $stmt = db()->prepare(
        '
        SELECT
            d.*,
            c.id AS contract_id
        FROM contract_documents d
        INNER JOIN contracts c
            ON c.id = d.contract_id
        WHERE d.id = :id
          AND d.deleted_at IS NULL
          AND c.deleted_at IS NULL
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $documentId,
    ]);

    $document =
        $stmt->fetch();

    if (!$document) {
        http_response_code(404);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        echo json_encode([
            'error' =>
                'Dokument nicht gefunden.',
        ]);

        exit;
    }

    $mode =
        document_preview_mode(
            $document
        );

    if (
        !in_array(
            $mode,
            [
                'pdf',
                'office',
            ],
            true
        )
    ) {
        http_response_code(415);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        echo json_encode([
            'error' =>
                'Für diesen Dateityp ist keine seitenbasierte Vorschau verfügbar.',
        ]);

        exit;
    }

    $filePath =
        resolve_document_path(
            $document['storage_path']
        );

    if ($filePath === null) {
        http_response_code(404);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        echo json_encode([
            'error' =>
                'Datei nicht gefunden.',
        ]);

        exit;
    }

    $pdfPath =
        document_preview_pdf_path(
            $document,
            $filePath
        );

    if ($pdfPath === null) {
        http_response_code(500);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        echo json_encode([
            'error' =>
                $mode === 'office'
                    ? 'Die Office-Datei konnte lokal nicht für die Vorschau aufbereitet werden.'
                    : 'Die PDF-Vorschau konnte nicht vorbereitet werden.',
        ]);

        exit;
    }

    $pageCount =
        document_pdf_page_count(
            $pdfPath
        );

    if ($pageCount === null) {
        http_response_code(500);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        echo json_encode([
            'error' =>
                'Die Seitenanzahl konnte nicht ermittelt werden.',
        ]);

        exit;
    }

    audit_log(
        (int) $user['id'],
        'document_previewed',
        'Dokument angesehen: '
            . $document['original_filename'],
        'document',
        $documentId,
        [
            'contract_id' =>
                (int) $document['contract_id'],
            'filename' =>
                $document['original_filename'],
            'preview_mode' =>
                $mode === 'office'
                    ? 'office_as_pdf'
                    : 'pdf_pages',
            'pages' =>
                $pageCount,
        ]
    );

    header(
        'Content-Type: application/json; charset=UTF-8'
    );

    header(
        'Cache-Control: private, no-store, max-age=0'
    );

    echo json_encode(
        [
            'document_id' =>
                $documentId,
            'mode' =>
                $mode,
            'pages' =>
                $pageCount,
        ],
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Dokumentvorschau – einzelne PDF/Office-Seite
|--------------------------------------------------------------------------
*/

if (
    $method === 'GET'
    && preg_match(
        '#^/documents/(\d+)/preview-page$#',
        $path,
        $matches
    )
) {
    require_permission(
        'documents.view'
    );

    $documentId =
        (int) $matches[1];

    $page =
        isset($_GET['page'])
        && ctype_digit(
            (string) $_GET['page']
        )
            ? max(
                1,
                (int) $_GET['page']
            )
            : 1;

    $stmt = db()->prepare(
        '
        SELECT
            d.*,
            c.id AS contract_id
        FROM contract_documents d
        INNER JOIN contracts c
            ON c.id = d.contract_id
        WHERE d.id = :id
          AND d.deleted_at IS NULL
          AND c.deleted_at IS NULL
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $documentId,
    ]);

    $document =
        $stmt->fetch();

    if (!$document) {
        http_response_code(404);
        exit('Dokument nicht gefunden.');
    }

    $mode =
        document_preview_mode(
            $document
        );

    if (
        !in_array(
            $mode,
            [
                'pdf',
                'office',
            ],
            true
        )
    ) {
        http_response_code(415);
        exit('Dieser Dateityp unterstützt keine seitenbasierte Vorschau.');
    }

    $filePath =
        resolve_document_path(
            $document['storage_path']
        );

    if ($filePath === null) {
        http_response_code(404);
        exit('Datei nicht gefunden.');
    }

    $pdfPath =
        document_preview_pdf_path(
            $document,
            $filePath
        );

    if ($pdfPath === null) {
        http_response_code(500);
        exit('Die Dokumentvorschau konnte nicht vorbereitet werden.');
    }

    $pageCount =
        document_pdf_page_count(
            $pdfPath
        );

    if (
        $pageCount === null
        || $page > $pageCount
    ) {
        http_response_code(404);
        exit('Seite nicht gefunden.');
    }

    $pageImage =
        document_render_pdf_page_png(
            $document,
            $pdfPath,
            $page
        );

    if ($pageImage === null) {
        http_response_code(500);
        exit('Die Dokumentseite konnte nicht gerendert werden.');
    }

    header(
        'X-Content-Type-Options: nosniff'
    );

    header(
        'Content-Type: image/png'
    );

    header(
        'Content-Length: '
        . filesize(
            $pageImage
        )
    );

    header(
        'Cache-Control: private, max-age=3600'
    );

    readfile(
        $pageImage
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Dokument im Browser anzeigen
|--------------------------------------------------------------------------
*/

if (
    $method === 'GET'
    && preg_match(
        '#^/documents/(\d+)/preview$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'documents.view'
        );

    $documentId =
        (int) $matches[1];

    $stmt = db()->prepare(
        '
        SELECT
            d.*,
            c.id AS contract_id
        FROM contract_documents d
        INNER JOIN contracts c
            ON c.id = d.contract_id
        WHERE d.id = :id
          AND d.deleted_at IS NULL
          AND c.deleted_at IS NULL
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $documentId,
    ]);

    $document =
        $stmt->fetch();

    if (!$document) {
        http_response_code(404);
        exit('Dokument nicht gefunden.');
    }

    if (
        !document_is_previewable(
            $document
        )
    ) {
        http_response_code(415);
        exit(
            'Für diesen Dateityp ist keine direkte Browser-Vorschau verfügbar.'
        );
    }

    $filePath =
        resolve_document_path(
            $document['storage_path']
        );

    if ($filePath === null) {
        http_response_code(404);
        exit('Datei nicht gefunden.');
    }

    audit_log(
        (int) $user['id'],
        'document_previewed',
        'Dokument angesehen: '
            . $document['original_filename'],
        'document',
        $documentId,
        [
            'contract_id' =>
                (int) $document['contract_id'],
            'filename' =>
                $document['original_filename'],
            'preview_mode' =>
                document_preview_mode(
                    $document
                ),
        ]
    );

    $originalFilename =
        (string) $document[
            'original_filename'
        ];

    $fallbackFilename =
        preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            $originalFilename
        );

    header(
        'X-Content-Type-Options: nosniff'
    );

    header(
        'Cache-Control: private, no-store, max-age=0'
    );

    header(
        'Pragma: no-cache'
    );

    header(
        'Content-Type: '
        . $document['mime_type']
    );

    header(
        'Content-Length: '
        . filesize($filePath)
    );

    header(
        'Content-Disposition: inline; filename="'
        . $fallbackFilename
        . '"; filename*=UTF-8\'\''
        . rawurlencode(
            $originalFilename
        )
    );

    readfile($filePath);

    exit;
}


/*
|--------------------------------------------------------------------------
| Dokument herunterladen
|--------------------------------------------------------------------------
*/

if (
    $method === 'GET'
    && preg_match(
        '#^/documents/(\d+)/download$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'documents.view'
        );

    $documentId =
        (int) $matches[1];

    $stmt = db()->prepare(
        '
        SELECT
            d.*,
            c.id AS contract_id
        FROM contract_documents d
        INNER JOIN contracts c
            ON c.id = d.contract_id
        WHERE d.id = :id
          AND d.deleted_at IS NULL
          AND c.deleted_at IS NULL
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $documentId,
    ]);

    $document =
        $stmt->fetch();

    if (!$document) {
        http_response_code(404);
        exit('Dokument nicht gefunden.');
    }

    $filePath =
        resolve_document_path(
            $document['storage_path']
        );

    if ($filePath === null) {
        http_response_code(404);
        exit('Datei nicht gefunden.');
    }

    audit_log(
        (int) $user['id'],
        'document_downloaded',
        'Dokument heruntergeladen: '
            . $document['original_filename'],
        'document',
        $documentId,
        [
            'contract_id' =>
                (int) $document['contract_id'],
            'filename' =>
                $document['original_filename'],
        ]
    );

    $originalFilename =
        (string) $document[
            'original_filename'
        ];

    $fallbackFilename =
        preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            $originalFilename
        );

    header(
        'X-Content-Type-Options: nosniff'
    );

    header(
        'Content-Type: '
        . $document['mime_type']
    );

    header(
        'Content-Length: '
        . filesize($filePath)
    );

    header(
        'Content-Disposition: attachment; filename="'
        . $fallbackFilename
        . '"; filename*=UTF-8\'\''
        . rawurlencode(
            $originalFilename
        )
    );

    readfile($filePath);

    exit;
}


/*
|--------------------------------------------------------------------------
| Dokument entfernen
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST'
    && preg_match(
        '#^/documents/(\d+)/delete$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'documents.delete'
        );

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    $documentId =
        (int) $matches[1];

    $stmt = db()->prepare(
        '
        SELECT
            id,
            contract_id,
            original_filename,
            replaces_document_id,
            is_current
        FROM contract_documents
        WHERE id = :id
          AND deleted_at IS NULL
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $documentId,
    ]);

    $document =
        $stmt->fetch();

    if (!$document) {
        http_response_code(404);
        exit('Dokument nicht gefunden.');
    }

    $delete = db()->prepare(
        '
        UPDATE contract_documents
        SET deleted_at = CURRENT_TIMESTAMP
        WHERE id = :id
        '
    );

    $delete->execute([
        'id' => $documentId,
    ]);

    if (
        (int) (
            $document[
                'is_current'
            ] ?? 0
        ) === 1
        && !empty(
            $document[
                'replaces_document_id'
            ]
        )
    ) {
        $restorePrevious = db()->prepare(
            '
            UPDATE contract_documents
            SET is_current = 1
            WHERE id = :id
              AND contract_id = :contract_id
              AND deleted_at IS NULL
            '
        );

        $restorePrevious->execute([
            'id' =>
                (int) $document[
                    'replaces_document_id'
                ],
            'contract_id' =>
                (int) $document[
                    'contract_id'
                ],
        ]);
    }

    audit_log(
        (int) $user['id'],
        'document_deleted',
        'Dokument entfernt: '
            . $document['original_filename'],
        'document',
        $documentId,
        [
            'contract_id' =>
                (int) $document['contract_id'],
            'filename' =>
                $document['original_filename'],
        ]
    );

    $_SESSION['flash_success'] =
        'Das Dokument wurde entfernt.';

    redirect(
        '/contracts/'
        . (int) $document['contract_id']
    );
}


/*
|--------------------------------------------------------------------------
| Vertragsdetail
|--------------------------------------------------------------------------
*/

if (
    $method === 'GET'
    && preg_match(
        '#^/contracts/(\d+)$#',
        $path,
        $matches
    )
) {
    $user =
        require_permission(
            'contracts.view'
        );

    $contractId =
        (int) $matches[1];

    $contract =
        find_contract($contractId);

    if ($contract === null) {
        http_response_code(404);
        exit('Vertrag nicht gefunden.');
    }

    $documents =
        get_contract_documents(
            $contractId
        );

    $documentHistory =
        get_contract_document_history(
            $contractId
        );

    $documentTypes =
        get_document_types();

    $priceHistory =
        get_contract_price_history(
            $contractId
        );

    $deadlineInfo =
        contract_deadline_info(
            $contract
        );

    $contractPauses =
        get_contract_pauses(
            $contractId
        );

    $pauseState =
        contract_pause_state(
            array_merge(
                $contract,
                [
                    '_pauses' =>
                        $contractPauses,
                ]
            )
        );

    $accumulatedCostSummary =
        contract_accumulated_cost_summary(
            $contract
        );

    $success =
        $_SESSION['flash_success']
        ?? null;

    $warning =
        $_SESSION['flash_warning']
        ?? null;

    $documentError =
        $_SESSION[
            'flash_document_error'
        ] ?? null;

    $priceError =
        $_SESSION[
            'flash_price_error'
        ] ?? null;

    $cancelError =
        $_SESSION[
            'flash_cancel_error'
        ] ?? null;

    $cancelFormValues =
        $_SESSION[
            'cancel_form_values'
        ] ?? [];

    $pauseError =
        $_SESSION[
            'flash_pause_error'
        ] ?? null;

    $pauseFormValues =
        $_SESSION[
            'pause_form_values'
        ] ?? [];

    $openPriceHistoryModal =
        !empty(
            $_SESSION[
                'open_price_history_modal'
            ]
        );

    $openCancelModal =
        !empty(
            $_SESSION[
                'open_cancel_modal'
            ]
        );

    $openPauseModal =
        !empty(
            $_SESSION[
                'open_pause_modal'
            ]
        );

    unset(
        $_SESSION['flash_success'],
        $_SESSION['flash_warning'],
        $_SESSION[
            'flash_document_error'
        ],
        $_SESSION[
            'flash_price_error'
        ],
        $_SESSION[
            'open_price_history_modal'
        ],
        $_SESSION[
            'flash_cancel_error'
        ],
        $_SESSION[
            'flash_pause_error'
        ],
        $_SESSION[
            'cancel_form_values'
        ],
        $_SESSION[
            'pause_form_values'
        ],
        $_SESSION[
            'open_cancel_modal'
        ],
        $_SESSION[
            'open_pause_modal'
        ]
    );

    render(
        'contracts/show',
        [
            'pageTitle' =>
                $contract['title'],
            'user' =>
                $user,
            'contract' =>
                $contract,
            'documents' =>
                $documents,
            'documentHistory' =>
                $documentHistory,
            'documentTypes' =>
                $documentTypes,
            'priceHistory' =>
                $priceHistory,
            'deadlineInfo' =>
                $deadlineInfo,
            'contractPauses' =>
                $contractPauses,
            'pauseState' =>
                $pauseState,
            'accumulatedCostSummary' =>
                $accumulatedCostSummary,
            'success' =>
                $success,
            'warning' =>
                $warning,
            'error' =>
                $documentError,
            'priceError' =>
                $priceError,
            'openPriceHistoryModal' =>
                $openPriceHistoryModal,
            'cancelError' =>
                $cancelError,
            'cancelFormValues' =>
                $cancelFormValues,
            'pauseError' =>
                $pauseError,
            'pauseFormValues' =>
                $pauseFormValues,
            'openCancelModal' =>
                $openCancelModal,
            'openPauseModal' =>
                $openPauseModal,
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

if (
    $path === '/logout'
    && $method === 'POST'
) {
    $user = require_login();

    if (!csrf_verify()) {
        http_response_code(419);
        exit('Ungültige Anfrage.');
    }

    audit_log(
        (int) $user['id'],
        'logout',
        'Benutzer abgemeldet'
    );

    logout_user();

    redirect('/login');
}


/*
|--------------------------------------------------------------------------
| 404
|--------------------------------------------------------------------------
*/

http_response_code(404);

echo 'Seite nicht gefunden.';
