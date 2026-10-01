# bbproject2

## Production email settings

Velmora uses a **Resend-first outbound email architecture** with SpaceMail retained as the physical receiving mailbox and SMTP fallback.

### Mailbox and aliases

- `support@velmorabank.us` — the physical SpaceMail mailbox and general support inbox.
- `security@velmorabank.us` — inbound alias routed to the Support mailbox; preferred outbound sender for account restrictions and security notices through Resend.
- `no-reply@velmorabank.us` — outbound transactional identity through Resend; replies are directed to Support.
- `admin@velmorabank.us` — optional operational alias/outbound identity where needed.

The SpaceMail plan does not require a separate mailbox password for each alias. Aliases receive into the physical Support mailbox; application outbound mail from aliases is sent through Resend.

### Required outbound setting

- `RESEND_API_KEY` — Resend API key used for outbound application mail after `velmorabank.us` has been verified in Resend.

When `RESEND_API_KEY` is present, application mail is sent through the Resend API. If Resend is unavailable or not configured, the application falls back to SpaceMail SMTP using the Support mailbox so critical notices do not silently disappear.

### SpaceMail fallback settings

- `SUPPORT_EMAIL_PASSWORD` for the physical `support@velmorabank.us` mailbox.
- SMTP host: `mail.spacemail.com`
- SMTP SSL port: `465`
- SMTP STARTTLS port: `587`
- IMAP host: `mail.spacemail.com`, SSL port `993`

Optional overrides are available through `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, and `SMTP_ENCRYPTION`.

## Database bootstrap

Run `php create_tables.php` from the server/CLI after deployment or database changes. It creates and updates the required `users`, `accounts`, `transactions`, `kyc_data`, and `dynamic_data` tables, then seeds default dynamic values such as the support phone and wallet address keys.
