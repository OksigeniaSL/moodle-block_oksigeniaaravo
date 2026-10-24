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
 * Upgrade steps for block_oksigeniaaravo.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Applies the upgrade steps.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_block_oksigeniaaravo_upgrade($oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026102401) {
        $table = new xmldb_table('block_oksigeniaaravo_snap');
        $fields = [
            new xmldb_field('activeusers', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'httpstatus'),
            new xmldb_field('diskmb', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'activeusers'),
            new xmldb_field('usageat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'diskmb'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_block_savepoint(true, 2026102401, 'oksigeniaaravo');
    }

    return true;
}
