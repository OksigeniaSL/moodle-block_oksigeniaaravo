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

namespace block_oksigeniaaravo\task;

use block_oksigeniaaravo\local\planusage;
use block_oksigeniaaravo\local\refresher;
use block_oksigeniaaravo\local\snapshot;
use block_oksigeniaaravo\local\store;

/**
 * Brings a fresh snapshot from the central registry into the local copy.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class refresh extends \core\task\scheduled_task {
    /**
     * The task name shown in the admin pages.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_refresh', 'block_oksigeniaaravo');
    }

    /**
     * Reads the central and keeps the local copy.
     *
     * @return void
     */
    public function execute(): void {
        $snap = refresher::refresh();
        $status = $snap && isset($snap->status) ? $snap->status : 'unknown';
        mtrace('block_oksigeniaaravo: central read finished with status ' . $status . '.');
        $this->measure_usage();
    }

    /**
     * Measures the plan usage when the client has limits to compare against, at most once every few hours (the disk
     * scan is heavy). The usage itself comes from this Moodle, not the central.
     *
     * @return void
     */
    private function measure_usage(): void {
        $current = snapshot::from_store();
        if (!$current || $current->limits() === null) {
            return;
        }
        $row = store::get();
        $stale = !$row || (int) $row->usageat === 0 || (time() - (int) $row->usageat) > 6 * HOURSECS;
        if (!$stale) {
            return;
        }
        store::save_usage(planusage::active_users(), planusage::disk_mb());
        mtrace('block_oksigeniaaravo: plan usage measured.');
    }
}
