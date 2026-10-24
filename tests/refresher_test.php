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

use block_oksigeniaaravo\local\refresher;
use block_oksigeniaaravo\local\snapshot;
use block_oksigeniaaravo\local\store;

/**
 * Tests for keeping the local copy from a central read, through every branch of the result.
 *
 * @package    block_oksigeniaaravo
 * @category   test
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_oksigeniaaravo\local\refresher
 * @covers     \block_oksigeniaaravo\local\store
 */
#[\PHPUnit\Framework\Attributes\CoversClass(refresher::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(store::class)]
final class refresher_test extends \advanced_testcase {
    /**
     * A valid snapshot body, like the central sends.
     *
     * @return string
     */
    private function body(): string {
        return json_encode([
            'schema' => 'aravo.snapshot.v1',
            'generated_at' => '2026-11-04T09:12:00+00:00',
            'bag' => ['has_bag' => false],
            'interventions' => ['allowed' => 0],
            'entries' => [],
            'monthly_summary' => [],
        ]);
    }

    public function test_a_good_read_is_stored(): void {
        $this->resetAfterTest();
        $row = refresher::store_result(['httpstatus' => 200, 'body' => $this->body(), 'error' => null]);
        $this->assertEquals('ok', $row->status);
        $this->assertEquals(200, $row->httpstatus);
        $this->assertGreaterThan(0, $row->fetchedat);
        $this->assertNotNull(snapshot::from_store());
    }

    public function test_an_http_error_is_recorded(): void {
        $this->resetAfterTest();
        $row = refresher::store_result(['httpstatus' => 404, 'body' => null, 'error' => null]);
        $this->assertEquals('http404', $row->status);
        $this->assertNull(snapshot::from_store());
    }

    public function test_a_network_error_is_recorded(): void {
        $this->resetAfterTest();
        $row = refresher::store_result(['httpstatus' => 0, 'body' => null, 'error' => 'network']);
        $this->assertEquals('network', $row->status);
    }

    public function test_an_invalid_body_is_rejected(): void {
        $this->resetAfterTest();
        $row = refresher::store_result(['httpstatus' => 200, 'body' => 'not json', 'error' => null]);
        $this->assertEquals('badjson', $row->status);
        $this->assertNull(snapshot::from_store());
    }

    public function test_a_failed_read_keeps_the_previous_copy(): void {
        $this->resetAfterTest();
        refresher::store_result(['httpstatus' => 200, 'body' => $this->body(), 'error' => null]);
        $row = refresher::store_result(['httpstatus' => 500, 'body' => null, 'error' => null]);
        $this->assertEquals('http500', $row->status);
        // The last good copy is still there to show.
        $this->assertNotNull(snapshot::from_store());
        $this->assertGreaterThan(0, $row->fetchedat);
    }

    public function test_without_an_endpoint_the_read_does_not_run(): void {
        $this->resetAfterTest();
        set_config('centralendpoint', '', 'block_oksigeniaaravo');
        $row = refresher::refresh();
        $this->assertEquals('noendpoint', $row->status);
    }

    public function test_a_fresh_copy_is_not_read_again_on_view(): void {
        $this->resetAfterTest();
        set_config('centralendpoint', '', 'block_oksigeniaaravo');
        set_config('pagettl', 10, 'block_oksigeniaaravo');
        refresher::store_result(['httpstatus' => 200, 'body' => $this->body(), 'error' => null]);
        // The copy was just read, so opening a page must not trigger another read.
        refresher::refresh_if_stale();
        $this->assertEquals('ok', store::get()->status);
    }

    public function test_a_stale_copy_is_read_again_on_view(): void {
        $this->resetAfterTest();
        set_config('centralendpoint', '', 'block_oksigeniaaravo');
        set_config('pagettl', 10, 'block_oksigeniaaravo');
        refresher::store_result(['httpstatus' => 200, 'body' => $this->body(), 'error' => null]);
        // Make the copy old, so opening a page reads again (and, with no endpoint, records that).
        $row = store::get();
        $row->attemptedat = time() - 3600;
        $GLOBALS['DB']->update_record(store::TABLE, $row);
        refresher::refresh_if_stale();
        $this->assertEquals('noendpoint', store::get()->status);
    }
}
