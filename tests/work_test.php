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

use block_oksigeniaaravo\local\presenter;
use block_oksigeniaaravo\local\store;
use block_oksigeniaaravo\local\work;

/**
 * Tests for the door of the support hours in «Your work» (block_oksigeniatembiapo, contract version 2), and for what the
 * block says before the first data arrives.
 *
 * @package    block_oksigeniaaravo
 * @category   test
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_oksigeniaaravo\local\work
 * @covers     \block_oksigeniaaravo\local\presenter
 */
#[\PHPUnit\Framework\Attributes\CoversClass(work::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(presenter::class)]
final class work_test extends \advanced_testcase {
    /** @var int A Wednesday, 4 November 2026 at noon UTC: the "now" of the tests. */
    const NOW = 1793793600;

    /**
     * An example snapshot, like the one the central sends.
     *
     * @return array
     */
    private function example(): array {
        return [
            'schema' => 'aravo.snapshot.v1',
            'generated_at' => '2026-11-04T09:12:00+00:00',
            'client' => ['provider' => 'Oksigenia support', 'plan' => 'aula',
                'limits' => ['active_users' => 200, 'disk_gb' => 15]],
            'bag' => [
                'has_bag' => true,
                'balance_minutes' => 225,
                'purchased_minutes' => 600,
                'used_minutes' => 375,
                'low_threshold_minutes' => 120,
                'items' => [],
            ],
            'interventions' => ['month' => '2026-11', 'allowed' => 1, 'used' => 0,
                'max_minutes_each' => 30, 'items' => []],
            'entries' => [],
            'monthly_summary' => [],
        ];
    }

    /**
     * Stores a snapshot as the local copy, as a good read from the central would.
     *
     * @param array $data The snapshot.
     * @return void
     */
    private function store(array $data): void {
        store::save('ok', 200, json_encode($data), time());
    }

    /**
     * A work entry of the register, on a given day.
     *
     * @param string $date The day, YYYY-MM-DD.
     * @return array
     */
    private function entry(string $date): array {
        return ['id' => 'e-' . $date, 'date' => $date, 'description' => 'Weekly update', 'minutes' => 30,
            'type' => 'maintenance', 'included_minutes' => 0, 'bag_minutes' => 0, 'out_of_bag_minutes' => 0,
            'reference' => null];
    }

    /**
     * A user with the manager role on the whole site, as the management of a client has.
     *
     * @return \stdClass
     */
    private function manager(): \stdClass {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        role_assign($roleid, $user->id, \context_system::instance()->id);
        return $user;
    }

    public function test_whoever_may_not_see_the_hours_gets_no_door(): void {
        $this->resetAfterTest();
        $this->store($this->example());
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertSame([], work::for_user((int) $user->id, self::NOW));
        $this->assertSame([], work::for_user(0, self::NOW));
    }

    public function test_there_is_no_door_before_the_first_data(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->assertSame([], work::for_user(2, self::NOW));
    }

    public function test_the_management_gets_the_door_with_its_state(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->store($this->example());
        $user = $this->manager();
        $this->setUser($user);
        require_once($CFG->dirroot . '/blocks/oksigeniaaravo/lib.php');
        $doors = block_oksigeniaaravo_oksigenia_work((int) $user->id);
        $this->assertCount(1, $doors);
        $door = $doors[0];
        $this->assertSame('block_oksigeniaaravo', $door['key']);
        $this->assertSame('Support hours', $door['title']);
        $this->assertStringEndsWith('/blocks/oksigeniaaravo/index.php', $door['url']);
        $this->assertStringContainsString("3\u{00A0}h\u{00A0}45\u{00A0}min in the bank", $door['status']);
        $this->assertStringContainsString('0 of 1 interventions this month', $door['status']);
        // A healthy bag and no plan limit reached: nothing to sort out, and never an «all fine».
        $this->assertSame([], $door['alerts']);
    }

    public function test_a_used_up_bag_is_an_alert_and_a_low_one_is_said_in_the_state(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $data = $this->example();
        $data['bag']['balance_minutes'] = 0;
        $this->store($data);
        $door = work::for_user(2, self::NOW)[0];
        $this->assertCount(1, $door['alerts']);
        $this->assertSame('today', $door['alerts'][0]['level']);
        $this->assertSame('The hours bank is used up', $door['alerts'][0]['text']);

        $data['bag']['balance_minutes'] = 60;
        $this->store($data);
        $door = work::for_user(2, self::NOW)[0];
        $this->assertSame([], $door['alerts']);
        $this->assertStringStartsWith("Low balance: 1\u{00A0}h in the bank", $door['status']);
    }

    public function test_a_plan_limit_reached_is_an_alert(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->store($this->example());
        // 200 active users of 200, and 1 GB of 15: only the users have reached the limit.
        store::save_usage(200, 1024);
        $door = work::for_user(2, self::NOW)[0];
        $this->assertCount(1, $door['alerts']);
        $this->assertSame('today', $door['alerts'][0]['level']);
        $this->assertSame('Active users have reached the plan limit: 200 of 200', $door['alerts'][0]['text']);
    }

    public function test_the_work_of_this_week_is_added_up_from_the_first_day_of_the_calendar(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $data = $this->example();
        // Newest first, as the central sends them: Wednesday, Monday, the Sunday before, and the week before.
        $data['entries'] = [$this->entry('2026-11-04'), $this->entry('2026-11-02'), $this->entry('2026-11-01'),
            $this->entry('2026-10-28')];
        $this->store($data);

        // A calendar whose week starts on Monday: Monday and Wednesday. Two figures at most: the bag and the week's work,
        // so this month's interventions are left for the register.
        set_config('calendar_startwday', 1);
        $status = work::for_user(2, self::NOW)[0]['status'];
        $this->assertStringStartsWith("3\u{00A0}h\u{00A0}45\u{00A0}min in the bank", $status);
        $this->assertStringContainsString('2 tasks this week', $status);
        $this->assertStringNotContainsString('interventions', $status);

        // One whose week starts on Sunday: that Sunday too.
        set_config('calendar_startwday', 0);
        $this->assertStringContainsString('3 tasks this week', work::for_user(2, self::NOW)[0]['status']);

        // Only one: the singular.
        $data['entries'] = [$this->entry('2026-11-04'), $this->entry('2026-10-28')];
        $this->store($data);
        $this->assertStringContainsString('1 task this week', work::for_user(2, self::NOW)[0]['status']);

        // Nothing this week: the state says nothing about it, and the month's interventions take its place.
        $data['entries'] = [$this->entry('2026-10-28')];
        $this->store($data);
        $status = work::for_user(2, self::NOW)[0]['status'];
        $this->assertStringNotContainsString('this week', $status);
        $this->assertStringContainsString('0 of 1 interventions this month', $status);
    }

    public function test_without_a_bag_or_interventions_the_door_has_no_state(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $data = $this->example();
        $data['bag'] = ['has_bag' => false];
        $data['interventions'] = ['month' => '2026-11', 'allowed' => 0, 'used' => 0, 'items' => []];
        $this->store($data);
        $door = work::for_user(2, self::NOW)[0];
        $this->assertArrayNotHasKey('status', $door);
        $this->assertSame('Support hours', $door['title']);
    }

    public function test_the_door_fits_the_board_of_your_work(): void {
        if (!class_exists('\block_oksigeniatembiapo\local\board')) {
            $this->markTestSkipped('«Your work» (block_oksigeniatembiapo) is not installed on this site.');
        }
        $this->resetAfterTest();
        $this->setAdminUser();
        $data = $this->example();
        $data['bag']['balance_minutes'] = 0;
        $this->store($data);
        $board = \block_oksigeniatembiapo\local\board::build(['block_oksigeniaaravo' => work::for_user(2, self::NOW)]);
        $this->assertTrue($board['hasdoors']);
        $this->assertSame('Support hours', $board['doors'][0]['title']);
        $this->assertTrue($board['hasalerts']);
        $this->assertSame('The hours bank is used up', $board['alerts'][0]['text']);
    }

    public function test_before_the_first_data_the_block_says_what_will_show_and_tells_the_admin_what_is_missing(): void {
        $this->resetAfterTest();
        // Whoever is not an administrator: what will show there and when, nothing else.
        $plain = presenter::empty_state(null, false);
        $this->assertTrue($plain['empty']);
        $this->assertStringContainsString('support hours', $plain['message']);
        $this->assertArrayNotHasKey('adminhint', $plain);

        // An administrator, with no central address: the hint and the settings.
        set_config('centralendpoint', '', 'block_oksigeniaaravo');
        $setup = presenter::empty_state(null, true);
        $this->assertStringContainsString('address of the central registry', $setup['adminhint']);
        $this->assertStringContainsString('section=blocksettingoksigeniaaravo', $setup['adminurl']);

        // An administrator, with the address and a failed read: why, and that it tries again by itself.
        set_config('centralendpoint', 'https://central.example.com/v1/snapshot', 'block_oksigeniaaravo');
        $failed = presenter::empty_state((object) ['status' => 'http403'], true);
        $this->assertStringContainsString('http403', $failed['adminhint']);
        $this->assertArrayNotHasKey('adminurl', $failed);

        // An administrator, with the address and no read yet: nothing to fix.
        $this->assertArrayNotHasKey('adminhint', presenter::empty_state(null, true));
    }
}
