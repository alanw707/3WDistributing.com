# Newsletter popup dismissal fix

## Production defect

The production-only `3w-newsletter-popup` plugin shows its modal seven seconds after cookie consent is resolved. The deployed 1.0.1 script has three accessibility and interaction defects:

- Escape does not dismiss the modal.
- Clicking the dimmed backdrop does not dismiss it.
- Focus remains on the page behind the modal and can tab out of the dialog despite `aria-modal="true"`.

While the modal is open, its full-viewport scrim correctly prevents activation of controls behind it. Without Escape or backdrop dismissal, however, the small close button is the only exit. The popup is absent from cart and checkout pages, so checkout is not blocked.

## Implemented behavior

`deploy/production/3w-newsletter-popup/assets/newsletter-popup.js` is a reviewed replacement for the live 1.0.1 asset. It preserves the existing delay, consent gate, signup request, design and 30-day suppression while adding:

- Escape dismissal;
- backdrop-click dismissal without activating the control underneath;
- focus placement on the first modal control;
- Tab/Shift+Tab wrapping inside the dialog;
- focus restoration after dismissal.

The replacement is verified by `tests/e2e/newsletter-popup.spec.js` at desktop and mobile viewports.

## Deployment and rollback

The production workflow reruns the desktop/mobile popup regression, deploys the theme as before, then uses `scripts/derive-newsletter-remote-path.sh` to derive the shop plugin path from relative, site-root-relative or absolute production theme directories. It validates the expected `wp-content/themes/3w-2025` path components and inserts only the bounded `shop` site directory. `scripts/deploy-file.sh` then:

1. downloads the current remote JS as a rollback artifact;
2. refuses to overwrite any hash except the reviewed original or this exact override (the latter makes retries idempotent);
3. rejects remote paths containing empty, `.` or `..` segments;
4. uploads exactly one file (no mirror and no delete);
5. downloads it again and verifies its SHA-256;
6. automatically restores and verifies the backup if upload/read-back verification fails. A later public-cache verification failure intentionally leaves the verified origin file in place; use the archived backup for manual rollback only if the new origin bytes themselves are unwanted.

The workflow then verifies the public asset through a cache-busting query and archives before/after files for 14 days.

To roll back, upload the workflow artifact `newsletter-popup.js.before` to:

```text
wp-content/plugins/3w-newsletter-popup/assets/newsletter-popup.js
```

The production script is still referenced as `?ver=1.0.1` and Cloudflare caches it for seven days. The first workflow run is expected to turn red after a successful origin upload when that canonical URL is still stale. This failure deliberately leaves the verified fix at the origin. Purge only this exact URL and rerun the workflow (or wait for its TTL); the idempotent override hash is allowlisted for that rerun. A green deployment therefore cannot be mistaken for a customer-visible fix:

```text
https://shop.3wdistributing.com/wp-content/plugins/3w-newsletter-popup/assets/newsletter-popup.js?ver=1.0.1
```
