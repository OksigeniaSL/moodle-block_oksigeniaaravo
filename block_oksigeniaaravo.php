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

use block_oksigeniaaravo\local\presenter;
use block_oksigeniaaravo\local\snapshot;
use block_oksigeniaaravo\local\store;

/**
 * The block of Aravo: shows the client's management the support hours (the bag, this month's intervention and the last
 * works), read from the central registry. Read only; nobody writes from Moodle.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_oksigeniaaravo extends block_base {
    /**
     * Its title, the provider name.
     *
     * @return void
     */
    public function init() {
        // A clean title for whoever uses it; the provider brand goes small at the foot of the card.
        $this->title = get_string('blocktitle', 'block_oksigeniaaravo');
    }

    /**
     * Where it can be added: the dashboard and the site front page.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['my' => true, 'site' => true, 'course-view' => false, 'mod' => false];
    }

    /**
     * It has global settings, not per-instance ones.
     *
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * Its content: the support hours for whoever may see them, or nothing (so Moodle does not paint the block).
     *
     * @return stdClass
     */
    public function get_content() {
        global $OUTPUT;
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = (object) ['text' => '', 'footer' => ''];
        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }
        if (!has_capability('block/oksigeniaaravo:view', context_system::instance())) {
            return $this->content;
        }
        $snap = snapshot::from_store();
        if (!$snap) {
            $this->content->text = $OUTPUT->render_from_template(
                'block_oksigeniaaravo/content',
                ['empty' => true, 'message' => get_string('nodatayet', 'block_oksigeniaaravo')]
            );
            return $this->content;
        }
        $context = presenter::card($snap, store::get());
        $this->content->text = $OUTPUT->render_from_template('block_oksigeniaaravo/content', $context);
        return $this->content;
    }
}
