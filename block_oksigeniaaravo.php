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
        $this->add_card_behaviour();
        return $this->content;
    }

    /**
     * Makes the card open on entry, show a one-time note, then fold itself to a compact summary the manager can
     * re-open. It is a progressive enhancement: with JavaScript off, the card simply stays fully shown.
     *
     * @return void
     */
    private function add_card_behaviour() {
        // Under Behat the baseline (fully shown card) is what the walkthrough asserts, so skip the auto-fold there.
        if (defined('BEHAT_SITE_RUNNING')) {
            return;
        }
        // An inline AMD module, so no build file is needed and there is no inline script in the markup.
        $this->page->requires->js_amd_inline(<<<'JS'
require([], function() {
    var roots = document.querySelectorAll('.block-oksigeniaaravo[data-aravo-summary]');
    Array.prototype.forEach.call(roots, function(root) {
        if (root.dataset.aravoReady) {
            return;
        }
        root.dataset.aravoReady = '1';
        var detail = root.querySelector('.aravo-detail');
        if (!detail) {
            return;
        }
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'aravo-toggle';
        toggle.setAttribute('aria-expanded', 'true');
        var main = document.createElement('span');
        main.className = 'aravo-toggle-main';
        var oks = document.createElement('span');
        oks.className = 'aravo-oks';
        oks.setAttribute('aria-hidden', 'true');
        var text = document.createElement('span');
        text.className = 'aravo-toggle-text';
        text.textContent = root.dataset.aravoSummary || '';
        main.appendChild(oks);
        main.appendChild(text);
        var chevron = document.createElement('span');
        chevron.className = 'aravo-toggle-chevron';
        chevron.setAttribute('aria-hidden', 'true');
        toggle.appendChild(main);
        toggle.appendChild(chevron);
        root.insertBefore(toggle, detail);

        var wrap = document.createElement('div');
        wrap.className = 'aravo-detail-wrap';
        root.insertBefore(wrap, detail);
        wrap.appendChild(detail);

        var open = true;
        var timer = null;
        var acted = false;

        var setOpen = function(value, animate) {
            open = value;
            toggle.setAttribute('aria-expanded', value ? 'true' : 'false');
            root.classList.toggle('aravo-collapsed', !value);
            if (reduce || !animate) {
                wrap.style.height = value ? 'auto' : '0px';
                return;
            }
            var from = wrap.offsetHeight;
            if (value) {
                wrap.style.height = 'auto';
                var to = wrap.offsetHeight;
                wrap.style.height = from + 'px';
                void wrap.offsetHeight;
                wrap.style.height = to + 'px';
                var settle = function() {
                    wrap.style.height = 'auto';
                    wrap.removeEventListener('transitionend', settle);
                };
                wrap.addEventListener('transitionend', settle);
            } else {
                wrap.style.height = from + 'px';
                void wrap.offsetHeight;
                wrap.style.height = '0px';
            }
        };

        var stop = function() {
            if (timer) {
                window.clearTimeout(timer);
                timer = null;
            }
        };

        toggle.addEventListener('click', function() {
            acted = true;
            stop();
            setOpen(!open, true);
        });
        ['pointerenter', 'focusin'].forEach(function(name) {
            root.addEventListener(name, function() {
                if (!acted) {
                    acted = true;
                    stop();
                }
            });
        });

        var delay = 6000;
        var seen = false;
        try {
            seen = window.localStorage.getItem('block_oksigeniaaravo-seen') === '1';
        } catch (e) {
            seen = true;
        }
        var intro = root.dataset.aravoIntro;
        if (!seen && intro) {
            var note = document.createElement('div');
            note.className = 'aravo-intro';
            note.setAttribute('role', 'status');
            note.textContent = intro;
            root.insertBefore(note, wrap);
            delay = 9000;
            window.setTimeout(function() {
                note.classList.add('aravo-intro-out');
            }, delay - 1400);
            window.setTimeout(function() {
                if (note.parentNode) {
                    note.parentNode.removeChild(note);
                }
            }, delay);
            // Mark it seen only after a stable view, so an intermediate redirect does not consume the first visit.
            window.setTimeout(function() {
                try {
                    window.localStorage.setItem('block_oksigeniaaravo-seen', '1');
                } catch (e) {
                    void e;
                }
            }, 3000);
        }

        setOpen(true, false);
        timer = window.setTimeout(function() {
            if (!acted) {
                setOpen(false, true);
            }
        }, delay);
    });
});
JS);
    }
}
