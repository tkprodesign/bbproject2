# Velmora Email Architecture

## Current hosting constraint

The SpaceMail plan provides one physical mailbox:

- `support@velmorabank.us`

Up to 10 aliases can receive mail into that mailbox. Velmora therefore does not require or assume a separate SpaceMail mailbox/password for each sending identity.

## Inbound routing

Recommended aliases routed to the Support mailbox:

1. `security@velmorabank.us` — security, restricted-access and investigation correspondence.
2. `admin@velmorabank.us` — operational/admin correspondence where required.
3. `privacy@velmorabank.us` — privacy requests if management chooses to publish it.
4. `complaints@velmorabank.us` — complaints/escalations if management chooses to publish it.
5. `cards@velmorabank.us` — card-service replies if a dedicated public route is later required.
6. `loans@velmorabank.us` — lending replies if a dedicated public route is later required.

Only aliases actually needed should be created; unused aliases should not be published.

`no-reply@velmorabank.us` is an outbound identity and does not need to invite inbound correspondence. Application replies from no-reply messages are directed to Support.

## Outbound routing

Preferred transport:

`Velmora application -> Resend API -> recipient`

Approved application sender identities:

- `support@velmorabank.us`
- `security@velmorabank.us`
- `no-reply@velmorabank.us`
- `admin@velmorabank.us`

The application uses one `RESEND_API_KEY`. Alias sender identities do not need individual SpaceMail passwords.

## Fallback

If Resend is not configured or a Resend request fails, the application attempts delivery through the physical `support@velmorabank.us` SpaceMail SMTP mailbox. This preserves delivery while avoiding false assumptions that aliases have independent SMTP credentials.

## Security notice behavior

When Resend is available:

- sender: **Velmora Bank Security <security@velmorabank.us>**
- reply-to: `security@velmorabank.us` (the inbound alias should route to Support)

Until Resend is configured:

- sender falls back to **Velmora Bank Support <support@velmorabank.us>**

## Provider-side setup still required

1. Add and verify `velmorabank.us` in Resend.
2. Publish the DNS records supplied by Resend (including its DKIM/SPF-related records as applicable).
3. Create `security@velmorabank.us` as a SpaceMail alias that delivers into `support@velmorabank.us`.
4. Store the Resend API key as `RESEND_API_KEY` in the private server environment used by deployment.
5. Send and receive a restriction-notice test, including a reply to `security@velmorabank.us`.
6. Verify domain-level DMARC policy/alignment before public launch.

Never commit the Resend API key to Git.
