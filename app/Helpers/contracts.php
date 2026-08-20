<?php

declare(strict_types=1);

function contract_billing_frequency_label(
    string $frequency
): string {
    return match ($frequency) {
        'monthly' => 'Monatlich',
        'quarterly' => 'Vierteljährlich',
        'semiannual' => 'Halbjährlich',
        'annual' => 'Jährlich',
        'one_time' => 'Einmalig',
        'custom' => 'Individuell',
        default => $frequency,
    };
}

function contract_status_label(
    string $status
): string {
    return match ($status) {
        'active' => 'Aktiv',
        'paused' => 'Pausiert',
        'planned' => 'Geplant',
        'cancelled' => 'Gekündigt',
        'expired' => 'Beendet',
        default => $status,
    };
}

function contract_notice_unit_label(
    ?string $unit
): string {
    return match ($unit) {
        'days' => 'Tage',
        'weeks' => 'Wochen',
        'months' => 'Monate',
        default => '',
    };
}

function contract_format_date(
    ?string $date
): string {
    if ($date === null || $date === '') {
        return '–';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return '–';
    }

    return date('d.m.Y', $timestamp);
}

function contract_format_money(
    float $amount
): string {
    return number_format(
        $amount,
        2,
        ',',
        '.'
    ) . ' €';
}

function contract_monthly_equivalent(
    array $contract
): float {
    $amount = (float) (
        $contract['amount'] ?? 0
    );

    $frequency =
        $contract['billing_frequency'] ?? '';

    return match ($frequency) {
        'monthly' => $amount,
        'quarterly' => $amount / 3,
        'semiannual' => $amount / 6,
        'annual' => $amount / 12,
        'custom' =>
            !empty($contract['custom_billing_months'])
                ? $amount / (int) $contract['custom_billing_months']
                : 0,
        default => 0,
    };
}

function contract_annual_equivalent(
    array $contract
): float {
    return contract_monthly_equivalent(
        $contract
    ) * 12;
}


function contract_billing_interval_months(
    array $contract
): ?int {
    $frequency =
        (string) (
            $contract[
                'billing_frequency'
            ] ?? ''
        );

    return match ($frequency) {
        'monthly' => 1,
        'quarterly' => 3,
        'semiannual' => 6,
        'annual' => 12,
        'custom' =>
            !empty(
                $contract[
                    'custom_billing_months'
                ]
            )
                ? max(
                    1,
                    (int) $contract[
                        'custom_billing_months'
                    ]
                )
                : null,
        default => null,
    };
}

function contract_date_add_months_clamped(
    string $firstDate,
    int $months
): ?DateTimeImmutable {
    $first =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $firstDate
        );

    if (!$first) {
        return null;
    }

    $originalDay =
        (int) $first->format('d');

    $year =
        (int) $first->format('Y');

    $month =
        (int) $first->format('n');

    $absoluteMonth =
        ($year * 12)
        + ($month - 1)
        + $months;

    $targetYear =
        intdiv(
            $absoluteMonth,
            12
        );

    $targetMonth =
        ($absoluteMonth % 12)
        + 1;

    $firstOfTargetMonth =
        DateTimeImmutable::createFromFormat(
            '!Y-n-j',
            sprintf(
                '%d-%d-1',
                $targetYear,
                $targetMonth
            )
        );

    if (!$firstOfTargetMonth) {
        return null;
    }

    $lastDay =
        (int) $firstOfTargetMonth->format(
            't'
        );

    $targetDay =
        min(
            $originalDay,
            $lastDay
        );

    return DateTimeImmutable::createFromFormat(
        '!Y-n-j',
        sprintf(
            '%d-%d-%d',
            $targetYear,
            $targetMonth,
            $targetDay
        )
    ) ?: null;
}

function contract_cancellation_effective_date(
    array $contract
): ?DateTimeImmutable {
    $value =
        trim(
            (string) (
                $contract[
                    'cancellation_effective_date'
                ] ?? ''
            )
        );

    if ($value === '') {
        return null;
    }

    return DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $value
    ) ?: null;
}

function get_contract_pauses(
    int $contractId,
    bool $includeDeleted = false
): array {
    $sql = '
        SELECT
            p.id,
            p.contract_id,
            p.pause_from,
            p.pause_to,
            p.reason,
            p.created_at,
            p.deleted_at,
            u.display_name AS created_by_name
        FROM contract_pauses p
        LEFT JOIN users u
            ON u.id = p.created_by
        WHERE p.contract_id = :contract_id
    ';

    if (!$includeDeleted) {
        $sql .= '
            AND p.deleted_at IS NULL
        ';
    }

    $sql .= '
        ORDER BY
            p.pause_from DESC,
            p.pause_to DESC,
            p.id DESC
    ';

    $stmt = db()->prepare($sql);

    $stmt->execute([
        'contract_id' =>
            $contractId,
    ]);

    return $stmt->fetchAll();
}

function contract_pause_ranges(
    array $contract
): array {
    if (
        isset($contract['_pauses'])
        && is_array(
            $contract['_pauses']
        )
    ) {
        return $contract['_pauses'];
    }

    if (empty($contract['id'])) {
        return [];
    }

    static $cache = [];

    $contractId =
        (int) $contract['id'];

    if (
        !array_key_exists(
            $contractId,
            $cache
        )
    ) {
        $cache[$contractId] =
            get_contract_pauses(
                $contractId
            );
    }

    return $cache[$contractId];
}

function contract_pause_for_date(
    array $contract,
    DateTimeImmutable $date
): ?array {
    $day =
        $date->format('Y-m-d');

    foreach (
        contract_pause_ranges(
            $contract
        )
        as $pause
    ) {
        if (
            $pause['pause_from'] <= $day
            && $pause['pause_to'] >= $day
        ) {
            return $pause;
        }
    }

    return null;
}

function contract_is_paused_on(
    array $contract,
    DateTimeImmutable $date
): bool {
    return contract_pause_for_date(
        $contract,
        $date
    ) !== null;
}

function contract_pause_state(
    array $contract,
    ?DateTimeImmutable $referenceDate = null
): array {
    $today =
        ($referenceDate
        ?? new DateTimeImmutable('today'))
            ->setTime(0, 0, 0);

    $todayValue =
        $today->format('Y-m-d');

    $current = null;
    $next = null;

    foreach (
        contract_pause_ranges(
            $contract
        )
        as $pause
    ) {
        if (
            $pause['pause_from'] <= $todayValue
            && $pause['pause_to'] >= $todayValue
        ) {
            $current = $pause;
            break;
        }

        if (
            $pause['pause_from'] > $todayValue
            && (
                $next === null
                || $pause['pause_from']
                    < $next['pause_from']
            )
        ) {
            $next = $pause;
        }
    }

    return [
        'is_paused' =>
            $current !== null,
        'current' =>
            $current,
        'next' =>
            $next,
    ];
}

function contract_is_running_on(
    array $contract,
    ?DateTimeImmutable $referenceDate = null
): bool {
    $date =
        ($referenceDate
        ?? new DateTimeImmutable('today'))
            ->setTime(0, 0, 0);

    $status =
        (string) (
            $contract['status']
            ?? 'active'
        );

    if (
        in_array(
            $status,
            [
                'planned',
                'expired',
            ],
            true
        )
    ) {
        return false;
    }

    if ($status === 'cancelled') {
        $cancellationDate =
            contract_cancellation_effective_date(
                $contract
            );

        if (
            $cancellationDate === null
            || $date > $cancellationDate
        ) {
            return false;
        }
    } elseif ($status !== 'active') {
        return false;
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
            && $date < $startDate
        ) {
            return false;
        }
    }

    $effectiveEnd =
        contract_effective_end_date(
            $contract,
            $date
        );

    if (
        $effectiveEnd !== null
        && $date > $effectiveEnd
    ) {
        return false;
    }

    return true;
}

function contract_is_historical(
    array $contract,
    ?DateTimeImmutable $referenceDate = null
): bool {
    $date =
        ($referenceDate
        ?? new DateTimeImmutable('today'))
            ->setTime(0, 0, 0);

    if (
        ($contract['status'] ?? '')
        === 'expired'
    ) {
        return true;
    }

    if (
        ($contract['status'] ?? '')
        !== 'cancelled'
    ) {
        return false;
    }

    $cancellationDate =
        contract_cancellation_effective_date(
            $contract
        );

    return (
        $cancellationDate === null
        || $date > $cancellationDate
    );
}

function contract_payment_date_is_after_end(
    DateTimeImmutable $paymentDate,
    array $contract
): bool {
    $cancellationDate =
        contract_cancellation_effective_date(
            $contract
        );

    if (
        ($contract['status'] ?? '')
        === 'cancelled'
        && $cancellationDate !== null
        && $paymentDate > $cancellationDate
    ) {
        return true;
    }

    $effectiveEnd =
        contract_effective_end_date(
            $contract,
            $paymentDate
        );

    return (
        $effectiveEnd !== null
        && $paymentDate > $effectiveEnd
    );
}


function contract_renewal_state(
    array $contract,
    ?DateTimeImmutable $referenceDate = null
): array {
    $referenceDate =
        ($referenceDate
        ?? new DateTimeImmutable('today'))
            ->setTime(0, 0, 0);

    $endDateValue =
        trim(
            (string) (
                $contract['end_date']
                ?? ''
            )
        );

    if ($endDateValue === '') {
        return [
            'original_end' => null,
            'effective_end' => null,
            'last_renewal_date' => null,
            'was_renewed' => false,
        ];
    }

    $originalEnd =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $endDateValue
        );

    if (!$originalEnd) {
        return [
            'original_end' => null,
            'effective_end' => null,
            'last_renewal_date' => null,
            'was_renewed' => false,
        ];
    }

    $cancellationDate =
        contract_cancellation_effective_date(
            $contract
        );

    if (
        ($contract['status'] ?? '')
        === 'cancelled'
        && $cancellationDate !== null
    ) {
        return [
            'original_end' =>
                $originalEnd,
            'effective_end' =>
                $cancellationDate,
            'last_renewal_date' =>
                null,
            'was_renewed' =>
                false,
        ];
    }

    $automaticRenewal =
        (int) (
            $contract[
                'automatic_renewal'
            ] ?? 0
        ) === 1;

    $renewalMonths =
        (int) (
            $contract[
                'renewal_period_months'
            ] ?? 0
        );

    if (
        !$automaticRenewal
        || $renewalMonths < 1
        || $originalEnd >= $referenceDate
    ) {
        return [
            'original_end' =>
                $originalEnd,
            'effective_end' =>
                $originalEnd,
            'last_renewal_date' =>
                null,
            'was_renewed' =>
                false,
        ];
    }

    $monthsDifference =
        (
            (int) $referenceDate->format(
                'Y'
            )
            - (int) $originalEnd->format(
                'Y'
            )
        ) * 12
        + (
            (int) $referenceDate->format(
                'n'
            )
            - (int) $originalEnd->format(
                'n'
            )
        );

    $estimatedIndex =
        max(
            1,
            intdiv(
                max(
                    0,
                    $monthsDifference
                ),
                $renewalMonths
            )
        );

    $effectiveEnd = null;
    $effectiveIndex = null;

    for (
        $index = max(
            1,
            $estimatedIndex - 1
        );
        $index <= $estimatedIndex + 3;
        $index++
    ) {
        $candidate =
            contract_date_add_months_clamped(
                $endDateValue,
                $index * $renewalMonths
            );

        if (
            $candidate !== null
            && $candidate >= $referenceDate
        ) {
            $effectiveEnd =
                $candidate;

            $effectiveIndex =
                $index;

            break;
        }
    }

    if (
        $effectiveEnd === null
        || $effectiveIndex === null
    ) {
        return [
            'original_end' =>
                $originalEnd,
            'effective_end' =>
                $originalEnd,
            'last_renewal_date' =>
                null,
            'was_renewed' =>
                false,
        ];
    }

    $lastRenewalDate =
        contract_date_add_months_clamped(
            $endDateValue,
            (
                $effectiveIndex - 1
            ) * $renewalMonths
        );

    return [
        'original_end' =>
            $originalEnd,
        'effective_end' =>
            $effectiveEnd,
        'last_renewal_date' =>
            $lastRenewalDate,
        'was_renewed' =>
            true,
    ];
}

function contract_effective_end_date(
    array $contract,
    ?DateTimeImmutable $referenceDate = null
): ?DateTimeImmutable {
    $state =
        contract_renewal_state(
            $contract,
            $referenceDate
        );

    return $state[
        'effective_end'
    ];
}

function contract_notifications(
    int $warningDays = 30,
    int $renewedNoticeDays = 30
): array {
    $stmt = db()->query(
        '
        SELECT
            c.id,
            c.title,
            c.provider,
            c.status,
            c.end_date,
            c.cancelled_at,
            c.cancellation_effective_date,
            c.automatic_renewal,
            c.renewal_period_months,

            COALESCE(
                NULLIF(h.name, ""),
                NULLIF(c.contract_holder, ""),
                "–"
            ) AS contract_holder_name

        FROM contracts c

        LEFT JOIN contract_holders h
            ON h.id = c.contract_holder_id

        WHERE c.deleted_at IS NULL
          AND c.notifications_enabled = 1
          AND (
              c.status = "active"
              OR (
                  c.status = "cancelled"
                  AND c.cancellation_effective_date IS NOT NULL
                  AND c.cancellation_effective_date >= CURRENT_DATE
              )
          )
          AND (
              c.end_date IS NOT NULL
              OR c.cancellation_effective_date IS NOT NULL
          )

        ORDER BY
            COALESCE(
                c.cancellation_effective_date,
                c.end_date
            ) ASC
        '
    );

    $contracts =
        $stmt->fetchAll();

    $today =
        new DateTimeImmutable('today');

    $warningUntil =
        $today->modify(
            '+'
            . max(
                1,
                $warningDays
            )
            . ' days'
        );

    $renewedSince =
        $today->modify(
            '-'
            . max(
                1,
                $renewedNoticeDays
            )
            . ' days'
        );

    $notifications = [];

    foreach ($contracts as $contract) {
        $cancellationDate =
            contract_cancellation_effective_date(
                $contract
            );

        if (
            ($contract['status'] ?? '')
            === 'cancelled'
            && $cancellationDate !== null
        ) {
            if (
                $cancellationDate <= $warningUntil
            ) {
                $daysRemaining =
                    (int) $today
                        ->diff(
                            $cancellationDate
                        )
                        ->format('%a');

                $notifications[] = [
                    'severity' =>
                        $daysRemaining <= 7
                            ? 'warning'
                            : 'info',
                    'type' =>
                        'cancellation_upcoming',
                    'contract_id' =>
                        (int) $contract['id'],
                    'title' =>
                        (string) $contract['title'],
                    'holder' =>
                        (string) $contract[
                            'contract_holder_name'
                        ],
                    'date' =>
                        $cancellationDate->format(
                            'Y-m-d'
                        ),
                    'message' =>
                        $daysRemaining === 0
                            ? 'Der bereits gekündigte Vertrag endet heute.'
                            : 'Der bereits gekündigte Vertrag läuft noch '
                                . $daysRemaining
                                . (
                                    $daysRemaining === 1
                                        ? ' Tag'
                                        : ' Tage'
                                )
                                . ' und endet am '
                                . $cancellationDate->format(
                                    'd.m.Y'
                                )
                                . '.',
                ];
            }

            continue;
        }

        $endDate =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                (string) $contract['end_date']
            );

        if (!$endDate) {
            continue;
        }

        $automaticRenewal =
            (int) $contract[
                'automatic_renewal'
            ] === 1;

        $renewalMonths =
            (int) (
                $contract[
                    'renewal_period_months'
                ] ?? 0
            );

        if (
            $endDate < $today
            && !$automaticRenewal
        ) {
            $daysPast =
                (int) $endDate
                    ->diff($today)
                    ->format('%a');

            $notifications[] = [
                'severity' => 'danger',
                'type' => 'expired',
                'contract_id' =>
                    (int) $contract['id'],
                'title' =>
                    (string) $contract['title'],
                'holder' =>
                    (string) $contract[
                        'contract_holder_name'
                    ],
                'date' =>
                    $endDate->format('Y-m-d'),
                'message' =>
                    'Der Vertrag ist seit '
                    . $daysPast
                    . (
                        $daysPast === 1
                            ? ' Tag'
                            : ' Tagen'
                    )
                    . ' abgelaufen.',
            ];

            continue;
        }

        if (
            $endDate < $today
            && $automaticRenewal
            && $renewalMonths < 1
        ) {
            $notifications[] = [
                'severity' => 'warning',
                'type' =>
                    'renewal_incomplete',
                'contract_id' =>
                    (int) $contract['id'],
                'title' =>
                    (string) $contract['title'],
                'holder' =>
                    (string) $contract[
                        'contract_holder_name'
                    ],
                'date' =>
                    $endDate->format('Y-m-d'),
                'message' =>
                    'Der Vertrag ist abgelaufen und als automatische Verlängerung markiert. Der Verlängerungszeitraum fehlt.',
            ];

            continue;
        }

        $renewalState =
            contract_renewal_state(
                $contract,
                $today
            );

        $effectiveEnd =
            $renewalState[
                'effective_end'
            ];

        $lastRenewalDate =
            $renewalState[
                'last_renewal_date'
            ];

        if (
            $renewalState[
                'was_renewed'
            ]
            && $lastRenewalDate !== null
            && $lastRenewalDate >= $renewedSince
            && $lastRenewalDate < $today
        ) {
            $notifications[] = [
                'severity' => 'info',
                'type' => 'renewed',
                'contract_id' =>
                    (int) $contract['id'],
                'title' =>
                    (string) $contract['title'],
                'holder' =>
                    (string) $contract[
                        'contract_holder_name'
                    ],
                'date' =>
                    $lastRenewalDate->format(
                        'Y-m-d'
                    ),
                'message' =>
                    'Der Vertrag wurde automatisch verlängert. Das berechnete neue Vertragsende ist der '
                    . $effectiveEnd->format(
                        'd.m.Y'
                    )
                    . '.',
            ];

            continue;
        }

        if (
            $effectiveEnd !== null
            && $effectiveEnd >= $today
            && $effectiveEnd <= $warningUntil
        ) {
            $daysRemaining =
                (int) $today
                    ->diff($effectiveEnd)
                    ->format('%a');

            if ($automaticRenewal) {
                $message =
                    $daysRemaining === 0
                        ? 'Der Vertrag erreicht heute sein Vertragsende und ist zur automatischen Verlängerung markiert.'
                        : 'Der Vertrag erreicht in '
                            . $daysRemaining
                            . (
                                $daysRemaining === 1
                                    ? ' Tag'
                                    : ' Tagen'
                            )
                            . ' sein Vertragsende und ist zur automatischen Verlängerung markiert.';
            } else {
                $message =
                    $daysRemaining === 0
                        ? 'Der Vertrag läuft heute aus.'
                        : 'Der Vertrag läuft in '
                            . $daysRemaining
                            . (
                                $daysRemaining === 1
                                    ? ' Tag'
                                    : ' Tagen'
                            )
                            . ' aus.';
            }

            $notifications[] = [
                'severity' =>
                    $automaticRenewal
                        ? 'warning'
                        : (
                            $daysRemaining <= 7
                                ? 'danger'
                                : 'warning'
                        ),
                'type' =>
                    $automaticRenewal
                        ? 'renewal_upcoming'
                        : 'expiry_upcoming',
                'contract_id' =>
                    (int) $contract['id'],
                'title' =>
                    (string) $contract['title'],
                'holder' =>
                    (string) $contract[
                        'contract_holder_name'
                    ],
                'date' =>
                    $effectiveEnd->format(
                        'Y-m-d'
                    ),
                'message' =>
                    $message,
            ];
        }
    }

    $severityOrder = [
        'danger' => 1,
        'warning' => 2,
        'info' => 3,
    ];

    usort(
        $notifications,
        static function (
            array $a,
            array $b
        ) use (
            $severityOrder
        ): int {
            $severityCompare =
                (
                    $severityOrder[
                        $a['severity']
                    ] ?? 99
                )
                <=>
                (
                    $severityOrder[
                        $b['severity']
                    ] ?? 99
                );

            if (
                $severityCompare !== 0
            ) {
                return $severityCompare;
            }

            return strcmp(
                $a['date'],
                $b['date']
            );
        }
    );

    return array_slice(
        $notifications,
        0,
        20
    );
}

function contract_payment_date_is_allowed(
    DateTimeImmutable $paymentDate,
    array $contract
): bool {
    if (
        contract_payment_date_is_after_end(
            $paymentDate,
            $contract
        )
    ) {
        return false;
    }

    if (
        contract_is_paused_on(
            $contract,
            $paymentDate
        )
    ) {
        return false;
    }

    return true;
}

function contract_next_payment_date(
    array $contract,
    ?DateTimeImmutable $fromDate = null
): ?string {
    $status =
        (string) (
            $contract['status']
            ?? 'active'
        );

    if ($status === 'expired') {
        return null;
    }

    $firstPaymentDate =
        trim(
            (string) (
                $contract[
                    'first_payment_date'
                ]
                ?? $contract[
                    'next_payment_date'
                ]
                ?? ''
            )
        );

    if ($firstPaymentDate === '') {
        return null;
    }

    $first =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $firstPaymentDate
        );

    if (!$first) {
        return null;
    }

    $from =
        $fromDate
        ?? new DateTimeImmutable('today');

    $from =
        $from->setTime(0, 0, 0);

    if (
        $contract[
            'billing_frequency'
        ] === 'one_time'
    ) {
        return (
            $first >= $from
            && contract_is_running_on(
                $contract,
                $first
            )
            && contract_payment_date_is_allowed(
                $first,
                $contract
            )
        )
            ? $first->format('Y-m-d')
            : null;
    }

    $intervalMonths =
        contract_billing_interval_months(
            $contract
        );

    if (
        $intervalMonths === null
        || $intervalMonths < 1
    ) {
        return null;
    }

    if (
        $first >= $from
        && contract_is_running_on(
            $contract,
            $first
        )
        && contract_payment_date_is_allowed(
            $first,
            $contract
        )
    ) {
        return $first->format('Y-m-d');
    }

    $monthsDifference =
        (
            (int) $from->format('Y')
            - (int) $first->format('Y')
        ) * 12
        + (
            (int) $from->format('n')
            - (int) $first->format('n')
        );

    $occurrenceIndex =
        max(
            0,
            intdiv(
                max(
                    0,
                    $monthsDifference
                ),
                $intervalMonths
            )
        );

    for (
        $i = max(
            0,
            $occurrenceIndex - 1
        );
        $i <= $occurrenceIndex + 120;
        $i++
    ) {
        $candidate =
            contract_date_add_months_clamped(
                $firstPaymentDate,
                $i * $intervalMonths
            );

        if ($candidate === null) {
            break;
        }

        if ($candidate < $from) {
            continue;
        }

        if (
            contract_payment_date_is_after_end(
                $candidate,
                $contract
            )
        ) {
            break;
        }

        if (
            !contract_is_running_on(
                $contract,
                $candidate
            )
            || contract_is_paused_on(
                $contract,
                $candidate
            )
        ) {
            continue;
        }

        return $candidate->format(
            'Y-m-d'
        );
    }

    return null;
}

function contract_payment_occurrences(
    array $contract,
    DateTimeImmutable $rangeStart,
    DateTimeImmutable $rangeEnd
): array {
    if (
        !contract_is_running_on(
            $contract,
            $rangeStart
        )
        && !(
            ($contract['status'] ?? '')
            === 'cancelled'
            && contract_cancellation_effective_date(
                $contract
            ) !== null
            && contract_cancellation_effective_date(
                $contract
            ) >= $rangeStart
        )
    ) {
        return [];
    }

    $firstPaymentDate =
        trim(
            (string) (
                $contract[
                    'first_payment_date'
                ]
                ?? $contract[
                    'next_payment_date'
                ]
                ?? ''
            )
        );

    if ($firstPaymentDate === '') {
        return [];
    }

    $first =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $firstPaymentDate
        );

    if (!$first) {
        return [];
    }

    $rangeStart =
        $rangeStart->setTime(0, 0, 0);

    $rangeEnd =
        $rangeEnd->setTime(23, 59, 59);

    if (
        ($contract['billing_frequency'] ?? '')
        === 'one_time'
    ) {
        if (
            $first >= $rangeStart
            && $first <= $rangeEnd
            && contract_payment_date_is_allowed(
                $first,
                $contract
            )
        ) {
            return [
                $first->format('Y-m-d'),
            ];
        }

        return [];
    }

    $intervalMonths =
        contract_billing_interval_months(
            $contract
        );

    if (
        $intervalMonths === null
        || $intervalMonths < 1
    ) {
        return [];
    }

    $monthsDifference =
        (
            (int) $rangeStart->format('Y')
            - (int) $first->format('Y')
        ) * 12
        + (
            (int) $rangeStart->format('n')
            - (int) $first->format('n')
        );

    $startIndex =
        max(
            0,
            intdiv(
                max(
                    0,
                    $monthsDifference
                ),
                $intervalMonths
            ) - 1
        );

    $occurrences = [];

    for (
        $i = $startIndex;
        $i < $startIndex + 500;
        $i++
    ) {
        $candidate =
            contract_date_add_months_clamped(
                $firstPaymentDate,
                $i * $intervalMonths
            );

        if ($candidate === null) {
            break;
        }

        if ($candidate > $rangeEnd) {
            break;
        }

        if (
            contract_payment_date_is_after_end(
                $candidate,
                $contract
            )
        ) {
            break;
        }

        if (
            $candidate >= $rangeStart
            && contract_is_running_on(
                $contract,
                $candidate
            )
            && !contract_is_paused_on(
                $contract,
                $candidate
            )
        ) {
            $occurrences[] =
                $candidate->format('Y-m-d');
        }
    }

    return $occurrences;
}

function contract_accumulated_cost_summary(
    array $contract,
    ?DateTimeImmutable $throughDate = null
): array {
    $throughDate =
        ($throughDate
        ?? new DateTimeImmutable('today'))
            ->setTime(23, 59, 59);

    $firstPaymentDate =
        trim(
            (string) (
                $contract[
                    'first_payment_date'
                ]
                ?? $contract[
                    'next_payment_date'
                ]
                ?? ''
            )
        );

    $emptyResult = [
        'payment_count' => 0,
        'total_cost' => 0.0,
        'first_payment_date' =>
            $firstPaymentDate !== ''
                ? $firstPaymentDate
                : null,
        'last_payment_date' => null,
        'calculated_through' =>
            $throughDate->format('Y-m-d'),
    ];

    if ($firstPaymentDate === '') {
        return $emptyResult;
    }

    $first =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $firstPaymentDate
        );

    if (!$first) {
        return $emptyResult;
    }

    if ($first > $throughDate) {
        return $emptyResult;
    }

    $cutoff =
        $throughDate;

    $cancellationDate =
        contract_cancellation_effective_date(
            $contract
        );

    if (
        ($contract['status'] ?? '')
        === 'cancelled'
        && $cancellationDate !== null
        && $cancellationDate < $cutoff
    ) {
        $cutoff =
            $cancellationDate->setTime(
                23,
                59,
                59
            );
    }

    $endDateValue =
        trim(
            (string) (
                $contract['end_date']
                ?? ''
            )
        );

    $status =
        (string) (
            $contract['status']
            ?? 'active'
        );

    $automaticRenewal =
        (int) (
            $contract[
                'automatic_renewal'
            ] ?? 0
        ) === 1;

    $renewalMonths =
        (int) (
            $contract[
                'renewal_period_months'
            ] ?? 0
        );

    if (
        $endDateValue !== ''
        && !(
            $status === 'cancelled'
            && $cancellationDate !== null
        )
    ) {
        $endDate =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $endDateValue
            );

        if ($endDate) {
            $historicalStatus =
                $status === 'expired';

            $renewalIsUsable =
                $automaticRenewal
                && $renewalMonths > 0
                && !$historicalStatus;

            if (
                !$renewalIsUsable
                && $endDate < $cutoff
            ) {
                $cutoff =
                    $endDate->setTime(
                        23,
                        59,
                        59
                    );
            }
        }
    }

    if ($first > $cutoff) {
        return $emptyResult;
    }

    $amount =
        max(
            0.0,
            (float) (
                $contract['amount']
                ?? 0
            )
        );

    if (
        ($contract[
            'billing_frequency'
        ] ?? '') === 'one_time'
    ) {
        if (
            contract_is_paused_on(
                $contract,
                $first
            )
        ) {
            return $emptyResult;
        }

        $historicalPrice =
            isset($contract['id'])
                ? contract_price_at_date(
                    (int) $contract['id'],
                    $first,
                    $contract
                )
                : $contract;

        return [
            'payment_count' => 1,
            'total_cost' =>
                max(
                    0.0,
                    (float) (
                        $historicalPrice[
                            'amount'
                        ] ?? $amount
                    )
                ),
            'first_payment_date' =>
                $first->format('Y-m-d'),
            'last_payment_date' =>
                $first->format('Y-m-d'),
            'calculated_through' =>
                $throughDate->format(
                    'Y-m-d'
                ),
        ];
    }

    $intervalMonths =
        contract_billing_interval_months(
            $contract
        );

    if (
        $intervalMonths === null
        || $intervalMonths < 1
    ) {
        return $emptyResult;
    }

    $paymentCount = 0;
    $totalCost = 0.0;
    $lastPaymentDate = null;

    for (
        $index = 0;
        $index < 2400;
        $index++
    ) {
        $candidate =
            contract_date_add_months_clamped(
                $firstPaymentDate,
                $index * $intervalMonths
            );

        if ($candidate === null) {
            break;
        }

        if ($candidate > $cutoff) {
            break;
        }

        if (
            contract_is_paused_on(
                $contract,
                $candidate
            )
        ) {
            continue;
        }

        $paymentCount++;

        $candidatePrice =
            isset($contract['id'])
                ? contract_price_at_date(
                    (int) $contract['id'],
                    $candidate,
                    $contract
                )
                : $contract;

        $totalCost +=
            max(
                0.0,
                (float) (
                    $candidatePrice[
                        'amount'
                    ] ?? $amount
                )
            );

        $lastPaymentDate =
            $candidate->format('Y-m-d');
    }

    return [
        'payment_count' =>
            $paymentCount,
        'total_cost' =>
            $totalCost,
        'first_payment_date' =>
            $first->format('Y-m-d'),
        'last_payment_date' =>
            $lastPaymentDate,
        'calculated_through' =>
            $throughDate->format('Y-m-d'),
    ];
}


function contract_payment_planner_events(
    array $contracts,
    ?DateTimeImmutable $rangeStart = null,
    ?DateTimeImmutable $rangeEnd = null
): array {
    $rangeStart =
        $rangeStart
        ?? new DateTimeImmutable('today');

    $rangeEnd =
        $rangeEnd
        ?? $rangeStart->modify('+1 year');

    $events = [];

    foreach ($contracts as $contract) {
        foreach (
            contract_payment_occurrences(
                $contract,
                $rangeStart,
                $rangeEnd
            )
            as $paymentDate
        ) {
            $events[] = [
                'date' =>
                    $paymentDate,
                'contract_id' =>
                    (int) $contract['id'],
                'title' =>
                    (string) $contract['title'],
                'provider' =>
                    (string) $contract['provider'],
                'holder_id' =>
                    (int) (
                        $contract[
                            'contract_holder_id'
                        ] ?? 0
                    ),
                'holder' =>
                    (string) (
                        $contract[
                            'contract_holder_name'
                        ] ?? '–'
                    ),
                'amount' =>
                    (float) $contract['amount'],
                'frequency' =>
                    (string) $contract[
                        'billing_frequency'
                    ],
            ];
        }
    }

    usort(
        $events,
        static function (
            array $a,
            array $b
        ): int {
            $dateCompare =
                strcmp(
                    $a['date'],
                    $b['date']
                );

            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            return strcmp(
                $a['title'],
                $b['title']
            );
        }
    );

    return $events;
}

function get_contract_price_history(
    int $contractId
): array {
    $stmt = db()->prepare(
        '
        SELECT
            ph.*,
            u.display_name AS created_by_name
        FROM contract_price_history ph
        LEFT JOIN users u
            ON u.id = ph.created_by
        WHERE ph.contract_id = :contract_id
        ORDER BY
            ph.valid_from DESC,
            ph.id DESC
        '
    );

    $stmt->execute([
        'contract_id' =>
            $contractId,
    ]);

    return $stmt->fetchAll();
}

function contract_price_at_date(
    int $contractId,
    DateTimeImmutable $date,
    ?array $fallbackContract = null
): array {
    $stmt = db()->prepare(
        '
        SELECT
            amount,
            billing_frequency,
            custom_billing_months,
            valid_from,
            valid_to
        FROM contract_price_history
        WHERE contract_id = :contract_id
          AND valid_from <= :price_date_from
          AND (
              valid_to IS NULL
              OR valid_to >= :price_date_to
          )
        ORDER BY
            valid_from DESC,
            id DESC
        LIMIT 1
        '
    );

    $dateValue =
        $date->format('Y-m-d');

    $stmt->execute([
        'contract_id' =>
            $contractId,
        'price_date_from' =>
            $dateValue,
        'price_date_to' =>
            $dateValue,
    ]);

    $price =
        $stmt->fetch();

    if ($price) {
        return $price;
    }

    return [
        'amount' =>
            (float) (
                $fallbackContract[
                    'amount'
                ] ?? 0
            ),
        'billing_frequency' =>
            (string) (
                $fallbackContract[
                    'billing_frequency'
                ] ?? 'monthly'
            ),
        'custom_billing_months' =>
            $fallbackContract[
                'custom_billing_months'
            ] ?? null,
        'valid_from' =>
            null,
        'valid_to' =>
            null,
    ];
}

function record_contract_price_change(
    int $contractId,
    float $amount,
    string $billingFrequency,
    ?int $customBillingMonths,
    string $validFrom,
    ?string $changeReason,
    int $userId
): int {
    $validFromDate =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $validFrom
        );

    if (!$validFromDate) {
        throw new RuntimeException(
            'Das Gültig-ab-Datum der Preisänderung ist ungültig.'
        );
    }

    $today =
        new DateTimeImmutable('today');

    if ($validFromDate > $today) {
        throw new RuntimeException(
            'Eine Preisänderung darf aktuell nicht in der Zukunft beginnen.'
        );
    }

    if ($amount < 0) {
        throw new RuntimeException(
            'Der Preis darf nicht negativ sein.'
        );
    }

    $allowedFrequencies = [
        'monthly',
        'quarterly',
        'semiannual',
        'annual',
        'one_time',
        'custom',
    ];

    if (
        !in_array(
            $billingFrequency,
            $allowedFrequencies,
            true
        )
    ) {
        throw new RuntimeException(
            'Das Abrechnungsintervall ist ungültig.'
        );
    }

    if (
        $billingFrequency === 'custom'
        && (
            $customBillingMonths === null
            || $customBillingMonths < 1
        )
    ) {
        throw new RuntimeException(
            'Bei individueller Abrechnung muss ein Intervall in Monaten angegeben werden.'
        );
    }

    $pdo = db();

    $pdo->beginTransaction();

    try {
        $sameDate = $pdo->prepare(
            '
            SELECT id
            FROM contract_price_history
            WHERE contract_id = :contract_id
              AND valid_from = :valid_from
            ORDER BY id DESC
            LIMIT 1
            FOR UPDATE
            '
        );

        $sameDate->execute([
            'contract_id' =>
                $contractId,
            'valid_from' =>
                $validFrom,
        ]);

        $existingSameDate =
            $sameDate->fetchColumn();

        if ($existingSameDate) {
            $update = $pdo->prepare(
                '
                UPDATE contract_price_history
                SET
                    amount = :amount,
                    billing_frequency = :billing_frequency,
                    custom_billing_months = :custom_billing_months,
                    change_reason = :change_reason,
                    created_by = :created_by
                WHERE id = :id
                '
            );

            $update->execute([
                'amount' =>
                    $amount,
                'billing_frequency' =>
                    $billingFrequency,
                'custom_billing_months' =>
                    $billingFrequency === 'custom'
                        ? $customBillingMonths
                        : null,
                'change_reason' =>
                    $changeReason !== null
                    && trim($changeReason) !== ''
                        ? trim($changeReason)
                        : null,
                'created_by' =>
                    $userId,
                'id' =>
                    (int) $existingSameDate,
            ]);

            $priceHistoryId =
                (int) $existingSameDate;
        } else {
            $dayBefore =
                $validFromDate
                    ->modify('-1 day')
                    ->format('Y-m-d');

            $nextPrice = $pdo->prepare(
                '
                SELECT valid_from
                FROM contract_price_history
                WHERE contract_id = :contract_id
                  AND valid_from > :valid_from
                ORDER BY valid_from ASC, id ASC
                LIMIT 1
                FOR UPDATE
                '
            );

            $nextPrice->execute([
                'contract_id' =>
                    $contractId,
                'valid_from' =>
                    $validFrom,
            ]);

            $nextValidFrom =
                $nextPrice->fetchColumn();

            $newValidTo =
                $nextValidFrom
                    ? (
                        DateTimeImmutable::createFromFormat(
                            '!Y-m-d',
                            (string) $nextValidFrom
                        )
                    )->modify(
                        '-1 day'
                    )->format(
                        'Y-m-d'
                    )
                    : null;

            $close = $pdo->prepare(
                '
                UPDATE contract_price_history
                SET valid_to = :valid_to
                WHERE contract_id = :contract_id
                  AND valid_from < :valid_from
                  AND (
                      valid_to IS NULL
                      OR valid_to >= :valid_from
                  )
                '
            );

            $close->execute([
                'valid_to' =>
                    $dayBefore,
                'contract_id' =>
                    $contractId,
                'valid_from' =>
                    $validFrom,
            ]);

            $insert = $pdo->prepare(
                '
                INSERT INTO contract_price_history (
                    contract_id,
                    amount,
                    billing_frequency,
                    custom_billing_months,
                    valid_from,
                    valid_to,
                    change_reason,
                    created_by
                )
                VALUES (
                    :contract_id,
                    :amount,
                    :billing_frequency,
                    :custom_billing_months,
                    :valid_from,
                    :valid_to,
                    :change_reason,
                    :created_by
                )
                '
            );

            $insert->execute([
                'contract_id' =>
                    $contractId,
                'amount' =>
                    $amount,
                'billing_frequency' =>
                    $billingFrequency,
                'custom_billing_months' =>
                    $billingFrequency === 'custom'
                        ? $customBillingMonths
                        : null,
                'valid_from' =>
                    $validFrom,
                'valid_to' =>
                    $newValidTo,
                'change_reason' =>
                    $changeReason !== null
                    && trim($changeReason) !== ''
                        ? trim($changeReason)
                        : null,
                'created_by' =>
                    $userId,
            ]);

            $priceHistoryId =
                (int) $pdo->lastInsertId();
        }

        $currentPrice = $pdo->prepare(
            '
            SELECT
                amount,
                billing_frequency,
                custom_billing_months
            FROM contract_price_history
            WHERE contract_id = :contract_id
              AND valid_from <= CURRENT_DATE
              AND (
                  valid_to IS NULL
                  OR valid_to >= CURRENT_DATE
              )
            ORDER BY valid_from DESC, id DESC
            LIMIT 1
            '
        );

        $currentPrice->execute([
            'contract_id' =>
                $contractId,
        ]);

        $effectiveCurrentPrice =
            $currentPrice->fetch();

        if ($effectiveCurrentPrice) {
            $updateContract = $pdo->prepare(
                '
                UPDATE contracts
                SET
                    amount = :amount,
                    billing_frequency = :billing_frequency,
                    custom_billing_months = :custom_billing_months,
                    updated_by = :updated_by
                WHERE id = :id
                  AND deleted_at IS NULL
                '
            );

            $updateContract->execute([
                'amount' =>
                    (float) $effectiveCurrentPrice[
                        'amount'
                    ],
                'billing_frequency' =>
                    $effectiveCurrentPrice[
                        'billing_frequency'
                    ],
                'custom_billing_months' =>
                    $effectiveCurrentPrice[
                        'custom_billing_months'
                    ],
                'updated_by' =>
                    $userId,
                'id' =>
                    $contractId,
            ]);
        }

        $pdo->commit();

        return $priceHistoryId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

function contract_notice_deadline_date(
    DateTimeImmutable $endDate,
    ?int $value,
    ?string $unit
): DateTimeImmutable {
    if (
        $value === null
        || $value < 1
        || $unit === null
        || $unit === ''
    ) {
        return $endDate;
    }

    return match ($unit) {
        'days' =>
            $endDate->modify(
                '-' . $value . ' days'
            ),
        'weeks' =>
            $endDate->modify(
                '-' . $value . ' weeks'
            ),
        'months' =>
            $endDate->modify(
                '-' . $value . ' months'
            ),
        default =>
            $endDate,
    };
}

function contract_saving_potential_after_date(
    array $contract,
    DateTimeImmutable $endDate,
    ?DateTimeImmutable $rangeEnd = null
): float {
    if (
        ($contract[
            'billing_frequency'
        ] ?? '') === 'one_time'
    ) {
        return 0.0;
    }

    $start =
        $endDate
            ->modify('+1 day')
            ->setTime(0, 0, 0);

    $rangeEnd =
        ($rangeEnd
        ?? (
            new DateTimeImmutable(
                'today'
            )
        )->modify('+1 year'))
            ->setTime(23, 59, 59);

    if ($start > $rangeEnd) {
        return 0.0;
    }

    $continuedContract =
        $contract;

    $continuedContract['status'] =
        'active';

    $continuedContract[
        'end_date'
    ] = null;

    $continuedContract[
        'cancellation_effective_date'
    ] = null;

    $continuedContract[
        'automatic_renewal'
    ] = 0;

    $continuedContract[
        'renewal_period_months'
    ] = null;

    $occurrences =
        contract_payment_occurrences(
            $continuedContract,
            $start,
            $rangeEnd
        );

    $total = 0.0;

    foreach ($occurrences as $dateValue) {
        $date =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $dateValue
            );

        if (!$date) {
            continue;
        }

        $price =
            isset($contract['id'])
                ? contract_price_at_date(
                    (int) $contract['id'],
                    $date,
                    $contract
                )
                : $contract;

        $total +=
            max(
                0.0,
                (float) (
                    $price['amount']
                    ?? $contract['amount']
                    ?? 0
                )
            );
    }

    return $total;
}


function contract_deadline_info(
    array $contract,
    ?DateTimeImmutable $referenceDate = null
): array {
    $today =
        ($referenceDate
        ?? new DateTimeImmutable('today'))
            ->setTime(0, 0, 0);

    $cancellationDate =
        contract_cancellation_effective_date(
            $contract
        );

    if (
        ($contract['status'] ?? '')
        === 'cancelled'
        && $cancellationDate !== null
    ) {
        $signedDays =
            (int) $today
                ->diff(
                    $cancellationDate
                )
                ->format('%r%a');

        return [
            'has_deadline' => true,
            'deadline_date' =>
                $cancellationDate->format(
                    'Y-m-d'
                ),
            'end_date' =>
                $cancellationDate->format(
                    'Y-m-d'
                ),
            'days_until_deadline' =>
                $signedDays,
            'urgency' =>
                $signedDays < 0
                    ? 'overdue'
                    : (
                        $signedDays <= 7
                            ? 'critical'
                            : (
                                $signedDays <= 30
                                    ? 'warning'
                                    : 'upcoming'
                            )
                    ),
            'missed_current_deadline' =>
                false,
            'automatic_renewal' =>
                false,
            'is_cancelled' =>
                true,
            'saving_12_months' =>
                contract_saving_potential_after_date(
                    $contract,
                    $cancellationDate,
                    $today->modify(
                        '+1 year'
                    )
                ),
        ];
    }

    $endDateValue =
        trim(
            (string) (
                $contract['end_date']
                ?? ''
            )
        );

    if ($endDateValue === '') {
        return [
            'has_deadline' => false,
            'deadline_date' => null,
            'end_date' => null,
            'days_until_deadline' => null,
            'urgency' => 'none',
            'missed_current_deadline' => false,
            'is_cancelled' => false,
            'automatic_renewal' =>
                (int) (
                    $contract[
                        'automatic_renewal'
                    ] ?? 0
                ) === 1,
            'saving_12_months' => 0.0,
        ];
    }

    $renewalState =
        contract_renewal_state(
            $contract,
            $today
        );

    $effectiveEnd =
        $renewalState[
            'effective_end'
        ];

    if ($effectiveEnd === null) {
        return [
            'has_deadline' => false,
            'deadline_date' => null,
            'end_date' => null,
            'days_until_deadline' => null,
            'urgency' => 'none',
            'missed_current_deadline' => false,
            'is_cancelled' => false,
            'automatic_renewal' => false,
            'saving_12_months' => 0.0,
        ];
    }

    $noticeValue =
        isset(
            $contract[
                'notice_period_value'
            ]
        )
        && $contract[
            'notice_period_value'
        ] !== null
            ? (int) $contract[
                'notice_period_value'
            ]
            : null;

    $noticeUnit =
        $contract[
            'notice_period_unit'
        ] ?? null;

    $automaticRenewal =
        (int) (
            $contract[
                'automatic_renewal'
            ] ?? 0
        ) === 1;

    $renewalMonths =
        (int) (
            $contract[
                'renewal_period_months'
            ] ?? 0
        );

    $deadline =
        contract_notice_deadline_date(
            $effectiveEnd,
            $noticeValue,
            $noticeUnit
        );

    $missedCurrentDeadline =
        false;

    $nextPossibleEnd =
        $effectiveEnd;

    if (
        $automaticRenewal
        && $renewalMonths > 0
        && $deadline < $today
    ) {
        $missedCurrentDeadline =
            true;

        $nextPossibleEnd =
            contract_date_add_months_clamped(
                $effectiveEnd->format(
                    'Y-m-d'
                ),
                $renewalMonths
            ) ?? $effectiveEnd;

        $deadline =
            contract_notice_deadline_date(
                $nextPossibleEnd,
                $noticeValue,
                $noticeUnit
            );
    }

    $signedDays =
        (int) $today
            ->diff(
                $deadline
            )
            ->format('%r%a');

    $urgency =
        $signedDays < 0
            ? 'overdue'
            : (
                $signedDays <= 7
                    ? 'critical'
                    : (
                        $signedDays <= 30
                            ? 'warning'
                            : (
                                $signedDays <= 90
                                    ? 'upcoming'
                                    : 'later'
                            )
                    )
            );

    $saving12Months = 0.0;

    if (
        ($contract['status'] ?? '') === 'active'
        && ($contract[
            'billing_frequency'
        ] ?? '') !== 'one_time'
    ) {
        $rangeEnd =
            $today->modify('+1 year');

        $saving12Months =
            contract_saving_potential_after_date(
                $contract,
                $nextPossibleEnd,
                $rangeEnd
            );
    }

    return [
        'has_deadline' => true,
        'deadline_date' =>
            $deadline->format('Y-m-d'),
        'end_date' =>
            $nextPossibleEnd->format('Y-m-d'),
        'days_until_deadline' =>
            $signedDays,
        'urgency' =>
            $urgency,
        'missed_current_deadline' =>
            $missedCurrentDeadline,
        'is_cancelled' => false,
        'automatic_renewal' =>
            $automaticRenewal,
        'saving_12_months' =>
            $saving12Months,
    ];
}

function contract_deadline_cockpit(
    ?int $holderId = null
): array {
    $sql = '
        SELECT
            c.*,
            ct.name AS contract_type,
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
          AND (
              c.end_date IS NOT NULL
              OR c.cancellation_effective_date IS NOT NULL
          )
    ';

    $params = [];

    if ($holderId !== null) {
        $sql .= '
            AND c.contract_holder_id = :holder_id
        ';

        $params['holder_id'] =
            $holderId;
    }

    $sql .= '
        ORDER BY
            COALESCE(
                c.cancellation_effective_date,
                c.end_date
            ),
            c.title
    ';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    $items = [];

    foreach (
        $stmt->fetchAll()
        as $contract
    ) {
        $deadline =
            contract_deadline_info(
                $contract
            );

        if (!$deadline['has_deadline']) {
            continue;
        }

        $items[] =
            array_merge(
                $contract,
                [
                    'deadline' =>
                        $deadline,
                ]
            );
    }

    usort(
        $items,
        static function (
            array $a,
            array $b
        ): int {
            return strcmp(
                $a['deadline'][
                    'deadline_date'
                ],
                $b['deadline'][
                    'deadline_date'
                ]
            );
        }
    );

    return $items;
}

function contract_cost_development(
    ?int $holderId = null
): array {
    $sql = '
        SELECT
            c.*,
            COALESCE(
                NULLIF(h.name, ""),
                NULLIF(c.contract_holder, ""),
                "–"
            ) AS contract_holder_name,
            ct.name AS contract_type
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

    if ($holderId !== null) {
        $sql .= '
            AND c.contract_holder_id = :holder_id
        ';

        $params['holder_id'] =
            $holderId;
    }

    $sql .= '
        ORDER BY c.title
    ';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    $contracts =
        $stmt->fetchAll();

    $today =
        new DateTimeImmutable('today');

    $comparisonDate =
        $today->modify('-1 year');

    $currentMonthly = 0.0;
    $previousMonthly = 0.0;
    $savingPotential = 0.0;
    $rows = [];

    foreach ($contracts as $contract) {
        $currentMonthlyValue =
            contract_monthly_equivalent(
                $contract
            );

        $contractStart =
            !empty(
                $contract['start_date']
            )
                ? DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    (string) $contract[
                        'start_date'
                    ]
                )
                : (
                    !empty(
                        $contract[
                            'created_at'
                        ]
                    )
                        ? DateTimeImmutable::createFromFormat(
                            '!Y-m-d',
                            substr(
                                (string) $contract[
                                    'created_at'
                                ],
                                0,
                                10
                            )
                        )
                        : null
                );

        if (
            $contractStart
            && $contractStart > $comparisonDate
        ) {
            $previousMonthlyValue = 0.0;
        } else {
            $previousPrice =
                contract_price_at_date(
                    (int) $contract['id'],
                    $comparisonDate,
                    $contract
                );

            $previousMonthlyValue =
                contract_monthly_equivalent(
                    $previousPrice
                );
        }

        $deadline =
            contract_deadline_info(
                $contract,
                $today
            );

        $pauseState =
            contract_pause_state(
                $contract,
                $today
            );

        $currentMonthly +=
            $currentMonthlyValue;

        $previousMonthly +=
            $previousMonthlyValue;

        $savingPotential +=
            (float) (
                $deadline[
                    'saving_12_months'
                ] ?? 0
            );

        $rows[] = [
            'contract' =>
                $contract,
            'current_monthly' =>
                $currentMonthlyValue,
            'previous_monthly' =>
                $previousMonthlyValue,
            'monthly_change' =>
                $currentMonthlyValue
                - $previousMonthlyValue,
            'annual_change' =>
                (
                    $currentMonthlyValue
                    - $previousMonthlyValue
                ) * 12,
            'saving_12_months' =>
                (float) (
                    $deadline[
                        'saving_12_months'
                    ] ?? 0
                ),
            'deadline' =>
                $deadline,
            'pause_state' =>
                $pauseState,
        ];
    }

    usort(
        $rows,
        static function (
            array $a,
            array $b
        ): int {
            return $b[
                'annual_change'
            ] <=> $a[
                'annual_change'
            ];
        }
    );

    return [
        'current_monthly' =>
            $currentMonthly,
        'current_annual' =>
            $currentMonthly * 12,
        'previous_monthly' =>
            $previousMonthly,
        'previous_annual' =>
            $previousMonthly * 12,
        'monthly_change' =>
            $currentMonthly
            - $previousMonthly,
        'annual_change' =>
            (
                $currentMonthly
                - $previousMonthly
            ) * 12,
        'saving_potential_12_months' =>
            $savingPotential,
        'comparison_date' =>
            $comparisonDate->format(
                'Y-m-d'
            ),
        'rows' =>
            $rows,
    ];
}


function get_contract_holders(
    bool $activeOnly = true
): array {
    $sql = '
        SELECT
            id,
            name,
            sort_order,
            is_active
        FROM contract_holders
    ';

    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1 ';
    }

    $sql .= '
        ORDER BY
            sort_order,
            name
    ';

    return db()
        ->query($sql)
        ->fetchAll();
}

function get_contract_types(
    bool $activeOnly = true
): array {
    $sql = '
        SELECT
            id,
            name,
            description,
            icon,
            sort_order,
            is_active
        FROM contract_types
    ';

    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1 ';
    }

    $sql .= '
        ORDER BY
            sort_order,
            name
    ';

    return db()
        ->query($sql)
        ->fetchAll();
}

function find_contract(
    int $contractId
): ?array {
    $stmt = db()->prepare(
        '
        SELECT
            c.*,
            ct.name AS contract_type,
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
        WHERE c.id = :id
          AND c.deleted_at IS NULL
        LIMIT 1
        '
    );

    $stmt->execute([
        'id' => $contractId,
    ]);

    $contract = $stmt->fetch();

    return $contract ?: null;
}

function get_contract_documents(
    int $contractId
): array {
    $stmt = db()->prepare(
        '
        SELECT
            d.id,
            d.document_type_id,
            d.document_name,
            d.document_date,
            d.version_no,
            d.replaces_document_id,
            d.is_current,
            d.original_filename,
            d.stored_filename,
            d.storage_path,
            d.mime_type,
            d.file_extension,
            d.file_size,
            d.checksum_sha256,
            d.created_at,
            dt.name AS document_type_name,
            u.display_name AS uploaded_by_name
        FROM contract_documents d
        LEFT JOIN document_types dt
            ON dt.id = d.document_type_id
        LEFT JOIN users u
            ON u.id = d.uploaded_by
        WHERE d.contract_id = :contract_id
          AND d.deleted_at IS NULL
          AND d.is_current = 1
        ORDER BY
            COALESCE(d.document_date, DATE(d.created_at)) DESC,
            d.created_at DESC,
            d.id DESC
        '
    );

    $stmt->execute([
        'contract_id' => $contractId,
    ]);

    return $stmt->fetchAll();
}

function get_contract_document_history(
    int $contractId
): array {
    $stmt = db()->prepare(
        '
        SELECT
            d.id,
            d.document_type_id,
            d.document_name,
            d.document_date,
            d.version_no,
            d.replaces_document_id,
            d.is_current,
            d.original_filename,
            d.file_extension,
            d.file_size,
            d.created_at,
            d.deleted_at,
            dt.name AS document_type_name,
            u.display_name AS uploaded_by_name
        FROM contract_documents d
        LEFT JOIN document_types dt
            ON dt.id = d.document_type_id
        LEFT JOIN users u
            ON u.id = d.uploaded_by
        WHERE d.contract_id = :contract_id
        ORDER BY
            COALESCE(d.document_date, DATE(d.created_at)) DESC,
            d.created_at DESC,
            d.id DESC
        '
    );

    $stmt->execute([
        'contract_id' =>
            $contractId,
    ]);

    return $stmt->fetchAll();
}

function get_document_types(
    bool $activeOnly = true
): array {
    $sql = '
        SELECT
            id,
            name,
            description,
            sort_order,
            is_active
        FROM document_types
    ';

    if ($activeOnly) {
        $sql .= '
            WHERE is_active = 1
        ';
    }

    $sql .= '
        ORDER BY
            sort_order,
            name
    ';

    return db()
        ->query($sql)
        ->fetchAll();
}


function format_file_size(
    int $bytes
): string {
    if ($bytes >= 1024 * 1024 * 1024) {
        return number_format(
            $bytes / (1024 * 1024 * 1024),
            2,
            ',',
            '.'
        ) . ' GB';
    }

    if ($bytes >= 1024 * 1024) {
        return number_format(
            $bytes / (1024 * 1024),
            2,
            ',',
            '.'
        ) . ' MB';
    }

    if ($bytes >= 1024) {
        return number_format(
            $bytes / 1024,
            1,
            ',',
            '.'
        ) . ' KB';
    }

    return $bytes . ' Byte';
}

function contract_form_values_from_request(): array
{
    return [
        'contract_type_id' =>
            trim((string) ($_POST['contract_type_id'] ?? '')),

        'contract_holder_id' =>
            trim((string) ($_POST['contract_holder_id'] ?? '')),

        'title' =>
            trim((string) ($_POST['title'] ?? '')),

        'provider' =>
            trim((string) ($_POST['provider'] ?? '')),

        'contract_number' =>
            trim((string) ($_POST['contract_number'] ?? '')),

        'customer_number' =>
            trim((string) ($_POST['customer_number'] ?? '')),

        'status' =>
            trim((string) ($_POST['status'] ?? 'active')),

        'start_date' =>
            trim((string) ($_POST['start_date'] ?? '')),

        'end_date' =>
            trim((string) ($_POST['end_date'] ?? '')),

        'minimum_term_months' =>
            trim((string) ($_POST['minimum_term_months'] ?? '')),

        'notice_period_value' =>
            trim((string) ($_POST['notice_period_value'] ?? '')),

        'notice_period_unit' =>
            trim((string) ($_POST['notice_period_unit'] ?? '')),

        'automatic_renewal' =>
            isset($_POST['automatic_renewal'])
                ? '1'
                : '0',

        'notifications_enabled' =>
            isset($_POST['notifications_enabled'])
                ? '1'
                : '0',

        'renewal_period_months' =>
            trim((string) ($_POST['renewal_period_months'] ?? '')),

        'amount' =>
            trim((string) ($_POST['amount'] ?? '')),

        'price_valid_from' =>
            trim((string) ($_POST['price_valid_from'] ?? '')),

        'price_change_reason' =>
            trim((string) ($_POST['price_change_reason'] ?? '')),

        'billing_frequency' =>
            trim((string) ($_POST['billing_frequency'] ?? 'monthly')),

        'custom_billing_months' =>
            trim((string) ($_POST['custom_billing_months'] ?? '')),

        'first_payment_date' =>
            trim((string) ($_POST['first_payment_date'] ?? '')),

        'description' =>
            trim((string) ($_POST['description'] ?? '')),

        'notes' =>
            trim((string) ($_POST['notes'] ?? '')),
    ];
}

function validate_contract_form_values(
    array $values,
    ?array $existingContract = null
): ?string {
    if (
        ($values['contract_type_id'] ?? '') === ''
        || ($values['contract_holder_id'] ?? '') === ''
        || ($values['title'] ?? '') === ''
        || ($values['provider'] ?? '') === ''
        || ($values['amount'] ?? '') === ''
    ) {
        return 'Bitte alle Pflichtfelder ausfüllen.';
    }

    if (
        !ctype_digit((string) $values['contract_type_id'])
        || !ctype_digit((string) $values['contract_holder_id'])
    ) {
        return 'Vertragsart oder Vertragsinhaber ist ungültig.';
    }

    $typeId =
        (int) $values['contract_type_id'];

    $holderId =
        (int) $values['contract_holder_id'];

    $typeCheck = db()->prepare(
        '
        SELECT
            id,
            is_active
        FROM contract_types
        WHERE id = :id
        LIMIT 1
        '
    );

    $typeCheck->execute([
        'id' => $typeId,
    ]);

    $type = $typeCheck->fetch();

    $typeAllowed =
        $type
        && (
            (int) $type['is_active'] === 1
            || (
                $existingContract !== null
                && $typeId === (int) $existingContract['contract_type_id']
            )
        );

    $holderCheck = db()->prepare(
        '
        SELECT
            id,
            is_active
        FROM contract_holders
        WHERE id = :id
        LIMIT 1
        '
    );

    $holderCheck->execute([
        'id' => $holderId,
    ]);

    $holder = $holderCheck->fetch();

    $holderAllowed =
        $holder
        && (
            (int) $holder['is_active'] === 1
            || (
                $existingContract !== null
                && $holderId === (int) $existingContract['contract_holder_id']
            )
        );

    if (
        !$typeAllowed
        || !$holderAllowed
    ) {
        return 'Vertragsart oder Vertragsinhaber ist nicht verfügbar.';
    }

    if (
        !is_numeric((string) $values['amount'])
        || (float) $values['amount'] < 0
    ) {
        return 'Bitte einen gültigen Betrag eingeben.';
    }

    $priceValidFromValue =
        trim(
            (string) (
                $values[
                    'price_valid_from'
                ] ?? ''
            )
        );

    if (
        $priceValidFromValue !== ''
    ) {
        $priceValidFromDate =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $priceValidFromValue
            );

        if ($priceValidFromDate === false) {
            return 'Das Gültig-ab-Datum des Preises ist ungültig.';
        }

        if (
            $priceValidFromDate
            > new DateTimeImmutable('today')
        ) {
            return 'Das Gültig-ab-Datum des Preises darf nicht in der Zukunft liegen.';
        }
    }

    if (
        trim(
            (string) (
                $values[
                    'first_payment_date'
                ] ?? ''
            )
        ) === ''
    ) {
        return 'Bitte den ersten Abbuchungstermin angeben.';
    }

    $firstPayment =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            (string) $values[
                'first_payment_date'
            ]
        );

    if ($firstPayment === false) {
        return 'Der erste Abbuchungstermin ist ungültig.';
    }

    $allowedStatuses = [
        'active',
        'planned',
        'cancelled',
        'expired',
    ];

    if (
        !in_array(
            (string) $values['status'],
            $allowedStatuses,
            true
        )
    ) {
        return 'Ungültiger Vertragsstatus.';
    }

    $allowedFrequencies = [
        'monthly',
        'quarterly',
        'semiannual',
        'annual',
        'one_time',
        'custom',
    ];

    if (
        !in_array(
            (string) $values['billing_frequency'],
            $allowedFrequencies,
            true
        )
    ) {
        return 'Ungültiges Abrechnungsintervall.';
    }

    if (
        $values['billing_frequency'] === 'custom'
        && (
            $values['custom_billing_months'] === ''
            || !ctype_digit((string) $values['custom_billing_months'])
            || (int) $values['custom_billing_months'] < 1
        )
    ) {
        return 'Bei individueller Abrechnung muss ein Intervall in Monaten angegeben werden.';
    }

    $allowedNoticeUnits = [
        '',
        'days',
        'weeks',
        'months',
    ];

    if (
        !in_array(
            (string) $values['notice_period_unit'],
            $allowedNoticeUnits,
            true
        )
    ) {
        return 'Ungültige Einheit für die Kündigungsfrist.';
    }

    return null;
}

function contract_to_form_values(
    array $contract
): array {
    $keys = [
        'contract_type_id',
        'contract_holder_id',
        'title',
        'provider',
        'contract_number',
        'customer_number',
        'status',
        'start_date',
        'end_date',
        'minimum_term_months',
        'notice_period_value',
        'notice_period_unit',
        'notifications_enabled',
        'renewal_period_months',
        'amount',
        'price_valid_from',
        'price_change_reason',
        'billing_frequency',
        'custom_billing_months',
        'first_payment_date',
        'description',
        'notes',
    ];

    $values = [];

    foreach ($keys as $key) {
        $value = $contract[$key] ?? '';

        $values[$key] =
            $value === null
                ? ''
                : (string) $value;
    }

    $values['automatic_renewal'] =
        (int) ($contract['automatic_renewal'] ?? 0) === 1
            ? '1'
            : '0';

    $values['notifications_enabled'] =
        (int) (
            $contract['notifications_enabled']
            ?? 1
        ) === 1
            ? '1'
            : '0';

    $values['price_valid_from'] =
        (new DateTimeImmutable('today'))
            ->format('Y-m-d');

    $values['price_change_reason'] =
        '';

    return $values;
}

function delete_contract_permanently(
    int $contractId
): array {
    $contract = find_contract($contractId);

    if ($contract === null) {
        throw new RuntimeException(
            'Vertrag nicht gefunden.'
        );
    }

    $stmt = db()->prepare(
        '
        SELECT
            id,
            storage_path
        FROM contract_documents
        WHERE contract_id = :contract_id
        '
    );

    $stmt->execute([
        'contract_id' => $contractId,
    ]);

    $documents = $stmt->fetchAll();
    $documentIds = array_map(
        static fn (array $row): int => (int) $row['id'],
        $documents
    );

    $pdo = db();
    $pdo->beginTransaction();

    try {
        if ($documentIds !== []) {
            $placeholders = implode(
                ',',
                array_fill(
                    0,
                    count($documentIds),
                    '?'
                )
            );

            $deleteDocumentAudit = $pdo->prepare(
                '
                DELETE FROM audit_log
                WHERE entity_type = "document"
                  AND entity_id IN (' . $placeholders . ')
                '
            );

            $deleteDocumentAudit->execute(
                $documentIds
            );
        }

        $deleteDocuments = $pdo->prepare(
            '
            DELETE FROM contract_documents
            WHERE contract_id = :contract_id
            '
        );

        $deleteDocuments->execute([
            'contract_id' => $contractId,
        ]);

        $deleteContractAudit = $pdo->prepare(
            '
            DELETE FROM audit_log
            WHERE entity_type = "contract"
              AND entity_id = :contract_id
            '
        );

        $deleteContractAudit->execute([
            'contract_id' => $contractId,
        ]);

        $deleteContract = $pdo->prepare(
            '
            DELETE FROM contracts
            WHERE id = :contract_id
            '
        );

        $deleteContract->execute([
            'contract_id' => $contractId,
        ]);

        if ($deleteContract->rowCount() !== 1) {
            throw new RuntimeException(
                'Der Vertrag konnte nicht gelöscht werden.'
            );
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

    $cleanupComplete = true;

    foreach ($documents as $document) {
        $path = resolve_document_path(
            (string) $document['storage_path']
        );

        if (
            $path !== null
            && is_file($path)
            && !@unlink($path)
        ) {
            $cleanupComplete = false;
        }
    }

    $contractDirectory =
        document_storage_root()
        . '/contracts/'
        . $contractId;

    if (
        is_dir($contractDirectory)
        && !remove_directory_if_empty(
            $contractDirectory
        )
    ) {
        $cleanupComplete = false;
    }

    return [
        'title' => (string) $contract['title'],
        'file_cleanup_complete' => $cleanupComplete,
    ];
}
