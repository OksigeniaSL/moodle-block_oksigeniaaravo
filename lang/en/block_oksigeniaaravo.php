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
 * Strings for block_oksigeniaaravo.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Support hours (Oksigenia Aravo)';

// Capabilities.
$string['oksigeniaaravo:addinstance'] = 'Add a support hours block';
$string['oksigeniaaravo:myaddinstance'] = 'Add a support hours block to the dashboard';
$string['oksigeniaaravo:view'] = 'See the support hours: the block, the register and the download';

// Settings.
$string['centralendpoint'] = 'Central registry endpoint';
$string['centralendpoint_desc'] = 'The full HTTPS URL the block reads the support data from.';
$string['centraltoken'] = 'Access token (optional)';
$string['centraltoken_desc'] = 'Only needed for a server that the central does not recognise by its IP. Leave it empty on a managed server.';
$string['provider'] = 'Provider name';
$string['provider_desc'] = 'Shown as the block title when the central does not send one, for example "Oksigenia support".';
$string['pagettl'] = 'Refresh on view (minutes)';
$string['pagettl_desc'] = 'When the register page is opened and the local copy is older than this, it is read again from the central.';

// Scheduled task.
$string['task_refresh'] = 'Read the support hours from the central registry';

// A month and year, as a date format for userdate (%B is the month name).
$string['monthyearformat'] = '%B %Y';

// Durations.
$string['durationhm'] = '{$a->h} h {$a->m} min';
$string['durationh'] = '{$a->h} h';
$string['durationm'] = '{$a->m} min';

// Block and register.
$string['blocktitle'] = 'Support hours';
$string['subtitle'] = 'The support time spent on your services · only the management sees it';
$string['bagtitle'] = 'Hours bank';
$string['bagline'] = '{$a->remaining} left of {$a->purchased} · {$a->used} used';
$string['bagof'] = 'of {$a}';
$string['bagstatuslow'] = 'Low balance';
$string['bagstatusempty'] = 'Bag used up';
$string['couldnotupdate'] = 'The data could not be updated; showing the last copy.';
$string['includedtitle'] = 'Included at no cost';
$string['recenttitle'] = 'Latest work';
$string['fullregister'] = 'Full register';
$string['updatedat'] = 'Updated on {$a->date} at {$a->time}';
$string['nodatayet'] = 'There is nothing to show yet.';
$string['registertitle'] = 'Support hours';
$string['summarytitle'] = 'By month';
$string['worktitle'] = 'Work log';
$string['centraladmin'] = 'Open in the central registry (administrators)';

// Collapsible card.
$string['summarybag'] = '{$a} in the bank';
$string['summarymonth'] = '{$a->used} of {$a->allowed} this month';
$string['summarydefault'] = 'See the support provided';
$string['cardintro'] = 'Only the platform management sees this: the support time we spend on your services.';

// Plan usage.
$string['usagetitle'] = 'Plan usage';
$string['usageusers'] = 'Active users (30 days)';
$string['usageusersof'] = '{$a->used} of {$a->limit}';
$string['usagedisk'] = 'Disk';
$string['usagediskof'] = '{$a->used} GB of {$a->limit} GB';
$string['percentformat'] = '{$a}%';

// This month's included interventions.
$string['interventionstitle'] = 'Included interventions';
$string['interventionline'] = 'Interventions for {$a->month}: {$a->used} of {$a->allowed}';
$string['interventionmonth'] = 'Interventions for {$a}';

// Tables.
$string['nofn'] = '{$a->a} of {$a->b}';
$string['colmonth'] = 'Month';
$string['colinterventions'] = 'Included interventions';
$string['colbag'] = 'Bag';
$string['coloutofbag'] = 'Out of bag';
$string['colmaintenance'] = 'Maintenance';
$string['coldate'] = 'Date';
$string['coldescription'] = 'Work';
$string['colminutes'] = 'Minutes';
$string['coltype'] = 'Counts as';
$string['colincluded'] = 'Included';
$string['colreference'] = 'Reference';

// Kinds of work.
$string['type_included'] = 'Included intervention';
$string['type_bag'] = 'From the bag';
$string['type_out_of_bag'] = 'Out of bag';
$string['type_maintenance'] = 'Plan maintenance';

// Privacy.
$string['privacy:metadata:central_registry'] = 'The block shows support data held in the central registry of Oksigenia, and keeps a local copy so it works when the central does not answer. It stores no personal data about the site\'s users.';
$string['privacy:metadata:central_registry:site'] = 'The site address is sent so the central knows which client is asking.';
