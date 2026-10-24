# Security policy

## Reporting a vulnerability

If you find a security issue in `block_oksigeniaaravo`, **do not open a public GitHub issue**. Use one of these instead:

1. **GitHub private vulnerability reporting** (preferred):
   <https://github.com/OksigeniaSL/moodle-block_oksigeniaaravo/security/advisories/new>
   This sends the report straight to the maintainer with no public exposure.

2. **Email**: `dev@oksigenia.cc` (PGP key
   [fingerprint `4D0E 67BD 1935 3CE2 A8E8  267F 8290 9111 546B AD97`](https://oksigenia.com/contacto_publickey.asc),
   public key on [oksigenia.com](https://oksigenia.com/contacto_publickey.asc)).

Please include:

- Affected version(s) of the plugin.
- Moodle version, PHP version, theme.
- A minimal proof of concept or reproduction steps.
- Your assessment of impact and any suggested mitigation.

## Response timeline

| Step | Target |
|---|---|
| Acknowledgement of the report | Within 5 business days |
| Initial assessment + severity rating | Within 10 business days |
| Patched release | Depends on severity; critical issues get out-of-band releases |

## Supported versions

Security patches land on the latest released line. Older minor versions are not back-patched.
