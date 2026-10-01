# Velmora Production Launch Readiness

**Started:** 2026-10-01  
**Current state:** DEMO / pre-launch  
**Rule:** The DEMO presentation and search-engine blocking stay in place until the P0 launch gates below are cleared.

## Launch principle

Moving out of demo mode is not a visual-label change. Production launch requires customer authentication, operational access, database safety, deployment, privacy, legal/compliance, monitoring, backup/recovery, and public discovery to be ready together.

The launch process is intentionally staged so the site cannot become publicly represented as production-ready before the underlying controls are ready.

---

## P0 — Must be cleared before DEMO mode is removed

### 1. Customer authentication trust model — BLOCKER

**Current finding:** Customer dashboard identity is still reconstructed from the client-side `login_email` cookie. The cookie has Secure/HttpOnly/SameSite protections, but the email itself remains a client-held identity value.

**Required production state:**
- Authenticated customer identity comes from a server-side session, not from a writable email cookie.
- Session is regenerated at login and invalidated at logout/password reset/security events as appropriate.
- "Remember me" uses an opaque, revocable token rather than storing customer identity as the trust credential.
- Dashboard authorization verifies the active user server-side on every authenticated request.
- Session expiration and concurrent-session policy are explicitly defined.

**Do not launch until this is replaced and tested.**

### 2. Database/schema mutation endpoint — HARDENED 2026-10-01

**Previous finding:** `create_tables.php` was explicitly included in the public-route allow-list and could be reached as a web route.

**Action completed:**
- `create_tables.php` is now CLI-only and returns 404 for web requests.
- `/create_tables` and `/create_tables.php` were removed from the public-route allow-list.
- GitHub deployment can still execute the migration with PHP CLI.

### 3. Production data separation and cleanup

Before launch:
- Identify all demo/test customers and transactions.
- Decide which records are legitimate production records and which must be removed or moved to a non-production database.
- Take a verified full database backup before any cleanup.
- Confirm no customer-specific hardcoded behavior remains.
- Confirm all balances derive from authoritative transaction/account records.
- Run reconciliation checks after cleanup.

No production-data cleanup should be performed without a verified backup.

### 4. Legal and regulatory launch authorization

Before removing the demo representation:
- Confirm the legal entity operating Velmora.
- Confirm the jurisdictions in which the service will operate.
- Verify that all banking, deposit, lending, card, transfer, FX, insurance/deposit-protection, address, regulatory and licensing claims shown publicly are accurate for that entity.
- Review Privacy Policy, Terms, Cookie Policy, AML/KYC disclosures and customer-contact details.
- Remove or rewrite any claim that cannot be substantiated.

This is a launch gate, not a design task.

### 5. Security review of money-moving workflows

Production sign-off required for:
- Transfers and transfer approval.
- Deposits/credits and withdrawals/debits.
- FX trades.
- Ledger adjustments.
- KYC decisions.
- Account/customer status changes.
- Password reset/change.
- Staff mail and support actions.

For each mutation confirm:
- authenticated role/customer;
- CSRF protection;
- server-side authorization;
- input validation;
- prepared SQL;
- audit trail;
- safe failure behavior;
- no duplicate submission/double posting;
- correct balance calculation.

### 6. Staff backend security

Current three roles remain separate:
- Support
- Admin
- Master

Before launch verify:
- credentials are environment/secret managed;
- no real credentials are committed;
- role isolation cannot be bypassed by changing the URL;
- rate limiting/lockout works;
- sessions expire correctly;
- logout invalidates access;
- sensitive actions are logged;
- master credentials are rotated before launch.

---

## P1 — Production readiness before public indexing

### Search/indexing

Current protections intentionally remain:
- Root homepage uses `noindex, nofollow, noarchive`.
- `robots.txt` currently contains `Disallow: /`.
- Customer/dashboard/backend pages remain `noindex,nofollow`.
- DEMO markers remain visible on canonical customer/public surfaces.

At launch:
- Public marketing/legal/resource pages may become indexable.
- Login, signup, password recovery, dashboard, staff login and backend panels should remain non-indexable.
- Replace blanket `robots.txt Disallow: /` with a production robots policy.
- Add/verify canonical URLs and sitemap only after public pages are approved.

### HTTPS and browser security

Verify on the live host:
- HTTPS forced site-wide.
- HSTS decision documented.
- Secure session cookies always enabled in production.
- Content Security Policy reviewed.
- X-Frame-Options/frame-ancestors.
- X-Content-Type-Options.
- Referrer-Policy.
- Permissions-Policy where appropriate.
- No mixed content.

### Email

Verify production delivery for:
- password reset;
- transfer notices;
- deposit/withdrawal notices;
- support/staff mail;
- security notifications.

Also verify SPF, DKIM and DMARC for the production domain and ensure customer-facing sender addresses are monitored appropriately.

### Database

Before launch:
- Automated backup schedule enabled.
- At least one restore test performed.
- Backup retention defined.
- Production DB credentials rotated.
- DB user granted only required privileges where practical.
- Migration procedure tested against a staging/backup copy.
- Database timezone/financial timestamp policy documented.

### Monitoring and incident response

Prepare:
- uptime monitoring;
- PHP/application error logging;
- failed-login monitoring;
- backend security-event review;
- database/storage capacity alerts;
- deployment failure alerting;
- contact/escalation path for incidents.

---

## P2 — Product and UX launch checks

### Customer experience

Test end-to-end on desktop and mobile:
- signup;
- login/logout;
- password recovery;
- account opening;
- KYC;
- account overview;
- statements;
- transaction detail;
- transfers;
- FX;
- beneficiaries;
- notifications;
- profile/security;
- support;
- Smartsupp.

### Public site

Review:
- all navigation;
- mobile layout;
- contact information;
- branch/location claims;
- support channels;
- forms;
- 404 handling;
- accessibility basics;
- legal footer links;
- favicon/social metadata;
- page titles/descriptions.

### Performance

Check:
- image sizes and formats;
- cache headers;
- unnecessary cache-busting query strings;
- third-party scripts/fonts;
- mobile page weight;
- PHP/opcache behavior.

---

## Launch switch — DO NOT FLIP YET

The final launch step will be deliberately small after all gates pass:

1. Create a verified production backup/snapshot.
2. Freeze risky changes during the launch window.
3. Deploy the final production configuration.
4. Remove public/customer-facing DEMO wording.
5. Keep internal/backend and authenticated pages non-indexable.
6. Change `robots.txt` from blanket blocking to the approved production policy.
7. Make approved public pages indexable.
8. Run production smoke tests.
9. Verify email, signup/login, support and core banking views.
10. Monitor logs/availability immediately after launch.

---

## Changes logged

### 2026-10-01 — Launch preparation opened
- Production launch-readiness process established.
- DEMO mode intentionally kept active.
- Search indexing intentionally kept blocked.
- Identified customer cookie-based identity as a P0 blocker.
- Closed public web access to `create_tables.php`.
- Removed migration routes from the public allow-list.
