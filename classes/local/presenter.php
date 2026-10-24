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
            'provider' => self::provider($snap),
            'updated' => self::updated($meta),
            'failed' => self::failed($meta),
            'hasbag' => $snap->has_bag(),
            'registerurl' => (new \moodle_url('/blocks/oksigeniaaravo/index.php'))->out(false),
        ];
        if ($snap->has_bag()) {
            $context['bag'] = self::bag($snap->bag());
        }
        $interventions = self::intervention_label($snap->interventions());
        if ($interventions !== '') {
            $context['interventions'] = $interventions;
        }
        $recent = [];
        foreach (array_slice($snap->entries(), 0, 3) as $entry) {
            $recent[] = self::entry_row($entry);
        }
        $context['hasrecent'] = !empty($recent);
        if ($recent) {
            $context['recent'] = $recent;
        }
        return $context;
    }

    /**
     * The template context for the register page: the bag, this month's intervention, the per-month summary and the
     * full list of works.
     *
     * @param snapshot $snap The snapshot to show.
     * @param \stdClass $meta The local copy's metadata.
     * @param bool $showadminlink Whether to paint the central admin link (site administrators only).
     * @return array
     */
    public static function page(snapshot $snap, \stdClass $meta, bool $showadminlink): array {
        $context = [
            'provider' => self::provider($snap),
            'updated' => self::updated($meta),
            'failed' => self::failed($meta),
            'hasbag' => $snap->has_bag(),
        ];
        if ($snap->has_bag()) {
            $context['bag'] = self::bag($snap->bag());
        }
        $interventions = self::intervention_label($snap->interventions());
        if ($interventions !== '') {
            $context['interventions'] = $interventions;
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
            $entries[] = self::entry_row($entry) + [
                'included' => minutes::format($entry['included']),
                'bagminutes' => minutes::format($entry['bag']),
                'outofbag' => minutes::format($entry['outofbag']),
                'reference' => $entry['reference'],
            ];
        }
        $context['hasentries'] = !empty($entries);
        if ($entries) {
            $context['entries'] = $entries;
        }
        $context['downloadcsv'] = (new \moodle_url(
            '/blocks/oksigeniaaravo/index.php',
            ['dataformat' => 'csv', 'sesskey' => sesskey()]
        ))->out(false);
        $context['downloadexcel'] = (new \moodle_url(
            '/blocks/oksigeniaaravo/index.php',
            ['dataformat' => 'excel', 'sesskey' => sesskey()]
        ))->out(false);
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
     * The provider name for the title: from the snapshot, then the setting, then the plugin name.
     *
     * @param snapshot $snap The snapshot.
     * @return string
     */
    private static function provider(snapshot $snap): string {
        $name = $snap->provider();
        if ($name !== '') {
            return $name;
        }
        $setting = trim((string) get_config('block_oksigeniaaravo', 'provider'));
        return $setting !== '' ? $setting : get_string('pluginname', 'block_oksigeniaaravo');
    }

    /**
     * The "Updated at…" line, or an empty string if nothing has ever been read.
     *
     * @param \stdClass $meta The local copy's metadata.
     * @return string
     */
    private static function updated(\stdClass $meta): string {
        $fetchedat = (int) ($meta->fetchedat ?? 0);
        if ($fetchedat <= 0) {
            return '';
        }
        return get_string(
            'updatedat',
            'block_oksigeniaaravo',
            userdate($fetchedat, get_string('strftimedatetimeshort', 'langconfig'))
        );
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
     * The bag section: readable durations, the percentage left and whether it is low.
     *
     * @param array $bag The bag from the snapshot.
     * @return array
     */
    private static function bag(array $bag): array {
        $percent = $bag['purchased'] > 0 ? (int) round($bag['remaining'] / $bag['purchased'] * 100) : 0;
        return [
            'remaining' => minutes::format($bag['remaining']),
            'summary' => get_string('bagusedof', 'block_oksigeniaaravo', [
                'used' => minutes::format($bag['used']),
                'purchased' => minutes::format($bag['purchased']),
            ]),
            'percent' => max(0, min(100, $percent)),
            'low' => $bag['threshold'] > 0 && $bag['remaining'] <= $bag['threshold'],
        ];
    }

    /**
     * One work row for the templates: the readable date, the duration and the kind of work.
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
     * The line about this month's included intervention, or an empty string if the plan has none.
     *
     * @param array $in This month's interventions from the snapshot.
     * @return string
     */
    private static function intervention_label(array $in): string {
        if ($in['allowed'] <= 0) {
            return '';
        }
        $month = self::month_name($in['month']);
        if ($in['allowed'] === 1) {
            if ($in['used'] <= 0) {
                return get_string('interventionavailable', 'block_oksigeniaaravo', $month);
            }
            $first = $in['items'][0] ?? null;
            if ($first) {
                $ts = $first['date'] !== '' ? strtotime($first['date']) : 0;
                return get_string('interventionused', 'block_oksigeniaaravo', [
                    'month' => $month,
                    'date' => $ts ? userdate($ts, get_string('strftimedate', 'langconfig')) : $first['date'],
                    'mins' => minutes::format($first['minutes']),
                ]);
            }
            return get_string('interventionusedplain', 'block_oksigeniaaravo', $month);
        }
        return get_string('interventionsused', 'block_oksigeniaaravo', [
            'month' => $month,
            'used' => $in['used'],
            'allowed' => $in['allowed'],
        ]);
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
        return userdate($ts, get_string('strftimemonthyear', 'langconfig'));
    }
}
