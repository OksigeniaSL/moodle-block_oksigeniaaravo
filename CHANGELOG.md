# Changelog

All notable changes to this plugin are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the plugin uses a `major.minor.patch` release string.

## [Unreleased]

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
