# Contributing to block_oksigeniaaravo

Thanks for taking the time. This plugin is FOSS and we want it to stay simple and usable. A few things to keep in mind
before opening a PR.

## Code style

- PHP follows the Moodle Coding Style: <https://moodledev.io/general/development/policies/codingstyle>.
- License header: GPL v3 or later, copyright `Oksigenia SL <dev@oksigenia.cc>`.
- All identifiers, comments and commit messages in **English**. Lang files carry the localised strings; everything else
  stays in English.
- The block only reads from the central registry and shows the data. It never writes support data and holds no form:
  recording work is the central's job.

## Translations

We accept new translations as pull requests. To add a locale:

1. Copy `lang/en/block_oksigeniaaravo.php` to `lang/<locale>/block_oksigeniaaravo.php` using a valid Moodle locale code
   (e.g. `ca`, `gl`, `eu`, `pt_br`, `de`).
2. Translate every `$string[...]` value, keeping the keys and the placeholders (`{$a}`, `{$a->...}`) intact.
3. Open a PR with a single commit titled `Add <Language> translation`.

If the plugin reaches the Moodle Marketplace, translations into other languages go through **AMOS**
(<https://lang.moodle.org>). GitHub PRs are for the English strings and for fixes.

## Tests

GitHub Actions runs `moodle-plugin-ci` on every push and pull request, on every supported Moodle branch from 4.1 to 5.3
(plus `main` as an allowed-failure job): validate, lint, code checker, PHPDoc, Mustache, Grunt and PHPUnit. A PR is ready
when that run is green. New behaviour comes with its PHPUnit test.

## Pull requests

- One topic per PR. Mixed feature + refactor PRs get split before review.
- Reference an issue when one exists.
- Keep diffs small.

## Reporting bugs

Open an issue with: Moodle version, PHP version, theme, browser, and a minimal reproduction. A screenshot of the block
and the register page helps.
