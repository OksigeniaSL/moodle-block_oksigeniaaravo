# Changelog

All notable changes to this plugin are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the plugin uses a `major.minor.patch` release string.

## [Unreleased]

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
