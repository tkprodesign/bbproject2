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
