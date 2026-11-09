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

/**
 * Library functions for block_oksigeniaaravo.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The door of the support hours in «Your work», the dashboard block of the Oksigenia plugins (block_oksigeniatembiapo,
 * contract version 2): what is left in the bag and this week's work (or this month's interventions), and an alert when
 * the bag is used up or a plan limit is reached.
 *
 * @param int $userid The person.
 * @return array[] Its door; none when the person may not see the support hours or no data has arrived yet.
 */
function block_oksigeniaaravo_oksigenia_work(int $userid): array {
    return \block_oksigeniaaravo\local\work::for_user($userid);
}
