<?php
$supportPhoneNumber = getSupportPhoneNumber();
$supportWhatsappLink = getSupportWhatsappLink();
?>
<div class="pv2-demo">DEMO ENVIRONMENT</div>
<div class="pv2-utility">
  <div class="pv2-container pv2-utility-inner">
    <div>
      <a href="mailto:support@velmorabank.us"><span class="material-symbols-rounded">mail</span>support@velmorabank.us</a>
      <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><span class="material-symbols-rounded">call</span><?php echo htmlspecialchars($supportPhoneNumber); ?></a>
    </div>
    <div>
      <a href="/locations-v2/"><span class="material-symbols-rounded">location_on</span>ATM &amp; locations</a>
      <a href="/contact-v2/"><span class="material-symbols-rounded">support_agent</span>Support</a>
    </div>
  </div>
</div>
<header class="pv2-header" id="pv2Header">
  <div class="pv2-container pv2-header-inner">
    <a class="pv2-logo" href="/" aria-label="Velmora Bank home">
      <img src="/assets/images/branding/logo.png" alt="Velmora Bank">
    </a>
    <nav class="pv2-desktop-nav" aria-label="Primary navigation">
      <a href="/personal-v2/">Personal</a>
      <a href="/business-v2/">Business</a>
      <a href="/credit-card-v2/">Cards</a>
      <a href="/loan-v2/">Loans</a>
      <a href="/international-v2/">International</a>
      <a href="/about-v2/">About</a>
    </nav>
    <div class="pv2-header-actions">
      <a class="pv2-signin" href="/login-v2/"><span class="material-symbols-rounded">lock</span>Sign in</a>
      <a class="pv2-open" href="/signup-v2/">Open an account</a>
    </div>
    <button class="pv2-menu-btn" id="pv2MenuBtn" type="button" aria-label="Open navigation" aria-expanded="false">
      <span class="material-symbols-rounded">menu</span>
    </button>
  </div>
  <nav class="pv2-mobile-nav" id="pv2MobileNav" aria-label="Mobile navigation">
    <a href="/personal-v2/">Personal Banking</a>
    <a href="/business-v2/">Business Banking</a>
    <a href="/credit-card-v2/">Credit Cards</a>
    <a href="/loan-v2/">Loans &amp; Financing</a>
    <a href="/international-v2/">International &amp; FX</a>
    <a href="/online-banking-v2/">Online Banking</a>
    <a href="/about-v2/">About Velmora</a>
    <a href="/contact-v2/">Support</a>
    <div>
      <a class="pv2-signin" href="/login-v2/">Sign in</a>
      <a class="pv2-open" href="/signup-v2/">Open an account</a>
    </div>
  </nav>
</header>