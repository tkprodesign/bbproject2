# SITE_AUDIT

## Executive Summary

- The repository is a monolithic PHP/MySQL banking-style site with clear functional separation (public site, customer dashboard, admin panel), but with significant security, secrets-management, and robustness risks that should be treated as high priority before production usage.


## Scope Reviewed

- Core runtime/bootstrap, auth handlers, dashboard/control-panel backends, selected frontend scripts, and schema bootstrap files listed in FULL_SITE_DOCS code archive.


## Findings

### [Critical] Hardcoded secrets and credentials in source
- Observation: Database usernames/passwords, mail credentials, and API keys are directly embedded in PHP/JS, creating immediate compromise risk if repo or logs leak.
- Recommendation: Move secrets to environment variables/secrets manager; rotate all exposed credentials immediately.

### [High] Authentication trust model relies on writable client cookie
- Observation: App authorization is based largely on `login_email` cookie presence/value with limited server-side session hardening, enabling impersonation risk if cookie can be forged/stolen.
- Recommendation: Use server-side session IDs only; enforce secure/HttpOnly/SameSite cookies and server-side user lookup per request.

### [High] No CSRF protection on privileged POST actions
- Observation: Admin operations (credit/debit/KYC actions) accept POST without CSRF token checks.
- Recommendation: Add anti-CSRF tokens + origin/referrer validation for all state-changing routes.

### [High] Potential SQL injection and inconsistent query safety
- Observation: Code mixes prepared statements with string-interpolated SQL in some listing logic.
- Recommendation: Use prepared statements exclusively; ban variable interpolation into SQL.

### [High] Production error display enabled
- Observation: `display_errors` and verbose reporting are enabled in runtime bootstrap files, risking data leakage.
- Recommendation: Disable display_errors in production; log securely with sanitized error output.

### [Medium] Authorization logic duplicated and fragmented
- Observation: Multiple `app.php` files perform overlapping role checks which can drift and produce bypass gaps.
- Recommendation: Centralize auth middleware and role policy checks into a single shared component.

### [Medium] Business logic mixed with rendering
- Observation: Large PHP pages combine querying, mutation logic, and HTML output, reducing maintainability/testability.
- Recommendation: Extract service/repository/controller layers and keep templates presentation-only.

### [Medium] Front-end keys exposed in client JavaScript
- Observation: Finnhub API key is embedded in public JS and can be harvested/abused.
- Recommendation: Proxy third-party requests server-side or use restricted key scopes + rotation.

### [Low] Archive snapshots in active repository tree
- Observation: Large historical snapshots may confuse maintenance and increase accidental deployment surface.
- Recommendation: Move archives outside deploy artifact or explicitly exclude via deployment config.

### [Low] No automated tests/lint pipeline evident
- Observation: No observable unit/integration tests tied to core banking flows.
- Recommendation: Add CI checks: PHP lint/static analysis, integration tests for auth and transaction workflows.


## Positive Notes
- Use of `password_hash`/`password_verify` for login credentials is a good baseline.
- Prepared statements are used in several sensitive paths.
- Role allow-listing exists for control panel separation.


## Recommended Remediation Plan
1. **Immediate (0-2 days):** rotate all exposed secrets; disable production error display; enforce secure cookie flags.
2. **Short term (3-10 days):** add CSRF defense, unify auth middleware, migrate remaining SQL to prepared statements.
3. **Mid term (1-3 weeks):** refactor monolithic handlers into service/controller structure; add automated tests for auth, balance calculations, and admin transaction actions.
4. **Ongoing:** dependency updates, logging/auditing improvements, and security review before each release.
