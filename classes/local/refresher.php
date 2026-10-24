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
 * Brings a fresh snapshot from the central and keeps the local copy, with the result of the read. The block and the
 * register always draw from the local copy; this only updates it.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class refresher {
    /**
     * Reads the central and stores the result. Any failure leaves the previous copy in place.
     *
     * @param string|null $from Start of the window as YYYY-MM-DD, or null for the default.
     * @param string|null $to End of the window as YYYY-MM-DD, or null for the default.
     * @return \stdClass|null The cache row after the read.
     */
    public static function refresh(?string $from = null, ?string $to = null): ?\stdClass {
        global $CFG;
        $endpoint = trim((string) get_config('block_oksigeniaaravo', 'centralendpoint'));
        $token = trim((string) get_config('block_oksigeniaaravo', 'centraltoken'));
        if ($endpoint === '') {
            store::save('noendpoint', 0, null, 0);
            return store::get();
        }
        $result = central::fetch($endpoint, $token, $CFG->wwwroot, $from, $to);
        if ($result['error'] !== null) {
            store::save($result['error'], $result['httpstatus'], null, 0);
            return store::get();
        }
        if ($result['httpstatus'] !== 200) {
            store::save('http' . $result['httpstatus'], $result['httpstatus'], null, 0);
            return store::get();
        }
        $data = json_decode((string) $result['body'], true);
        if (!is_array($data) || empty($data['schema'])) {
            store::save('badjson', $result['httpstatus'], null, 0);
            return store::get();
        }
        $generatedat = empty($data['generated_at']) ? 0 : (int) strtotime((string) $data['generated_at']);
        store::save('ok', $result['httpstatus'], (string) $result['body'], $generatedat ?: time());
        return store::get();
    }

    /**
     * Refreshes only if the local copy is older than the page TTL or there is none. Used when a page is opened, so the
     * central is not asked on every view.
     *
     * @return void
     */
    public static function refresh_if_stale(): void {
        $ttl = (int) get_config('block_oksigeniaaravo', 'pagettl');
        if ($ttl <= 0) {
            $ttl = 10;
        }
        $snap = store::get();
        if ($snap && (time() - (int) $snap->attemptedat) < $ttl * MINSECS) {
            return;
        }
        self::refresh();
    }
}
