<?php
require_once __DIR__ . '/../common-sections/app.php';
$supportPhoneNumber = getSupportPhoneNumber();
$supportWhatsappLink = getSupportWhatsappLink();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Velmora Bank — Modern Banking, Properly Structured</title>
    <meta name="description" content="A refined alternate homepage concept for Velmora Bank, built around clear banking products, secure digital access, international payments and human support.">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
    <link rel="stylesheet" href="/assets/stylesheets/home-v2.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="hv2-demo">DEMO ENVIRONMENT</div>

<div class="hv2-utility">
    <div class="hv2-container hv2-utility-inner">
        <div>
            <a href="mailto:support@velmorabank.us"><span class="material-symbols-rounded">mail</span>support@velmorabank.us</a>
            <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><span class="material-symbols-rounded">call</span><?php echo htmlspecialchars($supportPhoneNumber); ?></a>
        </div>
        <div>
            <a href="/atm-and-bank-locations/"><span class="material-symbols-rounded">location_on</span>ATM &amp; locations</a>
            <a href="/contact/"><span class="material-symbols-rounded">support_agent</span>Support</a>
        </div>
    </div>
</div>

<header class="hv2-header" id="hv2Header">
    <div class="hv2-container hv2-header-inner">
        <a class="hv2-logo" href="/" aria-label="Velmora Bank home">
            <img src="/assets/images/branding/logo.png" alt="Velmora Bank">
        </a>

        <nav class="hv2-desktop-nav" aria-label="Primary navigation">
            <a href="/personal/">Personal</a>
            <a href="/business/">Business</a>
            <a href="/credit-card/">Cards</a>
            <a href="/loan/">Loans</a>
            <a href="/about-us/">About</a>
        </nav>

        <div class="hv2-header-actions">
            <a class="hv2-signin" href="/login/"><span class="material-symbols-rounded">lock</span>Sign in</a>
            <a class="hv2-open" href="/signup/">Open an account</a>
        </div>

        <button class="hv2-menu-btn" id="hv2MenuBtn" type="button" aria-label="Open navigation" aria-expanded="false">
            <span class="material-symbols-rounded">menu</span>
        </button>
    </div>

    <nav class="hv2-mobile-nav" id="hv2MobileNav" aria-label="Mobile navigation">
        <a href="/personal/">Personal Banking</a>
        <a href="/business/">Business Banking</a>
        <a href="/credit-card/">Credit Cards</a>
        <a href="/loan/">Loans</a>
        <a href="/about-us/">About Velmora</a>
        <a href="/atm-and-bank-locations/">ATM &amp; Locations</a>
        <a href="/contact/">Support</a>
        <div>
            <a class="hv2-signin" href="/login/">Sign in</a>
            <a class="hv2-open" href="/signup/">Open an account</a>
        </div>
    </nav>
</header>

<main>
    <section class="hv2-hero">
        <div class="hv2-container hv2-hero-grid">
            <div class="hv2-hero-copy">
                <div class="hv2-overline"><span></span> PERSONAL · BUSINESS · INTERNATIONAL</div>
                <h1>Banking built to handle real financial life.</h1>
                <p class="hv2-lead">Everyday accounts, business banking, cards, lending and international money movement—organized around secure digital access and real bank processes.</p>
                <div class="hv2-hero-actions">
                    <a class="hv2-btn primary" href="/signup/">Open an account <span class="material-symbols-rounded">arrow_forward</span></a>
                    <a class="hv2-btn secondary" href="/login/">Access online banking</a>
                </div>
                <div class="hv2-hero-assurance">
                    <div><span class="material-symbols-rounded">verified_user</span><p><strong>Account controls</strong><small>Structured KYC and account security</small></p></div>
                    <div><span class="material-symbols-rounded">currency_exchange</span><p><strong>Bank-guided FX</strong><small>Quoted exchange before execution</small></p></div>
                    <div><span class="material-symbols-rounded">support_agent</span><p><strong>Human support</strong><small>Help when a process needs attention</small></p></div>
                </div>
            </div>

            <div class="hv2-hero-visual">
                <img src="/assets/images/home/hero/bank-exterior.jpg" alt="Velmora banking environment">
                <div class="hv2-hero-panel">
                    <span class="hv2-panel-label">ONLINE BANKING</span>
                    <strong>One workspace.<br>Proper banking flows.</strong>
                    <div class="hv2-panel-row"><span>Accounts</span><b>Multi-currency</b></div>
                    <div class="hv2-panel-row"><span>Payments</span><b>Review &amp; submit</b></div>
                    <div class="hv2-panel-row"><span>FX</span><b>Quote &amp; confirm</b></div>
                    <a href="/login/">Sign in securely <span class="material-symbols-rounded">arrow_forward</span></a>
                </div>
            </div>
        </div>
    </section>

    <section class="hv2-trust">
        <div class="hv2-container hv2-trust-grid">
            <div><span class="material-symbols-rounded">encrypted</span><p><strong>Secure digital access</strong><small>Authenticated sessions and controlled account actions</small></p></div>
            <div><span class="material-symbols-rounded">account_balance</span><p><strong>Structured banking</strong><small>Accounts remain denominated in their actual currencies</small></p></div>
            <div><span class="material-symbols-rounded">receipt_long</span><p><strong>Clear transaction records</strong><small>References, status, value dates and channels</small></p></div>
            <div><span class="material-symbols-rounded">public</span><p><strong>International movement</strong><small>Cross-currency activity through a quoted FX process</small></p></div>
        </div>
    </section>

    <section class="hv2-section hv2-products">
        <div class="hv2-container">
            <div class="hv2-heading">
                <div><span class="hv2-eyebrow">BANKING THAT STARTS WITH THE NEED</span><h2>Choose the relationship that fits.</h2></div>
                <p>Instead of crowding the page with products, Velmora groups banking around what clients are actually trying to do.</p>
            </div>

            <div class="hv2-product-grid">
                <a class="hv2-product-card featured" href="/personal/">
                    <span class="hv2-card-icon"><span class="material-symbols-rounded">account_balance_wallet</span></span>
                    <span class="hv2-card-tag">PERSONAL BANKING</span>
                    <h3>Everyday banking, without the clutter.</h3>
                    <p>Accounts, transfers and financial tools organized around daily use and long-term goals.</p>
                    <span class="hv2-card-link">Explore personal banking <span class="material-symbols-rounded">arrow_forward</span></span>
                </a>

                <a class="hv2-product-card" href="/business/">
                    <span class="hv2-card-icon"><span class="material-symbols-rounded">business_center</span></span>
                    <span class="hv2-card-tag">BUSINESS BANKING</span>
                    <h3>Operational banking for growing businesses.</h3>
                    <p>Manage business money movement with clearer account visibility and payment workflows.</p>
                    <span class="hv2-card-link">Explore business banking <span class="material-symbols-rounded">arrow_forward</span></span>
                </a>

                <a class="hv2-product-card" href="/credit-card/">
                    <span class="hv2-card-icon"><span class="material-symbols-rounded">credit_card</span></span>
                    <span class="hv2-card-tag">CARDS</span>
                    <h3>Flexible spending with modern controls.</h3>
                    <p>Card products designed for everyday purchases, travel and digital payment use.</p>
                    <span class="hv2-card-link">Explore cards <span class="material-symbols-rounded">arrow_forward</span></span>
                </a>

                <a class="hv2-product-card" href="/loan/">
                    <span class="hv2-card-icon"><span class="material-symbols-rounded">request_quote</span></span>
                    <span class="hv2-card-tag">LENDING</span>
                    <h3>Borrowing with a clearer path forward.</h3>
                    <p>Explore financing options with structured application steps and support when needed.</p>
                    <span class="hv2-card-link">Explore lending <span class="material-symbols-rounded">arrow_forward</span></span>
                </a>
            </div>
        </div>
    </section>

    <section class="hv2-section hv2-digital">
        <div class="hv2-container hv2-digital-grid">
            <div class="hv2-digital-copy">
                <span class="hv2-eyebrow light">VELMORA ONLINE BANKING</span>
                <h2>A banking dashboard that behaves like banking.</h2>
                <p>Balances stay attached to real account currencies. Transfers move through review. Currency exchange uses a bank quote. Transactions carry proper references and status.</p>
                <div class="hv2-checks">
                    <div><span class="material-symbols-rounded">check_circle</span><p><strong>Account-level balances</strong><small>See each account in its actual currency.</small></p></div>
                    <div><span class="material-symbols-rounded">check_circle</span><p><strong>Reviewed money movement</strong><small>Prepare, review and submit rather than instant UI tricks.</small></p></div>
                    <div><span class="material-symbols-rounded">check_circle</span><p><strong>Client profile &amp; security</strong><small>KYC, customer details and security activity in one place.</small></p></div>
                </div>
                <a class="hv2-btn gold" href="/login/">Access online banking <span class="material-symbols-rounded">arrow_forward</span></a>
            </div>

            <div class="hv2-dashboard-mock" aria-label="Online banking preview">
                <div class="hv2-mock-bar">
                    <div><span></span><span></span><span></span></div>
                    <small>Private Banking</small>
                </div>
                <div class="hv2-mock-body">
                    <aside>
                        <div class="hv2-mock-logo"><img src="/assets/images/branding/velmora/icon.png" alt=""></div>
                        <span class="active"></span><span></span><span></span><span></span><span></span>
                    </aside>
                    <section>
                        <div class="hv2-mock-title"><span></span><b></b></div>
                        <div class="hv2-mock-balance">
                            <small>PRIMARY ACCOUNT</small>
                            <strong>€ ••••••••</strong>
                            <div><span></span><span></span></div>
                        </div>
                        <div class="hv2-mock-stats"><span></span><span></span><span></span></div>
                        <div class="hv2-mock-table">
                            <b></b>
                            <div><span></span><span></span><span></span></div>
                            <div><span></span><span></span><span></span></div>
                            <div><span></span><span></span><span></span></div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>

    <section class="hv2-section hv2-fx">
        <div class="hv2-container hv2-fx-grid">
            <div class="hv2-fx-visual">
                <img src="/assets/images/home/world-map.png" alt="">
                <div class="hv2-fx-card">
                    <span class="material-symbols-rounded">currency_exchange</span>
                    <div><small>EXCHANGE WORKFLOW</small><strong>Quote → Review → Confirm</strong></div>
                </div>
            </div>
            <div>
                <span class="hv2-eyebrow">INTERNATIONAL MONEY</span>
                <h2>Foreign exchange should be a trade, not a dropdown.</h2>
                <p>When money moves from one currency to another, the client should know the quoted rate, the amount being sold, the amount being received and when the trade is executed.</p>
                <ul class="hv2-fx-list">
                    <li><span>01</span><p><strong>Select accounts</strong><small>Choose source and destination currency accounts.</small></p></li>
                    <li><span>02</span><p><strong>Receive bank quote</strong><small>Review the rate and trade terms before execution.</small></p></li>
                    <li><span>03</span><p><strong>Confirm the trade</strong><small>Debit and credit entries are recorded in the ledger.</small></p></li>
                </ul>
                <a class="hv2-text-link" href="/online-banking/">Learn about online banking <span class="material-symbols-rounded">arrow_forward</span></a>
            </div>
        </div>
    </section>

    <section class="hv2-section hv2-service">
        <div class="hv2-container hv2-service-grid">
            <div class="hv2-service-image">
                <img src="/assets/images/home/contact-2.jpg" alt="Banking support">
            </div>
            <div class="hv2-service-copy">
                <span class="hv2-eyebrow">SERVICE WHEN IT MATTERS</span>
                <h2>Digital convenience should not remove the human layer.</h2>
                <p>Some banking needs are simple. Others need review, explanation or help from a person. Velmora keeps direct support visible instead of hiding it behind the interface.</p>
                <div class="hv2-service-actions">
                    <a class="hv2-btn primary" href="/contact/">Speak to support</a>
                    <a class="hv2-btn secondary dark" href="/atm-and-bank-locations/">Find a location</a>
                </div>
                <div class="hv2-contact-line">
                    <span class="material-symbols-rounded">call</span>
                    <p><small>CLIENT SUPPORT</small><a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($supportPhoneNumber); ?></a></p>
                </div>
            </div>
        </div>
    </section>

    <section class="hv2-cta">
        <div class="hv2-container">
            <div>
                <span class="hv2-eyebrow light">START YOUR RELATIONSHIP</span>
                <h2>Open an account or return to online banking.</h2>
            </div>
            <div>
                <a class="hv2-btn gold" href="/signup/">Open an account</a>
                <a class="hv2-btn outline-light" href="/login/">Sign in</a>
            </div>
        </div>
    </section>
</main>

<footer class="hv2-footer">
    <div class="hv2-container">
        <div class="hv2-footer-top">
            <div class="hv2-footer-brand">
                <img src="/assets/images/branding/logo.png" alt="Velmora Bank">
                <p>Modern banking built around secure access, structured money movement and clearer client relationships.</p>
            </div>
            <div class="hv2-footer-links">
                <div><strong>Banking</strong><a href="/personal/">Personal</a><a href="/business/">Business</a><a href="/credit-card/">Cards</a><a href="/loan/">Loans</a></div>
                <div><strong>About</strong><a href="/about-us/">About Velmora</a><a href="/careers/">Careers</a><a href="/atm-and-bank-locations/">Locations</a><a href="/contact/">Contact</a></div>
                <div><strong>Security</strong><a href="/quick-links/#online-security-tips">Security tips</a><a href="/quick-links/#anti-money-laundering">AML</a><a href="/cookie-policy/">Cookie policy</a><a href="/quick-links/#support-center">Support center</a></div>
            </div>
        </div>
        <div class="hv2-footer-bottom">
            <span>© <?php echo date('Y'); ?> Velmora Bank. Demo environment.</span>
            <span>400 Park Ave, New York, NY 10022, United States</span>
        </div>
    </div>
</footer>

<script src="/assets/scripts/home-v2.js?v=<?php echo time(); ?>"></script>
</body>
</html>