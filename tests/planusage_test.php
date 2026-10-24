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

namespace block_oksigeniaaravo;

use block_oksigeniaaravo\local\planusage;

/**
 * Tests for the local plan-usage measurements.
 *
 * @package    block_oksigeniaaravo
 * @category   test
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_oksigeniaaravo\local\planusage
 */
#[\PHPUnit\Framework\Attributes\CoversClass(planusage::class)]
final class planusage_test extends \advanced_testcase {
    public function test_active_users_counts_recent_sign_ins(): void {
        global $DB;
        $this->resetAfterTest();
        $base = planusage::active_users();
        $gen = $this->getDataGenerator();
        $recentone = $gen->create_user();
        $recenttwo = $gen->create_user();
        $old = $gen->create_user();
        $DB->set_field('user', 'lastaccess', time() - 5, ['id' => $recentone->id]);
        $DB->set_field('user', 'lastaccess', time() - DAYSECS, ['id' => $recenttwo->id]);
        $DB->set_field('user', 'lastaccess', time() - 40 * DAYSECS, ['id' => $old->id]);
        $this->assertEquals($base + 2, planusage::active_users());
    }

    public function test_disk_counts_the_unique_file_content(): void {
        $this->resetAfterTest();
        $before = planusage::disk_mb();
        $fs = get_file_storage();
        $record = [
            'contextid' => \context_system::instance()->id,
            'component' => 'block_oksigeniaaravo',
            'filearea' => 'unittest',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'blob.bin',
        ];
        $fs->create_file_from_string($record, str_repeat('a', 3 * 1024 * 1024));
        $this->assertGreaterThanOrEqual($before + 2, planusage::disk_mb());
    }
}
