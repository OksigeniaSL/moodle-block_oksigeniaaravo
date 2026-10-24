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

use block_oksigeniaaravo\local\central;

/**
 * Tests for the central reader's guards that run without touching the network.
 *
 * @package    block_oksigeniaaravo
 * @category   test
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_oksigeniaaravo\local\central
 */
#[\PHPUnit\Framework\Attributes\CoversClass(central::class)]
final class central_test extends \advanced_testcase {
    public function test_a_non_https_endpoint_is_refused_without_a_request(): void {
        foreach (['http://registro.example/snapshot', 'ftp://x', 'registro.example'] as $url) {
            $result = central::fetch($url, '', 'https://site.example');
            $this->assertSame('badurl', $result['error']);
            $this->assertSame(0, $result['httpstatus']);
            $this->assertNull($result['body']);
        }
    }
}
