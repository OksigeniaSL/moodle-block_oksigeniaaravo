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
 * The full register of support work: the per-month summary, the list of works and the CSV/Excel download. Read only.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use block_oksigeniaaravo\local\presenter;
use block_oksigeniaaravo\local\refresher;
use block_oksigeniaaravo\local\snapshot;
use block_oksigeniaaravo\local\store;

require_login();
$context = context_system::instance();
require_capability('block/oksigeniaaravo:view', $context);

$dataformat = optional_param('dataformat', '', PARAM_ALPHA);
if ($dataformat !== '') {
    require_sesskey();
    $snap = snapshot::from_store();
    $rows = $snap ? presenter::export_rows($snap) : [];
    $columns = presenter::export_columns();
    $keys = array_keys($columns);
    \core\dataformat::download_data(
        'aravo-' . userdate(time(), '%Y%m%d'),
        $dataformat,
        $columns,
        new ArrayIterator($rows),
        function ($row) use ($keys) {
            $out = [];
            foreach ($keys as $key) {
                $out[] = $row[$key] ?? '';
            }
            return $out;
        }
    );
    die;
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/oksigeniaaravo/index.php'));
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('registertitle', 'block_oksigeniaaravo'));
$PAGE->set_heading(get_string('registertitle', 'block_oksigeniaaravo'));

refresher::refresh_if_stale();

echo $OUTPUT->header();
$snap = snapshot::from_store();
if (!$snap) {
    echo $OUTPUT->notification(get_string('nodatayet', 'block_oksigeniaaravo'), 'info');
} else {
    $data = presenter::page($snap, store::get(), is_siteadmin());
    echo $OUTPUT->render_from_template('block_oksigeniaaravo/register', $data);
}
echo $OUTPUT->footer();
