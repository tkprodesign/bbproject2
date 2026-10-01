# Velmora Site Overhaul Master Plan

## Objective

Build a coherent professional banking website, authenticated customer banking product, and internal bank-operations product. Every replacement page is reviewed on an alternate route before it replaces the predecessor.

## Promotion rule

A replacement is promoted only after explicit approval.

When a predecessor is retired, archive enough material to reproduce both its appearance and behavior:

- PHP/HTML source
- page-specific CSS
- page-specific JavaScript
- page-specific assets
- relevant shared component/version notes
- route/dependency notes
- README identifying the date and replacement route

Do not archive only the markup.

## Current status

### Active and approved

- Home — `/`

### Public review pages

- `/personal-v2/`
- `/business-v2/`
- `/credit-card-v2/`
- `/loan-v2/`
- `/international-v2/`
- `/online-banking-v2/`
- `/about-v2/`
- `/support-v2/`
- `/security-v2/`
- `/locations-v2/`
- `/contact-v2/`
- `/careers-v2/`
- `/rates-v2/`
- `/documents-v2/`
- `/legal-v2/`
- `/privacy-v2/`
- `/terms-v2/`
- `/cookie-policy-v2/`
- `/accessibility-v2/`

### Authentication and onboarding review pages

- `/login-v2/`
- `/signup-v2/`
- `/onboarding-v2/`

### Customer banking V3

- `/dashboard-v3/`
- `/dashboard-v3/accounts/`
- `/dashboard-v3/transactions/`
- `/dashboard-v3/transfer/`
- `/dashboard-v3/exchange/`
- `/dashboard-v3/beneficiaries/`
- `/dashboard-v3/notifications/`
- `/dashboard-v3/statements/`
- `/dashboard-v3/support/`
- `/dashboard-v3/profile/`
- `/dashboard-v3/profile-picture/`
- `/dashboard-v3/identity/`
- `/dashboard-v3/security/`
- `/dashboard-v3/security/change-password/`
- `/dashboard-v3/preferences/`

Dynamic V3 screens include account detail, account-opened confirmation, transaction receipt, and support-case conversation.

### Operations V2

- `/control-panel-v2/`
- `/control-panel-v2/site-review/`
- `/control-panel-v2/customers/`
- `/control-panel-v2/accounts/`
- `/control-panel-v2/transactions/`
- `/control-panel-v2/transfers/`
- `/control-panel-v2/fx/`
- `/control-panel-v2/adjustments/`
- `/control-panel-v2/kyc/`
- `/control-panel-v2/support-cases/`
- `/control-panel-v2/communications/`
- `/control-panel-v2/audit/`
- `/control-panel-v2/settings/`

Dynamic operations screens include customer detail, KYC detail and support-case conversation.

## Planned retirement/consolidation

After approval and promotion:

- `/sign-up/` → redirect to the approved `/signup/`
- `/quick-links/` → retire after useful content is moved into Support, Security, Legal and Documents
- old dashboard versions → archive after V3 becomes `/dashboard/`
- old control panel → archive after Operations V2 becomes the main operations console

## Design systems

- Public site: `docs/velmora-design-system.md`, `assets/stylesheets/public-v2.css`
- Customer banking: `assets/stylesheets/dashboard-v3.css`
- Operations: `assets/stylesheets/control-panel-v2.css`

## Banking interaction principles

- An account's denomination does not change because the UI changes a currency selector.
- FX is a quote → review → confirmation process with ledger entries.
- Transfers are prepared → reviewed → submitted → status-tracked.
- Balances derive from account ledger activity.
- Important activity should be traceable by references, status and dates.
- KYC/profile changes requiring verification should be reviewed rather than silently overwriting verified information.
- Authenticated support uses traceable support cases rather than unstructured page messages.
