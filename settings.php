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
 * Global settings of block_oksigeniaaravo: where the central registry is and how often to read it.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'block_oksigeniaaravo/centralendpoint',
        get_string('centralendpoint', 'block_oksigeniaaravo'),
        get_string('centralendpoint_desc', 'block_oksigeniaaravo'),
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'block_oksigeniaaravo/centraltoken',
        get_string('centraltoken', 'block_oksigeniaaravo'),
        get_string('centraltoken_desc', 'block_oksigeniaaravo'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'block_oksigeniaaravo/provider',
        get_string('provider', 'block_oksigeniaaravo'),
        get_string('provider_desc', 'block_oksigeniaaravo'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'block_oksigeniaaravo/pagettl',
        get_string('pagettl', 'block_oksigeniaaravo'),
        get_string('pagettl_desc', 'block_oksigeniaaravo'),
        '10',
        PARAM_INT
    ));
}
