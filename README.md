# bbproject2

## Production email settings

Outbound mail is sent through the bundled PHPMailer library using SpaceMail SMTP. Configure the mailbox passwords as environment variables instead of hardcoding them in PHP:

- `NOREPLY_EMAIL_PASSWORD` for `no-reply@velmorabank.us`
- `SUPPORT_EMAIL_PASSWORD` for `support@velmorabank.us`
- `ADMIN_EMAIL_PASSWORD` for `admin@velmorabank.us`

Default SpaceMail connection settings are:

- SMTP host: `mail.spacemail.com`
- SMTP SSL port: `465`
- SMTP STARTTLS port: `587`
- IMAP host: `mail.spacemail.com`, SSL port `993`
- POP3 host: `mail.spacemail.com`, SSL port `995`

Optional overrides are available through `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, and `SMTP_ENCRYPTION`.

## Database bootstrap

Run `/create_tables.php` after deployment or database changes. It creates and updates the required `users`, `accounts`, `transactions`, `kyc_data`, and `dynamic_data` tables, then seeds default dynamic values such as the support phone and wallet address keys.
