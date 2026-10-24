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

use block_oksigeniaaravo\local\minutes;
use block_oksigeniaaravo\local\presenter;
use block_oksigeniaaravo\local\snapshot;

/**
 * Tests for reading a central snapshot and showing it.
 *
 * @package    block_oksigeniaaravo
 * @category   test
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_oksigeniaaravo\local\snapshot
 * @covers     \block_oksigeniaaravo\local\minutes
 * @covers     \block_oksigeniaaravo\local\presenter
 */
#[\PHPUnit\Framework\Attributes\CoversClass(snapshot::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(minutes::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(presenter::class)]
final class snapshot_test extends \advanced_testcase {
    /**
     * An example snapshot, like the one the central sends.
     *
     * @return array
     */
    private function example(): array {
        return [
            'schema' => 'aravo.snapshot.v1',
            'generated_at' => '2026-11-04T09:12:00+00:00',
            'client' => ['provider' => 'Oksigenia support', 'plan' => 'aula'],
            'bag' => [
                'has_bag' => true,
                'balance_minutes' => 225,
                'purchased_minutes' => 600,
                'used_minutes' => 375,
                'low_threshold_minutes' => 120,
                'items' => [
                    ['id' => 'bag-1', 'purchased_on' => '2026-10-28', 'minutes' => 600,
                        'expires_on' => '2027-10-28', 'remaining_minutes' => 225, 'reference' => 'F-1'],
                ],
            ],
            'interventions' => ['month' => '2026-11', 'allowed' => 1, 'used' => 0,
                'max_minutes_each' => 30, 'items' => []],
            'entries' => [
                ['id' => 'e-1', 'date' => '2026-11-04', 'description' => 'Added 12 users', 'minutes' => 30,
                    'type' => 'included', 'included_minutes' => 30, 'bag_minutes' => 0,
                    'out_of_bag_minutes' => 0, 'reference' => null],
            ],
            'monthly_summary' => [
                ['month' => '2026-11', 'interventions_allowed' => 1, 'interventions_used' => 0,
                    'bag_minutes' => 0, 'out_of_bag_minutes' => 0, 'maintenance_minutes' => 60],
            ],
            'links' => ['central_admin' => 'https://example.com/clients/demo'],
        ];
    }

    public function test_minutes_turn_into_a_readable_duration(): void {
        $this->assertEquals('3 h 45 min', minutes::format(225));
        $this->assertEquals('2 h', minutes::format(120));
        $this->assertEquals('45 min', minutes::format(45));
        $this->assertEquals('0 min', minutes::format(0));
        $this->assertEquals('0 min', minutes::format(-10));
    }

    public function test_the_snapshot_reads_the_central_data(): void {
        $snap = new snapshot($this->example());
        $this->assertTrue($snap->has_bag());
        $this->assertEquals('Oksigenia support', $snap->provider());
        $this->assertEquals('aula', $snap->plan());
        $bag = $snap->bag();
        $this->assertEquals(225, $bag['remaining']);
        $this->assertEquals(600, $bag['purchased']);
        $this->assertCount(1, $bag['items']);
        $this->assertCount(1, $snap->entries());
        $this->assertEquals('included', $snap->entries()[0]['type']);
        $this->assertCount(1, $snap->monthly_summary());
        $this->assertEquals('https://example.com/clients/demo', $snap->central_admin_url());
    }

    public function test_the_balance_never_goes_negative(): void {
        $data = $this->example();
        $data['bag']['balance_minutes'] = -30;
        $snap = new snapshot($data);
        $this->assertEquals(0, $snap->bag()['remaining']);
    }

    public function test_without_a_bag_that_section_is_not_shown(): void {
        $data = $this->example();
        $data['bag'] = ['has_bag' => false];
        $snap = new snapshot($data);
        $this->assertFalse($snap->has_bag());
        $context = presenter::card($snap, (object) ['status' => 'ok', 'fetchedat' => time()]);
        $this->assertArrayNotHasKey('bag', $context);
        $this->assertTrue($context['hasrecent']);
    }

    public function test_the_card_shows_the_bag_and_the_intervention(): void {
        $snap = new snapshot($this->example());
        $context = presenter::card($snap, (object) ['status' => 'ok', 'fetchedat' => time()]);
        $this->assertArrayHasKey('bag', $context);
        $this->assertEquals('3 h 45 min', $context['bag']['remaining']);
        $this->assertEquals(38, $context['bag']['percent']);
        $this->assertFalse($context['bag']['low']);
        $this->assertNotEmpty($context['interventionline']);
        $this->assertFalse($context['failed']);
    }

    public function test_a_failed_read_is_flagged_when_there_is_a_previous_copy(): void {
        $snap = new snapshot($this->example());
        $context = presenter::card($snap, (object) ['status' => 'http500', 'fetchedat' => time()]);
        $this->assertTrue($context['failed']);
    }
}
