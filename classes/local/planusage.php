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
 * Reads the site's own plan usage: how many users are active this month and how much disk the files take. The limits to
 * compare against come from the central (per client); this only measures the real use, and only Moodle knows it.
 *
 * The disk sum scans the whole files table, so it is meant to run in the scheduled task, not on every page view.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class planusage {
    /** @var int The active window: a user counts if they signed in within the last 30 days. */
    const WINDOWDAYS = 30;

    /**
     * Users active this month: real users (not deleted, not the guest) who signed in within the last 30 days, the same
     * window as the hosting conditions.
     *
     * @return int
     */
    public static function active_users(): int {
        global $DB;
        $since = time() - self::WINDOWDAYS * DAYSECS;
        return $DB->count_records_select(
            'user',
            'deleted = 0 AND id > :guest AND lastaccess > :since',
            ['guest' => 1, 'since' => $since]
        );
    }

    /**
     * Disk used by the files, in whole megabytes: the unique content size, deduplicated by content hash, like the
     * filedir on disk.
     *
     * @return int
     */
    public static function disk_mb(): int {
        global $DB;
        $bytes = (int) $DB->get_field_sql(
            'SELECT SUM(filesize) FROM (SELECT DISTINCT contenthash, filesize FROM {files}) uniquefiles'
        );
        return (int) round($bytes / 1048576);
    }
}
