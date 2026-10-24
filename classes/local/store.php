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
 * The local copy of the last snapshot read from the central registry. One row per site, so the block works when the
 * central does not answer.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class store {
    /** @var string The cache table. */
    const TABLE = 'block_oksigeniaaravo_snap';

    /**
     * The single cache row, or null if nothing has been stored yet.
     *
     * @return \stdClass|null
     */
    public static function get(): ?\stdClass {
        global $DB;
        $rows = $DB->get_records(self::TABLE, null, 'id ASC', '*', 0, 1);
        return $rows ? reset($rows) : null;
    }

    /**
     * Records the result of a read. On success it keeps the payload; on failure it keeps the previous payload and only
     * notes that the try failed.
     *
     * @param string $status "ok" or a short error code.
     * @param int $httpstatus The HTTP status, or 0 if there was none.
     * @param string|null $payload The snapshot JSON on success, null otherwise.
     * @param int $generatedat When the central generated the snapshot, as a Unix time.
     * @return void
     */
    public static function save(string $status, int $httpstatus, ?string $payload, int $generatedat): void {
        global $DB;
        $now = time();
        $record = self::get() ?: new \stdClass();
        $new = empty($record->id);
        $record->attemptedat = $now;
        $record->status = $status;
        $record->httpstatus = $httpstatus;
        if ($status === 'ok' && $payload !== null) {
            $record->payload = $payload;
            $record->generatedat = $generatedat;
            $record->fetchedat = $now;
        } else if ($new) {
            // First read, and it failed: keep an empty row so we remember we tried.
            $record->payload = null;
            $record->generatedat = 0;
            $record->fetchedat = 0;
        }
        if ($new) {
            $DB->insert_record(self::TABLE, $record);
        } else {
            $DB->update_record(self::TABLE, $record);
        }
    }

    /**
     * Records the locally measured plan usage on the single row. Does nothing if there is no row yet.
     *
     * @param int $activeusers Users active in the last 30 days.
     * @param int $diskmb Disk used by the files, in whole megabytes.
     * @return void
     */
    public static function save_usage(int $activeusers, int $diskmb): void {
        global $DB;
        $row = self::get();
        if (!$row) {
            return;
        }
        $row->activeusers = $activeusers;
        $row->diskmb = $diskmb;
        $row->usageat = time();
        $DB->update_record(self::TABLE, $row);
    }
}
