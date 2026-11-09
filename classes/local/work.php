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
 * The door of the support hours in «Your work», the dashboard block of the Oksigenia plugins
 * (block_oksigeniatembiapo, contract version 2). Without that block installed nobody calls it.
 *
 * It reads only the local copy (one row, no network), so it stays well under the 50 ms the contract asks for.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class work {
    /**
     * The doors for one person: the support hours for whoever may see them and once the first data has arrived;
     * none otherwise.
     *
     * @param int $userid The person.
     * @param int $now The time to count this week from; now when 0.
     * @return array[] One door, or none.
     */
    public static function for_user(int $userid, int $now = 0): array {
        if ($userid <= 0 || isguestuser($userid)) {
            return [];
        }
        if (!has_capability('block/oksigeniaaravo:view', \context_system::instance(), $userid)) {
            return [];
        }
        $meta = store::get();
        $snap = snapshot::from_row($meta);
        if (!$snap) {
            return [];
        }
        $said = presenter::door($snap, $meta, $now);
        $door = [
            'key' => 'block_oksigeniaaravo',
            'title' => get_string('blocktitle', 'block_oksigeniaaravo'),
            'url' => (new \moodle_url('/blocks/oksigeniaaravo/index.php'))->out(false),
            'alerts' => $said['alerts'],
        ];
        if ($said['status'] !== '') {
            $door['status'] = $said['status'];
        }
        return [$door];
    }
}
