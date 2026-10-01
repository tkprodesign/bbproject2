# Backend Control Panel Commonality Record

## Purpose

This is the permanent common-function stamp for the three active Velmora backend control panels:

- `/support-control-panel/` — Support
- `/control-panel/` — Admin
- `/master-control-panel/` — Master

Unless a capability is explicitly documented as role-specific, functionality that exists in all three panels is treated as shared/common. A future change to a common capability must be applied consistently to all three panels.

## Common baseline — stamped 2026-10-01

The three panels currently share the same operational areas and corresponding workflows:

- Dashboard / operational overview
- Customers
- Accounts
- Transfers
- Transactions
- Ledger adjustments
- KYC review and KYC data
- Communications / in-app notifications
- Support cases
- Foreign exchange
- Audit
- Settings
- Site review
- Staff mail
- Site users
- Profile-picture operations
- Logout / backend authentication shell

The panels remain separately authenticated and may later receive explicitly documented role-specific restrictions or additions. Shared behavior must not silently diverge.

## Common transaction archive capability — added 2026-10-01

All three control panels can archive an existing transaction from the Transactions area.

### Archive behavior

- The transaction is never deleted and its amount is never zeroed.
- Its current transaction status becomes `Archived`.
- The pre-archive status is retained internally in `archived_previous_status`.
- The authenticated backend operator email is retained in `archived_by`.
- The archive date and time are retained in `archived_at`.
- The full transaction and monetary amount remain in the bank-side transaction record.
- The archive action creates an internal security-event audit entry.
- Archiving does not send a customer notification or customer email.

### Customer-facing effect

An archived transaction is excluded from:

- Customer transaction history and recent activity.
- Customer transaction receipts through direct transaction URLs.
- Customer statements and downloadable customer statement CSVs.
- Customer account activity lists.
- Customer-facing available-balance calculations.
- Customer transfer / FX availability checks that use the customer-visible balance.
- Customer-visible transaction totals.

This means the normal online/customer balance is calculated without archived transactions.

### Bank/HQ reconciliation rule

Archived transactions remain part of the bank's internal record and retain their full amount and audit metadata. If a customer requests a ledger-balance explanation through support channels or email, HQ may manually prepare a separate bank document that discloses the archived transaction(s), or their combined amount, and reconciles that internal ledger position against the normal customer-visible balance.

That HQ document is intentionally a manual bank process. No automatic document-generation feature is to be coded unless a future instruction explicitly changes this policy.
