# Backend Control Panel Commonalities

## Active panels

The following three separately authenticated backend panels share a common operational capability layer:

- Support: `/support-control-panel/`
- Admin: `/control-panel/`
- Master: `/master-control-panel/`

Unless a future change is explicitly documented as role-specific, a change to a shared capability must be applied to all three panels.

## Common customer relationship controls

### Customer access restriction / investigation hold — added 2026-10-01

All three panels can:

- open a customer detail record;
- review customer relationship status, KYC summary, linked bank accounts, login metadata and recent transactions;
- place the customer relationship into `Restricted` status;
- enter a mandatory reason for the restriction;
- record the staff operator who applied the restriction;
- record the restriction date/time;
- write the action to the security-event audit trail;
- send the customer an account-access restriction notice;
- restore the customer to `Active` status when the issue is resolved.

The restriction is applied to customer access, not to the underlying financial records. Bank accounts, balances, transactions, KYC and support history remain intact for investigation.

### Customer access behavior

A restricted customer:

- is denied new online-banking sign-in;
- sees a short readable notice that access is restricted and is directed to contact Velmora Bank;
- has any existing customer session/access terminated on the next authenticated request;
- cannot continue using the customer dashboard while the relationship is restricted.

### Email sender

Preferred sender: `security@velmorabank.us` / **Velmora Bank Security**.

Until that mailbox is provisioned with `SECURITY_EMAIL_PASSWORD`, the application safely falls back to `support@velmorabank.us`.

The actual SpaceMail mailbox/alias must be provisioned separately; adding application support does not create the provider mailbox.
