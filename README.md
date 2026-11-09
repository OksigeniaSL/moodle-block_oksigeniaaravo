# Support hours (Oksigenia Aravo)

A Moodle block that shows a hosting client's management how their support time is spent, inside their own Moodle and
read only:

- the **hours bank**: purchased, used and remaining;
- the **included interventions** of the month and whether they have been used;
- the **work log**: date, what was done, time and how it counts, with a CSV and Excel download.

The data is not kept in Moodle. A **central registry** holds it and does the sums; the block reads a snapshot over HTTPS,
keeps a local copy so it still works when the central does not answer ("Updated at…"), and never writes. Nobody records
work from Moodle.

*Aravo* means "hour" in Guaraní.

## Who sees it

Whoever has the `block/oksigeniaaravo:view` capability, given by default to the `manager` archetype, so a management role
sees it and students do not. Add the block to the dashboard. The link to the central registry is shown to site
administrators only.

The block does not show in the Moodle mobile app: the support hours are read in the browser, also on a phone, where the
card and the register adapt to the screen.

## In «Your work»

When the site has [«Your work» (Oksigenia Tembiapo)](https://github.com/OksigeniaSL/moodle-block_oksigeniatembiapo#readme),
the dashboard block where each service of the site has a door, the support hours add theirs (contract version 2), for
the same people who see the block:

- the door opens the register;
- its state, in one short line with two figures: what is left in the bank and how many tasks were done this week or, in
  a week with none yet, this month's interventions;
- an alert, for today, when the bank is used up or the site reaches a limit of its plan (active users or disk).

It reads only the local copy, never the network, and without «Your work» installed nothing calls it.

## Requirements

- Moodle 4.1 to 5.3.
- PHP 7.4 or later.
- Optional: «Your work» (Oksigenia Tembiapo), for the door (it needs Moodle 4.5 or later).
- Outbound HTTPS to the central registry.

## Install

Copy the plugin to `blocks/oksigeniaaravo` and finish the install as an administrator. Then, under
*Site administration → Plugins → Blocks → Support hours*, set the central registry endpoint and, if the registry asks
for one, an access token. Add the block to the dashboard.

## Settings

| Setting | What it does |
|---|---|
| Central registry endpoint | The full HTTPS URL the block reads from. |
| Access token | Optional. Only for a server the registry does not recognise by its IP. |
| Provider name | The block title when the central does not send one. |
| Refresh on view | When the register page is opened and the local copy is older than this many minutes, it is read again. |

A scheduled task also refreshes the local copy in the background.

## Privacy

The block stores no personal data about the site's users. It keeps a local copy of the support data held in the external
central registry and shows it. See the privacy provider for the declaration.

## Licence

GNU GPL v3 or later. Copyright Oksigenia SL.
