<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace block_oksigeniaaravo\local;

/**
 * Builds what the block and the register page show, from a snapshot and the local copy's metadata. All the text and the
 * dates go through the language pack here, so the snapshot stays a plain reader of the central's data.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class presenter {
    /**
     * The template context for the block card: the bag, this month's intervention and the last three works.
     *
     * @param snapshot $snap The snapshot to show.
     * @param \stdClass $meta The local copy's metadata (status, fetchedat).
     * @return array
     */
    public static function card(snapshot $snap, \stdClass $meta): array {
        $context = [
            'subtitle' => get_string('subtitle', 'block_oksigeniaaravo'),
            'brand' => self::brand($snap),
            'updated' => self::updated($meta),
            'failed' => self::failed($meta),
            'registerurl' => (new \moodle_url('/blocks/oksigeniaaravo/index.php'))->out(false),
        ];
        $notice = $snap->notice();
        if ($notice !== null) {
            $context['notice'] = $notice;
        }
        if ($snap->has_bag()) {
            $context['bag'] = self::bag($snap->bag());
        }
        $intervention = self::intervention_card($snap->interventions());
        if ($intervention !== null) {
            $context['intervention'] = $intervention;
        }
        $usage = self::usage($snap->limits(), $meta);
        if ($usage !== null) {
            $context['usage'] = $usage;
        }
        $gifts = self::gifts($snap);
        if ($gifts !== null) {
            $context['gifts'] = $gifts;
        }
        $recent = [];
        foreach (array_slice($snap->entries(), 0, 3) as $entry) {
            $recent[] = self::entry_row($entry);
        }
        $context['hasrecent'] = !empty($recent);
        if ($recent) {
            $context['recent'] = $recent;
        }
        $context['summaryline'] = self::summary_line($snap);
        return $context;
    }

    /**
     * The compact one-line summary shown when the card is collapsed (bag balance and this month's interventions).
     *
     * @param snapshot $snap The snapshot to summarise.
     * @return string
     */
    private static function summary_line(snapshot $snap): string {
        $parts = [];
        if ($snap->has_bag()) {
            $parts[] = get_string('summarybag', 'block_oksigeniaaravo', minutes::format($snap->bag()['remaining']));
        }
        $in = $snap->interventions();
        if ((int) $in['allowed'] > 0) {
            $a = ['used' => (int) $in['used'], 'allowed' => (int) $in['allowed']];
            $parts[] = get_string('summarymonth', 'block_oksigeniaaravo', $a);
        }
        if (!$parts) {
            return get_string('summarydefault', 'block_oksigeniaaravo');
        }
        return implode(' · ', $parts);
    }

    /**
     * The template context for the register page: the bag, this month's intervention, the per-month summary and the
     * full list of works. The download selector is added by the page, which has the output renderer.
     *
     * @param snapshot $snap The snapshot to show.
     * @param \stdClass $meta The local copy's metadata.
     * @param bool $showadminlink Whether to paint the central admin link (site administrators only).
     * @return array
     */
    public static function page(snapshot $snap, \stdClass $meta, bool $showadminlink): array {
        $context = [
            'brand' => self::brand($snap),
            'updated' => self::updated($meta),
            'failed' => self::failed($meta),
        ];
        $notice = $snap->notice();
        if ($notice !== null) {
            $context['notice'] = $notice;
        }
        if ($snap->has_bag()) {
            $context['bag'] = self::bag($snap->bag());
        }
        $intervention = self::intervention($snap->interventions());
        if ($intervention !== null) {
            $context['intervention'] = $intervention;
        }
        $usage = self::usage($snap->limits(), $meta);
        if ($usage !== null) {
            $context['usage'] = $usage;
        }
        $included = self::included_total($snap);
        if ($included > 0) {
            $context['included'] = minutes::format($included);
        }
        $summary = [];
        foreach ($snap->monthly_summary() as $row) {
            $summary[] = [
                'month' => self::month_name($row['month']),
                'interventions' => $row['allowed'] > 0
                    ? get_string('nofn', 'block_oksigeniaaravo', ['a' => $row['used'], 'b' => $row['allowed']])
                    : '—',
                'bag' => minutes::format($row['bagminutes']),
                'outofbag' => minutes::format($row['outofbagminutes']),
                'maintenance' => minutes::format($row['maintenanceminutes']),
            ];
        }
        $context['hassummary'] = !empty($summary);
        if ($summary) {
            $context['summary'] = $summary;
        }
        $entries = [];
        foreach ($snap->entries() as $entry) {
            $entries[] = self::entry_row($entry);
        }
        $context['hasentries'] = !empty($entries);
        if ($entries) {
            $context['entries'] = $entries;
        }
        $adminurl = $snap->central_admin_url();
        if ($showadminlink && $adminurl !== '') {
            $context['centraladminurl'] = $adminurl;
        }
        return $context;
    }

    /**
     * The flat rows for the CSV and Excel download, newest first.
     *
     * @param snapshot $snap The snapshot to export.
     * @return array
     */
    public static function export_rows(snapshot $snap): array {
        $rows = [];
        foreach ($snap->entries() as $entry) {
            $ts = $entry['date'] !== '' ? strtotime($entry['date']) : 0;
            $rows[] = [
                'date' => $ts ? userdate($ts, get_string('strftimedate', 'langconfig')) : $entry['date'],
                'description' => $entry['description'],
                'minutes' => $entry['minutes'],
                'type' => self::type_label($entry['type']),
                'included' => $entry['included'],
                'bag' => $entry['bag'],
                'outofbag' => $entry['outofbag'],
                'reference' => $entry['reference'],
            ];
        }
        return $rows;
    }

    /**
     * The column headings for the download, in the site language.
     *
     * @return array
     */
    public static function export_columns(): array {
        return [
            'date' => get_string('coldate', 'block_oksigeniaaravo'),
            'description' => get_string('coldescription', 'block_oksigeniaaravo'),
            'minutes' => get_string('colminutes', 'block_oksigeniaaravo'),
            'type' => get_string('coltype', 'block_oksigeniaaravo'),
            'included' => get_string('colincluded', 'block_oksigeniaaravo'),
            'bag' => get_string('colbag', 'block_oksigeniaaravo'),
            'outofbag' => get_string('coloutofbag', 'block_oksigeniaaravo'),
            'reference' => get_string('colreference', 'block_oksigeniaaravo'),
        ];
    }

    /**
     * The brand to show small at the foot: from the snapshot, then the setting, else an empty string.
     *
     * @param snapshot $snap The snapshot.
     * @return string
     */
    private static function brand(snapshot $snap): string {
        $name = $snap->provider();
        if ($name === '') {
            $name = trim((string) get_config('block_oksigeniaaravo', 'provider'));
        }
        return $name;
    }

    /**
     * The "Updated on … at …" line, or an empty string if nothing has ever been read.
     *
     * @param \stdClass $meta The local copy's metadata.
     * @return string
     */
    private static function updated(\stdClass $meta): string {
        $fetchedat = (int) ($meta->fetchedat ?? 0);
        if ($fetchedat <= 0) {
            return '';
        }
        return get_string('updatedat', 'block_oksigeniaaravo', [
            'date' => userdate($fetchedat, get_string('strftimedate', 'langconfig')),
            'time' => userdate($fetchedat, get_string('strftimetime', 'langconfig')),
        ]);
    }

    /**
     * Whether the last read failed although there is a previous copy to show.
     *
     * @param \stdClass $meta The local copy's metadata.
     * @return bool
     */
    private static function failed(\stdClass $meta): bool {
        return ($meta->status ?? '') !== 'ok' && (int) ($meta->fetchedat ?? 0) > 0;
    }

    /**
     * The bag section: the figure and the bar both show what is left, a caption that says the same, and the colour and
     * a word for the state (never colour alone).
     *
     * @param array $bag The bag from the snapshot.
     * @return array
     */
    private static function bag(array $bag): array {
        $remaining = $bag['remaining'];
        $purchased = $bag['purchased'];
        $percent = $purchased > 0 ? (int) round($remaining / $purchased * 100) : 0;
        $percent = max(0, min(100, $percent));
        if ($remaining <= 0) {
            $state = 'empty';
        } else if (
            ($bag['threshold'] > 0 && $remaining <= $bag['threshold'])
                || ($bag['threshold'] <= 0 && $percent < 20)
        ) {
            $state = 'low';
        } else {
            $state = 'ok';
        }
        $line = get_string('bagline', 'block_oksigeniaaravo', [
            'remaining' => minutes::format($remaining),
            'purchased' => minutes::format($purchased),
            'used' => minutes::format($bag['used']),
        ]);
        $words = ['empty' => 'bagstatusempty', 'low' => 'bagstatuslow'];
        $split = self::split_duration($remaining);
        return [
            // Shared by the block card (the ring) and the register page (the figure and the bar).
            'remaining' => minutes::format($remaining),
            'line' => $line,
            'percent' => $percent,
            'low' => $state !== 'ok',
            'statusword' => isset($words[$state]) ? get_string($words[$state], 'block_oksigeniaaravo') : '',
            // The register page colours by class; the block card by state word.
            'numberclass' => $state === 'empty' ? 'text-danger' : '',
            'barclass' => $state === 'empty' ? 'bg-danger' : ($state === 'low' ? 'bg-warning' : ''),
            'state' => $state,
            // The block card splits the balance to fit the ring centre, and names the purchased total.
            'remainingmain' => $split['main'],
            'remainingrest' => $split['rest'],
            'of' => get_string('bagof', 'block_oksigeniaaravo', minutes::format($purchased)),
        ];
    }

    /**
     * A duration split for the ring centre: the main figure ("3 h", or "8 h", or "30 min") and an optional rest
     * ("30 min" when there are both hours and minutes).
     *
     * @param int $minutes The duration in minutes.
     * @return array With 'main' and 'rest' (the latter may be empty).
     */
    private static function split_duration(int $minutes): array {
        $minutes = max(0, $minutes);
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        if ($hours > 0 && $mins > 0) {
            return [
                'main' => get_string('durationh', 'block_oksigeniaaravo', ['h' => $hours]),
                'rest' => get_string('durationm', 'block_oksigeniaaravo', ['m' => $mins]),
            ];
        }
        if ($hours > 0) {
            return ['main' => get_string('durationh', 'block_oksigeniaaravo', ['h' => $hours]), 'rest' => ''];
        }
        return ['main' => get_string('durationm', 'block_oksigeniaaravo', ['m' => $mins]), 'rest' => ''];
    }

    /**
     * The plan usage section: a bar for active users and one for disk, each against its limit, coloured by how close
     * it is. Null when there are no limits or the usage has not been measured yet.
     *
     * @param array|null $limits The plan limits from the snapshot.
     * @param \stdClass $meta The local copy's metadata, with the measured usage.
     * @return array|null
     */
    private static function usage(?array $limits, \stdClass $meta): ?array {
        if ($limits === null || (int) ($meta->usageat ?? 0) <= 0) {
            return null;
        }
        $rows = [];
        if ($limits['active_users'] > 0) {
            $used = (int) ($meta->activeusers ?? 0);
            $rows[] = self::usage_row(
                get_string('usageusers', 'block_oksigeniaaravo'),
                get_string('usageusersof', 'block_oksigeniaaravo', ['used' => $used, 'limit' => $limits['active_users']]),
                $used,
                $limits['active_users']
            );
        }
        if ($limits['disk_gb'] > 0) {
            $usedgb = (int) ($meta->diskmb ?? 0) / 1024;
            $rows[] = self::usage_row(
                get_string('usagedisk', 'block_oksigeniaaravo'),
                get_string('usagediskof', 'block_oksigeniaaravo', [
                    'used' => format_float($usedgb, 1),
                    'limit' => format_float($limits['disk_gb'], 0),
                ]),
                $usedgb,
                $limits['disk_gb']
            );
        }
        return $rows ? ['rows' => $rows] : null;
    }

    /**
     * The "at no cost" card(s) for the block: the contract's included work (interventions, the welcome allowance and
     * plan maintenance) as the headline, and, when there is any, the discretionary courtesy shown apart. Null when
     * there is nothing free to show.
     *
     * @param snapshot $snap The snapshot.
     * @return array|null
     */
    private static function gifts(snapshot $snap): ?array {
        $interventionmin = 0;
        $maintenancemin = 0;
        $monthmin = 0;
        $incoldest = 0;
        $courtesymin = 0;
        $couoldest = 0;
        $courtesylatest = '';
        $currentmonth = (string) $snap->interventions()['month'];
        foreach ($snap->entries() as $entry) {
            $ts = $entry['date'] !== '' ? strtotime((string) $entry['date']) : 0;
            if ($entry['type'] === 'courtesy') {
                // Discretionary: it counts as free and never touches the bag; shown apart from the contract.
                $courtesymin += (int) $entry['minutes'];
                if ($ts > 0 && ($couoldest === 0 || $ts < $couoldest)) {
                    $couoldest = $ts;
                }
                if ($courtesylatest === '') {
                    // Entries come newest first, so the first courtesy seen is the latest.
                    $courtesylatest = (string) $entry['description'];
                }
                continue;
            }
            $interventionmin += (int) $entry['included'];
            if ($entry['type'] === 'maintenance') {
                $maintenancemin += (int) $entry['minutes'];
            }
            if ($currentmonth !== '' && strpos((string) $entry['date'], $currentmonth) === 0) {
                $monthmin += (int) $entry['included'] + ($entry['type'] === 'maintenance' ? (int) $entry['minutes'] : 0);
            }
            if ($ts > 0 && ($incoldest === 0 || $ts < $incoldest)) {
                $incoldest = $ts;
            }
        }
        $gifts = [];
        if ($courtesymin > 0) {
            $courtesy = ['total' => minutes::format($courtesymin), 'period' => self::since($couoldest)];
            if ($courtesylatest !== '') {
                $courtesy['latest'] = $courtesylatest;
            }
            $gifts['courtesy'] = $courtesy;
        }
        $includedtotal = $interventionmin + $maintenancemin;
        if ($includedtotal > 0) {
            $parts = [];
            if ($maintenancemin > 0) {
                $parts[] = ['label' => self::type_label('maintenance'), 'text' => minutes::format($maintenancemin),
                    'minutes' => $maintenancemin, 'kind' => 'maintenance'];
            }
            if ($interventionmin > 0) {
                $parts[] = ['label' => get_string('interventionstitle', 'block_oksigeniaaravo'),
                    'text' => minutes::format($interventionmin), 'minutes' => $interventionmin, 'kind' => 'intervention'];
            }
            $included = ['total' => minutes::format($includedtotal), 'period' => self::since($incoldest),
                'hasparts' => count($parts) >= 2, 'parts' => $parts];
            if ($monthmin > 0) {
                $included['month'] = minutes::format($monthmin);
            }
            $gifts['included'] = $included;
        }
        if (!$gifts) {
            return null;
        }
        $gifts['two'] = isset($gifts['courtesy']) && isset($gifts['included']);
        return $gifts;
    }

    /**
     * A "Since <date>" line in words, for the period a figure accumulates over.
     *
     * @param int $ts The start as a Unix time (0 gives an empty string).
     * @return string
     */
    private static function since(int $ts): string {
        if ($ts <= 0) {
            return '';
        }
        return get_string('includedsince', 'block_oksigeniaaravo',
            userdate($ts, get_string('strftimedate', 'langconfig')));
    }

    /**
     * The support time done at no cost over the window: the minutes of the included interventions (and the first-month
     * welcome allowance, which are included entries) plus the plan maintenance, none of which discount the bag.
     *
     * @param snapshot $snap The snapshot.
     * @return int
     */
    private static function included_total(snapshot $snap): int {
        $total = 0;
        foreach ($snap->entries() as $entry) {
            $total += $entry['included'];
            if ($entry['type'] === 'maintenance') {
                $total += $entry['minutes'];
            }
        }
        return $total;
    }

    /**
     * One usage bar: its label, the "used of limit" text, the percentage and the colour by how close it is.
     *
     * @param string $label The row label.
     * @param string $text The "used of limit" text.
     * @param float $used What is used.
     * @param float $limit The limit.
     * @return array
     */
    private static function usage_row(string $label, string $text, float $used, float $limit): array {
        $percent = $limit > 0 ? (int) round($used / $limit * 100) : 0;
        $percent = max(0, min(100, $percent));
        $over = $used >= $limit;
        $warn = !$over && $percent >= 80;
        return [
            'label' => $label,
            'text' => $text,
            'percent' => $percent,
            // The register page colours its bar by class; the block card's gauge by state, with the figure as text.
            'barclass' => $over ? 'bg-danger' : ($warn ? 'bg-warning' : ''),
            'state' => $over ? 'full' : ($warn ? 'warn' : ''),
            'percenttext' => get_string('percentformat', 'block_oksigeniaaravo', $percent),
        ];
    }

    /**
     * One work row for the templates: the readable date, the duration, the kind of work and a coloured dot for it.
     *
     * @param array $entry The entry from the snapshot.
     * @return array
     */
    private static function entry_row(array $entry): array {
        $ts = $entry['date'] !== '' ? strtotime($entry['date']) : 0;
        return [
            'date' => $ts ? userdate($ts, get_string('strftimedate', 'langconfig')) : $entry['date'],
            'description' => $entry['description'],
            'minutes' => minutes::format($entry['minutes']),
            'type' => self::type_label($entry['type']),
            'typedot' => self::type_dot($entry['type']),
        ];
    }

    /**
     * The label for a kind of work, or the raw code if there is no string for it.
     *
     * @param string $type The type code.
     * @return string
     */
    private static function type_label(string $type): string {
        $key = 'type_' . $type;
        return get_string_manager()->string_exists($key, 'block_oksigeniaaravo')
            ? get_string($key, 'block_oksigeniaaravo') : $type;
    }

    /**
     * A text colour utility for the dot next to a kind of work. The utilities exist from Moodle 4.1 to 5.3; the label
     * text carries the meaning, the dot is only a hint.
     *
     * @param string $type The type code.
     * @return string
     */
    private static function type_dot(string $type): string {
        $dots = [
            'included' => 'text-success',
            'bag' => 'text-primary',
            'out_of_bag' => 'text-danger',
            'maintenance' => 'text-secondary',
            'courtesy' => 'text-info',
        ];
        return $dots[$type] ?? 'text-secondary';
    }

    /**
     * This month's included interventions: a one-line summary for the card, and a count with segments for the page.
     * Null when the plan has none.
     *
     * @param array $in This month's interventions from the snapshot.
     * @return array|null
     */
    private static function intervention(array $in): ?array {
        if ($in['allowed'] <= 0) {
            return null;
        }
        $month = self::month_name($in['month']);
        $used = max(0, min($in['used'], $in['allowed']));
        $segments = [];
        for ($i = 0; $i < $in['allowed']; $i++) {
            $segments[] = ['filled' => $i < $used];
        }
        return [
            'line' => get_string('interventionline', 'block_oksigeniaaravo', [
                'month' => $month,
                'used' => $in['used'],
                'allowed' => $in['allowed'],
            ]),
            'label' => get_string('interventionstitle', 'block_oksigeniaaravo') . ' · ' . $month,
            'count' => get_string('nofn', 'block_oksigeniaaravo', ['a' => $in['used'], 'b' => $in['allowed']]),
            'segments' => $segments,
            'available' => $in['used'] < $in['allowed'],
        ];
    }

    /**
     * This month's included interventions for the block card: the month label, the "n of m" text, and a pip per
     * intervention, filled if already used. Null when the plan has none.
     *
     * @param array $in This month's interventions from the snapshot.
     * @return array|null
     */
    private static function intervention_card(array $in): ?array {
        if ($in['allowed'] <= 0) {
            return null;
        }
        $used = max(0, min($in['used'], $in['allowed']));
        $pips = [];
        for ($i = 0; $i < $in['allowed']; $i++) {
            $pips[] = ['used' => $i < $used];
        }
        return [
            'label' => get_string('interventionmonth', 'block_oksigeniaaravo', self::month_name($in['month'])),
            'text' => get_string('nofn', 'block_oksigeniaaravo', ['a' => $in['used'], 'b' => $in['allowed']]),
            'pips' => $pips,
        ];
    }

    /**
     * A month name from a YYYY-MM string, or the string itself if it is not one.
     *
     * @param string $month The month as YYYY-MM.
     * @return string
     */
    private static function month_name(string $month): string {
        if (!preg_match('/^(\d{4})-(\d{2})$/', $month, $parts)) {
            return $month;
        }
        $ts = make_timestamp((int) $parts[1], (int) $parts[2], 1);
        return userdate($ts, get_string('monthyearformat', 'block_oksigeniaaravo'));
    }
}
