<?php
$supportPhoneNumber = getSupportPhoneNumber();
$supportWhatsappLink = getSupportWhatsappLink();
?>
<div class="pv2-utility">
  <div class="pv2-container pv2-utility-inner">
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
<header class="pv2-header" id="pv2Header">
  <div class="pv2-container pv2-header-inner">
    <a class="pv2-logo" href="/" aria-label="Velmora Bank home">
      <img src="/assets/images/branding/logo.png" alt="Velmora Bank">
    </a>
    <nav class="pv2-desktop-nav" aria-label="Primary navigation">
      <a href="/personal/">Personal</a>
      <a href="/business/">Business</a>
      <a href="/credit-card/">Cards</a>
      <a href="/loan/">Loans</a>
      <a href="/international/">International</a>
      <a href="/about-us/">About</a>
    </nav>
    <div class="pv2-header-actions">
      <a class="pv2-signin" href="/login/"><span class="material-symbols-rounded">lock</span>Sign in</a>
      <a class="pv2-open" href="/signup/">Open an account</a>
    </div>
    <button class="pv2-menu-btn" id="pv2MenuBtn" type="button" aria-label="Open navigation" aria-expanded="false">
      <span class="material-symbols-rounded">menu</span>
    </button>
  </div>
  <nav class="pv2-mobile-nav" id="pv2MobileNav" aria-label="Mobile navigation">
    <a href="/personal/">Personal Banking</a>
    <a href="/business/">Business Banking</a>
    <a href="/credit-card/">Credit Cards</a>
    <a href="/loan/">Loans &amp; Financing</a>
    <a href="/international/">International &amp; FX</a>
    <a href="/online-banking/">Online Banking</a>
    <a href="/about-us/">About Velmora</a>
    <a href="/contact/">Support</a>
    <div>
      <a class="pv2-signin" href="/login/">Sign in</a>
      <a class="pv2-open" href="/signup/">Open an account</a>
    </div>
  </nav>
</header>