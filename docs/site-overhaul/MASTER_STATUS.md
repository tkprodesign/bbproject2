# Velmora Site Overhaul — Master Status

This document is the working source of truth for the current site overhaul.

## Workflow

1. Build a separate alternate version.
2. Keep the current live predecessor untouched.
3. Review the alternate version.
4. Only after explicit approval, promote the alternate version to the canonical route.
5. Archive the predecessor as a complete reproducible package.
6. Update this document with the approval and promotion status.

No page should be promoted merely because an alternate version exists.

## Public Website

| Final Page | Alternate Review Route | Status |
|---|---|---|
| Home | / | Approved and active |
| Personal Banking | /personal-v2/ | Approved and promoted |
| Business Banking | /business-v2/ | Approved and promoted |
| Credit Cards | /credit-card-v2/ | Approved and promoted |
| Loans & Financing | /loan-v2/ | Approved and promoted |
| International Banking & FX | /international-v2/ | Approved and promoted |
| Online Banking | /online-banking-v2/ | Approved and promoted |
| About Velmora | /about-v2/ | Approved and promoted |
| Support Center | /support-v2/ | Approved and promoted |
| Security & Fraud Center | /security-v2/ | Approved and promoted |
| ATM & Locations | /locations-v2/ | Approved and promoted |
| Contact | /contact-v2/ | Approved and promoted |
| Careers | /careers-v2/ | Approved and promoted |
| Rates & Fees | /rates-v2/ | Approved and promoted |
| Documents & Forms | /documents-v2/ | Approved and promoted |
| Legal & Disclosures | /legal-v2/ | Approved and promoted |
| Privacy Policy | /privacy-v2/ | Approved and promoted |
| Terms of Use | /terms-v2/ | Approved and promoted |
| Cookie Policy | /cookie-policy-v2/ | Approved and promoted |
| Accessibility Statement | /accessibility-v2/ | Approved and promoted |
| Page Not Found | /not-found-v2/ | Approved and promoted |

## Authentication & Onboarding

| Final Page | Alternate Review Route | Status |
|---|---|---|
| Sign In | /login-v2/ | Approved and promoted |
| Open an Account | /signup-v2/ | Approved and promoted |
| Onboarding / Next Steps | /onboarding-v2/ | Approved and promoted |
| Forgot Password | /forgot-password-v2/ | Approved and promoted |
| Reset Password | /reset-password-v2/ | Approved and promoted |

Legacy /sign-up/ should eventually redirect to the canonical signup route after approval.

## Customer Banking — V3

| Module | Review Route | Status |
|---|---|---|
| Dashboard Overview | /dashboard-v3/ | Approved and promoted |
| Accounts | /dashboard-v3/accounts/ | Approved and promoted |
| Account Detail | /dashboard-v3/accounts/detail/ | Approved and promoted |
| Account Opened Confirmation | /dashboard-v3/accounts/opened/ | Approved and promoted |
| Transactions | /dashboard-v3/transactions/ | Approved and promoted |
| Transaction Detail / Receipt | /dashboard-v3/transactions/detail/ | Approved and promoted |
| Transfer Funds | /dashboard-v3/transfer/ | Approved and promoted |
| Beneficiaries | /dashboard-v3/beneficiaries/ | Approved and promoted |
| Currency Exchange / FX | /dashboard-v3/exchange/ | Approved and promoted |
| Statements | /dashboard-v3/statements/ | Approved and promoted |
| Notifications | /dashboard-v3/notifications/ | Approved and promoted |
| Profile | /dashboard-v3/profile/ | Approved and promoted |
| Identity & KYC | /dashboard-v3/identity/ | Approved and promoted |
| Security Center | /dashboard-v3/security/ | Approved and promoted |
| Change Password | /dashboard-v3/security/change-password/ | Approved and promoted |
| Preferences | /dashboard-v3/preferences/ | Approved and promoted |
| Secure Support Messages | /dashboard-v3/support/ | Approved and promoted |
| Support Case Detail | /dashboard-v3/support/detail/ | Approved and promoted |

Dashboard V3 is approved and promoted into the canonical /dashboard/ family. The predecessor is preserved under docs/archive/dashboard-predecessor-2026-10-01/.

## Bank Operations — V2

| Module | Review Route | Status |
|---|---|---|
| Operations Overview | /control-panel-v2/ | Approved and promoted |
| Customers | /control-panel-v2/customers/ | Approved and promoted |
| Customer Detail | /control-panel-v2/customers/detail/ | Approved and promoted |
| Accounts | /control-panel-v2/accounts/ | Approved and promoted |
| Transactions | /control-panel-v2/transactions/ | Approved and promoted |
| Transfer Review | /control-panel-v2/transfers/ | Approved and promoted |
| FX Trades | /control-panel-v2/fx/ | Approved and promoted |
| Ledger Adjustments | /control-panel-v2/adjustments/ | Approved and promoted |
| KYC Review Queue | /control-panel-v2/kyc/ | Approved and promoted |
| KYC Detail | /control-panel-v2/kyc/detail/ | Approved and promoted |
| Support Cases | /control-panel-v2/support-cases/ | Approved and promoted |
| Support Case Detail | /control-panel-v2/support-cases/detail/ | Approved and promoted |
| Communications | /control-panel-v2/communications/ | Approved and promoted |
| Security & Audit | /control-panel-v2/audit/ | Approved and promoted |
| Settings | /control-panel-v2/settings/ | Approved and promoted |

Operations Console V2 is approved and promoted into the canonical /control-panel/ family. The predecessor is preserved under docs/archive/control-panel-predecessor-2026-10-01/.

## Completed Retirement / Consolidation

- /quick-links/ now redirects to the canonical Support Center.
- /sign-up/ now redirects to the canonical signup route.
- The prior /dashboard/ implementation is archived in the museum snapshot before V3 promotion.
- The prior /control-panel/ implementation is archived in the museum snapshot before Operations V2 promotion.
- Legacy dashboard and control-panel subroutes now redirect into their approved canonical replacements where applicable.

## Supporting Product Infrastructure Already Added

- Customer numbers and relationship status.
- Account aliases and opening dates.
- Transaction channel, value date and posting date metadata.
- Beneficiaries.
- Notifications.
- Security events.
- User preferences.
- FX trade records.
- Support cases and support-case messages.
- Secure password-reset tokens.
- Account and transaction detail/receipt workflows.
- Statement CSV export.
- Operations transfer review, KYC review and ledger adjustments.

These supporting structures are part of the product overhaul and should be preserved when canonical pages are promoted.