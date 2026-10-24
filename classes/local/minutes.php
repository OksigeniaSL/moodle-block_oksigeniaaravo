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
 * Turns a number of minutes into a readable duration, never a decimal ("3 h 45 min", "45 min", "2 h").
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class minutes {
    /**
     * A duration as hours and minutes, with the units from the language pack.
     *
     * @param int $minutes The number of minutes, never negative on screen.
     * @return string
     */
    public static function format(int $minutes): string {
        $minutes = max(0, $minutes);
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;
        if ($hours > 0 && $rest > 0) {
            return get_string('durationhm', 'block_oksigeniaaravo', ['h' => $hours, 'm' => $rest]);
        }
        if ($hours > 0) {
            return get_string('durationh', 'block_oksigeniaaravo', ['h' => $hours]);
        }
        return get_string('durationm', 'block_oksigeniaaravo', ['m' => $rest]);
    }
}
