# Changelog

All notable changes to this plugin are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the plugin uses a `major.minor.patch` release string.

## [Unreleased]

## [0.9.0] - 2026-11-14

### Changed
- In the card, each of the latest tasks shows in two lines at most; the register keeps the whole text.

### Fixed
- The download's file name keeps the leading zero of the day: from the 1st to the 9th of each month it was
  `aravo-2026112` (Moodle's `userdate()` drops that zero by default) and is now `aravo-20261102`.

## [0.8.0] - 2026-11-09

### Added
- A door in «Your work» (`block_oksigeniatembiapo`, contract version 2) through
  `block_oksigeniaaravo_oksigenia_work()` in `lib.php`: for whoever may see the block, it opens the register and says in
  one short line, with two figures, what is left in the bank (or that the balance is low) and how many tasks were done
  this week, counted from the first day of the week of the person's calendar, or, in a week with none yet, this month's
  interventions. It gives an alert for today when the bank is used up or the site reaches a limit of its plan. It reads
  only the local copy.
- Before the first data arrives, the block and the register say what will show there and when; a site administrator
  also sees what is missing (the central address, with a link to the settings) or why the last read did not work.

### Fixed
- The block's settings page now shows in the site administration (*Plugins → Blocks → Support hours*). The block said it
  had no site settings (`has_config()` was false), so Moodle never loaded `settings.php`, and the central address could
  only be set from the command line.

### Changed
- A figure and its unit never split across lines: non-breaking spaces in the durations, the disk and the percentage.
- The provider's name is marked `translate="no"`, so the browser's translator leaves it as it is.
- The README says that the block does not show in the Moodle mobile app and that the support hours are read in the
  browser.

## [0.7.1] and earlier - 2026-10-26

### Fixed
- A half-moon gauge at 0% no longer shows a stray second dot at the far end of the arc (the round-capped
  `stroke-dasharray` pattern repeated); the gap is now longer than the path so only the start dot shows.

### Added
- "At no cost" cards next to the ring: the contract's included work (interventions, the welcome allowance and plan
  maintenance) as a headline figure with the accumulated total, its period, a split bar by type and this month's
  amount; and, apart, the discretionary "Courtesy" (work Oksigenia chooses not to charge, outside the contract), shown
  when the central sends `courtesy` entries. Replaces the small "included" chip.

### Added
- Central notice: when the snapshot carries a `notice` (a `label` and a `text`), the block and the register show it on
  top — also when the card is folded. It is meant for the central to mark a site (for example a demo showing sample
  data); absent on real clients, so nothing shows. The text comes ready from the central; the block only displays it.

### Changed
- On load the bag ring and the usage gauges sweep up from zero to their value (CSS animation, `prefers-reduced-motion`).

### Changed
- Visual redesign of the card: the bag is a ring (with the emblem's ñandutí lace inside and the balance at the
  centre), this month's interventions are pips, the plan usage shows as half-moon gauges, and there is a subtitle.
  States carry a word as well as a colour. All colours come from the theme variables (light, dark and each client's
  brand), and the layout adapts to the block's own width with container queries. Oksigenia's symbol appears in the
  foot signature and in the collapsed summary. Inline SVGs, no CDN. The register page keeps its earlier look.

### Added
- The card opens on entry, shows a one-time note that only the management sees it, then folds itself to a compact
  one-line summary (bag balance and this month's interventions) that the manager can re-open with a click. Progressive
  enhancement (an inline AMD module): with JavaScript off the card stays fully shown. Honours `prefers-reduced-motion`.
- "Included at no cost" figure: the support time done for the client that does not discount the bag (included
  interventions, the first-month courtesy and plan maintenance), so the value of the work is visible.

### Changed
- The bars rise from zero on load and the card eases in (CSS only, guarded by `prefers-reduced-motion`); the figure
  carries more weight and the bars a subtle depth and sheen that read the same in light and dark.

### Added
- Plan usage: a section with active users (last 30 days) and disk, each against the client's limit, measured locally
  and compared to the limits the central sends per client (`client.limits`). Shown only when limits are set.

## [0.1.0]

### Added
- First skeleton of the block: reads a snapshot from the central registry over HTTPS, keeps a local copy, and shows the
  hours bank, this month's included intervention and the latest work on the dashboard card.
- Register page with the per-month summary, the full work log and a CSV/Excel download.
- Global settings (central endpoint, optional token, provider name, refresh interval), a scheduled task to refresh the
  local copy, capabilities, and the privacy provider.
- PHPUnit tests for the snapshot reading, the duration formatting, the presenter, and every branch of keeping the local
  copy from a central read; a Behat scenario for the manager's walkthrough.
- Continuous integration from Moodle 4.1 to 5.3.
