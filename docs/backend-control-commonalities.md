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
- has all persistent remember-me tokens revoked when restriction is applied;
- has the customer session version incremented, invalidating previously issued customer sessions on their next request;
- cannot continue using the customer dashboard while the relationship is restricted.

### Email sender

Preferred sender: `security@velmorabank.us` / **Velmora Bank Security**.

Support and Security are operated by the same department head. The `security@velmorabank.us` alias intentionally uses the same SpaceMail mailbox credential as `support@velmorabank.us`, stored only as `SUPPORT_EMAIL_PASSWORD`. There is no separate `SECURITY_EMAIL_PASSWORD` requirement.

Outbound alias mail remains Resend-first. If Resend is unavailable, the application safely falls back to the physical Support mailbox. The actual `security@` alias still has to exist provider-side and route into Support.
