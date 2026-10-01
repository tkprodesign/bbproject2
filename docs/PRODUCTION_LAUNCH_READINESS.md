# Velmora Production Launch Readiness

**Started:** 2026-10-01  
**Current state:** Management review / pre-launch  
**Rule:** Visible demo presentation has been removed for management review. Search-engine blocking remains in place until external/public launch approval.

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

Current review protections intentionally remain:
- Root homepage uses `noindex, nofollow, noarchive`.
- `robots.txt` currently contains `Disallow: /`.
- Customer/dashboard/backend pages remain `noindex,nofollow`.
- Visible DEMO markers have been removed from canonical public, customer and backend surfaces.

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


## P0 — Product/content truth audit — OPEN

A production launch cannot be completed by deleting DEMO labels. Canonical public pages must be reconciled against the products, pricing, legal position, support channels and physical presence that actually exist at launch.

### Initial canonical-page audit — 2026-10-01

| Page | Current launch status | Required action |
| --- | --- | --- |
| Personal Banking | Structurally usable | Verify every advertised account/payment capability against the final product. Replace generic product copy where actual product names, eligibility or terms exist. |
| Business Banking | Structurally usable | Verify business account, payment, beneficiary and FX capabilities. Add actual product/service details where approved. |
| Credit Cards | Incomplete product content | Current page intentionally avoids actual pricing, eligibility, limits and terms. Either supply approved card products/terms or keep cards unavailable from launch navigation. |
| Loans & Financing | Demo logic present | Replace demo calculator assumptions, demo wording, eligibility logic, pricing/fees and application terms with approved lending data before launch. |
| Online Banking | Demo/review wording remains | Rewrite demo-era explanatory copy into customer-facing production language after authentication and transaction workflows pass launch review. |
| International Banking & FX | Structurally usable | Verify supported currencies, FX availability, fees/margins, transfer jurisdictions and operational limits before publication. |
| About Velmora | Incomplete institutional content | Replace product-design philosophy copy with verified company/institution profile, history, ownership/leadership and approved institutional information where applicable. |
| Contact | Requires verification | Verify support phone/email, physical address, service hours and escalation channels. Do not publish unverified premises or hours. |
| ATM & Locations | BLOCKER | Current page explicitly states the directory is demo data. Every branch/ATM/address/hours entry must be verified, replaced, or removed. |
| Careers | Structurally usable | Publish actual openings only when they exist. Verify career-contact workflow. |
| Support Center | Demo-era wording remains | Remove references to the demo location directory and verify all support paths against production operations. |
| Security & Fraud | Structurally usable | Verify incident-reporting channels and operational response instructions. |
| Rates & Fees | BLOCKER | Current page explicitly has no approved production pricing. Publish approved fee/rate data or do not present the page as a pricing schedule. |
| Documents & Forms | BLOCKER | Current page explicitly contains no approved downloads. Publish real approved forms/disclosures or hide unavailable categories. |
| Legal & Disclosures | BLOCKER | Current page is written around demo status and intentionally avoids licensing/regulatory claims. Replace only after legal/regulatory facts are verified. |
| Privacy Policy | BLOCKER | Current text explicitly says it is a review version requiring legal review. Production policy must match actual data handling, vendors, retention, rights and jurisdiction. |
| Terms of Use | BLOCKER | Current terms explicitly govern a demo environment. Production terms require legal review and actual service/product terms. |
| Accessibility | Pre-launch draft | Keep factual; complete accessibility testing and update known limitations before making stronger claims. |
| Cookie Policy | Needs production update | Reconcile with the final authentication architecture, Smartsupp and every production third-party/analytics/marketing cookie. |

### Content rule for launch

For every public product or institutional page, choose one of three outcomes before launch:

1. **Publish verified production content** — supported by real product, operational and legal data.
2. **Keep the page but narrow the claims** — describe only capabilities that actually exist and are approved.
3. **Hide/remove the product from launch navigation** — when the product, pricing, documents, location or legal basis is not ready.

No page should be converted from demo to production by deleting warnings while leaving unverified claims behind.

### Information still needed from the business/operations side

The implementation team must receive or independently verify the authoritative launch data for:
- actual account products and eligibility;
- actual card products and card availability;
- lending products, limits, pricing, fees, tenors and approval rules;
- supported currencies, FX/transfer coverage, fees and limits;
- branch/ATM/service-center locations and hours;
- rates and fee schedules;
- approved public documents/forms;
- legal entity, jurisdiction, regulatory/licensing status and required disclosures;
- final privacy/terms/cookie language;
- production support hours and escalation channels.

Until those are supplied and verified, the affected pages remain launch blockers or should be removed from launch navigation.


### 2026-10-01 — Customer investigation restriction workflow

Added a common customer-access restriction workflow to Support, Admin and Master control panels.

- Mandatory restriction reason.
- Staff operator and timestamp recorded.
- Security-event audit entry recorded.
- Restricted customers are blocked from sign-in.
- Existing customer access is terminated on the next authenticated request.
- Login shows a brief customer-readable restriction notice and directs the customer to contact the bank.
- Customer financial records remain intact.
- Preferred security-notice sender is `security@velmorabank.us`.
- **Launch task:** provision the `security@velmorabank.us` SpaceMail mailbox/alias and `SECURITY_EMAIL_PASSWORD`. Until then, notices fall back to Support.


### 2026-10-01 — Management review conversion

- Removed visible DEMO ENVIRONMENT / DEMO OPERATIONS presentation from canonical public, authentication, customer and staff surfaces.
- Kept robots/noindex protections active so management can review by direct link without accidentally opening public indexing.
- Replaced browser-email authentication trust with server-side session authentication.
- Added revocable opaque remember-me tokens stored server-side as hashes.
- Added customer session-version revocation for restrictions, suspensions, password changes and password resets.
- Removed hardcoded Jennifer customer/account/transaction seeding from the production dashboard path.
- Replaced demo lending calculator content, fabricated branch listings, provisional rates content and invented card product names with conservative service information.
- Reworked Legal, Privacy, Terms, Cookies, Documents and Accessibility pages to remove demo/review language without inventing regulatory claims.
- Removed unverified physical address/service hours from customer-facing Contact and notification email templates.
- Redirected retired *-v2 and predecessor dashboard/control-panel routes to canonical routes.
- Added deployment smoke tests for canonical review routes and customer-auth schema.
- SpaceMail/Spaceship mailbox provisioning for security@velmorabank.us remains provider-side; application support is complete and support@ fallback remains active until the mailbox credential exists.


### 2026-10-01 — Resend + single SpaceMail mailbox architecture

- SpaceMail is treated as one physical receiving mailbox: `support@velmorabank.us`.
- Alias identities no longer require individual SMTP passwords in application code.
- Outbound application email is Resend-first using `RESEND_API_KEY`.
- Approved Resend sender identities are `support@`, `security@`, `no-reply@` and `admin@velmorabank.us`.
- `security@velmorabank.us` becomes the preferred account-restriction/security sender when Resend is configured.
- If Resend is unavailable or not configured, the application falls back to SpaceMail SMTP through the physical Support mailbox.
- Public/customer pages now load the same private mail environment used by deployment/backend runtime.
- Deployment now syncs `RESEND_API_KEY` from the private environment and checks whether the Resend HTTP runtime is available.
- Provider-side remaining work: verify `velmorabank.us` in Resend, publish the DNS records Resend provides, create the `security@` inbound alias in SpaceMail pointing to Support, and store `RESEND_API_KEY` in the private environment.
