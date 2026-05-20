# Production deployment pipeline

## Overview

GitHub Actions owns CI/security gates and production deployment. Secrets stay in GitHub Actions secrets or protected environments only; no production secret belongs in repository files.

## Pull request checks

Workflow: `.github/workflows/pr-checks.yml`

Runs on pull requests targeting `main`:

- Theme dependency install with `npm ci`
- CSS lint with `npm run lint:css`
- JavaScript lint with `npm run lint:js`
- Theme asset build with `npm run build`
- PHP syntax validation for theme PHP files
- Production dependency audit with `npm audit --omit=dev --audit-level=moderate`
- GitHub dependency review, failing on moderate or higher severity
- Gitleaks secret scan
- CodeQL JavaScript/TypeScript analysis

Recommended branch protection for `main`:

- Require pull request before merge
- Require all PR check jobs to pass
- Require conversation resolution
- Block force pushes
- Restrict who can dismiss reviews

## Production deployment

Workflow: `.github/workflows/deploy.yml`

Runs on pushes to `main` and manual `workflow_dispatch`.

Deployment reuses the existing script:

```bash
./scripts/deploy-theme.sh --target production
```

The workflow performs quality gates before calling the script:

- `npm ci`
- `npm run lint:css`
- `npm run lint:js`
- `npm run build`
- PHP syntax validation
- required deployment secret presence check

The deploy script then builds the theme again and mirrors runtime-safe theme files through `lftp`, using the script's existing exclude rules.

## Required GitHub configuration

Create a protected GitHub environment named `production`.

Recommended environment protection:

- Required reviewers enabled
- Deployment branches limited to `main`
- Environment secrets scoped to `production`

Required secrets:

| Secret | Purpose |
| --- | --- |
| `THREEW_PROD_FTP_HOST` | Production FTP/FTPS/SFTP host |
| `THREEW_PROD_FTP_PORT` | Production port; optional if default protocol port is acceptable |
| `THREEW_PROD_FTP_USER` | Production deploy username |
| `THREEW_PROD_FTP_PASS` | Production deploy password |
| `THREEW_PROD_REMOTE_THEME_DIR` | Remote theme directory, e.g. `public_html/wp-content/themes/3w-2025` |
| `THREEW_PROD_DEPLOY_SCHEME` | `ftp`, `ftps`, or `sftp` |
| `THREEW_PROD_SSL_VERIFY` | `yes` or `no`; prefer `yes` for FTPS |

Do not add `.env`, passwords, deploy keys, or host credentials to git.

## Notes

- `scripts/deploy-theme.sh` loads `.env` for local runs only. GitHub Actions supplies the same variable names from GitHub Secrets.
- The npm audit gate omits development dependencies because the WordPress build toolchain currently reports dev-only advisories that require breaking tool upgrades; production dependency exposure is still gated, while CodeQL, Gitleaks, dependency review, linting, and builds cover PR quality/security.
- The production deploy job is attached to the `production` environment so GitHub can enforce approvals before secrets are exposed to the runner.
- If SFTP key auth is required later, update `scripts/deploy-theme.sh` to support a production-specific key secret materialized into a temporary runner file, then pass `THREEW_SSH_KEY_PATH` to the script.
