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
| Personal Banking | /personal-v2/ | Ready for review |
| Business Banking | /business-v2/ | Ready for review |
| Credit Cards | /credit-card-v2/ | Ready for review |
| Loans & Financing | /loan-v2/ | Ready for review |
| International Banking & FX | /international-v2/ | Ready for review |
| Online Banking | /online-banking-v2/ | Ready for review |
| About Velmora | /about-v2/ | Ready for review |
| Support Center | /support-v2/ | Ready for review |
| Security & Fraud Center | /security-v2/ | Ready for review |
| ATM & Locations | /locations-v2/ | Ready for review |
| Contact | /contact-v2/ | Ready for review |
| Careers | /careers-v2/ | Ready for review |
| Rates & Fees | /rates-v2/ | Ready for review |
| Documents & Forms | /documents-v2/ | Ready for review |
| Legal & Disclosures | /legal-v2/ | Ready for review |
| Privacy Policy | /privacy-v2/ | Ready for review |
| Terms of Use | /terms-v2/ | Ready for review |
| Cookie Policy | /cookie-policy-v2/ | Ready for review |
| Accessibility Statement | /accessibility-v2/ | Ready for review |
| Page Not Found | /not-found-v2/ | Ready for review |

## Authentication & Onboarding

| Final Page | Alternate Review Route | Status |
|---|---|---|
| Sign In | /login-v2/ | Ready for review |
| Open an Account | /signup-v2/ | Ready for review |
| Onboarding / Next Steps | /onboarding-v2/ | Ready for review |
| Forgot Password | /forgot-password-v2/ | Ready for review |
| Reset Password | /reset-password-v2/ | Ready for review |

Legacy /sign-up/ should eventually redirect to the canonical signup route after approval.

## Customer Banking — V3

| Module | Review Route | Status |
|---|---|---|
| Dashboard Overview | /dashboard-v3/ | Ready for review |
| Accounts | /dashboard-v3/accounts/ | Ready for review |
| Account Detail | /dashboard-v3/accounts/detail/ | Ready for review |
| Account Opened Confirmation | /dashboard-v3/accounts/opened/ | Ready for review |
| Transactions | /dashboard-v3/transactions/ | Ready for review |
| Transaction Detail / Receipt | /dashboard-v3/transactions/detail/ | Ready for review |
| Transfer Funds | /dashboard-v3/transfer/ | Ready for review |
| Beneficiaries | /dashboard-v3/beneficiaries/ | Ready for review |
| Currency Exchange / FX | /dashboard-v3/exchange/ | Ready for review |
| Statements | /dashboard-v3/statements/ | Ready for review |
| Notifications | /dashboard-v3/notifications/ | Ready for review |
| Profile | /dashboard-v3/profile/ | Ready for review |
| Identity & KYC | /dashboard-v3/identity/ | Ready for review |
| Security Center | /dashboard-v3/security/ | Ready for review |
| Change Password | /dashboard-v3/security/change-password/ | Ready for review |
| Preferences | /dashboard-v3/preferences/ | Ready for review |
| Secure Support Messages | /dashboard-v3/support/ | Ready for review |
| Support Case Detail | /dashboard-v3/support/detail/ | Ready for review |

After the full V3 family is approved, it can replace the legacy /dashboard/ family as one coordinated promotion.

## Bank Operations — V2

| Module | Review Route | Status |
|---|---|---|
| Operations Overview | /control-panel-v2/ | Ready for review |
| Customers | /control-panel-v2/customers/ | Ready for review |
| Customer Detail | /control-panel-v2/customers/detail/ | Ready for review |
| Accounts | /control-panel-v2/accounts/ | Ready for review |
| Transactions | /control-panel-v2/transactions/ | Ready for review |
| Transfer Review | /control-panel-v2/transfers/ | Ready for review |
| FX Trades | /control-panel-v2/fx/ | Ready for review |
| Ledger Adjustments | /control-panel-v2/adjustments/ | Ready for review |
| KYC Review Queue | /control-panel-v2/kyc/ | Ready for review |
| KYC Detail | /control-panel-v2/kyc/detail/ | Ready for review |
| Support Cases | /control-panel-v2/support-cases/ | Ready for review |
| Support Case Detail | /control-panel-v2/support-cases/detail/ | Ready for review |
| Communications | /control-panel-v2/communications/ | Ready for review |
| Security & Audit | /control-panel-v2/audit/ | Ready for review |
| Settings | /control-panel-v2/settings/ | Ready for review |

The legacy control panel remains separate until explicit approval to replace it.

## Retire / Consolidate Later

- /quick-links/ — distribute useful material into Support, Security, Legal and Documents.
- /sign-up/ — redirect to the canonical signup route.
- Old Dashboard V1 / V2 — archive after V3 promotion.
- Old control-panel interface — archive after operations V2 promotion.

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