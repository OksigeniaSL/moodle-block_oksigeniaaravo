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

## Requirements

- Moodle 4.1 to 5.3.
- PHP 7.4 or later.
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
