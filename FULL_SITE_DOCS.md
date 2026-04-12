# FULL_SITE_DOCS

## 1. Website Overview

Velmora Bank is a PHP-based banking web application with a public marketing site, account authentication, customer dashboard, and an admin/control-panel workflow for account, KYC, and transaction operations.


## 2. Architecture

### Folder structure (brief)

- `/` root entry and marketing pages (`index.php`, `about-us/`, `loan/`, `contact/`, etc.)
- `common-sections/` shared runtime/bootstrap and shared UI fragments (header/footer).
- `login/`, `signup/`, `sign-up/` auth entry points and handlers.
- `dashboard/` authenticated customer banking area (accounts, transfers, profile, security).
- `control-panel/` admin-only operations (users, KYC review, transaction actions).
- `assets/` front-end JS/CSS/images/fonts.
- `PHPMailer/` bundled mailing library.
- `archive/` historical snapshots/backups (not active runtime).

### How pages connect

- Most pages include `common-sections/app.php` directly or indirectly for DB access + auth guard behavior.
- Marketing pages render shared top/bottom layout via `common-sections/header.php` and `common-sections/footer.php`.
- Authentication flow:
  - Sign-up via `signup/app.php` inserts `users` record.
  - Login via `login/app.php` validates credentials and writes `login_email` cookie.
- App routing by role:
  - Regular users are redirected to `/dashboard`.
  - allow-listed emails are redirected to `/control-panel`.
- Dashboard and control panel both include their own `app.php` files that enforce additional role checks and load operational data.

### Key systems

- **Frontend:** server-rendered PHP templates + vanilla JS + CSS (desktop/tablet/mobile variants).
- **Backend:** plain PHP (procedural + helper functions), MySQL via `mysqli`.
- **Auth:** cookie-based (`login_email`) checks plus role allow-list for admin panel.
- **Dashboard:** user balances, accounts, transactions, profile/KYC summaries.
- **APIs/Integrations:** Resend email API usage in control panel, Finnhub API usage for homepage news ticker, bundled PHPMailer library.

### Responsive layout update (April 2026)

- Mobile spacing/width handling for marketing-style pages now uses a dedicated stylesheet at `assets/stylesheets/mobile/marketing-pages.css`.
- Shared mobile container sizing was tightened to increase usable width on small screens (`assets/stylesheets/mobile/main.css`).
- Marketing templates that share `.page-hero`, `.page-content`, `.grid`, and `.card` structures now explicitly load the mobile marketing stylesheet for consistent narrow-screen behavior:
  - `about-us/index.php`
  - `quick-links/index.php`
  - `contact/index.php`
  - `cookie-policy/index.php`
  - `business/index.php`
  - `credit-card/index.php`
  - `personal/index.php`
  - `careers/index.php`
  - `atm-and-bank-locations/index.php`
  - `loan/index.php`
  - `online-banking/index.php`


## 3. Key Files Only

- `common-sections/app.php`
  - Purpose: Core bootstrap: DB connection, support contact lookup, login gating for internal pages, utility helpers.
  - Key logic/functions: `connectToDatabase`, `getSupportPhoneNumber`, `requireLoginForInternalPages`, `normalizeLegacyTransactionStatuses`.

- `index.php`
  - Purpose: Main homepage/landing page with marketing sections and loan calculator UI.
  - Key logic/functions: Loads shared header/footer and dynamic support contact data; renders loan calculator + action links.

- `login/app.php`
  - Purpose: Sign-in request handler and role-based redirect logic.
  - Key logic/functions: Credential validation with `password_verify`, sets `login_email` cookie, redirects to `/dashboard` or `/control-panel`.

- `signup/app.php`
  - Purpose: Account creation flow for new users.
  - Key logic/functions: Checks existing user, hashes password, inserts new row into `users` table.

- `dashboard/app.php`
  - Purpose: Authenticated user bootstrap and transactional business logic.
  - Key logic/functions: Session/cookie validation, role separation, user/account aggregate queries, transaction and email helper logic.

- `dashboard/index.php`
  - Purpose: User dashboard page composition and summary metrics rendering.
  - Key logic/functions: Queries metrics (balance, credits/debits, KYC/account info), renders transaction table.

- `control-panel/app.php`
  - Purpose: Admin operations backend for deposits/withdrawals/KYC and messaging.
  - Key logic/functions: Role allow-list check, update support phone, create transaction rows, trigger email notifications.

- `control-panel/index.php`
  - Purpose: Admin UI dashboard and forms/tables for ops workflows.
  - Key logic/functions: Renders feedback states, user/account lists, action forms wired to control-panel handlers.

- `create_tables.php`
  - Purpose: Database schema bootstrap script.
  - Key logic/functions: Creates `users`, `accounts`, `transactions`, `kyc_data`, `dynamic_data` + seeds defaults.

- `assets/scripts/header.js`
  - Purpose: Public-site header interactions.
  - Key logic/functions: Mobile nav toggle, active link state, cookie banner logic.

- `assets/scripts/home.js`
  - Purpose: Homepage interactivity layer.
  - Key logic/functions: Hero/layout behavior, Swiper init, Finnhub ticker fetch, loan calculator + apply-link state.

- `assets/scripts/dashboard.js`
  - Purpose: Dashboard UX helpers.
  - Key logic/functions: Menu/submenu UX, sticky header, transaction filtering/search/export CSV.

- `assets/scripts/control-panel.js`
  - Purpose: Control-panel nav + greeting script.
  - Key logic/functions: Mobile menu toggle and greeting text by hour.


## 4. Code Archive (IMPORTANT)


## /common-sections/app.php
```php
<?php
// Setting initials
// rsend api re_6UXBpV3q_Ee83gTNZod4QexanZjZh9Ss8
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');




// Database connection function
function connectToDatabase() {
    $servername = getenv('DB_HOST') ?: '127.0.0.1';
    $dbusername = getenv('DB_USER') ?: 'bzvgbkjtlx_user';
    $dbpassword = getenv('DB_PASS') ?: 'Wateva06@';
    $dbname = getenv('DB_NAME') ?: 'bzvgbkjtlx_db';
    
    $dbconn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
    
    if (!$dbconn) {
        die("Database connection failed. Please verify database configuration.");
    }

    mysqli_set_charset($dbconn, 'utf8mb4');
    
    return $dbconn;
}





// Dynamic contact details
define('DEFAULT_SUPPORT_PHONE', '+17252885411');

function normalizePhoneForWhatsapp(string $phone): string {
    $digits = preg_replace('/\D+/', '', $phone);
    return $digits ?: preg_replace('/\D+/', '', DEFAULT_SUPPORT_PHONE);
}

function getSupportPhoneNumber(): string {
    static $cachedPhone = null;

    if ($cachedPhone !== null) {
        return $cachedPhone;
    }

    $dbconn = connectToDatabase();
    $phone = DEFAULT_SUPPORT_PHONE;

    $query = "SELECT `value` FROM dynamic_data WHERE `name` = 'phone_number' LIMIT 1";
    $result = mysqli_query($dbconn, $query);

    if ($result && ($row = mysqli_fetch_assoc($result))) {
        $value = trim((string) ($row['value'] ?? ''));
        if ($value !== '') {
            $phone = $value;
        }
    }

    mysqli_close($dbconn);
    $cachedPhone = $phone;

    return $cachedPhone;
}

function getSupportWhatsappLink(): string {
    return 'https://wa.me/' . normalizePhoneForWhatsapp(getSupportPhoneNumber());
}

//Check for item in database
function isInTable($email, $table) {
    $dbconn = connectToDatabase();

    // Validate table names to avoid SQL injection
    $allowedTables = ['users']; // List of allowed tables
    if (!in_array($table, $allowedTables)) {
        die("Invalid table name.");
    }

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $dbconn->prepare("SELECT COUNT(*) FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $dbconn->close();

    return $count > 0;
}

function normalizeLegacyTransactionStatuses(): void {
    static $normalized = false;
    if ($normalized) {
        return;
    }

    $dbconn = connectToDatabase();
    $stmt = $dbconn->prepare("UPDATE transactions SET status = 'Successful' WHERE LOWER(status) = 'completed'");
    if ($stmt) {
        $stmt->execute();
        $stmt->close();
    }
    $dbconn->close();
    $normalized = true;
}



// Restrict access to internal pages when the visitor is not logged in.
function requireLoginForInternalPages() {
    if (php_sapi_name() === 'cli') {
        return;
    }

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
    $normalizedPath = rtrim($path, '/');
    if ($normalizedPath === '') {
        $normalizedPath = '/';
    }

    $publicPaths = [
        '/',
        '/index.php',
        '/create_tables',
        '/create_tables.php',
        '/login',
        '/login/index.php',
        '/signup',
        '/signup/',
        '/signup/index.php',
        '/sign-up',
        '/sign-up/',
        '/sign-up/index.php',
    ];

    if (!in_array($normalizedPath, $publicPaths, true) && !isset($_COOKIE['login_email'])) {
        header('Location: /login');
        exit;
    }
}

requireLoginForInternalPages();

?>

```


## /common-sections/header.php
```php
<?php
require_once __DIR__ . '/app.php';
$supportPhoneNumber = getSupportPhoneNumber();
$supportWhatsappLink = getSupportWhatsappLink();
?>
<header class="site-header">
    <div class="header-meta-bar">
        <div class="container">
            <div class="meta-links">
                <a href="mailto:support@velmorabank.us">
                    <i class="bi bi-envelope-fill"></i>
                    <span>support@velmorabank.us</span>
                </a>
                <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener">
                    <i class="bi bi-telephone-fill"></i>
                    <span><?php echo htmlspecialchars($supportPhoneNumber); ?></span>
                </a>
            </div>
            <div class="meta-links right">
                <a href="/atm-and-bank-locations/">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>ATM &amp; Branch Locations</span>
                </a>
                <a href="/contact/">
                    <i class="bi bi-headset"></i>
                    <span>24/7 Support</span>
                </a>
            </div>
        </div>
    </div>
    <div class="header-nav-bar">
        <div class="container">
            <a href="/" id="logo" aria-label="Velmora Bank Home">
                <img src="/assets/images/branding/logo.png" alt="Velmora Bank">
            </a>
            <nav class="desktop-nav">
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/personal/">Personal Banking</a></li>
                    <li><a href="/business/">Business Banking</a></li>
                    <li><a href="/credit-card/">Credit Cards</a></li>
                    <li><a href="/loan/">Loans</a></li>
                    <li><a href="/contact/">Contact</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="/login/" class="login-link">Login</a>
                <a href="/online-banking/" class="cta">
                    <i class="bi bi-lock"></i>
                    <span>Online Banking</span>
                </a>
            </div>
            <a href="#" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobileNav">
                <i class="bi bi-list open"></i>
                <i class="bi bi-x-lg close"></i>
            </a>
        </div>
    </div>
    <nav class="mobile-nav" id="mobileNav">
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/personal/">Personal Banking</a></li>
            <li><a href="/business/">Business Banking</a></li>
            <li><a href="/credit-card/">Credit Cards</a></li>
            <li><a href="/loan/">Loans</a></li>
            <li><a href="/contact/">Contact</a></li>
            <li><a href="/atm-and-bank-locations/">ATM &amp; Branch Locations</a></li>
            <li><a href="/online-banking/">Online Banking</a></li>
            <li><a href="/signup/">Create Account</a></li>
        </ul>
    </nav>
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
    <div class="cookie-consent" id="cookieConsent" role="region" aria-label="Cookie consent" hidden>
        <p>We use cookies to improve your experience.</p>
        <div class="cookie-actions">
            <a href="/cookie-policy/" class="learn-more">Learn More</a>
            <button type="button" id="cookieAcceptBtn">Accept</button>
        </div>
    </div>
</header>
<script src="/assets/scripts/header.js?v=<?php echo time(); ?>"></script>

```


## /common-sections/footer.php
```php
<?php
require_once __DIR__ . '/app.php';
$supportPhoneNumber = getSupportPhoneNumber();
$supportWhatsappLink = getSupportWhatsappLink();
?>
<footer>
    <div class="container">
        <div class="left">
            <ul>
                <li><h3>About Us</h3></li>
                <li><a href="/about-us/#our-history">Our History</a></li>
                <li><a href="/about-us/#corporate-profile">Corporate Profile</a></li>
                <li><a href="/about-us/#corporate-governance">Corporate Governance</a></li>
                <li><a href="/about-us/#board-management">Board and Management Team</a></li>
                <li><a href="/about-us/#our-awards">Our Awards</a></li>
            </ul>
            <ul>
                <li><h3>Careers</h3></li>
                <li><a href="/careers/#working-with-us">Working At With Us</a></li>
                <li><a href="/careers/#your-career">Your Career</a></li>
                <li><a href="/careers/#recruitment-process">Recruitment Process</a></li>
            </ul>
            <ul>
                <li><h3>Quick Links</h3></li>
                <li><a href="/quick-links/#anti-money-laundering">Anti-Money Laundering</a></li>
                <li><a href="/quick-links/#download-center">Download Center</a></li>
                <li><a href="/quick-links/#online-security-tips">Online Security Tips</a></li>
                <li><a href="/quick-links/#scam-alert">Scam Alert</a></li>
                <li><a href="/quick-links/#support-center">Support Center</a></li>
            </ul>
        </div>
        <div class="right">
            <form action="/contact/" method="get">
                <h3>Subscribe to Our Newsletter</h3>
                <input type="email" name="email" placeholder="Your Email Address">
                <button type="submit">Subscribe</button>
            </form>
            <div class="contacts">
                <a href="https://maps.google.com/?q=400+Park+Ave,+New+York,+NY+10022" target="_blank" rel="noopener" style="display: block; margin-bottom: 8px;">400 Park Ave, New York, NY 10022, United States</a>
                <a href="mailto:support@velmorabank.us" style="display: block; margin-bottom: 8px;">support@velmorabank.us</a>
                <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener" style="display: block; margin-bottom: 8px;"><?php echo htmlspecialchars($supportPhoneNumber); ?></a>
            </div>
            <div class="social-links">
                <a href="/contact/" aria-label="Contact us"><i class="bi bi-facebook"></i></a>
                <a href="/contact/" aria-label="Contact us"><i class="bi bi-twitter"></i></a>
                <a href="/contact/" aria-label="Contact us"><i class="bi bi-instagram"></i></a>
                <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i></a>
            </div>
        </div>
    </div>
</footer>

```


## /index.php
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">

    <!-- Primary Meta Tags -->
    <title>Velmora Bank — Digital Financial Solutions</title>
    <meta name="title" content="Velmora Bank — Digital Financial Solutions">
    <meta name="description" content="Velmora Bank provides secure, innovative financial services to individuals, businesses, and institutions worldwide.">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://velmorabank.us/">
    <meta property="og:title" content="Velmora Bank — Digital Financial Solutions">
    <meta property="og:description" content="Velmora Bank provides secure, innovative financial services to individuals, businesses, and institutions worldwide.">
    <meta property="og:image" content="https://velmorabank.us/assets/images/home/hero/bank-exterior.jpg">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://velmorabank.us/">
    <meta name="twitter:title" content="Velmora Bank — Digital Financial Solutions">
    <meta name="twitter:description" content="Velmora Bank provides secure, innovative financial services to individuals, businesses, and institutions worldwide.">
    <meta name="twitter:image" content="https://velmorabank.us/assets/images/home/hero/bank-exterior.jpg">

    <link rel="stylesheet" href="/assets/stylesheets/desktop/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css?v=<?php echo time(); ?>">
</head>
<body>
<?php
require_once('./common-sections/app.php');
$supportPhoneNumber = getSupportPhoneNumber();
$supportWhatsappLink = getSupportWhatsappLink();
$loanFlowMessage = '';
if (isset($_GET['loan_flow']) && $_GET['loan_flow'] === 'invalid') {
    $loanFlowMessage = 'Please calculate a valid loan estimate before submitting an application.';
}
include('./common-sections/header.php');
?>





<!-- Hero Section -->
<section class="hero hero-static" style="background-image: linear-gradient(90deg, rgba(10,28,52,.64), rgba(10,28,52,.28)), url('/assets/images/home/hero/consulting-banner.jpg');">
    <div class="hero-static-inner">
        <div class="content">
            <h1>Secure Everyday Banking for Individuals, Families, and Businesses</h1>
            <p>Manage accounts, transfer funds, access lending, and get expert support from one trusted banking partner built for your daily and long-term financial goals.</p>
            <div class="hero-actions">
                <a href="/login/" class="primary"><span class="material-symbols-outlined">lock</span>Access Online Banking</a>
                <a href="/contact/" class="secondary"><span class="material-symbols-outlined">support_agent</span>Speak to a Banking Advisor</a>
            </div>
        </div>
    </div>
</section>

<section class="trust-strip" aria-label="Trust and security highlights">
    <div class="container">
        <div class="trust-item"><span class="material-symbols-outlined">encrypted</span><p>Protected sessions and encrypted transactions</p></div>
        <div class="trust-item"><span class="material-symbols-outlined">verified_user</span><p>Compliance-first banking operations and controls</p></div>
        <div class="trust-item"><span class="material-symbols-outlined">support_agent</span><p>Dedicated customer support when you need assistance</p></div>
        <div class="trust-item"><span class="material-symbols-outlined">credit_card</span><p>Card and digital payment services for daily use</p></div>
    </div>
</section>




<!-- Features 1 Section -->
<section class="features-1">
    <div class="container">
        <div class="feature">
            <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M32.3667 18.8V16.3333C32.3667 16.0062 32.2367 15.6925 32.0054 15.4612C31.7741 15.2299 31.4604 15.1 31.1333 15.1V12.6333C31.1333 10.3436 30.2238 8.14771 28.6047 6.52864C26.9856 4.90958 24.7897 4 22.5 4C20.2103 4 18.0144 4.90958 16.3953 6.52864C14.7762 8.14771 13.8667 10.3436 13.8667 12.6333V15.1C13.5396 15.1 13.2259 15.2299 12.9946 15.4612C12.7633 15.6925 12.6333 16.0062 12.6333 16.3333V18.8C12.6333 19.1271 12.7633 19.4408 12.9946 19.6721C13.2259 19.9034 13.5396 20.0333 13.8667 20.0333C13.8701 21.664 14.3352 23.2604 15.2083 24.6377C16.0814 26.015 17.3267 27.1168 18.8 27.8157V31.1333L8.93333 34.8333C8.93333 34.8333 4 36.0667 4 38.5333V41H21.2667" stroke="black" stroke-width="2" stroke-linejoin="round"/>
                <path d="M32.3667 40.9999C37.1348 40.9999 41.0001 37.1347 41.0001 32.3666C41.0001 27.5986 37.1348 23.7333 32.3667 23.7333C27.5987 23.7333 23.7334 27.5986 23.7334 32.3666C23.7334 37.1347 27.5987 40.9999 32.3667 40.9999Z" stroke="black" stroke-width="2" stroke-linejoin="round"/>
                <path d="M32.3667 27.4333V37.3" stroke="black" stroke-width="2" stroke-linejoin="round"/>
                <path d="M37.2998 32.3667H27.4331" stroke="black" stroke-width="2" stroke-linejoin="round"/>
                </svg>                
            <h2>Open an account.</h2>
            <p>Open your account quickly and start banking with confidence.</p>
        </div>
        <div class="feature">
            <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0)">
                <path d="M44.2003 8.51039C43.6172 7.82115 42.8007 7.40016 41.901 7.32501L10.2115 4.67899C9.31171 4.60384 8.43685 4.8836 7.74753 5.46657C7.06102 6.04726 6.64064 6.85963 6.56312 7.75497L5.92556 13.7364H3.37982C1.5162 13.7364 0 15.2526 0 17.1163V36.9533C0 38.8169 1.5162 40.3331 3.37982 40.3331H35.1797C37.0434 40.3331 38.5596 38.8169 38.5596 36.9533V33.7353L39.688 33.8296C39.7832 33.8375 39.8778 33.8414 39.9717 33.8414C41.7091 33.8414 43.1902 32.5047 43.3374 30.7427L44.988 10.9745C45.0631 10.0747 44.7833 9.19971 44.2003 8.51039ZM3.37982 15.4942H35.1797C36.074 15.4942 36.8018 16.2219 36.8018 17.1163V18.9238H1.75781V17.1163C1.75781 16.2219 2.48545 15.4942 3.37982 15.4942ZM1.75781 20.6816H36.8018V24.3281H1.75781V20.6816ZM35.1797 38.5753H3.37982C2.48545 38.5753 1.75781 37.8476 1.75781 36.9533V26.0859H36.8018V36.9533C36.8018 37.8476 36.074 38.5753 35.1797 38.5753ZM43.2363 10.8281L41.5856 30.5963C41.5112 31.4876 40.7254 32.1525 39.8342 32.0778L38.5596 31.9714V17.1163C38.5596 15.2526 37.0434 13.7364 35.1797 13.7364H7.69339L8.31205 7.93216C8.31275 7.92548 8.31337 7.91889 8.31389 7.91212C8.38834 7.02082 9.17372 6.35602 10.0653 6.43073L41.7548 9.07675C42.1866 9.11279 42.5785 9.31485 42.8583 9.64567C43.138 9.97649 43.2723 10.3963 43.2363 10.8281Z" fill="black"/>
                <path d="M33.1236 28.699H24.6648C24.1794 28.699 23.7859 29.0925 23.7859 29.5779V35.0964C23.7859 35.5819 24.1794 35.9753 24.6648 35.9753H33.1236C33.609 35.9753 34.0025 35.5819 34.0025 35.0964V29.5779C34.0025 29.0925 33.609 28.699 33.1236 28.699ZM32.2447 34.2175H25.5437V30.4568H32.2447V34.2175Z" fill="black"/>
                </g>
                <defs>
                <clipPath id="clip0">
                <rect width="45" height="45" fill="white"/>
                </clipPath>
                </defs>
                </svg>
                
            <h2>Cards</h2>
            <p>Cards designed for secure everyday spending, online payments, and travel.</p>
        </div>
        <div class="feature">
            <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.65661 4.88395C10.8667 3.69229 14.516 3.71284 17.726 4.90108C19.3514 5.54485 21.1557 6.73994 21.301 8.6781C21.3619 10.8012 21.3247 12.9277 21.3146 15.0542C24.4638 14.746 27.907 14.9651 30.5798 16.8554C31.7489 17.6635 32.3301 19.0024 32.4112 20.4029C35.1144 20.4475 38.0339 21.0364 40.0478 23.002C40.7844 23.7006 41.0446 24.7484 40.9939 25.7449C40.9635 29.2959 41.0108 32.8469 40.9702 36.3979C40.8891 37.9662 39.5409 39.0278 38.2738 39.6989C35.0806 41.3426 31.3164 41.2467 27.9104 40.4146C24.6699 41.2091 21.2335 41.1406 18.1011 39.9352C14.5396 41.1166 10.5152 41.3358 7.03487 39.7298C5.47039 39.0175 3.87888 37.6067 4.00728 35.7062C4.01404 26.8064 4.00052 17.9066 4.01742 9.00683C3.93632 6.88033 5.92994 5.56882 7.65661 4.88395ZM10.5085 5.79825C8.93725 6.10986 7.23085 6.55845 6.10227 7.78093C5.37578 8.57195 5.68665 9.86291 6.54492 10.4245C8.4946 11.8387 11.0187 12.2086 13.3671 12.1298C15.4722 11.938 17.8545 11.5477 19.3209 9.85263C20.1454 8.84931 19.3108 7.50013 18.3343 6.99676C15.9656 5.68525 13.1441 5.4113 10.5085 5.79825ZM5.69003 11.8387C5.80829 13.1879 5.22372 14.9891 6.59222 15.8657C9.03186 17.595 12.2419 17.8073 15.1073 17.3587C16.2731 17.256 17.1922 16.4581 18.2194 15.9822C18.7634 15.7151 19.4595 15.4411 19.5777 14.7563C19.7467 13.7735 19.6419 12.7702 19.6115 11.784C18.3647 12.7907 16.807 13.2838 15.256 13.5577C12.0088 14.0132 8.47771 13.7426 5.69003 11.8387ZM21.2841 16.7423C19.5575 17.1396 17.3104 17.6601 16.6448 19.5503C16.5265 20.896 17.8477 21.7042 18.9019 22.1767C21.9802 23.3924 25.5552 23.4026 28.5896 22.0363C29.4377 21.5878 30.5156 21.0125 30.5865 19.9201C30.5899 18.7148 29.4951 17.9614 28.5254 17.5231C26.258 16.5403 23.7035 16.3759 21.2841 16.7423ZM5.69341 17.2492C5.81167 18.6018 5.22035 20.4303 6.5956 21.307C8.9947 23.0362 12.1237 23.1321 14.9485 22.8445C14.962 21.5775 14.9586 20.3071 14.9823 19.0401C11.8195 19.3893 8.3831 19.1428 5.69341 17.2492ZM32.222 22.0706C32.2152 24.2073 32.3132 26.3407 32.3267 28.474C34.5873 28.5186 37.0472 28.0837 38.7773 26.5085C39.5815 25.7997 39.4125 24.4573 38.5779 23.8547C36.7972 22.4438 34.4319 22.0261 32.222 22.0706ZM5.68665 22.7006C5.82519 23.995 5.23048 25.7209 6.45368 26.6283C8.85616 28.474 12.0595 28.5562 14.9384 28.2857C14.9722 27.0187 14.962 25.7517 14.962 24.4847C11.7959 24.81 8.40337 24.5361 5.68665 22.7006ZM16.6211 22.7657C16.7225 24.0978 16.219 25.8613 17.503 26.7687C19.7974 28.4569 22.8148 28.635 25.5451 28.3473C27.2785 28.0905 29.1674 27.5529 30.323 26.1318C30.8366 25.1079 30.5899 23.8992 30.6575 22.8C29.3093 23.5533 27.8901 24.2245 26.3527 24.4505C23.0581 24.9573 19.4662 24.6696 16.6211 22.7657ZM5.67989 28.159C5.7914 29.45 5.28117 31.1142 6.45706 32.0456C8.87305 33.8536 12.0493 33.9906 14.935 33.7304C14.9722 32.4599 14.962 31.1895 14.962 29.9225C11.7858 30.241 8.4304 29.9328 5.67989 28.159ZM16.6312 28.1967C16.7225 29.4568 16.246 31.0491 17.3104 32.0114C18.8107 33.3297 20.8753 33.7338 22.8013 33.8742C25.2038 33.9769 27.8056 33.6345 29.7857 32.1449C30.9954 31.2203 30.5697 29.5287 30.6643 28.2138C26.4675 30.7341 20.8043 30.8094 16.6312 28.1967ZM32.3943 30.1246C32.2051 31.3881 32.2051 32.6688 32.3098 33.9427C34.6853 33.8742 37.4493 33.5146 39.0712 31.5628C39.5544 30.5355 39.2165 29.313 39.2672 28.2104C37.3074 29.6794 34.79 30.152 32.3943 30.1246ZM5.67989 33.5626C5.74409 34.9563 5.28117 36.778 6.70035 37.6512C9.45762 39.4661 13.009 39.5551 16.1345 38.8497C15.4925 37.7813 14.7255 36.6992 14.9485 35.374C11.779 35.6446 8.41013 35.3672 5.67989 33.5626ZM16.6177 33.6687C16.7022 34.9974 16.2427 36.7506 17.5368 37.6341C19.8143 39.2504 22.7878 39.5517 25.4842 39.199C27.1906 38.884 29.1201 38.473 30.2791 37.0519C30.9413 36.3123 30.6643 35.0147 30.6034 33.6927C26.3763 36.1479 20.8314 36.1753 16.6177 33.6687ZM32.4112 35.5658C32.3639 37.0896 31.5462 38.4319 30.2892 39.2435C33.0904 39.5654 36.2227 39.3565 38.5509 37.5793C39.7943 36.6753 39.1794 34.9597 39.2672 33.6619C37.277 35.0727 34.8035 35.5658 32.4112 35.5658Z" fill="black"/>
                </svg>
                
            <h2>Quick Loans</h2>
            <p>Access short-term lending options with clear terms and guided support.</p>
        </div>
        <div class="feature">
            <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.9105 16.6385H11.6753C12.4081 15.5982 12.596 14.9047 12.4854 13.7279H13.7289C14.1604 13.7279 14.5102 13.3781 14.5102 12.9467C14.5102 12.5153 14.1604 12.1654 13.7289 12.1654H12.1005C11.8266 11.7481 11.3603 10.8932 11.3633 10.1939C11.3617 9.74932 11.556 9.42299 11.9571 9.19643C12.5714 8.84963 13.5618 8.84307 14.1196 9.18237C14.5639 9.45245 14.8989 9.82862 14.9941 10.1641C15.1118 10.5791 15.5436 10.8201 15.959 10.7025C16.374 10.5847 16.615 10.1528 16.4973 9.73761C16.2929 9.01698 15.7222 8.32799 14.9314 7.84721C13.8816 7.20885 12.3076 7.20416 11.1889 7.8358C10.2902 8.3433 9.79721 9.18268 9.8008 10.1933C9.79776 10.9046 10.0417 11.6083 10.3085 12.1653H9.65869C9.22721 12.1653 8.87744 12.5152 8.87744 12.9466C8.87744 13.378 9.22721 13.7279 9.65869 13.7279H10.9152L10.9174 13.747C11.0184 14.6493 10.9646 14.9846 10.2394 15.9572L9.49112 16.9492C9.31268 17.1857 9.28338 17.5029 9.41557 17.768C9.54768 18.0333 9.81854 18.2008 10.1148 18.2008H15.9106C16.3421 18.2008 16.6918 17.851 16.6918 17.4196C16.6918 16.9882 16.342 16.6385 15.9105 16.6385Z" fill="black"/>
                <path d="M12.7843 2C6.83775 2 2 6.83775 2 12.7842C2 18.7306 6.83775 23.5684 12.7843 23.5684C18.7308 23.5684 23.5685 18.7306 23.5685 12.7842C23.5685 6.83775 18.7307 2 12.7843 2V2ZM12.7843 22.0059C7.69939 22.0059 3.56251 17.8691 3.56251 12.7842C3.56251 7.69932 7.69939 3.56251 12.7843 3.56251C17.8691 3.56251 22.006 7.69932 22.006 12.7842C22.0059 17.8691 17.8691 22.0059 12.7843 22.0059Z" fill="black"/>
                <path d="M31.2159 20.4318C25.2695 20.4318 20.4316 25.2696 20.4316 31.216C20.4316 37.1624 25.2695 42.0002 31.2159 42.0002C37.1623 42.0002 42.0002 37.1624 42.0002 31.216C42.0002 25.2696 37.1624 20.4318 31.2159 20.4318ZM31.2159 40.4377C26.131 40.4377 21.9941 36.3009 21.9941 31.216C21.9941 26.1311 26.131 21.9943 31.2159 21.9943C36.3008 21.9943 40.4376 26.1311 40.4376 31.216C40.4376 36.3009 36.3008 40.4377 31.2159 40.4377Z" fill="black"/>
                <path d="M32.4911 30.4227C32.3286 30.3653 32.163 30.3056 31.9972 30.2439V27.1741C32.413 27.3116 32.6862 27.5059 32.6995 27.5155C33.0431 27.7725 33.5304 27.7041 33.7899 27.3616C34.0505 27.0177 33.9829 26.5276 33.639 26.267C33.5974 26.2356 32.9511 25.7562 31.9971 25.5591V24.9108C31.9971 24.4794 31.6474 24.1295 31.2159 24.1295C30.7844 24.1295 30.4346 24.4794 30.4346 24.9108V25.5537C30.3309 25.5753 30.226 25.601 30.1199 25.6329C29.0953 25.9407 28.3296 26.8162 28.1216 27.9175C27.9313 28.9254 28.2559 29.8988 28.9685 30.4578C29.3402 30.7493 29.8018 31.0156 30.4346 31.2965V35.2885C29.9066 35.246 29.5658 35.1136 28.9891 34.7364C28.628 34.5002 28.1438 34.6013 27.9076 34.9624C27.6714 35.3235 27.7726 35.8077 28.1337 36.0439C29.0318 36.6315 29.6462 36.8092 30.4346 36.8553V37.521C30.4346 37.9524 30.7844 38.3022 31.2159 38.3022C31.6474 38.3022 31.9971 37.9524 31.9971 37.521V36.7666C33.46 36.4081 34.3982 35.1642 34.6044 33.9375C34.8713 32.3504 34.0417 30.9708 32.4911 30.4227ZM29.933 29.2284C29.6851 29.0339 29.5768 28.6331 29.6572 28.2074C29.7284 27.8305 29.97 27.381 30.4348 27.1796V29.5552C30.2469 29.4501 30.0768 29.3413 29.933 29.2284ZM33.0636 33.6784C32.97 34.2353 32.6083 34.8242 31.9972 35.1174V31.9062C33.1885 32.3432 33.1152 33.3716 33.0636 33.6784Z" fill="black"/>
                <path d="M26.6094 5.6493C32.5204 5.6493 37.4243 10.0402 38.2329 15.7314C37.9269 15.4409 37.4434 15.445 37.1432 15.7451C36.8381 16.0502 36.8381 16.5449 37.1432 16.85L38.5795 18.2864C38.7321 18.4389 38.932 18.5152 39.1319 18.5152C39.3319 18.5152 39.5319 18.4389 39.6844 18.2864L41.1207 16.85C41.4258 16.545 41.4258 16.0503 41.1207 15.7451C40.8156 15.4401 40.3209 15.4401 40.0158 15.7451L39.8324 15.9285C39.1021 9.27814 33.451 4.08679 26.6094 4.08679C26.1779 4.08679 25.8281 4.43664 25.8281 4.86804C25.8281 5.29945 26.1779 5.6493 26.6094 5.6493Z" fill="black"/>
                <path d="M17.3909 38.3508C11.4799 38.3508 6.57594 33.9598 5.76734 28.2686C5.91789 28.4116 6.1111 28.4837 6.30461 28.4837C6.50453 28.4837 6.70454 28.4074 6.85704 28.2549C7.16211 27.9498 7.16211 27.4551 6.85704 27.1499L5.4207 25.7136C5.11562 25.4086 4.62093 25.4086 4.31578 25.7136L2.87944 27.1499C2.57436 27.455 2.57436 27.9497 2.87944 28.2549C3.18452 28.5599 3.67921 28.5599 3.98437 28.2549L4.16773 28.0715C4.8982 34.7219 10.5493 39.9133 17.3909 39.9133C17.8224 39.9133 18.1722 39.5634 18.1722 39.132C18.1722 38.7006 17.8224 38.3508 17.3909 38.3508Z" fill="black"/>
                </svg>
                
            <h2>Money Transfer</h2>
            <p>Send and receive money securely across local and international destinations.</p>
        </div>
    </div>
</section>





<!-- Latest news Banner Section -->
<section class="latest-news">
    <div class="title">
        <span>Latest News</span>
    </div>
    <div class="ticker-wrapper"></div>
</section>





<!-- Features 2 Section -->
<section class="features-2">
    <div class="container">
        <div class="heading">
            <h2>Banking Made Practical: <span>Services Built Around Real Financial Needs.</span></h2>
            <p>From everyday payments to savings, lending, and advisory support, access core banking services through secure digital channels and responsive branch support.</p>
        </div>
        <div class="features">
            <div class="feature">
                <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_3_14)">
                    <path d="M3.51412 46.0124H18.5492V50.1122H15.247H15.197V50.1622V51.8421V51.8921H15.247H30.332H30.382V51.8421V50.1622V50.1122H30.332H27.0298V46.0124H42.0648C43.4805 46.0107 44.6273 44.8612 44.6289 43.4426V30.8432V30.7932H44.5789H42.9028H42.8528V30.8432V35.8329H2.72612V13.2041C2.72651 12.7677 3.0791 12.4145 3.51422 12.4141H21.1134H21.1634V12.3641V10.6842V10.6342H21.1134H3.51418C2.09848 10.6359 0.951681 11.7854 0.950012 13.204V43.4425C0.951681 44.8611 2.09842 46.0107 3.51412 46.0124ZM25.2536 50.1122H20.3253V46.0124H25.2536V50.1122ZM3.51422 44.2325C3.0791 44.2321 2.72651 43.8789 2.72612 43.4425V37.6128H42.8528V43.4425C42.8524 43.8789 42.4999 44.2321 42.0647 44.2325H3.51422Z" fill="#444444" stroke="#444444" stroke-width="0.1"/>
                    <path d="M25.2104 40.3422C25.2104 41.3449 24.3974 42.1579 23.3946 42.1579C22.3919 42.1579 21.5789 41.3449 21.5789 40.3422C21.5789 39.3394 22.3919 38.5264 23.3946 38.5264C24.3974 38.5264 25.2104 39.3394 25.2104 40.3422Z" fill="#444444"/>
                    <path d="M37.3159 0.950012C29.2657 0.950012 22.7396 7.47619 22.7396 15.5263C22.7396 23.5764 29.2657 30.1026 37.3159 30.1026C45.366 30.1026 51.8922 23.5764 51.8922 15.5263C51.8834 7.47994 45.3623 0.958804 37.3159 0.950012ZM31.3306 8.22635C30.0302 8.04938 28.7465 7.76733 27.4923 7.38249C29.0641 5.48821 31.1483 4.08773 33.4946 3.34826C32.4873 4.83144 31.754 6.48363 31.3306 8.22635ZM26.4137 8.91595C27.8808 9.41773 29.3927 9.77839 30.9282 9.9928C30.6479 11.5198 30.4881 13.0663 30.4507 14.6185H24.5954C24.7369 12.5997 25.3605 10.6441 26.4137 8.91595ZM30.9282 21.0598C29.3927 21.2743 27.8808 21.6349 26.4136 22.1367C25.3592 20.4064 24.7357 18.4487 24.595 16.4275H30.4507C30.4876 17.9818 30.6475 19.5307 30.9282 21.0598ZM31.3306 22.8263C31.754 24.569 32.4873 26.2212 33.4946 27.7044C31.1483 26.9649 29.0641 25.5644 27.4923 23.6702C28.7465 23.2853 30.0302 23.0033 31.3306 22.8263ZM36.4114 28.0459C35.8007 27.7251 35.1921 27.091 34.6353 26.1856C34.0648 25.2578 33.5512 24.0491 33.1469 22.6116C34.1944 22.5119 35.2885 22.4504 36.4114 22.4282V28.0459ZM36.4114 16.4275V20.6196C35.1455 20.6436 33.9081 20.7157 32.7269 20.8366C32.453 19.3816 32.2967 17.9072 32.259 16.4275H36.4114ZM36.4114 14.6185H32.2594C32.2972 13.1408 32.4534 11.6685 32.7269 10.216C33.9081 10.3369 35.1456 10.4086 36.4114 10.433V14.6185ZM36.4114 8.62442C35.2885 8.60227 34.1944 8.54077 33.1469 8.44107C33.5512 7.00355 34.0648 5.79483 34.6353 4.86707C35.1921 3.96163 35.8007 3.32752 36.4114 3.00679V8.62442ZM43.3011 8.22635C42.8777 6.48363 42.1445 4.83144 41.1372 3.34826C43.4834 4.08774 45.5676 5.48821 47.1394 7.38249C45.8853 7.76733 44.6015 8.04938 43.3011 8.22635ZM38.2204 8.62442V3.00679C38.831 3.32752 39.4397 3.96163 39.9964 4.86707C40.5669 5.79483 41.0806 7.00355 41.4849 8.44107C40.4373 8.54077 39.3433 8.60227 38.2204 8.62442ZM38.2204 14.6185V10.433C39.4862 10.409 40.7236 10.3369 41.9049 10.216C42.1784 11.6685 42.3346 13.1408 42.3724 14.6185H38.2204ZM38.2204 16.4275H42.3728C42.335 17.9072 42.1788 19.3816 41.9049 20.8366C40.7236 20.7157 39.4862 20.6436 38.2204 20.6196V16.4275ZM38.2204 28.0459V22.4282C39.3433 22.4504 40.4373 22.5119 41.4849 22.6116C41.0806 24.0491 40.5669 25.2578 39.9964 26.1856C39.4397 27.091 38.831 27.7251 38.2204 28.0459ZM43.3011 22.8263C44.6015 23.0033 45.8853 23.2853 47.1394 23.6701C45.5676 25.5644 43.4834 26.9649 41.1372 27.7044C42.1445 26.2212 42.8777 24.569 43.3011 22.8263ZM50.0367 16.4275C49.8961 18.4487 49.2725 20.4064 48.2181 22.1367C46.751 21.6349 45.239 21.2743 43.7035 21.0598C43.9843 19.5307 44.1441 17.9818 44.181 16.4275H50.0367ZM48.2181 8.91595C49.2713 10.6441 49.8948 12.5997 50.0363 14.6185H44.181C44.1437 13.0667 43.9839 11.5198 43.7035 9.9928C45.239 9.77839 46.751 9.41773 48.2181 8.91595Z" fill="#F13223" stroke="#F13223" stroke-width="0.1"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_3_14">
                    <rect width="52" height="52" fill="white"/>
                    </clipPath>
                    </defs>
                </svg>
                <span>Online Banking</span>
            </div>
            <div class="feature">
                <svg width="61" height="80" viewBox="0 0 61 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <mask id="mask0_3_4" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="61" height="80">
                    <path d="M61 0H0V80H61V0Z" fill="white"/>
                    </mask>
                    <g mask="url(#mask0_3_4)">
                    <path d="M15.962 0.00612993C16.4209 0.00509421 16.8817 -0.0104417 17.3406 0.012344C18.0148 0.0258082 18.6062 0.672092 18.5586 1.3453C18.5927 2.08894 17.8657 2.71969 17.1386 2.65444C14.4271 2.55501 11.6845 3.24169 9.36455 4.65544C7.08909 6.00911 5.23206 8.04739 4.08242 10.4316C3.07053 12.4875 2.58168 14.7982 2.67593 17.0881C2.74843 17.7479 2.26786 18.4118 1.61122 18.5298C0.896577 18.7059 0.142577 18.1653 0.025542 17.4475C-0.207493 13.344 1.16275 9.17425 3.78 6.00393C6.73177 2.38516 11.2962 0.146987 15.962 0.00612993Z" fill="#F13223"/>
                    <path d="M13.1657 6.61279C13.6794 6.58172 14.2097 6.84894 14.4365 7.32226C14.739 7.83804 14.6023 8.53818 14.1579 8.92761C13.9218 9.16065 13.5862 9.22382 13.2848 9.32739C11.3708 9.91982 9.81106 11.5335 9.28389 13.4661C9.20724 13.7913 9.04049 14.1052 8.76603 14.305C8.22125 14.7525 7.32743 14.6303 6.909 14.0679C6.69357 13.781 6.58482 13.4081 6.64386 13.0518C7.36886 9.90636 10.0016 7.28083 13.1657 6.61279Z" fill="#F13223"/>
                    <path d="M14.6868 14.9483C15.6532 13.9063 17.0555 13.259 18.4827 13.2714C25.6291 13.2714 32.7755 13.2714 39.9219 13.2714C41.3233 13.2694 42.6759 13.9239 43.6422 14.9193C44.536 15.8959 45.0995 17.202 45.0725 18.536C45.0725 19.2216 45.0725 19.9083 45.0653 20.5939C45.3211 20.5245 45.5272 20.3495 45.7623 20.2356C46.7276 19.7301 47.8627 19.4557 48.944 19.7032C49.8834 19.9041 50.7223 20.539 51.1615 21.3956C51.6369 22.2614 51.7353 23.2692 51.7094 24.2407C51.8564 28.2872 53.0113 32.3088 55.0858 35.7909C56.2126 37.563 57.5228 39.2295 58.428 41.1352C60.1152 44.6121 60.6186 48.5126 60.8671 52.3271C60.9914 55.2789 61.1768 58.269 60.6248 61.1917C60.1287 63.9322 58.9821 66.5795 57.179 68.7162C55.0123 71.2941 51.9849 73.0486 48.7793 73.9993C48.2739 75.2753 47.405 76.4012 46.2988 77.2121C45.2683 77.9858 44.07 78.513 42.8344 78.8589C41.0146 79.3809 39.112 79.5362 37.226 79.558C35.6279 79.5539 34.0205 79.4109 32.4762 78.9842C29.8994 78.3017 27.5462 76.9138 25.5711 75.1386C24.9839 74.5918 24.3759 74.0563 23.9016 73.4038C22.9446 72.4085 22.0249 71.3769 21.188 70.278C20.1875 70.2635 19.187 70.2842 18.1865 70.2635C17.2223 70.1765 16.259 69.8544 15.4926 69.2516C15.4553 69.2485 15.3818 69.2413 15.3445 69.2371C14.5304 69.9383 13.445 70.336 12.3679 70.2677C11.2472 70.2035 10.1774 69.6566 9.42335 68.8322C7.60568 66.8933 7.45447 63.6236 9.0971 61.5335C9.10228 61.4942 9.11264 61.4144 9.11782 61.3751C7.74343 60.416 6.87965 58.8179 6.67872 57.169C6.40529 55.167 7.15204 53.0655 8.6331 51.6912C7.29497 50.5094 6.5824 48.6959 6.63211 46.9238C6.66318 45.2501 7.36125 43.5681 8.62482 42.4516C7.42236 41.2968 6.67147 39.68 6.63315 38.0073C6.56272 36.2259 7.24525 34.3948 8.56889 33.183C9.49896 32.2975 10.7812 31.7703 12.0706 31.8221C12.4725 31.8179 12.8629 31.9215 13.2576 31.9836C13.2617 27.4659 13.2586 22.9481 13.2586 18.4303C13.2897 17.1481 13.7992 15.8773 14.6868 14.9483ZM15.9276 18.4324C15.9162 21.7819 15.9255 25.1303 15.9224 28.4798C15.9369 30.284 15.8934 32.0913 15.9442 33.8945C17.6345 36.0343 17.6707 39.2947 16.0726 41.4956C16.0094 41.6013 15.9038 41.7017 15.9235 41.8374C15.9297 42.3801 15.9017 42.9259 15.939 43.4676C16.7407 44.7415 17.2678 46.2247 17.2264 47.7451C17.2637 48.9921 16.8504 50.2505 16.0488 51.2106C15.8385 51.4146 15.9463 51.7243 15.9214 51.9832C15.9525 52.3126 15.8303 52.6989 16.0612 52.9785C16.8453 54.1748 17.2015 55.5999 17.2627 57.0168C25.6353 57.0085 34.0091 57.0033 42.3807 57.0199C42.4739 56.6967 42.417 56.357 42.4315 56.0266C42.4304 50.0537 42.4366 44.0807 42.4284 38.1078C42.0037 36.0913 41.258 34.1431 40.9711 32.0945C40.5434 29.1841 40.9732 26.1049 42.4325 23.5281C42.4284 21.8295 42.4398 20.131 42.4273 18.4324C42.3745 17.0839 41.1689 15.9104 39.8163 15.9156C32.7393 15.9166 25.6612 15.9146 18.5842 15.9166C17.2181 15.8783 15.9473 17.059 15.9276 18.4324ZM46.9171 22.6394C45.7489 23.2723 44.913 24.3815 44.3579 25.5653C43.3657 27.6968 43.23 30.1504 43.7013 32.4321C44.1953 34.5273 44.8737 36.576 45.3263 38.6826C45.6391 40.1927 45.8162 41.797 45.3325 43.2926C45.2496 43.6043 45.0539 43.8922 45.0705 44.2257C45.0725 51.164 45.0725 58.1032 45.0705 65.0425C45.0932 66.7866 44.1176 68.4769 42.6604 69.4142C41.8732 69.9414 40.9338 70.2262 39.9903 70.2687C34.8718 70.2873 29.7533 70.249 24.6359 70.2873C25.2117 70.9823 25.9046 71.5716 26.4515 72.2904C28.4494 74.3546 30.9827 75.9869 33.8226 76.57C35.3151 76.9014 36.8531 76.9345 38.3746 76.8683C39.9955 76.7802 41.6371 76.57 43.1523 75.9568C44.4055 75.4556 45.5769 74.5949 46.1569 73.3448C46.4045 72.8891 46.4252 72.3101 46.8218 71.9424C47.1895 71.6151 47.7043 71.5685 48.1538 71.4142C50.6933 70.6229 53.0972 69.2465 54.8673 67.2382C56.6756 65.23 57.7372 62.6324 58.1215 59.9758C58.4311 58.0204 58.3069 56.0339 58.2924 54.0629C58.1215 50.3831 57.8397 46.6141 56.4219 43.1714C55.6938 41.3558 54.5462 39.7608 53.4514 38.1554C52.1288 36.2456 51.1594 34.112 50.4251 31.9132C49.698 29.6957 49.2247 27.3892 49.0859 25.0589C49.0372 24.3629 49.1191 23.6544 48.9482 22.9719C48.8715 22.7161 48.7307 22.4458 48.4759 22.3318C47.9404 22.1817 47.3873 22.3888 46.9171 22.6394ZM11.7143 34.4901C11.2462 34.5346 10.804 34.75 10.4497 35.0535C9.01528 36.3139 8.92414 38.7178 10.1028 40.1782C10.5564 40.7178 11.2348 41.1186 11.9567 41.0886C12.5999 41.1072 13.2058 40.7768 13.6418 40.3222C14.8681 38.9933 14.8992 36.7614 13.7506 35.3756C13.2617 34.7873 12.488 34.3979 11.7143 34.4901ZM11.1934 43.8902C10.7501 44.0528 10.3658 44.3562 10.0696 44.7198C9.01114 46.0672 9.02668 48.1532 10.1225 49.4758C10.5937 50.0361 11.3104 50.4338 12.0582 50.3799C12.7873 50.3427 13.5569 50.0941 14.0312 49.511C14.663 48.7414 14.6817 47.6519 14.4704 46.7239C14.2518 45.7431 13.7661 44.7716 12.9634 44.145C12.4704 43.7441 11.7796 43.6437 11.1934 43.8902ZM11.4823 53.0883C10.5834 53.2851 9.89046 54.0329 9.57146 54.8729C9.07328 56.1064 9.23382 57.6082 10.051 58.6708C10.4839 59.2197 11.1354 59.634 11.85 59.6537C12.5709 59.6755 13.3228 59.4683 13.8686 58.9815C14.5304 58.3135 14.6868 57.2995 14.5429 56.4026C14.3699 55.2416 13.8417 54.064 12.8712 53.3618C12.4777 53.0573 11.9598 52.9889 11.4823 53.0883ZM16.5936 59.6848C16.4341 60.039 16.1627 60.3207 15.9514 60.6428C15.8903 60.8551 15.9307 61.083 15.9287 61.3036C17.2585 62.8478 17.5827 65.1233 16.8825 67.0176C17.3662 67.3967 17.9711 67.6339 18.5925 67.6049C25.6695 67.608 32.7455 67.6059 39.8225 67.6059C40.8189 67.6121 41.7821 66.9741 42.1839 66.0648C42.3818 65.6505 42.4429 65.1865 42.4335 64.7318C42.4294 63.0477 42.4346 61.3637 42.4315 59.6796C33.8185 59.6744 25.2055 59.6651 16.5936 59.6848ZM12.0965 62.4087C11.3953 62.6821 10.9334 63.3595 10.7366 64.0638C10.4995 64.969 10.6186 65.985 11.1582 66.7639C11.4927 67.261 12.0665 67.6639 12.69 67.6038C13.1892 67.5924 13.6263 67.2776 13.9328 66.9047C14.8494 65.7592 14.7987 63.9592 13.8086 62.8748C13.3798 62.4232 12.6993 62.1601 12.0965 62.4087Z" fill="#444444"/>
                    <path d="M26.5181 63.6908C26.4663 62.9814 27.0939 62.3134 27.8024 62.3113C28.7024 62.3102 29.6035 62.3082 30.5035 62.3123C30.9385 62.3133 31.3766 62.5288 31.6065 62.9047C31.9307 63.3822 31.8748 64.0596 31.5195 64.507C31.2523 64.7835 30.8867 64.9731 30.4952 64.972C29.5993 64.9751 28.7024 64.9772 27.8065 64.972C27.1312 64.9586 26.5233 64.3713 26.5181 63.6908Z" fill="#444444"/>
                    <path d="M34.121 41.607C34.121 42.343 33.937 43.0483 33.569 43.723C33.201 44.3823 32.6567 44.9343 31.936 45.379C31.2307 45.8237 30.3797 46.0767 29.383 46.138V48.001H28.141V46.138C26.7457 46.0153 25.611 45.5707 24.737 44.804C23.863 44.022 23.4183 43.033 23.403 41.837H25.634C25.6953 42.481 25.933 43.0407 26.347 43.516C26.7763 43.9913 27.3743 44.2903 28.141 44.413V38.663C27.1137 38.4023 26.2857 38.134 25.657 37.858C25.0283 37.582 24.4917 37.1527 24.047 36.57C23.6023 35.9873 23.38 35.2053 23.38 34.224C23.38 32.982 23.8093 31.9547 24.668 31.142C25.542 30.3293 26.6997 29.877 28.141 29.785V27.876H29.383V29.785C30.6863 29.8923 31.7367 30.314 32.534 31.05C33.3313 31.7707 33.7913 32.7137 33.914 33.879H31.683C31.6063 33.3423 31.3687 32.8593 30.97 32.43C30.5713 31.9853 30.0423 31.694 29.383 31.556V37.168C30.395 37.4287 31.2153 37.697 31.844 37.973C32.488 38.2337 33.0247 38.6553 33.454 39.238C33.8987 39.8207 34.121 40.6103 34.121 41.607ZM25.519 34.109C25.519 34.8603 25.7413 35.4353 26.186 35.834C26.6307 36.2327 27.2823 36.5623 28.141 36.823V31.51C27.3437 31.5867 26.7073 31.8473 26.232 32.292C25.7567 32.7213 25.519 33.327 25.519 34.109ZM29.383 44.436C30.211 44.344 30.855 44.045 31.315 43.539C31.7903 43.033 32.028 42.4273 32.028 41.722C32.028 40.9707 31.798 40.3957 31.338 39.997C30.878 39.583 30.2263 39.2533 29.383 39.008V44.436Z" fill="#F13223"/>
                    </g>
                </svg>                
                <span>Mobile Banking</span>
            </div>
            <div class="feature">
                <svg width="61" height="61" viewBox="0 0 61 61" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M29.4928 26L26.7937 32.037L24.1061 26H20.661V34.6669L16.8244 26H13.9179L10 35H12.3174L13.1758 32.9896H17.5646L18.4327 35H22.8602V28.3162L25.7958 35H27.7955L30.8027 28.4312V35H33V26H29.4928ZM14.0051 31.0455L15.3169 27.8994L16.681 31.0455H14.0051Z" fill="#F13223"/>
                    <path d="M47.1639 30.3892L51 26.0175H48.2745L45.819 28.805L43.4448 26H35V34.9806H43.169L45.7416 32.0123L48.2518 35H50.9679L47.1639 30.3892ZM42.0509 33.095H37.1117V31.3203H41.8374V29.6136H37.1117V27.9322L42.3248 27.9458L44.41 30.3911L42.0509 33.095Z" fill="#F13223"/>
                    <path d="M52.1041 10.1666H8.89587C3.99038 10.1666 0 14.1571 0 19.0625V41.9375C0 46.8429 3.99038 50.8334 8.89587 50.8334H52.1042C57.0096 50.8334 61 46.8429 61 41.9375V19.0625C61 14.1571 57.0096 10.1666 52.1041 10.1666ZM58.4584 41.9375C58.4584 45.4399 55.6091 48.2916 52.1042 48.2916H8.89587C5.39087 48.2916 2.54175 45.4399 2.54175 41.9375V19.0625C2.54175 15.5601 5.39099 12.7084 8.89587 12.7084H52.1042C55.6092 12.7084 58.4584 15.5601 58.4584 19.0625V41.9375Z" fill="#444444"/>
                </svg>  
                <span>American Express Cards</span>
            </div>
            <div class="feature">
                <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50.3601 7.81251C37.2632 7.65626 25.7792 16.5525 21.701 28.9978C17.387 42.1619 24.0407 52.5629 29.0985 60.4675C31.272 63.8644 33.2198 66.907 34.0057 69.5679C34.4057 70.9226 35.6023 71.875 37.0148 71.875H39.0625C39.0625 67.3422 36.2756 62.986 33.0475 57.9407C29.2741 52.0454 25 45.3641 25 37.5C25 23.8281 36.03 12.6844 49.6613 12.5C60.405 12.3578 69.6817 19.3995 73.4833 29.4495C77.8302 40.9417 71.951 50.1297 66.9525 57.9407C63.7244 62.986 60.9375 67.3422 60.9375 71.875H62.9852C64.3977 71.875 65.5943 70.921 65.9943 69.5679C66.7818 66.907 68.7281 63.8675 70.9015 60.4706C74.8171 54.3519 79.6875 46.736 79.6875 37.5C79.6875 21.25 66.5632 8.00782 50.3601 7.81251ZM13.5223 10.2326L10.6415 13.9313L17.7277 19.455L20.6085 15.7562L13.5223 10.2326ZM86.4807 10.2387L79.3915 15.7593L82.2723 19.458L89.3616 13.9343L86.4807 10.2387ZM3.125 35.9375V40.625H10.9375V35.9375H3.125ZM89.0625 35.9375V40.625H96.875V35.9375H89.0625ZM41.8793 38.681L41.3238 38.9954C39.3863 40.0985 38.5649 42.4453 39.3524 44.5313C41.1133 49.1953 43.75 58.4203 43.75 71.875H48.4375C48.4375 50.4063 42.1465 39.1514 41.8793 38.681ZM58.1207 38.681C57.8535 39.1498 51.5625 50.4063 51.5625 71.875H56.25C56.25 58.4188 58.8852 49.1922 60.6445 44.5313C61.432 42.4484 60.6151 40.1062 58.6823 39.0015L58.1207 38.681ZM82.2723 55.5451L79.3915 59.2438L86.4777 64.7675L89.3585 61.0687L82.2723 55.5451ZM17.7246 55.5481L10.6384 61.0718L13.5193 64.7675L20.6055 59.2468L17.7246 55.5481ZM35.9375 76.5625L36.8897 83.2215C37.2568 85.7965 39.1815 87.8728 41.7206 88.4369L43.9301 88.9282C44.578 91.689 47.0417 93.75 50 93.75C52.9584 93.75 55.422 91.689 56.07 88.9282L58.2794 88.4369C60.8185 87.8728 62.7432 85.7965 63.1104 83.2215L64.0625 76.5625H51.5625H48.4375H35.9375Z" fill="#444444"/>
                    <path d="M13.5223 10.2325L10.6415 13.9313L17.7277 19.455L20.6085 15.7562L13.5223 10.2325ZM86.4807 10.2386L79.3915 15.7593L82.2723 19.458L89.3616 13.9343L86.4807 10.2386ZM3.125 35.9375V40.625H10.9375V35.9375H3.125ZM89.0625 35.9375V40.625H96.875V35.9375H89.0625ZM82.2723 55.545L79.3915 59.2438L86.4777 64.7675L89.3585 61.0687L82.2723 55.545ZM17.7246 55.5481L10.6384 61.0718L13.5193 64.7675L20.6055 59.2468L17.7246 55.5481Z" fill="#F13223"/>
                    </svg>                
                <span>Financial Advice</span>
            </div>
            <div class="feature">
                <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.9105 16.6385H11.6753C12.4081 15.5982 12.596 14.9047 12.4854 13.7279H13.7289C14.1604 13.7279 14.5102 13.3781 14.5102 12.9467C14.5102 12.5153 14.1604 12.1654 13.7289 12.1654H12.1005C11.8266 11.7481 11.3603 10.8932 11.3633 10.1939C11.3617 9.74932 11.556 9.42299 11.9571 9.19643C12.5714 8.84963 13.5618 8.84307 14.1196 9.18237C14.5639 9.45245 14.8989 9.82862 14.9941 10.1641C15.1118 10.5791 15.5436 10.8201 15.959 10.7025C16.374 10.5847 16.615 10.1528 16.4973 9.73761C16.2929 9.01698 15.7222 8.32799 14.9314 7.84721C13.8816 7.20885 12.3076 7.20416 11.1889 7.8358C10.2902 8.3433 9.79721 9.18268 9.8008 10.1933C9.79776 10.9046 10.0417 11.6083 10.3085 12.1653H9.65869C9.22721 12.1653 8.87744 12.5152 8.87744 12.9466C8.87744 13.378 9.22721 13.7279 9.65869 13.7279H10.9152L10.9174 13.747C11.0184 14.6493 10.9646 14.9846 10.2394 15.9572L9.49112 16.9492C9.31268 17.1857 9.28338 17.5029 9.41557 17.768C9.54768 18.0333 9.81854 18.2008 10.1148 18.2008H15.9106C16.3421 18.2008 16.6918 17.851 16.6918 17.4196C16.6918 16.9882 16.342 16.6385 15.9105 16.6385Z" fill="#F13223"/>
                    <path d="M12.7843 2C6.83775 2 2 6.83775 2 12.7842C2 18.7306 6.83775 23.5684 12.7843 23.5684C18.7308 23.5684 23.5685 18.7306 23.5685 12.7842C23.5685 6.83775 18.7307 2 12.7843 2ZM12.7843 22.0059C7.69939 22.0059 3.56251 17.8691 3.56251 12.7842C3.56251 7.69932 7.69939 3.56251 12.7843 3.56251C17.8691 3.56251 22.006 7.69932 22.006 12.7842C22.0059 17.8691 17.8691 22.0059 12.7843 22.0059Z" fill="#444444"/>
                    <path d="M31.2159 20.4318C25.2695 20.4318 20.4316 25.2696 20.4316 31.216C20.4316 37.1624 25.2695 42.0002 31.2159 42.0002C37.1623 42.0002 42.0002 37.1624 42.0002 31.216C42.0002 25.2696 37.1624 20.4318 31.2159 20.4318ZM31.2159 40.4377C26.131 40.4377 21.9941 36.3009 21.9941 31.216C21.9941 26.1311 26.131 21.9943 31.2159 21.9943C36.3008 21.9943 40.4376 26.1311 40.4376 31.216C40.4376 36.3009 36.3008 40.4377 31.2159 40.4377Z" fill="#444444"/>
                    <path d="M32.4911 30.4227C32.3286 30.3653 32.163 30.3056 31.9972 30.2439V27.1741C32.413 27.3116 32.6862 27.5059 32.6995 27.5155C33.0431 27.7725 33.5304 27.7041 33.7899 27.3616C34.0505 27.0177 33.9829 26.5276 33.639 26.267C33.5974 26.2356 32.9511 25.7562 31.9971 25.5591V24.9108C31.9971 24.4794 31.6474 24.1295 31.2159 24.1295C30.7844 24.1295 30.4346 24.4794 30.4346 24.9108V25.5537C30.3309 25.5753 30.226 25.601 30.1199 25.6329C29.0953 25.9407 28.3296 26.8162 28.1216 27.9175C27.9313 28.9254 28.2559 29.8988 28.9685 30.4578C29.3402 30.7493 29.8018 31.0156 30.4346 31.2965V35.2885C29.9066 35.246 29.5658 35.1136 28.9891 34.7364C28.628 34.5002 28.1438 34.6013 27.9076 34.9624C27.6714 35.3235 27.7726 35.8077 28.1337 36.0439C29.0318 36.6315 29.6462 36.8092 30.4346 36.8553V37.521C30.4346 37.9524 30.7844 38.3022 31.2159 38.3022C31.6474 38.3022 31.9971 37.9524 31.9971 37.521V36.7666C33.46 36.4081 34.3982 35.1642 34.6044 33.9375C34.8713 32.3504 34.0417 30.9708 32.4911 30.4227ZM29.933 29.2284C29.6851 29.0339 29.5768 28.6331 29.6572 28.2074C29.7284 27.8305 29.97 27.381 30.4348 27.1796V29.5552C30.2469 29.4501 30.0768 29.3413 29.933 29.2284ZM33.0636 33.6784C32.97 34.2353 32.6083 34.8242 31.9972 35.1174V31.9062C33.1885 32.3432 33.1152 33.3716 33.0636 33.6784Z" fill="#F13223"/>
                    <path d="M26.6094 5.6493C32.5204 5.6493 37.4243 10.0402 38.2329 15.7314C37.9269 15.4409 37.4434 15.445 37.1432 15.7451C36.8381 16.0502 36.8381 16.5449 37.1432 16.85L38.5795 18.2864C38.7321 18.4389 38.932 18.5152 39.1319 18.5152C39.3319 18.5152 39.5319 18.4389 39.6844 18.2864L41.1207 16.85C41.4258 16.545 41.4258 16.0503 41.1207 15.7451C40.8156 15.4401 40.3209 15.4401 40.0158 15.7451L39.8324 15.9285C39.1021 9.27814 33.451 4.08679 26.6094 4.08679C26.1779 4.08679 25.8281 4.43664 25.8281 4.86804C25.8281 5.29945 26.1779 5.6493 26.6094 5.6493Z" fill="#444444"/>
                    <path d="M17.3909 38.3508C11.4799 38.3508 6.57594 33.9598 5.76734 28.2686C5.91789 28.4116 6.1111 28.4837 6.30461 28.4837C6.50453 28.4837 6.70454 28.4074 6.85704 28.2549C7.16211 27.9498 7.16211 27.4551 6.85704 27.1499L5.4207 25.7136C5.11562 25.4086 4.62093 25.4086 4.31578 25.7136L2.87944 27.1499C2.57436 27.455 2.57436 27.9497 2.87944 28.2549C3.18452 28.5599 3.67921 28.5599 3.98437 28.2549L4.16773 28.0715C4.8982 34.7219 10.5493 39.9133 17.3909 39.9133C17.8224 39.9133 18.1722 39.5634 18.1722 39.132C18.1722 38.7006 17.8224 38.3508 17.3909 38.3508Z" fill="#444444"/>
                    </svg>
                    
                <span>Foreign Exchange</span>
            </div>
        </div>
    </div>
</section>





<!-- Loan Calculator Section -->
<section class="loan-calculator">
    <div class="container">
        <?php if ($loanFlowMessage !== ''): ?>
            <div class="loan-flow-alert" role="status">
                <?php echo htmlspecialchars($loanFlowMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <div class="left">
            <div class="details">
                <h1>Payday Loan<br>Calculator</h1>
                <p>Use this tool to estimate a short-term loan based on your monthly income and repayment period. We calculate principal, service fee, total repayment, and an equivalent monthly installment.</p>
                <p class="no">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>* Estimates only. Final approval depends on eligibility, account history, and underwriting checks.</span>
                </p>
            </div>
            <div class="calculator">
                <p>What is your monthly salary?</p>
                <input type="number" id="salaryLiveInput" class="salary-live-input" min="500" max="50000" step="50" placeholder="Enter monthly salary">
                <label for="loanTenorDays">Select repayment tenor</label>
                <select id="loanTenorDays" class="loan-tenor-select">
                    <option value="14">14 days</option>
                    <option value="30" selected>30 days</option>
                    <option value="60">60 days</option>
                </select>
                <div class="toggle-box">
                    <div class="toggle">
                        <span class="ball"></span>
                    </div>
                    <div class="fig">
                        <span>$</span>
                        <span id="salaryInput">0</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="right">
            <p>Eligible Loan Amount</p>
            <h1>$<span id="loanAmount">0.00</span></h1>
            <ul class="loan-breakdown">
                <li><span>Processing Fee</span><strong>$<span id="loanFee">0.00</span></strong></li>
                <li><span>Total Repayment</span><strong>$<span id="loanTotalRepayment">0.00</span></strong></li>
                <li><span>Est. Monthly Installment</span><strong>$<span id="loanInstallment">0.00</span></strong></li>
            </ul>
            <div>Tenor: <p><span id="loanTenorDisplay">30</span> days</p></div>
            <a id="applyLoanLink" href="/loan/?salary=0&amount=0&fee=0&tenor=30&repayment=0&installment=0">Apply with These Estimates</a>
        </div>
    </div>
</section>




<!-- About Us Section -->
<section class="about-us">
    <div class="wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#fff" fill-opacity="1" d="M0,128L60,117.3C120,107,240,85,360,74.7C480,64,600,64,720,90.7C840,117,960,171,1080,208C1200,245,1320,267,1380,277.3L1440,288L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path></svg>
    </div>
    <div class="container">
        <div class="container-wrapper">
            <div class="left">
    
            </div>
            <div class="right">
                <div class="heading">
                    <h2>About Us</span></h2>
                    <p>Discover our commitment to shaping a future of financial excellence. Explore our mission, vision, and goals that drive our dedication to your success.</p>
                </div>
                <div class="content">
                    <div>
                        <i class="bi bi-hourglass"></i>
                        <div>
                            <h2>Our Mission</h2>
                            <p>We are focused on building and sustaining long-term generational relationships with our customers</p>
                        </div>
                    </div>
                    <div>
                        <span class="material-symbols-outlined">
                            mindfulness
                            </span>
                        <div>
                            <h2>Our Vision</h2>
                            <p>Our Bank aims to be the most reliable and trusted banking platform rated amongst the best in the world because of our services.</p>
                        </div>
                    </div>
                    <div>
                        <i class="bi bi-bullseye"></i>
                        <div>
                            <h2>Our Goal</h2>
                            <p>Our Bank will serve their customers from all over the world and becomes the popular bank in this universe.</p>
                        </div>
                    </div>
                </div>
    
            </div>

        </div>
    </div>
</section>





<!-- Benefits Section -->
<section class="benefits">
    <div class="container">
        <div class="heading">
            <h2>Benefits of Banking With <i>Velmora Bank</i></h2>
        </div>
        <div class="benefits">
            <div class="left">
                <img src="/assets/images/home/benefits/mobile-banking.jpg" alt="Convenience and Accessibility" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
                <img src="/assets/images/home/benefits/financial-growth.jpg" alt="Financial Growth and Opportunities" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
                <img src="/assets/images/home/benefits/financial-security.jpg" alt="Security and Protection" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
            </div>
            <div class="center buttons">
                <div class="left">
                    <i class="bi bi-arrow-left"></i>
                </div>
                <div class="right">
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
            <div class="right">
                <div class="benefit">
                    <h2>Convenience and Accessibility</h2>
                    <p>Enjoy the freedom to manage your finances anytime,
                        anywhere. Our user-friendly online and mobile banking platforms allow you to access your accounts, transfer funds, pay bills, and more with just a few clicks.</p>
                </div>
                <div class="benefit">
                    <h2>Financial Growth and Opportunities</h2>
                    <p>Partner with us to achieve your financial goals. We offer a range of products and services, including savings accounts,
                        investments, and loans, designed to help you build wealth and secure your financial future.</p>
                </div>
                <div class="benefit">
                    <h2>Security and Protection</h2>
                    <p>Banking with us ensures the safety of your hard-earned money.
                        Our advanced security measures safeguard your funds against theft, loss, and fraud. Your peace of mind is our priority.</p>
                </div>
            </div>
        </div>
    </div>
</section>





<!-- Features 3 Section -->
<section class="features-3">
    <div class="container">
        <div class="heading">
            <h2>Velmora Bank Services</h2>
            <p>Whether it's your child's first savings account, your personal savings account, your first retirement plan or a business loan, we offer personal and corporate banking products and services tailored to meet your needs. </p>
        </div>
        <div class="swiper-2">
            <!-- Additional required wrapper -->
            <div class="swiper-wrapper">
                <!-- Slides -->
                <div class="swiper-slide slide-1">
                    <img src="/assets/images/home/features/online-banking.jpg" alt="Customer using secure online banking" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
                    <div class="content">
                        <h3>Online Banking</h3>
                    </div>
                </div>
                <div class="swiper-slide slide-2">
                    <img src="/assets/images/home/features/mobile-banking.jpg" alt="Customer using mobile banking app" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
                    <div class="content">
                        <h3>Mobile Banking</h3>
                    </div>
                </div>
                <div class="swiper-slide slide-3">
                    <img src="/assets/images/home/features/mortgage-and-loans.jpg" alt="Client reviewing mortgage and loan options" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
                    <div class="content">
                        <h3>Loan and Mortgage Services</h3>
                    </div>
                </div>
                <div class="swiper-slide slide-4">
                    <img src="/assets/images/home/features/wealth-management.jpg" alt="Advisor discussing investment and wealth planning" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
                    <div class="content">
                        <h3>Investment and Wealth Management</h3>
                    </div>
                </div>
                <div class="swiper-slide slide-5">
                    <img src="/assets/images/home/features/customer-support.jpg" alt="Customer support and financial advisory meeting" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
                    <div class="content">
                        <h3>Customer Support and Financial Advisory</h3>
                    </div>
                </div>
                <div class="swiper-slide slide-6">
                    <img src="/assets/images/home/features/atms.jpg" alt="24/7 ATM and card self-service banking" onerror="this.onerror=null;this.src='/assets/images/placeholder-image.png';">
                    <div class="content">
                        <h3>Standby 24/7 ATMs</h3>
                    </div>
                </div>
            </div>
            <!-- If we need pagination -->
            <div class="swiper-pagination"></div>
          
            <!-- If we need navigation buttons -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
          
            <!-- If we need scrollbar -->
            <div class="swiper-scrollbar"></div>
          </div>
          
          
    </div>
</section>





<!-- Frequently Asked Questions Section -->
<section class="faq">
    <div class="container">
        <div class="heading">
            <h2>Frequently Asked Questions</h2>
        </div>
        <div class="questions" id="faqAccordion">
            <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false" aria-controls="faqAnswer1" id="faqQuestion1">What types of accounts can I open with your bank?</button>
                <div class="faq-answer" id="faqAnswer1" role="region" aria-labelledby="faqQuestion1" aria-hidden="true"><p>We offer savings, checking, fixed deposit, and business accounts. Each option is built for a different financial goal and transaction need.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false" aria-controls="faqAnswer2" id="faqQuestion2">How can I access my account online?</button>
                <div class="faq-answer" id="faqAnswer2" role="region" aria-labelledby="faqQuestion2" aria-hidden="true"><p>You can access your account through our secure online banking portal and mobile channels using your registered credentials.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false" aria-controls="faqAnswer3" id="faqQuestion3">What are the international transaction fees?</button>
                <div class="faq-answer" id="faqAnswer3" role="region" aria-labelledby="faqQuestion3" aria-hidden="true"><p>International transfer and card charges vary by product and transaction route. Please review the latest fee schedule before initiating cross-border transactions.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false" aria-controls="faqAnswer4" id="faqQuestion4">How can I apply for a loan?</button>
                <div class="faq-answer" id="faqAnswer4" role="region" aria-labelledby="faqQuestion4" aria-hidden="true"><p>You can start from our loan calculator and continue to the application page. Our team will review eligibility, affordability, and supporting documents.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false" aria-controls="faqAnswer5" id="faqQuestion5">How is my account protected?</button>
                <div class="faq-answer" id="faqAnswer5" role="region" aria-labelledby="faqQuestion5" aria-hidden="true"><p>We apply layered security controls including encrypted sessions, authentication checks, and continuous transaction monitoring.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false" aria-controls="faqAnswer6" id="faqQuestion6">How do I report a lost or stolen card?</button>
                <div class="faq-answer" id="faqAnswer6" role="region" aria-labelledby="faqQuestion6" aria-hidden="true"><p>Contact support immediately via phone, WhatsApp, or the support center so your card can be blocked and replaced promptly.</p></div>
            </div>
        </div>
    </div>
</section>



<!-- Footer-->
<footer>
    <div class="container">
        <div class="left">
            <ul>
                <li><h3>About Us</h3></li>
                <li><a href="/about-us/#our-history">Our History</a></li>
                <li><a href="/about-us/#corporate-profile">Corporate Profile</a></li>
                <li><a href="/about-us/#corporate-governance">Corporate Governance</a></li>
                <li><a href="/about-us/#board-management">Board and Management Team</a></li>
                <li><a href="/about-us/#our-awards">Our Awards</a></li>
            </ul>
            <ul>
                <li><h3>Careers</h3></li>
                <li><a href="/careers/#working-with-us">Working At With Us</a></li>
                <li><a href="/careers/#your-career">Your Career</a></li>
                <li><a href="/careers/#recruitment-process">Recruitment Process</a></li>
            </ul>
            <ul>
                <li><h3>Quick Links</h3></li>
                <li><a href="/quick-links/#anti-money-laundering">Anti-Money Laundering</a></li>
                <li><a href="/quick-links/#download-center">Download Center</a></li>
                <li><a href="/quick-links/#online-security-tips">Online Security Tips</a></li>
                <li><a href="/quick-links/#scam-alert">Scam Alert</a></li>
                <li><a href="/quick-links/#support-center">Support Center</a></li>
            </ul>
            <ul>
                <li><h3>Legal</h3></li>
                <li><a href="/quick-links/#terms-and-conditions">Terms & Conditions</a></li>
                <li><a href="/quick-links/#privacy-policy">Privacy Policy</a></li>
                <li><a href="/cookie-policy/">Cookie Policy</a></li>
                <li><a href="/quick-links/#complaints-procedure">Complaints Procedure</a></li>
            </ul>
        </div>
        <div class="right">
            <form action="/contact/" method="get">
                <h3>Subscribe to Our Newsletter</h3>
                <input type="email" name="email" placeholder="Your Email Address">
                <button type="submit">Subscribe</button>
            </form>
            <div class="contacts">
                <a href="https://maps.google.com/?q=400+Park+Ave,+New+York,+NY+10022" target="_blank" rel="noopener" style="display: block; margin-bottom: 8px;">400 Park Ave, New York, NY 10022, United States</a>
                <a href="mailto:support@velmorabank.us" style="display: block; margin-bottom: 8px;">support@velmorabank.us</a>
                <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener" style="display: block; margin-bottom: 8px;"><?php echo htmlspecialchars($supportPhoneNumber); ?></a>
            </div>
            <div class="social-links">
                <a href="/contact/" aria-label="Contact us"><i class="bi bi-facebook"></i></a>
                <a href="/contact/" aria-label="Contact us"><i class="bi bi-twitter"></i></a>
                <a href="/contact/" aria-label="Contact us"><i class="bi bi-instagram"></i></a>
                <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i></a>
            </div>
        </div>
    </div>
</footer>





<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets/scripts/home.js"></script>
<?php include('./common-sections/smartsupp-live-chat.html'); ?>
</body>
</html>

```


## /login/app.php
```php
<?php
    // Include the common sections application file
    if (file_exists('../common-sections/app.php')) {
        require '../common-sections/app.php';
    } elseif (file_exists('../../common-sections/app.php')) {
        require '../../common-sections/app.php';
    } else {
        require '../../../common-sections/app.php';
    }

    // Handle alert info section based on time
    if (isset($_GET['alert_info_section'])) {
        $alert_time = $_GET['alert_time'];
        $time = time();

        if (($time - $alert_time) > 10) {
            $_GET['alert_info_section'] = '';
        } else {
            $_GET['alert_info_section'] = $_GET['alert_info_section'];
        }
    } else {
        $_GET['alert_info_section'] = '';
    }


    $controlPanelAllowedEmails = [
        'tkprodesign96@gmail.com',
        'support@velmorabank.us',
        'admin@velmorabank.us',
    ];

    // Form handler for sign-in
    if (isset($_POST['sign_in'])) {
        $dbconn = connectToDatabase();

        // Sanitize input data
        $email = mysqli_real_escape_string($dbconn, $_POST['email']);
        $password = mysqli_real_escape_string($dbconn, $_POST['password']);
        $remember_me = isset($_POST['remember_me']) ? mysqli_real_escape_string($dbconn, $_POST['remember_me']) : 0;

        $table = 'users';

        // Check if the email exists in the table
        if (!isInTable($email, $table)) {
            $_GET['alert_time'] = time();
            $_GET['error'] = 'yes';
        } else {
            // Query to retrieve hashed password from the database
            $sql = "SELECT password FROM users WHERE email = ?";
            $stmt = $dbconn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($hashed_password);

            // Fetch the result and verify password
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) {
                    $cookie_timeout = $remember_me == 1 ? 30 * 24 * 60 * 60 : 1 * 60 * 60;
                    $dbconn->close();

                    // Set the login cookie
                    setcookie("login_email", $email, time() + $cookie_timeout, "/");

                    // Redirect based on control panel allow-list
                    if (in_array(strtolower($email), $controlPanelAllowedEmails, true)) {
                        header("Location: /control-panel");
                    } else {
                        header("Location: /dashboard");
                    }
                    exit;
                } else {
                    // If password verification fails
                    $_GET['alert_time'] = time();
                    $_GET['error'] = 'yes';
                    $dbconn->close();
                }
            }
        }
    }

    // Show signup success message if account registration was successful
    if (isset($_GET['signup']) && $_GET['signup'] == 'true') {
        $_GET['alert_info_section'] = '
            <section class="alert-info">
                <div class="container">
                    <span>Account Successfully Registered, Login With Your Registered Email Address.</span>
                </div>
            </section>';
    }
?>

```


## /signup/app.php
```php
<?php
// Include app.php based on its location in the directory structure
if (file_exists('../common-sections/app.php')) {
    require '../common-sections/app.php';
} elseif (file_exists('../../common-sections/app.php')) {
    require '../../common-sections/app.php';
} else {
    require '../../../common-sections/app.php';
}

// Form handler for alert information section
if (isset($_GET['alert_info_section'])) {
    $alert_time = $_GET['alert_time'];
    $time = time();

    if (($time - $alert_time) > 10) {
        $_GET['alert_info_section'] = '';
    } else {
        $_GET['alert_info_section'] = $_GET['alert_info_section'];
    }
} else {
    $_GET['alert_info_section'] = '';
}

// Form handler for user sign-up
if (isset($_POST['sign_up'])) {
    $dbconn = connectToDatabase();

    // Sanitize user inputs
    $name = mysqli_real_escape_string($dbconn, $_POST['full_name']);
    $email = mysqli_real_escape_string($dbconn, $_POST['email']);
    $password = mysqli_real_escape_string($dbconn, $_POST['password']);

    // Check if user already exists in the database
    $table = 'users';
    if (isInTable($email, $table)) {
        $_GET['alert_time'] = time();
        $_GET['alert_info_section'] = 
        '<section class="alert-info">
            <div class="container">
                <span>User Already Exists, Login With Your Registered Email Address.</span>
            </div>
        </section>'; 
    } else {
        // Prepare and insert user into the database
        $password = password_hash($password, PASSWORD_DEFAULT);
        $time = time();
        $date_registered = $time;
        $human_time = date('H:i | d/m/Y', $time) . ' | New York Time';

        $sql = "INSERT INTO users (name, email, password, date_registered, human_time) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbconn->prepare($sql);

        // Bind parameters and execute statement
        $stmt->bind_param('sssis', $name, $email, $password, $date_registered, $human_time);
        $stmt->execute();
        $stmt->close();
        $dbconn->close();

        // Stay on signup page after successful registration
        header('location: /signup/?registered=true');
        exit;
    }
}

if (isset($_GET['registered']) && $_GET['registered'] === 'true') {
    $_GET['alert_info_section'] =
    '<section class="alert-info">
        <div class="container">
            <span>Account created successfully. You can now <a href="/login/">sign in</a>.</span>
        </div>
    </section>';
}
?>

```


## /dashboard/app.php
```php
<?php
if (file_exists('../common-sections/app.php')) {
    require '../common-sections/app.php';
} elseif (file_exists('../../common-sections/app.php')) {
    require '../../common-sections/app.php';
} else {
    require '../../../common-sections/app.php';
}






// Require PHP Admin
if (file_exists('../PHPMailer/src/PHPMailer.php')) {
    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';
    require '../PHPMailer/src/Exception.php';
} elseif (file_exists('../../PHPMailer/src/PHPMailer.php')) {
    require '../../PHPMailer/src/PHPMailer.php';
    require '../../PHPMailer/src/SMTP.php';
    require '../../PHPMailer/src/Exception.php'; 
} else {
    require '../../../PHPMailer/src/PHPMailer.php';
    require '../../../PHPMailer/src/SMTP.php';
    require '../../../PHPMailer/src/Exception.php'; 
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;






// Retrieve email from cookie and session email variables
$controlPanelAllowedEmails = [
    'tkprodesign96@gmail.com',
    'support@velmorabank.us',
    'admin@velmorabank.us',
];

if (isset($_COOKIE['login_email'])) {
    $cookieEmail = strtolower(trim((string)$_COOKIE['login_email']));
    if (!filter_var($cookieEmail, FILTER_VALIDATE_EMAIL)) {
        setcookie('login_email', '', time() - 3600, '/');
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }

    $_SESSION['user_email'] = $cookieEmail;
    $session_email = $_SESSION['user_email'];

    if (in_array($session_email, $controlPanelAllowedEmails, true)) {
        header('Location: /control-panel');
        exit;
    }
} else {
    header('Location: /login');
    exit;
}

normalizeLegacyTransactionStatuses();





//Logout function
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    // Destroy the cookie
    if (isset($_COOKIE['login_email'])) {
        unset($_COOKIE['login_email']);
        setcookie('login_email', '', time() - 3600, '/'); // set the expiration date to one hour ago
    }
    // End the session
    session_unset();
    session_destroy();
    // Redirect to login page
    header('Location: /login');
    exit();
}






//Get user data from users table
$dbconn = connectToDatabase();
$sql = "SELECT `name`, email, kyc_level, profile_picture, last_active FROM users WHERE email = ?";
$stmt = $dbconn->prepare($sql);
$hasUser = false;


if ($stmt) {
    $stmt->bind_param('s', $session_email);
    $stmt->execute();
    $stmt->bind_result($user_name, $user_email, $user_kyc_level, $user_profile_picture, $user_last_active);
    $hasUser = $stmt->fetch();
    $stmt->close();
}
$dbconn->close();

if (empty($hasUser) || empty($user_email)) {
    setcookie('login_email', '', time() - 3600, '/');
    session_unset();
    session_destroy();
    header('Location: /login');
    exit;
}





// Seed the requested Jennifer reference transactions into the DB.
function seedJenniferReferenceData($email, $name) {
    if (strcasecmp($email, 'Jenniferaniston11909@gmail.com') !== 0) {
        return;
    }

    $db = connectToDatabase();
    $currency = 'USD';
    $accountType = 'Premium Savings';
    $accountNumber = 200007845;

    $existingAccountNumber = null;
    $stmt = $db->prepare('SELECT account_number FROM accounts WHERE user_email = ? ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($existingAccountNumber);
    if ($stmt->fetch() && !empty($existingAccountNumber)) {
        $accountNumber = (int)$existingAccountNumber;
    }
    $stmt->close();

    if (empty($existingAccountNumber)) {
        $createdAt = time();
        $active = 'Active';
        $createAccount = $db->prepare('INSERT INTO accounts (account_type, user_name, user_email, currency, account_number, account_status, creation_time) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $createAccount->bind_param('ssssisi', $accountType, $name, $email, $currency, $accountNumber, $active, $createdAt);
        $createAccount->execute();
        $createAccount->close();
    }

    $referenceTransactions = [
        ['id' => 'JENN-20240302-1', 'type' => 'Income', 'description' => 'Private Equity Distribution', 'amount' => 450000.00, 'date' => '2024-03-02 10:15:00'],
        ['id' => 'JENN-20240319-1', 'type' => 'Income', 'description' => 'Real Estate Proceeds', 'amount' => 320000.00, 'date' => '2024-03-19 13:40:00'],
        ['id' => 'JENN-20240404-1', 'type' => 'Income', 'description' => 'Consulting Retainer', 'amount' => 275000.00, 'date' => '2024-04-04 09:20:00'],
        ['id' => 'JENN-20240428-1', 'type' => 'Income', 'description' => 'Portfolio Dividend Sweep', 'amount' => 210000.00, 'date' => '2024-04-28 15:05:00'],
        ['id' => 'JENN-20240522-1', 'type' => 'Income', 'description' => 'Film Royalty Deposit', 'amount' => 180000.00, 'date' => '2024-05-22 11:30:00'],
        ['id' => 'JENN-20240610-1', 'type' => 'Income', 'description' => 'Short-Term Treasury Coupon', 'amount' => 140000.00, 'date' => '2024-06-10 10:50:00'],
        ['id' => 'JENN-20240708-1', 'type' => 'Income', 'description' => 'International Licensing Income', 'amount' => 94250.00, 'date' => '2024-07-08 16:35:00'],
        ['id' => 'JENN-20240726-1', 'type' => 'Bills', 'description' => 'Estate Maintenance Payment', 'amount' => -120000.00, 'date' => '2024-07-26 08:55:00'],
        ['id' => 'JENN-20240811-1', 'type' => 'Transfer', 'description' => 'Family Trust Transfer', 'amount' => -85000.00, 'date' => '2024-08-11 12:25:00'],
        ['id' => 'JENN-20240903-1', 'type' => 'Luxury', 'description' => 'Custom Interior Design Installment', 'amount' => -58500.00, 'date' => '2024-09-03 14:10:00'],
        ['id' => 'JENN-20240928-1', 'type' => 'Investment', 'description' => 'Investment Returns - Q3', 'amount' => 165385.50, 'date' => '2024-09-28 10:10:00'],
        ['id' => 'JENN-20241115-1', 'type' => 'Transfer', 'description' => 'Charitable Donation', 'amount' => -25000.00, 'date' => '2024-11-15 09:30:00'],
        ['id' => 'JENN-20250214-1', 'type' => 'Bills', 'description' => 'Property Tax Payment', 'amount' => -18250.00, 'date' => '2025-02-14 08:05:00'],
        ['id' => 'JENN-20251228-1', 'type' => 'Income', 'description' => 'Year-End Bonus', 'amount' => 275000.00, 'date' => '2025-12-28 12:00:00'],
        ['id' => 'JENN-20260310-1', 'type' => 'Luxury', 'description' => 'Art Collection Purchase', 'amount' => -278500.00, 'date' => '2026-03-10 10:45:00'],
    ];

    $insertStmt = $db->prepare('INSERT IGNORE INTO transactions (type, transaction_id, user_email, account_number, amount, currency, description, status, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');

    foreach ($referenceTransactions as $transaction) {
        $type = $transaction['type'];
        $transactionId = $transaction['id'];
        $amount = $transaction['amount'];
        $description = $transaction['description'];
        $status = 'Successful';
        $timestamp = strtotime($transaction['date']);

        $insertStmt->bind_param('sssidsssi', $type, $transactionId, $email, $accountNumber, $amount, $currency, $description, $status, $timestamp);
        $insertStmt->execute();
    }

    $insertStmt->close();
    $db->close();
}

seedJenniferReferenceData($user_email, $user_name);


//Get user's number of accounts from accounts table
$dbconn = connectToDatabase();
$sql = "SELECT COUNT(*) FROM accounts WHERE user_email = ?";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($accounts_count);
$stmt->fetch();
$stmt->close();
$dbconn->close();





//Sum up user's balance from transaction table
$dbconn = connectToDatabase();
$sql = "SELECT SUM(amount) AS user_balance FROM transactions WHERE user_email = ? AND (status IS NULL OR LOWER(status) <> 'failed')";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($user_balance);
$stmt->fetch();
$stmt->close();
$dbconn->close();
$user_balance = $user_balance > 0 ? number_format($user_balance, 2) : '0.00';






//Count how many transactions have been made
$dbconn = connectToDatabase();
$sql = "SELECT COUNT(*) FROM transactions WHERE user_email = ? AND (status IS NULL OR LOWER(status) <> 'failed')";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($transaction_count);
$stmt->fetch();
$stmt->close();
$dbconn->close();




//1. accounts/create

function renderBankEmailTemplate($subject, $headline, $introHtml, $detailsHtml, $ctaText = '', $ctaUrl = '') {
    $logoUrl = 'https://velmorabank.us/assets/images/branding/logo.png';
    $ctaBlock = '';
    if (!empty($ctaText) && !empty($ctaUrl)) {
        $ctaBlock = '<tr><td align="center" style="padding: 0 32px 24px 32px;"><a href="' . htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#0ddbb9;color:#0f1f33;text-decoration:none;font-weight:700;font-size:14px;line-height:1;padding:14px 22px;border-radius:6px;">' . htmlspecialchars($ctaText, ENT_QUOTES, 'UTF-8') . '</a></td></tr>';
    }

    return '<!DOCTYPE html><html lang="en"><head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</title></head><body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#1a2b44;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6fb;padding:24px 0;"><tr><td align="center"><table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:94%;background:#ffffff;border:1px solid #e4e9f2;border-radius:12px;overflow:hidden;"><tr><td style="background:#0f2742;padding:22px 28px;"><img src="' . $logoUrl . '" alt="Velmora Bank" style="height:36px;width:auto;display:block;"></td></tr><tr><td style="padding:28px 32px 8px 32px;"><p style="margin:0 0 8px 0;font-size:12px;letter-spacing:.08em;color:#6f8199;text-transform:uppercase;">Velmora Bank Notification</p><h1 style="margin:0;font-size:24px;line-height:1.35;color:#0f2742;">' . htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') . '</h1></td></tr><tr><td style="padding:0 32px 10px 32px;font-size:15px;line-height:1.7;color:#3a4a62;">' . $introHtml . '</td></tr><tr><td style="padding:6px 32px 24px 32px;">' . $detailsHtml . '</td></tr>' . $ctaBlock . '<tr><td style="padding:18px 32px;background:#f8faff;border-top:1px solid #e4e9f2;"><p style="margin:0 0 6px 0;font-size:12px;line-height:1.5;color:#6f8199;">Velmora Bank, 400 Park Ave, New York, NY 10022, United States</p><p style="margin:0;font-size:12px;line-height:1.5;color:#6f8199;">Need help? <a href="mailto:support@velmorabank.us" style="color:#0f2742;text-decoration:none;font-weight:600;">support@velmorabank.us</a></p></td></tr></table></td></tr></table></body></html>';
}


//Create account button function
if (isset($_POST['create_account'])) {
    $user_name = $_POST['user_name'];
    $user_email = $user_email; 
    $currency = $_POST['currency'];
    $account_type = $_POST['account_type'];
    $time = time();


    $dbconn = connectToDatabase();
    do {
        $randomNumber = random_int(100000000, 999999999);
        $bank_account_number_str = '2' . $randomNumber;
        $bank_account_number = (int)$bank_account_number_str;
        $sql = "SELECT COUNT(*) FROM accounts WHERE account_number = $bank_account_number";
        $result = $dbconn->query($sql);
        if ($result === false) {
            die("Error executing query: " . $dbconn->error);
        }
        $row = $result->fetch_row();
        $count = $row[0];
        $result->free();
    } while ($count > 0);

   

    // Define the email subject from your PHPMailer example
    $email_subject = 'Your New Velmora Bank Account Has Been Successfully Created';

    $introHtml = '<p style="margin:0;">Your new account has been opened successfully and is ready for use.</p>';
    $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;"><tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Account Number</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars((string)$bank_account_number, ENT_QUOTES, 'UTF-8') . '</td></tr><tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status</td><td style="padding:12px 16px;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">Active</td></tr></table>';
    $email_body = renderBankEmailTemplate($email_subject, 'Account Successfully Created', $introHtml, $detailsHtml, 'View Account', 'https://velmorabank.us/dashboard/accounts');


    // Prepare the data for the Resend API call
    $post_data = [
        "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>", // Sender name and email
        "to" => $user_email,
        "subject" => $email_subject,
        "html" => $email_body
    ];

    // Initialize cURL session
    $ch = curl_init("https://api.resend.com/emails");

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_POST, true);         // Set as POST request
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer re_6UXBpV3q_Ee83gTNZod4QexanZjZh9Ss8", // Your Resend API Key
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data)); // Encode data as JSON

    // Execute the cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
    curl_close($ch); // Close cURL session

    // Handle the Resend API response
    if ($httpCode === 200 || $httpCode === 202) {
        $fgfgf = "Message sent successfully via Resend.";
    } else {
        echo "Failed to send message. HTTP Code: $httpCode <br> Response: $response";
    }

    // // Create a new PHPMailer instance
    // $mail = new PHPMailer(true);

    // try {
    //     //Server settings
    //     $mail->isSMTP();                                            // Set mailer to use SMTP
    //     $mail->Host       = 'velmorabank.us';                     // Specify main and backup SMTP servers
    //     $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    //     $mail->Username   = 'no-reply@velmorabank.us';               // SMTP username
    //     $mail->Password   = 'jh22,-K<G38f(;9';                  // SMTP password
    //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption, `ssl` also accepted
    //     $mail->Port       = 587;                                    // TCP port to connect to

    //     //Recipients
    //     $mail->setFrom('no-reply@velmorabank.us', 'Velmora Bank Notifications');
    //     $mail->addAddress($user_email);                 // Add a recipient

    //     // Content
    //     $mail->isHTML(true);                                        // Set email format to HTML
    //     $mail->Subject = 'Your New Velmora Bank Account Has Been Successfully Created';                                        // Empty subject
    //     $mail->Body    = 
    //                 '<!DOCTYPE html>
    //                 <html lang="en">
    //                 <head>
    //                     <meta charset="UTF-8">
    //                     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //                     <title>Your New Velmora Bank Account Has Been Successfully Created</title>
    //                 </head>
    //                 <body style="font-family: Inter, sans-serif; padding: 0; margin: 0; background: #fff;">
    //                     <section style="width: 90%; max-width: 600px; border-radius: 1rem; margin: auto;">
    //                         <header style="padding: 1rem 0;">
    //                             <div style="padding: 1rem;">
    //                                 <a href="https://velmorabank.us" id="logo">
    //                                     <img src="https://velmorabank.us/assets/images/branding/logo.png" alt="Velmora Bank" style="height: 48px; width: auto;">
    //                                 </a>
    //                             </div>
    //                         </header>
    //                         <div class="content">
    //                             <div style="padding: 1rem;">
    //                                 <div class="account-success">
    //                                     <div class="wrapper" style="width: 90%; max-width: 500px; margin: 60px auto; display: flex; flex-direction: column; align-items: center; padding: 1.875rem 1.875rem; border-radius: 6px; border: 1px solid #e7eaed; background-color: #fff; color: #6c7293;">
    //                                         <div style="font-size: 58px; line-height: 1; margin-bottom: 20px;" aria-hidden="true">✓</div>
    //                                         <p style="text-align: center; margin-bottom: 25px;">Congratulations, your new account has been created with account number <strong>'.$bank_account_number.'</strong>.</p>
    //                                         <a href="https://velmorabank.us/dashboard/accounts" class="cta" style="padding: 0.625rem 1.125rem; color: #fff; background-color: #0ddbb9; border-color: #0ddbb9; border-radius: 0.25rem; display: inline-block; box-shadow: 0 2px 2px 0 rgba(13, 219, 185, 0.14), 0 3px 1px -2px rgba(13, 219, 185, 0.2), 0 1px 5px 0 rgba(13, 219, 185, 0.12); text-decoration: none; font-weight: 600;">View Details</a>
    //                                     </div>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                         <footer style="background: #fbfdff; display: flex; flex-direction: column; gap: .75rem; font-size: .875rem; padding: 1rem;">
    //                             <p style="margin: 0;">Thank you for choosing Velmora Bank!</p>
    //                             <p style="margin: 0;">© 2024 Velmora Bank. All rights reserved.</p>
    //                             <p style="margin: 0;">400 Park Ave, New York, NY 10022, United States</p>
    //                             <p style="margin: 0;">
    //                                 <a href="mailto:support@velmorabank.us" style="color: inherit;">support@velmorabank.us</a> | 
    //                                 <a href="tel:+1234567890" style="color: inherit;">+1 (234) 567-890</a>
    //                             </p>
    //                             <!-- Uncomment if needed
    //                             <div class="social-media-links" style="margin: 10px 0;">
    //                                 <a href="https://facebook.com/#" style="margin: 0 5px;">
    //                                     <img src="/assets/images/social/facebook.png" alt="Facebook" style="width: 24px; height: 24px;">
    //                                 </a>
    //                                 <a href="https://twitter.com/#" style="margin: 0 5px;">
    //                                     <img src="/assets/images/social/twitter.png" alt="Twitter" style="width: 24px; height: 24px;">
    //                                 </a>
    //                                 <a href="https://linkedin.com/#" style="margin: 0 5px;">
    //                                     <img src="/assets/images/social/linkedin.png" alt="LinkedIn" style="width: 24px; height: 24px;">
    //                                 </a>
    //                             </div>
    //                             -->
    //                             <p style="margin: 0;"><a href="#" style="color: inherit;">Unsubscribe</a> from these emails.</p>
    //                         </footer>
    //                     </section>
    //                 </body>
    //                 </html>';                                       

    //     $mail->send();


    //     $message_sent = 'yes';
    // } catch (Exception $e) {
    //     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    // }

    


    $sql = "INSERT INTO accounts (account_type, user_name, user_email, currency, account_number, creation_time) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param('sssssi', $account_type, $user_name, $user_email, $currency, $bank_account_number, $time);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: ../success?nos='.$bank_account_number.'s');
        exit();
    } else {
        echo "Error executing statement: " . $dbconn->error . "<br>";
    }
    $dbconn->close();
}





//2 security/complete-kyc
function handleProfilePictureUpload($dbconn, $user_email) {
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        return 'Please select a valid image file.';
    }

    $file = $_FILES['profile_picture'];
    $originalName = basename($file["name"]);
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $targetDir = __DIR__ . "/security/complete-kyc/uploads/";

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        return 'Upload directory is not available.';
    }
    if (!is_writable($targetDir)) {
        return 'Upload directory is not writable.';
    }

    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return 'File is not an image.';
    }
    if ($file["size"] > 2 * 1024 * 1024) {
        return 'File is too large.';
    }
    if (!in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
        return 'Only JPG, JPEG & PNG files are allowed.';
    }
    $fileName = uniqid('profile_', true) . '.' . $fileExtension;
    $targetFile = $targetDir . $fileName;

    if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
        return 'Sorry, there was an error uploading your file.';
    }

    $stmt = $dbconn->prepare("UPDATE users SET profile_picture = ? WHERE email = ?");
    if (!$stmt) {
        return "Prepare failed: " . $dbconn->error;
    }
    $stmt->bind_param("ss", $fileName, $user_email);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? 'Profile picture updated successfully!' : 'Failed to update profile picture.';
}

if (isset($_POST['submit_profile_picture'])) {
    $dbconn = connectToDatabase();
    $ppstate = handleProfilePictureUpload($dbconn, $user_email);
    $dbconn->close();
}

//Submit KYC data
if (isset($_POST['submit_kyc_data'])) {
    $dbconn = connectToDatabase();
    if ($dbconn->connect_error) {
        die("Connection failed: " . $dbconn->connect_error);
    }

    // if ($file['error'] === UPLOAD_ERR_OK) {
    //     $uploadDir = '../uploads/'; // make sure this directory exists and is writable
    //     $fileName = basename($file['name']);
    //     $targetPath = $uploadDir . $fileName;

    //     if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    //         // Update the user's profile_picture column
    //         $stmt = $dbconn->prepare("UPDATE users SET profile_picture = ? WHERE email = ?");
    //         if ($stmt) {
    //             $stmt->bind_param("ss", $fileName, $email);
    //             if ($stmt->execute()) {
    //                 echo "Profile picture updated successfully!";
    //             } else {
    //                 echo "Execute failed: " . $stmt->error;
    //             }
    //             $stmt->close();
    //         } else {
    //             echo "Prepare failed: " . $dbconn->error;
    //         }
    //     } else {
    //         echo "Failed to move uploaded file.";
    //     }
    // } else {
    //     echo "Upload error: " . $file['error'];
    // }

    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $suffix = $_POST['suffix'];
    $gender = $_POST['gender'];
    $address1 = $_POST['address1'];
    $address2 = $_POST['address2'];
    $apartment_no = $_POST['apartment_no'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $phone_number = $_POST['phone_number'];
    $date_of_birth = $_POST['date_of_birth'];
    $zip_code = $_POST['zip_code'];
    $us_citizen = $_POST['us_citizen'];
    $dual_citizenship = $_POST['dual_citizenship'];
    $country_of_residence = $_POST['country_of_residence'];
    $source_of_income = $_POST['source_of_income'];
    $nationality = $_POST['nationality'];
    $email = $user_email;  // Assuming $user_email is already defined
    $time_uploaded = date('Y-m-d H:i:s'); // Current timestamp

    $sql = "INSERT INTO kyc_data  (
                first_name, middle_name, last_name, suffix, gender, address1, address2, apartment_no, city, state,
                phone_number, date_of_birth, zip_code, us_citizen, dual_citizenship, country_of_residence,
                source_of_income, nationality, email, time_uploaded
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $dbconn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error);
    }

    $stmt->bind_param(
        'ssssssssssssssssssss',
        $first_name, $middle_name, $last_name, $suffix, $gender, $address1, $address2, $apartment_no, $city, $state,
        $phone_number, $date_of_birth, $zip_code, $us_citizen, $dual_citizenship, $country_of_residence,
        $source_of_income, $nationality, $email, $time_uploaded
    );

    if ($stmt->execute()) {
        $sql = "UPDATE users SET kyc_level = 2 WHERE email = ?";
        $stmt = $dbconn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error);
        }

        $stmt->bind_param('s', $user_email);

        if ($stmt->execute()) {
            $Update = 'successful';
        } else {
            echo "Error executing query: (" . $stmt->errno . ") " . $stmt->error . "<br>";
        }

        // $stmt->close();

        header('Location: /dashboard');
        exit();
    } else {
        echo "Error executing query: (" . $stmt->errno . ") " . $stmt->error . "<br>";
    }

    $stmt->close();
    $dbconn->close();
}





//2 funds/transfer
if (isset($_POST['transfer_funds'])) {
    // Collect and sanitize form data
    $to_bank_name = htmlspecialchars($_POST['bank_name']);
    $to_account_number = htmlspecialchars($_POST['account_number']);
    $to_account_type = htmlspecialchars($_POST['account_type']);
    $currency = htmlspecialchars($_POST['currency']);
    $amount = htmlspecialchars($_POST['amount']);
    $from_account = htmlspecialchars($_POST['from_account']);
    list($from_account_number, $from_account_type) = explode('-', $from_account, 2);
    // $user_email is expected to be defined from a session or user data lookup
    // $user_name is expected to be defined from a session or user data lookup
    $time = time();

    // Database connection
    $db = connectToDatabase(); // Call your defined function

    // --- Insufficient Funds Check (Re-enable if needed) ---
    // The original code had this commented out and hardcoded to 'false'.
    // If you want to enable real fund checking, uncomment and implement the logic.
    // Example of how it would look if enabled:
    // $stmt = $db->prepare("SELECT SUM(amount) as balance FROM transactions WHERE account_number = ? AND status != 'Pending'");
    // $stmt->bind_param("s", $from_account_number);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // $balance_data = $result->fetch_assoc();
    // $available_balance = $balance_data['balance'];
    //
    // if (abs($amount) > $available_balance) {
    //     $errors = 'Insufficient Funds on account';
    //     // You might want to redirect or show an error message here
    // } else {
    //     // ... rest of the transfer logic
    // }
    // --- End Insufficient Funds Check ---

    // Currently, it's hardcoded to always proceed as per your original code:
    if (false) { // This condition will always be false, making the else block always execute
        $errors = 'Insufficient Funds on account';
        // echo 'Insufficient Funds on account'; // Consider redirecting or displaying a user-friendly error
    } else {
        // Generate a unique transaction ID
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $transaction_id = '';
        for ($i = 0; $i < 15; $i++) {
            $transaction_id .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        // Log the transaction into the transactions table
        $stmt = $db->prepare("INSERT INTO transactions (transaction_id, `type`, user_email, account_number, amount, currency, `description`, `status`, `time`, to_bank_name, to_account_type, to_account_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo "Prepare failed: (" . $db->errno . ") " . $db->error;
            exit();
        }
        $negative_amount = -abs($amount);
        $status = 'Pending';
        $type = 'Transfer';
        $description = 'Transfer to ' . $to_bank_name . ' account number ' . $to_account_number;
        $stmt->bind_param("sssidsssisss", $transaction_id, $from_account_type, $user_email, $from_account_number, $negative_amount, $currency, $description, $status, $time, $to_bank_name, $to_account_type, $to_account_number);

        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        } else {
            // --- Send an email notification to the admin via Resend ---
            $admin_email_subject = 'New Transfer Attempt';
            $admin_intro = '<p style="margin:0;">A new outbound transfer has been initiated by a client and is currently pending compliance review.</p>';
            $admin_details = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">From Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($from_account, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Destination Bank</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($to_bank_name, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Destination Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($to_account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($amount . ' ' . $currency, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status</td><td style="padding:12px 16px;font-size:14px;color:#a16b00;font-weight:700;text-align:right;">Pending</td></tr>            </table>';
            $admin_email_body = renderBankEmailTemplate($admin_email_subject, 'New Transfer Attempt', $admin_intro, $admin_details);

            $resend_api_key = "re_6UXBpV3q_Ee83gTNZod4QexanZjZh9Ss8"; // Your NEW Resend API Key

            $admin_post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => "admin@velmorabank.us", // Admin's email address
                "subject" => $admin_email_subject,
                "html" => $admin_email_body
            ];

            $ch_admin = curl_init("https://api.resend.com/emails");
            curl_setopt($ch_admin, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_admin, CURLOPT_POST, true);
            curl_setopt($ch_admin, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch_admin, CURLOPT_POSTFIELDS, json_encode($admin_post_data));

            $response_admin = curl_exec($ch_admin);
            $httpCode_admin = curl_getinfo($ch_admin, CURLINFO_HTTP_CODE);
            curl_close($ch_admin);

            if ($httpCode_admin === 200 || $httpCode_admin === 202) {
                // Admin notification sent successfully via Resend.
            } else {
                error_log("Failed to send admin notification. HTTP Code: $httpCode_admin | Response: $response_admin");
            }

            // --- Send an email notification to the user via Resend ---
            $user_email_subject = 'New Transfer Initiated';
            $user_intro = '<p style="margin:0;">Dear ' . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . ', your transfer request has been received and is now awaiting approval.</p>';
            $user_details = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">From Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($from_account, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">To Bank</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($to_bank_name, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">To Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($to_account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($amount . ' ' . $currency, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status</td><td style="padding:12px 16px;font-size:14px;color:#a16b00;font-weight:700;text-align:right;">Pending</td></tr>            </table>';
            $user_email_body = renderBankEmailTemplate($user_email_subject, 'Transfer Initiated', $user_intro, $user_details, 'View Transactions', 'https://velmorabank.us/dashboard/accounts/transactions');

            $user_post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => $user_email,
                "subject" => $user_email_subject,
                "html" => $user_email_body
            ];

            $ch_user = curl_init("https://api.resend.com/emails");
            curl_setopt($ch_user, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_user, CURLOPT_POST, true);
            curl_setopt($ch_user, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch_user, CURLOPT_POSTFIELDS, json_encode($user_post_data));

            $response_user = curl_exec($ch_user);
            $httpCode_user = curl_getinfo($ch_user, CURLINFO_HTTP_CODE);
            curl_close($ch_user);

            if ($httpCode_user === 200 || $httpCode_user === 202) {
                header('location: /dashboard/accounts/transactions');
                exit();
            } else {
                error_log("Failed to send user notification. HTTP Code: $httpCode_user | Response: $response_user");
                header('location: /dashboard/accounts/transactions?email_failed=true');
                exit();
            }
        }
    }

    // Close the statement and database connection
    // Ensure $stmt is defined before closing. It might not be if initial checks fail.
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
}
?>

```


## /dashboard/index.php
```php
<?php include('app.php') ?>
<?php
$pending_transactions = 0;
$successful_transactions = 0;
$latest_transaction_time = null;
$active_accounts = 0;
$kyc_status_label = 'Not Submitted';

$dashboardRows = [];
$dashboardMeta = [
    'account_holder' => $user_name,
    'date_of_birth' => 'Not available',
    'account_number' => 'Not available',
    'account_type' => 'Primary',
];

$dbMetrics = connectToDatabase();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND status = 'Pending'");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($pending_transactions);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND status IN ('Successful')");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($successful_transactions);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT MAX(`time`) FROM transactions WHERE user_email = ?");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($latest_transaction_time);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM accounts WHERE user_email = ? AND account_status = 'Active'");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($active_accounts);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT status, date_of_birth, first_name, middle_name, last_name FROM kyc_data WHERE email = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($latest_kyc_status, $kyc_dob, $kyc_first_name, $kyc_middle_name, $kyc_last_name);
if ($stmt->fetch()) {
    if (!empty($latest_kyc_status)) {
        $kyc_status_label = $latest_kyc_status;
    }
    if (!empty($kyc_dob)) {
        $dashboardMeta['date_of_birth'] = $kyc_dob;
    }

    $kycFullName = trim(implode(' ', array_filter([$kyc_first_name, $kyc_middle_name, $kyc_last_name])));
    if (!empty($kycFullName)) {
        $dashboardMeta['account_holder'] = $kycFullName;
    }
}
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT account_number, account_type FROM accounts WHERE user_email = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($meta_account_number, $meta_account_type);
if ($stmt->fetch()) {
    $dashboardMeta['account_number'] = '****' . substr((string)$meta_account_number, -4);
    $dashboardMeta['account_type'] = $meta_account_type;
}
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT type, description, amount, status, `time` FROM transactions WHERE user_email = ? AND (status IS NULL OR LOWER(status) <> 'failed') ORDER BY `time` DESC LIMIT 10");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($tx_type, $tx_description, $tx_amount, $tx_status, $tx_time);
while ($stmt->fetch()) {
    $normalizedType = strtolower(trim((string)$tx_type));
    if ($normalizedType === '' || $normalizedType === 'current') {
        $normalizedType = ((float)$tx_amount < 0) ? 'withdrawal' : 'deposit';
    }
    $normalizedType = ucwords(str_replace(['_', '-'], ' ', $normalizedType));
    $normalizedStatus = strtolower(trim((string)$tx_status));
    if ($normalizedStatus === 'completed') {
        $normalizedStatus = 'successful';
    }
    if ($normalizedStatus === '' || $normalizedStatus === 'current') {
        $normalizedStatus = 'posted';
    }
    $dashboardRows[] = [
        'date' => date('M d, Y', (int)$tx_time),
        'description' => $tx_description,
        'category' => $normalizedType,
        'status' => ucwords(str_replace(['_', '-'], ' ', $normalizedStatus)),
        'amount' => (float)$tx_amount,
    ];
}
$stmt->close();
$dbMetrics->close();

$summaryDb = connectToDatabase();
$summaryStmt = $summaryDb->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END), 0) AS total_credits,
        COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) AS total_debits,
        COALESCE(SUM(CASE WHEN amount >= 0 THEN 1 ELSE 0 END), 0) AS credit_count,
        COALESCE(SUM(CASE WHEN amount < 0 THEN 1 ELSE 0 END), 0) AS debit_count
    FROM transactions
    WHERE user_email = ?
      AND (status IS NULL OR LOWER(status) <> 'failed')
");
$summaryStmt->bind_param('s', $user_email);
$summaryStmt->execute();
$summaryStmt->bind_result($totalCredits, $totalDebits, $creditCount, $debitCount);
$summaryStmt->fetch();
$summaryStmt->close();
$summaryDb->close();

$runningBalance = (float)str_replace(',', '', $user_balance);
foreach ($dashboardRows as &$row) {
    $row['balance'] = $runningBalance;
    if (strtolower((string)$row['status']) !== 'failed') {
        $runningBalance -= (float)$row['amount'];
    }
}
unset($row);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
    <title>Dashboard</title>

    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include('../common-sections/dashboard-header.html')?>
<section class="account-info reference-dashboard">
    <div class="container">
        <div class="cta-sec">
            <div class="left">
                <h2 class="greeting">Welcome back, <?php echo htmlspecialchars(explode(' ', $dashboardMeta['account_holder'])[0]); ?></h2>
                <p class="last-login">Here's what's happening with your account today.</p>
            </div>
            <div class="right profile-avatar-wrap">
                <?php if ($user_profile_picture && $user_profile_picture !== 'nil'): ?>
                    <img src="/dashboard/security/complete-kyc/uploads/<?php echo htmlspecialchars($user_profile_picture); ?>" alt="<?php echo htmlspecialchars($dashboardMeta['account_holder']); ?> profile picture" class="dashboard-avatar">
                <?php else: ?>
                    <img src="/assets/images/placeholder-image.png" alt="Default profile picture" class="dashboard-avatar">
                <?php endif; ?>
            </div>
        </div>

        <div class="bars">
            <div class="bar account hero-balance">
                <p class="title">Available Balance</p>
                <h1 class="figure">$<?php echo htmlspecialchars($user_balance); ?></h1>
                <span class="month">Based on <?php echo (int)$transaction_count; ?> transactions</span>
            </div>
            <div class="bar">
                <p class="title">Total Credits</p>
                <h1 class="figure text-green">$<?php echo number_format($totalCredits, 2); ?></h1>
                <span class="month"><?php echo $creditCount; ?> transactions</span>
            </div>
            <div class="bar">
                <p class="title">Total Debits</p>
                <h1 class="figure text-red">$<?php echo number_format($totalDebits, 2); ?></h1>
                <span class="month"><?php echo $debitCount; ?> transactions</span>
            </div>
        </div>

        <div class="account-summary-panel">
            <h3>Account Information</h3>
            <div class="summary-grid">
                <div><span>Account Holder</span><strong><?php echo htmlspecialchars($dashboardMeta['account_holder']); ?></strong></div>
                <div><span>Date of Birth</span><strong><?php echo htmlspecialchars($dashboardMeta['date_of_birth']); ?></strong></div>
                <div><span>Account Number</span><strong><?php echo htmlspecialchars($dashboardMeta['account_number']); ?></strong></div>
                <div><span>Account Type</span><strong><?php echo htmlspecialchars($dashboardMeta['account_type']); ?></strong></div>
            </div>
        </div>
    </div>
</section>

<main class="reference-transactions">
    <div class="container">
        <div class="right full-width">
            <div class="transactions-toolbar">
                <h2>Recent Transactions</h2>
                <a href="accounts/transactions" class="sec-cta">View all</a>
            </div>
            <div class="accounts-list">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardRows as $row):
                        $amount = (float)$row['amount'];
                        $isCredit = $amount >= 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><span class="tx-category"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td class="<?php echo $isCredit ? 'amount-credit' : 'amount-debit'; ?>">
                                <?php echo $isCredit ? '+' : '-'; ?>$<?php echo number_format(abs($amount), 2); ?>
                            </td>
                            <td>
                                <?php
                                if (isset($row['balance'])) {
                                    echo '$' . number_format((float)$row['balance'], 2);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>

```


## /control-panel/app.php
```php
<?php
//Setting initials
$baseDir = __DIR__;
if (file_exists($baseDir . '/../common-sections/app.php')) {
    $baseDir = realpath($baseDir . '/..');
} elseif (file_exists($baseDir . '/../../common-sections/app.php')) {
    $baseDir = realpath($baseDir . '/../..');
} elseif (file_exists($baseDir . '/../../../common-sections/app.php')) {
    $baseDir = realpath($baseDir . '/../../..');
} else {
    error_log("Error: Could not locate the 'common-sections' directory.");
    die("An internal error occurred. Please try again later.");
}
$appPath = $baseDir . '/common-sections/app.php';
if (file_exists($appPath)) {
    require_once $appPath;
} else {
    error_log("Error: app.php not found at " . $appPath);
    die("An internal error occurred. Please try again later.");
}

// Your Resend API Key
$resend_api_key = "re_6UXBpV3q_Ee83gTNZod4QexanZjZh9Ss8";

function renderControlPanelBankEmail($subject, $headline, $introHtml, $detailsHtml) {
    $logoUrl = 'https://velmorabank.us/assets/images/branding/logo.png';
    return '<!DOCTYPE html><html lang="en"><head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</title></head><body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#1a2b44;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6fb;padding:24px 0;"><tr><td align="center"><table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:94%;background:#ffffff;border:1px solid #e4e9f2;border-radius:12px;overflow:hidden;"><tr><td style="background:#0f2742;padding:22px 28px;"><img src="' . $logoUrl . '" alt="Velmora Bank" style="height:36px;width:auto;display:block;"></td></tr><tr><td style="padding:28px 32px 8px 32px;"><p style="margin:0 0 8px 0;font-size:12px;letter-spacing:.08em;color:#6f8199;text-transform:uppercase;">Velmora Bank Notification</p><h1 style="margin:0;font-size:24px;line-height:1.35;color:#0f2742;">' . htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') . '</h1></td></tr><tr><td style="padding:0 32px 10px 32px;font-size:15px;line-height:1.7;color:#3a4a62;">' . $introHtml . '</td></tr><tr><td style="padding:6px 32px 24px 32px;">' . $detailsHtml . '</td></tr><tr><td style="padding:18px 32px;background:#f8faff;border-top:1px solid #e4e9f2;"><p style="margin:0 0 6px 0;font-size:12px;line-height:1.5;color:#6f8199;">Velmora Bank, 400 Park Ave, New York, NY 10022, United States</p><p style="margin:0;font-size:12px;line-height:1.5;color:#6f8199;">Need help? <a href="mailto:support@velmorabank.us" style="color:#0f2742;text-decoration:none;font-weight:600;">support@velmorabank.us</a></p></td></tr></table></td></tr></table></body></html>';
}


$controlPanelAllowedEmails = [
    'tkprodesign96@gmail.com',
    'support@velmorabank.us',
    'admin@velmorabank.us',
];

if (isset($_COOKIE['login_email'])) {
    $_SESSION['user_email'] = strtolower($_COOKIE['login_email']);
    $session_email = $_SESSION['user_email'];

    if (!in_array($session_email, $controlPanelAllowedEmails, true)) {
        header('Location: /dashboard');
        exit;
    }
} else {
    header('Location: /login');
    exit;
}

normalizeLegacyTransactionStatuses();











// Retrieve email from cookie and session email variables and block unauthorized users from using the control panel
// if (isset($_COOKIE['login_email'])) {
//     $_SESSION['user_email'] = $_COOKIE['login_email'];
//     $session_email = $_SESSION['user_email'];
//     if ($session_email != 'admin@velmorabank.us' && $session_email != 'itekena.s.iyowuna@gmail.com') {
//         header('Location: /login');
//     }
// } else {
//     header('Location: /login');
//     exit;
// }











// Update support phone number
if (isset($_POST['update_support_phone'])) {
    $supportPhone = trim($_POST['support_phone_number'] ?? '');

    if ($supportPhone !== '') {
        $db = connectToDatabase();
        $stmt = $db->prepare("INSERT INTO dynamic_data (`name`, `value`) VALUES ('phone_number', ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

        if ($stmt) {
            $stmt->bind_param('s', $supportPhone);
            $stmt->execute();
            $stmt->close();
        }

        $db->close();
    }

    header('Location: /control-panel');
    exit;
}

// Deposit into user account
if (isset($_POST['credit_user'])) {
    $user_email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $account_number = (int) preg_replace('/\D+/', '', $_POST['account_number'] ?? '');
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $currency = strtoupper(trim($_POST['currency'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $transaction_type = 'Deposit';
    $status = 'Successful';
    $time = time();

    if (!$user_email || !$account_number || !is_numeric($amount) || $amount <= 0 || !$currency) {
        header('Location: /control-panel?credit_user=invalid');
        exit;
    }

    $dbconn = connectToDatabase();

    $accountStmt = $dbconn->prepare("SELECT id FROM accounts WHERE user_email = ? AND account_number = ? LIMIT 1");
    if (!$accountStmt) {
        error_log("Prepare failed (account validation): (" . $dbconn->errno . ") " . $dbconn->error);
        header('Location: /control-panel?credit_user=failed');
        exit;
    }
    $accountStmt->bind_param("si", $user_email, $account_number);
    $accountStmt->execute();
    $accountResult = $accountStmt->get_result();
    $accountExists = $accountResult && $accountResult->num_rows > 0;
    $accountStmt->close();

    if (!$accountExists) {
        $dbconn->close();
        header('Location: /control-panel?credit_user=account_mismatch');
        exit;
    }

    $formatted_time = date('H:i | d F Y /T', $time);
    $description = $description !== '' ? $description : 'Manual deposit by control panel';
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $transaction_id = '';
    do {
        $transaction_id = '';
        for ($i = 0; $i < 15; $i++) {
            $transaction_id .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        $stmtCheck = $dbconn->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_id = ?");
        if (!$stmtCheck) {
            error_log("Prepare failed (transaction id check): (" . $dbconn->errno . ") " . $dbconn->error);
            $dbconn->close();
            header('Location: /control-panel?credit_user=failed');
            exit;
        }
        $stmtCheck->bind_param("s", $transaction_id);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();
    } while ($count > 0);

    $stmt = $dbconn->prepare("INSERT INTO transactions (`type`, transaction_id, user_email, account_number, amount, currency, `description`, `status`, `time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed (deposit insert): (" . $dbconn->errno . ") " . $dbconn->error);
        $dbconn->close();
        header('Location: /control-panel?credit_user=failed');
        exit;
    }

    $stmt->bind_param("sssidsssi", $transaction_type, $transaction_id, $user_email, $account_number, $amount, $currency, $description, $status, $time);
    if ($stmt->execute()) {
        $display_amount = number_format($amount, 2);
        $email_subject = 'Deposit Confirmation - Velmora Bank';
        $introHtml = '<p style="margin:0;">Dear Valued Customer, a deposit has been posted to your account successfully.</p>';
        $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Transaction ID</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($transaction_id, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Account Number</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($currency . ' ' . $display_amount, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Description</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status / Time</td><td style="padding:12px 16px;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($status . ' • ' . $formatted_time, ENT_QUOTES, 'UTF-8') . '</td></tr>            </table>';
        $email_body = renderControlPanelBankEmail($email_subject, 'Deposit Confirmation', $introHtml, $detailsHtml);

        $post_data = [
            "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
            "to" => $user_email,
            "subject" => $email_subject,
            "html" => $email_body
        ];

        $ch = curl_init("https://api.resend.com/emails");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $resend_api_key,
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!($httpCode === 200 || $httpCode === 202)) {
            error_log("Failed to send deposit confirmation email. HTTP Code: $httpCode | Response: $response");
        }
        $stmt->close();
        $dbconn->close();
        header('Location: /control-panel?credit_user=success');
        exit;
    }

    error_log("Error: Could not record deposit transaction. " . $stmt->error);
    $stmt->close();
    $dbconn->close();
    header('Location: /control-panel?credit_user=failed');
    exit;
}

// Withdraw from user account
if (isset($_POST['debit_user'])) {
    // Collect and sanitize form data
    $user_email = htmlspecialchars($_POST['email']);
    $account_number = (int)($_POST['account_number'] ?? 0);
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT); // Sanitize as float
    $amount = -abs($amount); // Ensure amount is negative for a debit
    $currency = htmlspecialchars($_POST['currency']);
    $description = htmlspecialchars($_POST['description']);
    $transaction_type = 'Withdrawal';
    $status = 'Successful';
    $time = time(); // Current timestamp

    // Basic validation for critical fields
    if ($user_email && $account_number && is_numeric($amount) && $currency) {
        $dbconn = connectToDatabase();
        // Removed /E/ from date format as it may cause issues or be unnecessary depending on environment
        $formatted_time = date('H:i | d F Y /T', $time);

        // Generate a unique transaction ID
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $transaction_id = '';
        do {
            $transaction_id = '';
            for ($i = 0; $i < 15; $i++) {
                $transaction_id .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            $stmt = $dbconn->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_id = ?");
            if (!$stmt) {
                error_log("Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error);
                // Handle error appropriately, e.g., display a user-friendly message and exit
                die("An internal error occurred. Please try again later.");
            }
            $stmt->bind_param("s", $transaction_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close(); // Close this statement before preparing a new one
        } while ($count > 0);

        // Insert the transaction into the database
        $stmt = $dbconn->prepare("INSERT INTO transactions (`type`, transaction_id, user_email, account_number, amount, currency, `description`, `status`, `time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error);
            die("An internal error occurred. Please try again later.");
        }
        // Use "d" for float/double for the amount to ensure precision
        $stmt->bind_param("sssidsssi", $transaction_type, $transaction_id, $user_email, $account_number, $amount, $currency, $description, $status, $time);

        if ($stmt->execute()) {
            // --- Send Withdrawal Confirmation Email via Resend API ---
            $email_subject = 'Withdrawal Confirmation - Velmora Bank';
            // Use abs($amount) for display to show a positive withdrawal amount to the user
            $display_amount = number_format(abs($amount), 2);
            $introHtml = '<p style="margin:0;">Dear Valued Customer, your withdrawal has been processed successfully. The transaction summary is below.</p>';
            $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Transaction ID</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($transaction_id, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Account Number</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($currency . ' ' . $display_amount, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Description</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status / Time</td><td style="padding:12px 16px;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($status . ' • ' . $formatted_time, ENT_QUOTES, 'UTF-8') . '</td></tr>            </table>';
            $email_body = renderControlPanelBankEmail($email_subject, 'Withdrawal Confirmation', $introHtml, $detailsHtml);

            $post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => $user_email,
                "subject" => $email_subject,
                "html" => $email_body
            ];

            $ch = curl_init("https://api.resend.com/emails");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!($httpCode === 200 || $httpCode === 202)) {
                error_log("Failed to send withdrawal confirmation email. HTTP Code: $httpCode | Response: $response");
            }
            header('Location: /control-panel?debit_user=success');
            exit;

        } else {
            // Transaction insertion failed
            error_log("Error: Could not record the withdrawal transaction in the database. " . $stmt->error);
            header('Location: /control-panel?debit_user=failed');
            exit;
        }

        $stmt->close(); // Close the statement after use
        $dbconn->close(); // Close the database connection
    } else {
        // Handle cases where required fields are missing or invalid
        header('Location: /control-panel?debit_user=invalid');
        exit;
    }
}







// Approve/Reject Withdrawal
if (isset($_POST['judge_withdrawal'])) {
    // Database connection
    $db = connectToDatabase();

    // Collecting and sanitizing form data
    $withdrawal_id = filter_var($_POST['withdrawal_id'], FILTER_VALIDATE_INT);
    $decision = htmlspecialchars($_POST['decision']);
    $description = htmlspecialchars($_POST['description']);

    // Validate inputs
    if ($withdrawal_id === false || !in_array(strtolower($decision), ['completed', 'failed'])) {
        echo "Error: Invalid withdrawal ID or decision.";
        $db->close();
        exit();
    }

    // Fetch transaction details
    $stmt = $db->prepare("SELECT user_email, transaction_id, account_number, amount, currency, `time` FROM transactions WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: (" . $db->errno . ") " . $db->error);
        die("An internal error occurred while fetching transaction details.");
    }
    $stmt->bind_param("i", $withdrawal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close(); // Close the first statement

    // Ensure transaction exists
    if ($transaction) {
        // Convert amount to absolute value for display purposes
        $transaction['amount'] = abs($transaction['amount']);
        
        // Prepare and execute the update query
        $stmt = $db->prepare("UPDATE transactions SET `status` = ?, description = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed: (" . $db->errno . ") " . $db->error);
            die("An internal error occurred while updating transaction status.");
        }
        $stmt->bind_param("ssi", $decision, $description, $withdrawal_id);
        
        if ($stmt->execute()) {
            // Send an email notification based on the decision using Resend API
            $user_email = $transaction['user_email'];
            $transaction_id = $transaction['transaction_id'];
            $account_number = $transaction['account_number'];
            $amount_display = number_format($transaction['amount'], 2);
            $currency = $transaction['currency'];
            $formatted_time = date('H:i | d F Y T', $transaction['time']);
            $status = ucfirst($decision); // Capitalize first letter for display

            $email_subject = '';
            $email_heading = '';
            $email_message = '';

            if (strtolower($decision) === 'completed') {
                $email_subject = 'Withdrawal Successful - Velmora Bank';
                $email_heading = 'Withdrawal Successful';
                $email_message = 'We are pleased to inform you that your recent withdrawal request has been successfully processed.';
            } else if (strtolower($decision) === 'failed') {
                $email_subject = 'Withdrawal Failed - Velmora Bank';
                $email_heading = 'Withdrawal Failed';
                $email_message = 'We regret to inform you that your recent withdrawal request has failed.';
            }

            $introHtml = '<p style="margin:0;">Dear Valued Customer, ' . htmlspecialchars($email_message, ENT_QUOTES, 'UTF-8') . '</p>';
            $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Transaction ID</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($transaction_id, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Account Number</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($currency . ' ' . $amount_display, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Description</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status / Time</td><td style="padding:12px 16px;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($status . ' • ' . $formatted_time, ENT_QUOTES, 'UTF-8') . '</td></tr>            </table>';
            $email_body = renderControlPanelBankEmail($email_subject, $email_heading, $introHtml, $detailsHtml);

            $post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => $user_email,
                "subject" => $email_subject,
                "html" => $email_body
            ];

            $ch = curl_init("https://api.resend.com/emails");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!($httpCode === 200 || $httpCode === 202)) {
                error_log("Failed to send withdrawal " . strtolower($decision) . " email. HTTP Code: $httpCode | Response: $response");
            }
            header('Location: /control-panel?judge_withdrawal=success');
            exit;
        } else {
            error_log("Error updating transaction status: " . $stmt->error);
            header('Location: /control-panel?judge_withdrawal=failed');
            exit;
        }
        $stmt->close(); // Close the second statement
    } else {
        header('Location: /control-panel?judge_withdrawal=not_found');
        exit;
    }

    // Close the database connection
    $db->close();
}





// Approve/Reject KYC
if (isset($_POST['judge_kyc'])) {
    // Database connection
    $db = connectToDatabase();

    // Collecting and sanitizing form data
    $kyc_id = filter_var($_POST['kyc_id'], FILTER_VALIDATE_INT);
    $decision = htmlspecialchars($_POST['decision']);
    $description = htmlspecialchars($_POST['description']);

    // Validate inputs
    if ($kyc_id === false || !in_array(strtolower($decision), ['approved', 'rejected'])) {
        echo "Error: Invalid KYC ID or decision.";
        $db->close();
        exit();
    }

    // Fetch KYC details
    $stmt = $db->prepare("SELECT first_name, last_name, email FROM kyc_data WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed (fetch kyc_data): (" . $db->errno . ") " . $db->error);
        die("An internal error occurred while fetching KYC details.");
    }
    $stmt->bind_param("i", $kyc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $kyc_data = $result->fetch_assoc();
    $stmt->close(); // Close the first statement

    // Ensure KYC data exists
    if ($kyc_data) {
        $first_name = ucfirst(strtolower($kyc_data['first_name']));
        $last_name = ucfirst(strtolower($kyc_data['last_name']));
        $full_name = $first_name . ' ' . $last_name;
        $user_email = $kyc_data['email'];

        // Update KYC status in kyc_data table
        $stmt = $db->prepare("UPDATE kyc_data SET `status` = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed (update kyc_data status): (" . $db->errno . ") " . $db->error);
            die("An internal error occurred while updating KYC status.");
        }
        $stmt->bind_param("si", $decision, $kyc_id);
        
        if ($stmt->execute()) {
            // Update user KYC level if approved
            if (strtolower($decision) === 'approved') {
                $stmt_user_update = $db->prepare("UPDATE users SET kyc_level = 2 WHERE email = ?");
                if (!$stmt_user_update) {
                    error_log("Prepare failed (update user kyc_level): (" . $db->errno . ") " . $db->error);
                    // Decide whether to die here or just log and continue
                } else {
                    $stmt_user_update->bind_param("s", $user_email);
                    $stmt_user_update->execute();
                    $stmt_user_update->close(); // Close this specific statement
                }
            }

            // --- Send KYC Notification Email via Resend API ---
            $email_subject = '';
            $email_message = '';
            $email_heading = '';

            if (strtolower($decision) === 'approved') {
                $email_subject = 'KYC Approved - Velmora Bank';
                $email_heading = 'KYC Approved';
                $email_message = '<p style="line-height: 1.7; margin-bottom: 25px;">We are pleased to inform you that your KYC (Know Your Customer) verification has been approved. You can now enjoy the full benefits of our services.</p>';
            } else if (strtolower($decision) === 'rejected') {
                $email_subject = 'KYC Rejected - Velmora Bank';
                $email_heading = 'KYC Rejected';
                $email_message = '<p style="line-height: 1.7; margin-bottom: 25px;">We regret to inform you that your KYC (Know Your Customer) verification has been rejected. Please contact our support team for further assistance.</p>
                                  <p style="line-height: 1.7; margin-bottom: 25px;">Reason: ' . $description . '</p>';
            }

            $introHtml = '<p style="margin:0;">Dear ' . htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') . ',</p>' . $email_message;
            $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Verification Type</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">KYC Review</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Decision</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars(ucfirst($decision), ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Reference</td><td style="padding:12px 16px;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8') . '</td></tr>            </table>';
            $email_body = renderControlPanelBankEmail($email_subject, $email_heading, $introHtml, $detailsHtml);

            $post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => $user_email,
                "subject" => $email_subject,
                "html" => $email_body
            ];

            $ch = curl_init("https://api.resend.com/emails");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!($httpCode === 200 || $httpCode === 202)) {
                error_log("Failed to send KYC " . strtolower($decision) . " email. HTTP Code: $httpCode | Response: $response");
            }
            header('Location: /control-panel?judge_kyc=success');
            exit;
        } else {
            error_log("Error updating KYC status: " . $stmt->error);
            header('Location: /control-panel?judge_kyc=failed');
            exit;
        }
        $stmt->close(); // Close the statement for kyc_data status update
    } else {
        header('Location: /control-panel?judge_kyc=not_found');
        exit;
    }

    // Close the database connection
    $db->close();
}



?>

```


## /control-panel/index.php
```php
<?php include('app.php') ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
    <title>Control Panel</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<header>
    <div class="container">
        <a href="/" id="logo">
            <img src="/assets/images/branding/velmora/logo.png" alt="Velmora Bank" style="height: 40px; width: auto;">
        </a>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/control-panel">Control Panel</a></li>
                <li><a href="/site-users">All Users</a></li>
                <li><a href="/kyc-data">All KYC Details</a></li>
                <li><a href="/transactions">All Transactions</a></li>
            </ul>
        </nav>
        <a href="#" id="menuToggle">
            <span class="material-symbols-outlined open">
                menu
                </span>
            <span class="material-symbols-outlined close">
                close
                </span>
        </a>
    </div>
</header>
<section class="greetings">
    <div class="container">
        <h1 id="greeting-text">Good Evening Boss</h1>
        <p>Hope you're having a wonderful day.</p>
        <p>Your controls are easy to use and you alone bear access to this page.</p>
    </div>
</section>

<?php
$controlPanelActionMessages = [
    'credit_user' => [
        'success' => ['type' => 'success', 'text' => 'Deposit posted successfully.'],
        'invalid' => ['type' => 'error', 'text' => 'Deposit failed: invalid input values.'],
        'account_mismatch' => ['type' => 'error', 'text' => 'Deposit failed: the account number does not match the provided user email.'],
        'failed' => ['type' => 'error', 'text' => 'Deposit failed due to a server/database error.'],
    ],
    'debit_user' => [
        'success' => ['type' => 'success', 'text' => 'Withdrawal recorded successfully.'],
        'invalid' => ['type' => 'error', 'text' => 'Withdrawal failed: invalid input values.'],
        'failed' => ['type' => 'error', 'text' => 'Withdrawal failed due to a server/database error.'],
    ],
    'judge_withdrawal' => [
        'success' => ['type' => 'success', 'text' => 'Withdrawal review saved successfully.'],
        'not_found' => ['type' => 'error', 'text' => 'Withdrawal review failed: transaction not found.'],
        'failed' => ['type' => 'error', 'text' => 'Withdrawal review failed due to a server/database error.'],
    ],
    'judge_kyc' => [
        'success' => ['type' => 'success', 'text' => 'KYC decision saved successfully.'],
        'not_found' => ['type' => 'error', 'text' => 'KYC decision failed: KYC record not found.'],
        'failed' => ['type' => 'error', 'text' => 'KYC decision failed due to a server/database error.'],
    ],
];

$actionFeedback = null;
foreach ($controlPanelActionMessages as $actionKey => $actionStates) {
    if (isset($_GET[$actionKey])) {
        $statusKey = $_GET[$actionKey];
        if (isset($actionStates[$statusKey])) {
            $actionFeedback = $actionStates[$statusKey];
        }
        break;
    }
}
?>

<?php if ($actionFeedback): ?>
<section class="form">
    <div class="container">
        <div style="padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo $actionFeedback['type'] === 'success' ? '#b8ebde' : '#ffd0cc'; ?>; background: <?php echo $actionFeedback['type'] === 'success' ? '#edfdf7' : '#fff2f1'; ?>; color: <?php echo $actionFeedback['type'] === 'success' ? '#0e6b4d' : '#8a1f17'; ?>;">
            <?php echo htmlspecialchars($actionFeedback['text']); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="form">
    <div class="container">
        <form action="" method="post">
            <h2>Support Phone Number</h2>
            <div class="input-box">
                <label>Phone Number</label>
                <input type="text" name="support_phone_number" value="<?php echo htmlspecialchars(getSupportPhoneNumber()); ?>" required>
            </div>
            <div class="input-box">
                <button type="submit" name="update_support_phone" value="1">Update Number</button>
            </div>
        </form>
    </div>
</section>

<section class="table site-users">
    <div class="container">
        <h2>Site Users Short List (Max 10 Users)</h2>
        <?php
            $db = connectToDatabase();
            $query = "SELECT * FROM users ORDER BY `date_registered` DESC LIMIT 10";
            $result = $db->query($query);
        ?>

        <table>
            <thead>
                <tr>
                    <td>User ID</td>
                    <td>Name</td>
                    <td>Email</td>
                    <td>Balance</td>
                    <td>KYC Level</td>
                    <td>Date Registered</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <?php 
                                $select_user_email = $row['email'];
                                $su_query = "SELECT SUM(amount) AS user_balance FROM transactions WHERE user_email = '$select_user_email' AND status IN ('Successful', 'Pending')";
                                $su_result = $db->query($su_query);
                                $user_balance = $su_result->fetch_assoc()['user_balance'] ?? 0;
                            ?>
                            <td>$<?php echo htmlspecialchars(number_format($user_balance, 2)); ?></td>
                            <td><?php echo htmlspecialchars($row['kyc_level']); ?></td>
                            <td><?php echo htmlspecialchars(date('d, F Y', $row['date_registered'])); ?></td>
                            <td><a href="/control-panel/profile-picture/?id=<?php echo htmlspecialchars($row['id']); ?>">View Profile Picture</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $db->close();
        ?>

        <a href="/control-panel/site-users" class="cta">View all users</a>
    </div>
</section>
<section class="form">
<?php
    if (isset($_POST['view_user_dashboard'])) {
        // Sanitize and retrieve the client's email from the form
        $client_email = filter_var($_POST['client_email'], FILTER_SANITIZE_EMAIL);
    
        // Set a cookie for the client's email that expires in 1 hour
        setcookie('client_email', $client_email, time() + 3600, "/");
    
        // Redirect to the client dashboard
        header("Location: /client-dashboard?client_email=" . urlencode($client_email));
        exit();
    }
    
    ?>
    <div class="container">
        <form action="" method="post">
            <h2>View User Dashboard Temporarily</h2>
            <div class="input-box">
                <label>User Email</label>
                <input type="email" name="client_email" required>
            </div>
            <div class="input-box">
                <button type="submit" name="view_user_dashboard">View User Dashboard</button>
            </div>
        </form>
    </div>
</section>
<section class="table user-accounts">
    <div class="container">
        <h2>User Accounts Short List (Max 10 Users)</h2>
        <?php
            $db = connectToDatabase();
            $query = "SELECT * FROM accounts ORDER BY `creation_time` DESC LIMIT 10";
            $result = $db->query($query);
        ?>

        <table>
            <thead>
                <tr>
                    <td>User ID</td>
                    <td>User Name</td>
                    <td>User Email</td>
                    <td>Account Number</td>
                    <td>Balance</td>
                    <td>Account Status</td>
                    <td>Date</td>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                            <?php 
                                $select_user_account = $row['account_number'];
                                $su_query = "SELECT SUM(amount) AS account_balance FROM transactions WHERE account_number = '$select_user_account' AND status IN ('Successful', 'Pending')";
                                $su_result = $db->query($su_query);
                                $account_balance = $su_result->fetch_assoc()['account_balance'] ?? 0;
                                ?>
                            <td>$<?php echo htmlspecialchars(number_format($account_balance, 2)); ?></td>
                            <td><?php echo htmlspecialchars($row['account_status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d, F Y', $row['creation_time'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No accounts found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $db->close();
        ?>

        <a href="/control-panel/accounts" class="cta">View all accounts</a>
    </div>
</section>
<section class="form">
    <div class="container">
    <?php
        if (isset($_POST['delete_user'])) {
            // Collect and sanitize the form data
            $user_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

            if ($user_id) {
                // Database connection
                $db = connectToDatabase();

                // Prepare and execute the delete query
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);

                if ($stmt->execute()) {
                    // Successful deletion
                    echo "User with ID $user_id has been deleted.";
                } else {
                    // Error handling
                    echo "Error deleting user: " . $stmt->error;
                }

                // Close the statement and database connection
                $stmt->close();
                $db->close();

                // Redirect to another page (e.g., dashboard) after deletion
                header("Location: /control-panel");
                exit();
            } else {
                echo "Invalid ID provided.";
            }
        }
    ?>
        <form action="" method="post">
            <h2>Delete User</h2>
            <div class="input-box">
                <label>ID</label>
                <input type="number" name="id" required>
            </div>
            <div class="input-box">
                <button type="submit" name="delete_user">Delete User</button>
            </div>
        </form>
    </div>
</section>
<section class="form">
<?php
if (isset($_POST['delete_user_account'])) {
    // Collect and sanitize the form data
    $account_number = filter_var($_POST['account_number'], FILTER_SANITIZE_NUMBER_INT);

    if ($account_number) {
        // Database connection
        $db = connectToDatabase();

        // Prepare and execute the delete query
        $stmt = $db->prepare("DELETE FROM accounts WHERE account_number = ?");
        $stmt->bind_param("i", $account_number);

        if ($stmt->execute()) {
            // Successful deletion
            echo "Account with number $account_number has been deleted.";
        } else {
            // Error handling
            echo "Error deleting account: " . $stmt->error;
        }

        // Close the statement and database connection
        $stmt->close();
        $db->close();

        // Redirect to another page (e.g., account management) after deletion
        header("Location: /control-panel");
        exit();
    } else {
        echo "Invalid account number provided.";
    }
}
?>

    <div class="container">
    <form action="" method="post">
    <h2>Delete User Bank Account</h2>
    <div class="input-box">
        <label>Account Number</label>
        <input type="number" name="account_number" required>
    </div>
    <div class="input-box">
        <button type="submit" name="delete_user_account">Delete Account</button>
    </div>
</form>

    </div>
</section>
<section class="form deposit-user" name="Credit User">
    <div class="container">
        <form action="" method="post">
            <h3>Deposit Into User Account</h3>
            <div class="input-box">
                <label>User Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-box">
                <label>Account Number</label>
                <input type="number" name="account_number" required>
            </div>
            <div class="input-box">
                <label>Amount ($)</label>
                <input type="number" name="amount" required>
            </div>
            <div class="input-box">
                <label>Currency</label>
                <select name="currency">
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="input-box">
                <label>Description</label>
                <textarea name="description" cols="10" rows="10"></textarea>
            </div>
            <div class="input-box">
                <button type="submit" name="credit_user" value="1">Deposit</button>
            </div>
        </form>
    </div>
</section>
<section class="form withdraw-from-user" name="Debit User" style="display: none;">
    <div class="container">
        <form action="" method="post">
            <h3>Withdraw From User Account</h3>
            <div class="input-box">
                <label>User Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-box">
                <label>Account Number</label>
                <input type="number" name="account_number" required>
            </div>
            <div class="input-box">
                <label>Amount</label>
                <input type="number" name="amount" required>
            </div>
            <div class="input-box">
                <label>Currency</label>
                <select name="currency">
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="input-box">
                <label>Description</label>
                <textarea name="description" cols="10" rows="10"></textarea>
            </div>
            <div class="input-box">
                <button type="submit" name="debit_user" value="100">Withdraw</button>
            </div>
        </form>
    </div>
</section>
<section class="table list-of-withdrawals">
    <div class="container">
        <h2>List of Transactions (10)</h2>
        <?php
            // Database connection
            $db = connectToDatabase();

            // Query to get data from transactions table
            $query = "SELECT * FROM transactions ORDER BY time DESC LIMIT 10";
            $result = $db->query($query);
            ?>

            <table>
                <thead>
                    <tr>
                        <td>ID</td>
                        <td>Transaction Type</td>
                        <td>Email</td>
                        <td>Account Number</td>
                        <td>Amount</td>
                        <td>Status</td>
                        <td>Time</td>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['type']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                                <td><?php echo htmlspecialchars(abs($row['amount'])); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars(date('d, F Y H:i:s /E/T', $row['time'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No transactions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // Close the database connection
            $db->close();
        ?>

        <a href="/control-panel/transactions" class="cta">View all transactions</a>
    </div>
</section>
<section class="form approve-withdrawal">
    <div class="container">
        <form action="" method="post">
            <h3>Approve/Reject User Withdrawal</h3>
            <div class="input-box">
                <label>ID</label>
                <input type="number" name="withdrawal_id" required>
            </div>
            <div class="input-box">
                <label>Decision</label>
                <select name="decision">
                    <option value="Successful">Approve</option>
                    <option value="Failed">Reject</option>
                </select>
            </div>
            <div class="input-box">
                <label>Description/Reason</label>
                <textarea name="description" cols="10" rows="10"></textarea>
            </div>
            <div class="input-box">
                <button type="submit" name="judge_withdrawal" value="1">Submit</button>
            </div>
        </form>
    </div>
</section>
<section class="table list-of-kyc">
    <div class="container">
        <h2>List of KYC</h2>
        <?php
            // Connect to the database
            $db = connectToDatabase();

            // Query to select data from the kyc_data table
            $query = "SELECT * FROM kyc_data ORDER BY id DESC LIMIT 10"; // Adjust ORDER BY as needed
            $result = $db->query($query);
            ?>

            <table>
                <thead>
                    <tr>
                        <td>ID</td>
                        <td>First Name</td>
                        <td>Last Name</td>
                        <td>Email</td>
                        <td>Status</td>
                        <td>Date of Birth</td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars(date('d, F Y', strtotime($row['date_of_birth']))); ?></td>
                                <td><a href="/control-panel/kyc/?id=<?php echo htmlspecialchars($row['id']); ?>">View Full Details</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // Close the database connection
            $db->close();
        ?>

        <a href="kyc-data" class="cta">View All KYC Data</a>
    </div>
</section>
<section class="form approve-kyc">
    <div class="container">
        <form action="" method="post">
            <h3>Approve/Reject User KYC Verificatiom</h3>
            <div class="input-box">
                <label>KYC ID</label>
                <input type="number" name="kyc_id" required>
            </div>
            <div class="input-box">
                <label>Decision</label>
                <select name="decision">
                    <option value="Approved">Approve</option>
                    <option value="Rejected">Reject</option>
                </select>
            </div>
            <div class="input-box">
                <label>Description/Reaon</label>
                <textarea name="description" cols="10" rows="10"></textarea>
            </div>
            <div class="input-box">
                <button type="submit" name="judge_kyc" value="1">Submit</button>
            </div>
        </form>
    </div>
</section>
<script src="/assets/scripts/control-panel.js?v=<?php echo time(); ?>"></script>
</body>
</html>

```


## /create_tables.php
```php
<?php
/**
 * Database bootstrapper for Velmora Bank.
 * Creates required tables if they do not already exist.
 */

require_once __DIR__ . '/common-sections/app.php';

$db = connectToDatabase();
if (!$db) {
    die('Database connection failed.');
}

$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        date_registered INT NOT NULL,
        human_time VARCHAR(100) NOT NULL,
        kyc_level TINYINT UNSIGNED NOT NULL DEFAULT 1,
        profile_picture VARCHAR(255) DEFAULT NULL,
        last_active INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS accounts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        account_type VARCHAR(100) NOT NULL,
        user_name VARCHAR(150) NOT NULL,
        user_email VARCHAR(190) NOT NULL,
        currency VARCHAR(20) NOT NULL,
        account_number BIGINT NOT NULL UNIQUE,
        account_status VARCHAR(50) NOT NULL DEFAULT 'Active',
        creation_time INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_accounts_user_email (user_email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS transactions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(80) NOT NULL,
        transaction_id VARCHAR(40) NOT NULL UNIQUE,
        user_email VARCHAR(190) NOT NULL,
        account_number BIGINT NOT NULL,
        amount DECIMAL(18,2) NOT NULL,
        currency VARCHAR(20) NOT NULL,
        description TEXT,
        status VARCHAR(40) NOT NULL DEFAULT 'Pending',
        time INT NOT NULL,
        to_bank_name VARCHAR(190) DEFAULT NULL,
        to_account_type VARCHAR(100) DEFAULT NULL,
        to_account_number VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_transactions_user_email (user_email),
        INDEX idx_transactions_account_number (account_number),
        INDEX idx_transactions_time (time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS kyc_data (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(120) NOT NULL,
        middle_name VARCHAR(120) DEFAULT NULL,
        last_name VARCHAR(120) NOT NULL,
        suffix VARCHAR(50) DEFAULT NULL,
        gender VARCHAR(30) DEFAULT NULL,
        address1 VARCHAR(255) NOT NULL,
        address2 VARCHAR(255) DEFAULT NULL,
        apartment_no VARCHAR(80) DEFAULT NULL,
        city VARCHAR(120) NOT NULL,
        state VARCHAR(120) NOT NULL,
        phone_number VARCHAR(40) NOT NULL,
        date_of_birth VARCHAR(30) NOT NULL,
        zip_code VARCHAR(30) NOT NULL,
        us_citizen VARCHAR(30) DEFAULT NULL,
        dual_citizenship VARCHAR(100) DEFAULT NULL,
        country_of_residence VARCHAR(120) NOT NULL,
        source_of_income VARCHAR(120) NOT NULL,
        nationality VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'Pending',
        description TEXT,
        time_uploaded DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_kyc_email (email),
        INDEX idx_kyc_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS dynamic_data (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        value TEXT DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$errors = [];
foreach ($queries as $query) {
    if (!$db->query($query)) {
        $errors[] = $db->error;
    }
}

$seedData = [
    ['phone_number', '+17252885411'],
    ['btc_address', ''],
    ['eth_address', ''],
    ['usdt_address', ''],
    ['doge_address', ''],
];

$seedStmt = $db->prepare('INSERT IGNORE INTO dynamic_data (`name`, `value`) VALUES (?, ?)');
if ($seedStmt) {
    foreach ($seedData as [$name, $value]) {
        $seedStmt->bind_param('ss', $name, $value);
        $seedStmt->execute();
    }
    $seedStmt->close();
}

header('Content-Type: text/plain');
if (empty($errors)) {
    echo "Success: database tables are ready.\n";
} else {
    echo "Finished with errors:\n- " . implode("\n- ", $errors) . "\n";
}

$db->close();

```


## /assets/scripts/header.js
```js
document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.getElementById('menuToggle');
  const mobileNav = document.getElementById('mobileNav');
  const mobileNavOverlay = document.getElementById('mobileNavOverlay');

  const consentBanner = document.getElementById('cookieConsent');
  const acceptCookieBtn = document.getElementById('cookieAcceptBtn');
  const cookieName = 'velmora_cookie_consent';

  const hasConsent = document.cookie.split(';').some((cookie) => cookie.trim().startsWith(`${cookieName}=`));
  if (consentBanner && !hasConsent) {
    consentBanner.hidden = false;
  }

  if (consentBanner && acceptCookieBtn) {
    acceptCookieBtn.addEventListener('click', () => {
      const expires = new Date(Date.now() + (30 * 24 * 60 * 60 * 1000)).toUTCString();
      document.cookie = `${cookieName}=accepted; expires=${expires}; path=/; SameSite=Lax`;
      consentBanner.hidden = true;
    });
  }

  if (!menuToggle || !mobileNav) return;

  let lockedScrollY = 0;

  const lockPageScroll = () => {
    lockedScrollY = window.scrollY || window.pageYOffset || 0;
    document.documentElement.classList.add('menu-open');
    document.body.classList.add('menu-open');
    document.body.style.position = 'fixed';
    document.body.style.top = `-${lockedScrollY}px`;
    document.body.style.width = '100%';
  };

  const unlockPageScroll = () => {
    document.documentElement.classList.remove('menu-open');
    document.body.classList.remove('menu-open');
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    window.scrollTo(0, lockedScrollY);
  };

  const setMenuState = (isOpen) => {
    menuToggle.classList.toggle('active', isOpen);
    menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    mobileNav.classList.toggle('active', isOpen);

    if (mobileNavOverlay) {
      mobileNavOverlay.classList.toggle('active', isOpen);
    }

    if (isOpen) {
      lockPageScroll();
    } else {
      unlockPageScroll();
    }
  };

  const toggleMenu = (event) => {
    event.preventDefault();
    event.stopPropagation();
    setMenuState(!mobileNav.classList.contains('active'));
  };

  menuToggle.addEventListener('click', toggleMenu);

  if (mobileNavOverlay) {
    mobileNavOverlay.addEventListener('click', () => setMenuState(false));
  }

  mobileNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setMenuState(false));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setMenuState(false);
    }
  });
});

```


## /assets/scripts/home.js
```js
// hero image fallback for environments with root-path issues
const heroImages = document.querySelectorAll('section.hero img');
heroImages.forEach((img) => {
  img.addEventListener('error', () => {
    const src = img.getAttribute('src') || '';
    if (src.startsWith('/assets/')) {
      img.setAttribute('src', src.replace('/assets/', '../assets/'));
    }
  });
});

// Setting Hero Height and Mobile Nav height
const heroSection = document.querySelector('.hero');
const header = document.querySelector('header');
const mobileNav = document.querySelector('.mobile-nav');
const mobileNavList = mobileNav ? mobileNav.querySelector('ul') : null;
document.addEventListener('DOMContentLoaded', function() {
    function adjustHeroHeight() {
      if (header && heroSection) {
          const headerHeight = header.offsetHeight;
          const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
          const computedHeight = Math.max(560, viewportHeight - headerHeight);
          heroSection.style.height = `${computedHeight}px`;
      }

      if (header && mobileNavList) {
          const headerHeight = header.offsetHeight;
          mobileNavList.style.paddingTop = `calc(64px + ${headerHeight}px)`;
      }
  }

    adjustHeroHeight();
    window.addEventListener('resize', adjustHeroHeight);
});




const heroSwiperEl = document.querySelector('.swiper-1');
if (heroSwiperEl && typeof Swiper !== 'undefined') {
  const swiper1 = new Swiper('.swiper-1', {
    direction: 'horizontal',
    loop: true,
    speed: 1000,
    autoplay: {
        delay: 5000
    },
    pagination: {
        el: '.swiper-pagination-1',
    },
    navigation: {
        nextEl: '.swiper-button-next-1',
        prevEl: '.swiper-button-prev-1',
    },
    scrollbar: {
        el: '.swiper-scrollbar-1',
    },
  });
}

const swiper2El = document.querySelector('.swiper-2');
if (swiper2El && typeof Swiper !== 'undefined') {
  const swiper2 = new Swiper('.swiper-2', {
    direction: 'horizontal',
    loop: true,
    speed: 1000,
    slidesPerView: 1,
    spaceBetween: 36,
    autoplay: {
        delay: 5000
    },
    breakpoints: {
      750: {
          slidesPerView: 1,
      },
      1000: {
        slidesPerView: 2,
      },
      1200: {
        slidesPerView: 3,
      },
    },
    pagination: {
        el: '.swiper-pagination-2',
    },
    navigation: {
        nextEl: '.swiper-button-next-2',
        prevEl: '.swiper-button-prev-2',
    },
    scrollbar: {
        el: '.swiper-scrollbar-2',
    },
  });
}


  function fetchFinancialNews(apiKey) {
    const url = 'https://finnhub.io/api/v1/news';
    const params = {
      token: apiKey,
      category: 'general' // Adjust category as needed
    };
    const queryString = new URLSearchParams(params).toString();
  
    return fetch(`${url}?${queryString}`)
      .then(response => response.json())
      .then(data => {
        // Extract the first 10 news articles
        return data.slice(0, 10);
      })
      .catch(error => {
        console.error('Error fetching news:', error);
        return [];
      });
  }
  
  // Example usage
  const apiKey = 'cqta661r01qvdch30k6gcqta661r01qvdch30k70';
  fetchFinancialNews(apiKey)
    .then(newsData => {
        console.log(newsData);
        const tickerWrapper = document.querySelector('.latest-news .ticker-wrapper');
        if (!tickerWrapper) {
          return;
        }

        newsData.forEach(article => {
            const span = document.createElement('span');
            span.textContent = article.headline;
            tickerWrapper.appendChild(span);
        });

        if (!tickerWrapper.children.length) {
          return;
        }

        const clone = tickerWrapper.cloneNode(true);
        tickerWrapper.appendChild(clone);
        
        tickerWrapper.style.whiteSpace = 'nowrap';
        tickerWrapper.style.overflow = 'hidden';
        
        let tickerWidth = tickerWrapper.scrollWidth;
        tickerWrapper.style.width = `${tickerWidth * 2}px`;
        
        let scrollPosition = 0;
        const scrollSpeed = 1;
        
        function scrollTicker() {
            scrollPosition -= scrollSpeed;
            tickerWrapper.style.transform = `translateX(${scrollPosition}px)`;
        
            if (Math.abs(scrollPosition) >= tickerWidth) {
            scrollPosition = 0;
            tickerWrapper.style.transform = `translateX(${scrollPosition}px)`;
            }
        
            requestAnimationFrame(scrollTicker);
        }
        
        scrollTicker();
    });

const toggleBox = document.querySelector('.toggle-box');
const toggle = toggleBox ? toggleBox.querySelector('.toggle') : null;
const salaryInput = document.getElementById('salaryInput');
const salaryLiveInput = document.getElementById('salaryLiveInput');
const loanAmount = document.getElementById('loanAmount');
const loanFee = document.getElementById('loanFee');
const loanTotalRepayment = document.getElementById('loanTotalRepayment');
const loanInstallment = document.getElementById('loanInstallment');
const loanTenorDays = document.getElementById('loanTenorDays');
const loanTenorDisplay = document.getElementById('loanTenorDisplay');
const applyLoanLink = document.getElementById('applyLoanLink');
const allowedTenors = [14, 30, 60];

if (toggleBox && toggle && salaryInput && loanAmount) {
    function getCenter() {
        return {
            x: toggleBox.offsetWidth / 2,
            y: toggleBox.offsetHeight / 2,
        };
    }

    function getAngleBetweenPoints(cx, cy, ex, ey) {
        const radians = Math.atan2(ey - cy, ex - cx);
        return radians * (180 / Math.PI);
    }

    function angleToSalary(angle) {
        const normalizedAngle = (angle % 360 + 360) % 360;
        return Math.round((normalizedAngle / 360) * 50000);
    }

    function salaryToAngle(salary) {
        const safeSalary = Math.max(0, Math.min(50000, salary));
        return (safeSalary / 50000) * 360;
    }

    function formatCurrency(value) {
        return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getFeeRateByTenor(tenorDays) {
        if (tenorDays <= 14) return 0.08;
        if (tenorDays <= 30) return 0.12;
        return 0.18;
    }

    function updateCalculator(salary) {
        const safeSalary = Math.max(0, Math.min(50000, Math.round(salary)));
        const selectedTenor = Number(loanTenorDays?.value || 30);
        const tenor = allowedTenors.includes(selectedTenor) ? selectedTenor : 30;

        salaryInput.textContent = safeSalary.toLocaleString('en-US');
        if (salaryLiveInput && document.activeElement !== salaryLiveInput) {
            salaryLiveInput.value = safeSalary;
        }

        const principal = Math.max(0, Math.min(safeSalary * 0.4, 12000));
        const feeRate = getFeeRateByTenor(tenor);
        const fee = principal * feeRate;
        const totalRepayment = principal + fee;
        const monthlyEquivalent = tenor > 0 ? (totalRepayment / tenor) * 30 : totalRepayment;

        loanAmount.textContent = formatCurrency(principal);
        if (loanFee) loanFee.textContent = formatCurrency(fee);
        if (loanTotalRepayment) loanTotalRepayment.textContent = formatCurrency(totalRepayment);
        if (loanInstallment) loanInstallment.textContent = formatCurrency(monthlyEquivalent);
        if (loanTenorDisplay) loanTenorDisplay.textContent = tenor;

        if (applyLoanLink) {
            const query = new URLSearchParams({
                salary: String(safeSalary),
                amount: principal.toFixed(2),
                fee: fee.toFixed(2),
                tenor: String(tenor),
                repayment: totalRepayment.toFixed(2),
                installment: monthlyEquivalent.toFixed(2),
            });
            applyLoanLink.href = `/loan/?${query.toString()}`;
            const isValidEstimate = safeSalary >= 500 && principal > 0;
            applyLoanLink.classList.toggle('is-disabled', !isValidEstimate);
            applyLoanLink.setAttribute('aria-disabled', isValidEstimate ? 'false' : 'true');
        }

        const angle = salaryToAngle(Math.min(safeSalary, 15000));
        toggle.style.transform = `rotate(${angle}deg)`;
    }

    function onPointerMove(event) {
        let x;
        let y;

        if (event.type === 'mousemove') {
            x = event.clientX;
            y = event.clientY;
        } else if (event.type === 'touchmove') {
            const touch = event.touches[0];
            x = touch.clientX;
            y = touch.clientY;
        }

        const boxRect = toggleBox.getBoundingClientRect();
        const pointerX = x - boxRect.left;
        const pointerY = y - boxRect.top;

        const center = getCenter();
        const angle = getAngleBetweenPoints(center.x, center.y, pointerX, pointerY);
        const calcAngle = angle + 90;
        const salary = angleToSalary(calcAngle);

        updateCalculator(salary);
    }

    function onPointerEnter(event) {
        if (event.type === 'touchstart') {
            event.preventDefault();
        }
        toggleBox.addEventListener('mousemove', onPointerMove);
        toggleBox.addEventListener('touchmove', onPointerMove);
    }

    function onPointerLeave() {
        toggleBox.removeEventListener('mousemove', onPointerMove);
        toggleBox.removeEventListener('touchmove', onPointerMove);
    }

    toggleBox.addEventListener('mouseenter', onPointerEnter);
    toggleBox.addEventListener('mouseleave', onPointerLeave);
    toggleBox.addEventListener('touchstart', onPointerEnter);
    toggleBox.addEventListener('touchend', onPointerLeave);

    if (salaryLiveInput) {
        salaryLiveInput.addEventListener('input', () => {
            updateCalculator(Number(salaryLiveInput.value) || 0);
        });
    }

    if (loanTenorDays) {
        loanTenorDays.addEventListener('change', () => {
            updateCalculator(Number(salaryLiveInput?.value || salaryInput.textContent.replace(/,/g, '')) || 0);
        });
    }

    updateCalculator(Number(salaryLiveInput?.value) || 0);
}


// Benefits Section JS Functions
const benefitsBlock = document.querySelector('section.benefits');
const benefitsImages = benefitsBlock ? benefitsBlock.querySelectorAll('.left img') : [];
const benefitsTextBlocks = benefitsBlock ? benefitsBlock.querySelectorAll('.right .benefit') : [];
const benefitsLeftToggle = benefitsBlock ? benefitsBlock.querySelector('.center .left') : null;
const benefitsRightToggle = benefitsBlock ? benefitsBlock.querySelector('.center .right') : null;
const benefitFallbackImage = '/assets/images/placeholder-image.png';
benefitsImages.forEach((img) => {
  img.addEventListener('error', () => {
    img.onerror = null;
    img.src = benefitFallbackImage;
    if (benefitsBlock) {
      const leftPanel = benefitsBlock.querySelector('.benefits .left');
      if (leftPanel) {
        leftPanel.style.background = '#e9eff6';
      }
    }
  });
});


const benefitsImageProperties = [
  {
    number: 0,
    left: '0',
    zIndex: '2',
  },
  {
    number: 1,
    left: '100%',
    zIndex: '1',
  },
  {
    number: 2,
    left: '200%',
    zIndex: '0',
  }
];

let benefitsState = 0;
let autoSlideInterval;

// Function to set the state of the benefits section
function setBenefitsState() {
  benefitsImages.forEach((img, index) => {
    const stateIndex = (benefitsState + index) % 3;
    img.style.left = benefitsImageProperties[stateIndex].left;
    img.style.zIndex = benefitsImageProperties[stateIndex].zIndex;
    img.style.transition = 'all 0.5s ease';
  });

  benefitsTextBlocks.forEach((textBlock, index) => {
    textBlock.style.display = index === benefitsState ? 'block' : 'none';
  });
}

// Function to move to the next slide (simulates right toggle click)
function nextSlide() {
  benefitsState = (benefitsState + 1) % 3;
  setBenefitsState();
}

// Function to reset the auto-slide interval
function resetInterval() {
  clearInterval(autoSlideInterval);
  autoSlideInterval = setInterval(nextSlide, 5000);
}

if (benefitsImages.length && benefitsTextBlocks.length && benefitsLeftToggle && benefitsRightToggle) {
  benefitsLeftToggle.addEventListener('click', () => {
    benefitsState = (benefitsState - 1 + 3) % 3;
    setBenefitsState();
    resetInterval();
  });

  benefitsRightToggle.addEventListener('click', () => {
    benefitsState = (benefitsState + 1) % 3;
    setBenefitsState();
    resetInterval();
  });

  setBenefitsState();
  autoSlideInterval = setInterval(nextSlide, 5000);
}

// FAQ accordion
const faqItems = document.querySelectorAll('#faqAccordion .faq-item');
if (faqItems.length) {
  faqItems.forEach((item, idx) => {
    const question = item.querySelector('.faq-question');
    const answer = item.querySelector('.faq-answer');
    if (!question || !answer) return;

    const answerId = `faqAnswer${idx + 1}`;
    question.setAttribute('aria-controls', answerId);
    answer.setAttribute('id', answerId);
    answer.setAttribute('role', 'region');
    answer.setAttribute('aria-hidden', 'true');

    if (idx === 0) {
      item.classList.add('active');
      question.setAttribute('aria-expanded', 'true');
      answer.setAttribute('aria-hidden', 'false');
      answer.style.maxHeight = `${answer.scrollHeight}px`;
    }

    question.addEventListener('click', () => {
      const isOpen = item.classList.contains('active');

      faqItems.forEach((other) => {
        other.classList.remove('active');
        const otherQ = other.querySelector('.faq-question');
        const otherA = other.querySelector('.faq-answer');
        if (otherQ) otherQ.setAttribute('aria-expanded', 'false');
        if (otherA) {
          otherA.style.maxHeight = '0px';
          otherA.setAttribute('aria-hidden', 'true');
        }
      });

      if (!isOpen) {
        item.classList.add('active');
        question.setAttribute('aria-expanded', 'true');
        answer.style.maxHeight = `${answer.scrollHeight}px`;
        answer.setAttribute('aria-hidden', 'false');
      }
    });
  });
}

```


## /assets/scripts/dashboard.js
```js
// Close pre-header alert
const preHeader = document.querySelector('.pre-header');
if (preHeader) {
  const preHeaderCTA = preHeader.querySelector('.close');
  if (preHeaderCTA) {
    preHeaderCTA.addEventListener('click', (event) => {
      event.preventDefault();
      preHeader.style.display = 'none';
    });
  }
}

// Set active CTA based on current path
const currentPath = window.location.pathname;
document.querySelectorAll('.dashboard-header .menu-item, .dashboard-header .submenu-link').forEach((link) => {
  const href = link.getAttribute('href');
  if (!href || href === '#') return;

  if (currentPath === href || (href !== '/dashboard' && currentPath.startsWith(href))) {
    link.classList.add('active');
  }
});

// Sticky header
const header = document.querySelector('header');
if (header) {
  const sticky = header.offsetTop;
  window.addEventListener('scroll', () => {
    if (window.pageYOffset > sticky) {
      header.classList.add('sticky');
    } else {
      header.classList.remove('sticky');
    }
  });
}

// Menu toggle (dashboard)
const nav = document.querySelector('.dashboard-header .mobile-drawer');
const menuToggle = document.getElementById('menuToggle');

if (nav && menuToggle) {
  const header = document.querySelector('.dashboard-header');

  const setMenuState = (isActive) => {
    nav.classList.toggle('active', isActive);
    menuToggle.classList.toggle('active', isActive);
    menuToggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
    if (header) {
      header.classList.toggle('nav-open', isActive);
    }
    document.documentElement.classList.toggle('menu-open', isActive);
    document.body.classList.toggle('menu-open', isActive);
  };

  menuToggle.addEventListener('click', (event) => {
    event.preventDefault();
    setMenuState(!nav.classList.contains('active'));
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      setMenuState(false);
    });
  });

  document.addEventListener('click', (event) => {
    const clickedInsideMenu = nav.contains(event.target) || menuToggle.contains(event.target);
    if (!clickedInsideMenu) {
      setMenuState(false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setMenuState(false);
    }
  });
}

// Submenu behavior (desktop + mobile)
document.querySelectorAll('.dashboard-header [data-submenu]').forEach((group) => {
  const trigger = group.querySelector('.menu-trigger');
  if (!trigger) return;

  trigger.addEventListener('click', (event) => {
    event.preventDefault();
    const isMobileGroup = group.classList.contains('is-mobile');
    if (!isMobileGroup && window.matchMedia('(min-width: 1001px)').matches) {
      return;
    }

    const isActive = group.classList.toggle('active');
    trigger.setAttribute('aria-expanded', isActive ? 'true' : 'false');

    if (isMobileGroup && isActive) {
      nav?.querySelectorAll('[data-submenu].is-mobile').forEach((otherGroup) => {
        if (otherGroup !== group) {
          otherGroup.classList.remove('active');
          otherGroup.querySelector('.menu-trigger')?.setAttribute('aria-expanded', 'false');
        }
      });
    }
  });

  if (!group.classList.contains('is-mobile')) {
    group.addEventListener('mouseleave', () => {
      group.classList.remove('active');
      trigger.setAttribute('aria-expanded', 'false');
    });
  }
});

// Transactions search/filter/export UX
const txTable = document.getElementById('transactionsTable');
if (txTable) {
  const searchInput = document.getElementById('txSearchInput');
  const filterButtons = document.querySelectorAll('.tx-filter');
  const emptyState = document.getElementById('txEmptyState');
  const exportButton = document.getElementById('txExportBtn');
  let activeFilter = 'all';

  const applyTxFilters = () => {
    const query = (searchInput?.value || '').trim().toLowerCase();
    let visibleCount = 0;

    txTable.querySelectorAll('tbody tr').forEach((row) => {
      const rowText = row.innerText.toLowerCase();
      const txType = row.getAttribute('data-tx-type') || 'all';
      const typeMatch = activeFilter === 'all' || activeFilter === txType;
      const searchMatch = !query || rowText.includes(query);
      const isVisible = typeMatch && searchMatch;
      row.style.display = isVisible ? '' : 'none';
      if (isVisible) visibleCount++;
    });

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', applyTxFilters);
  }

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      activeFilter = button.getAttribute('data-filter') || 'all';
      filterButtons.forEach((btn) => btn.classList.remove('active'));
      button.classList.add('active');
      applyTxFilters();
    });
  });

  if (exportButton) {
    exportButton.addEventListener('click', () => {
      const rows = [...txTable.querySelectorAll('tr')].filter((row) => row.style.display !== 'none');
      const csv = rows
        .map((row) => [...row.querySelectorAll('th, td')]
          .map((cell) => `"${cell.innerText.replace(/"/g, '""')}"`)
          .join(','))
        .join('\n');

      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', 'transactions.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    });
  }
}

```


## /assets/scripts/control-panel.js
```js
// Menu nav toggle behavior
const menuToggle = document.querySelector('#menuToggle');
const nav = document.querySelector('nav');

if (menuToggle && nav) {
  menuToggle.addEventListener('click', () => {
    nav.classList.toggle('active');
    menuToggle.classList.toggle('active');
  });
}

function getGreeting() {
  const hour = new Date().getHours();

  if (hour >= 5 && hour < 12) {
    return 'Good Morning Boss';
  }

  if (hour >= 12 && hour < 17) {
    return 'Good Afternoon Boss';
  }

  return 'Good Evening Boss';
}

const greetingText = document.getElementById('greeting-text');
if (greetingText) {
  greetingText.textContent = getGreeting();
}

```
