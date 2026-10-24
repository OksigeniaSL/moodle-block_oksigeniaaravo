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
 * Behat steps for block_oksigeniaaravo.
 *
 * @package    block_oksigeniaaravo
 * @category   test
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps to set up block_oksigeniaaravo for Behat.
 *
 * @package    block_oksigeniaaravo
 * @category   test
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_oksigeniaaravo extends behat_base {
    /**
     * Stores a snapshot in the local copy, as if it had been read from the central, so the block has something to show.
     *
     * @Given a support hours snapshot is stored
     * @return void
     */
    public function a_support_hours_snapshot_is_stored(): void {
        global $DB;
        $payload = json_encode([
            'schema' => 'aravo.snapshot.v1',
            'generated_at' => '2026-11-04T09:12:00+00:00',
            'client' => ['provider' => 'Oksigenia support', 'plan' => 'centro'],
            'bag' => [
                'has_bag' => true,
                'balance_minutes' => 210,
                'purchased_minutes' => 600,
                'used_minutes' => 390,
                'low_threshold_minutes' => 120,
                'items' => [],
            ],
            'interventions' => ['month' => '2026-11', 'allowed' => 2, 'used' => 1, 'max_minutes_each' => 30,
                'items' => []],
            'entries' => [
                ['id' => 'e-1', 'date' => '2026-11-04', 'description' => 'Added 12 users', 'minutes' => 30,
                    'type' => 'bag', 'included_minutes' => 0, 'bag_minutes' => 30, 'out_of_bag_minutes' => 0,
                    'reference' => null],
            ],
            'monthly_summary' => [
                ['month' => '2026-11', 'interventions_allowed' => 2, 'interventions_used' => 1, 'bag_minutes' => 30,
                    'out_of_bag_minutes' => 0, 'maintenance_minutes' => 0],
            ],
        ]);
        $now = time();
        $DB->delete_records('block_oksigeniaaravo_snap');
        $DB->insert_record('block_oksigeniaaravo_snap', (object) [
            'payload' => $payload,
            'generatedat' => $now,
            'fetchedat' => $now,
            'attemptedat' => $now,
            'status' => 'ok',
            'httpstatus' => 200,
        ]);
    }

    /**
     * Goes to the support hours register page.
     *
     * @When I am on the support hours register page
     * @return void
     */
    public function i_am_on_the_support_hours_register_page(): void {
        $this->getSession()->visit($this->locate_path('/blocks/oksigeniaaravo/index.php'));
    }
}
