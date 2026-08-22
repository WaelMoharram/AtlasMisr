<?php

namespace App\Support;

use DateTimeImmutable;

/**
 * Packs a (possibly BCE, possibly imprecise) year/month/day/hour/minute
 * into a single sortable, decodable integer. A real DATE/DATETIME column
 * can't represent BCE years, which this app needs for ancient events, so
 * chronological ordering and "is this active right now" comparisons are
 * done against this integer instead.
 *
 * Years are shifted by YEAR_OFFSET before packing so the packed value is
 * always non-negative — this keeps decoding (see the JS port in app.js)
 * a simple division/modulo instead of having to special-case negative
 * numbers, which don't divide cleanly once positive month/day/hour/minute
 * components are added on top of a negative year term.
 */
class Moment
{
    public const YEAR_OFFSET = 10_000;

    public static function pack(int $year, ?int $month, ?int $day, ?int $hour, ?int $minute): int
    {
        return ($year + self::YEAR_OFFSET) * 100_000_000
            + ($month ?? 1) * 1_000_000
            + ($day ?? 1) * 10_000
            + ($hour ?? 0) * 100
            + ($minute ?? 0);
    }

    /**
     * The moment right after the finest specified unit of precision ends
     * — e.g. year-only 1973 -> start of 1974, 1973-10-06 -> start of
     * 1973-10-07. Used as the default "disappears at" point for events
     * that don't have an explicit end set.
     */
    public static function packEndOfPrecision(int $year, ?int $month, ?int $day, ?int $hour, ?int $minute): int
    {
        if ($minute !== null) {
            return self::addMinutes($year, $month ?? 1, $day ?? 1, $hour ?? 0, $minute, 1);
        }

        if ($hour !== null) {
            return self::addMinutes($year, $month ?? 1, $day ?? 1, $hour, 0, 60);
        }

        if ($day !== null) {
            return self::addMinutes($year, $month ?? 1, $day, 0, 0, 24 * 60);
        }

        if ($month !== null) {
            $nextMonth = $month === 12 ? 1 : $month + 1;
            $nextYear = $month === 12 ? $year + 1 : $year;

            return self::pack($nextYear, $nextMonth, 1, 0, 0);
        }

        return self::pack($year + 1, 1, 1, 0, 0);
    }

    private static function addMinutes(int $year, int $month, int $day, int $hour, int $minute, int $minutesToAdd): int
    {
        // BCE years fall outside DateTimeImmutable's supported range, but
        // hour/day precision only ever occurs on well-documented (CE)
        // events in practice — BCE dates just roll to the next year.
        if ($year < 1) {
            return self::pack($year + 1, 1, 1, 0, 0);
        }

        $date = DateTimeImmutable::createFromFormat(
            'Y-n-j H:i',
            sprintf('%04d-%d-%d %02d:%02d', $year, $month, $day, $hour, $minute)
        )->modify("+{$minutesToAdd} minutes");

        return self::pack(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j'),
            (int) $date->format('G'),
            (int) $date->format('i'),
        );
    }
}
