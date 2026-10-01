<?php
require_once __DIR__ . '/../common-sections/app.php';
http_response_code(404);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Page Not Found | Velmora Bank</title>
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<?php include __DIR__ . '/../common-sections/public-v2-header.php'; ?>
<main>
<section class="pv2-page-hero" style="min-height:58vh;display:grid;align-items:center">
  <div class="pv2-container">
    <span class="pv2-eyebrow">404 · PAGE NOT FOUND</span>
    <h1 style="max-width:780px">That page isn’t part of the current Velmora experience.</h1>
    <p style="max-width:680px">The address may be outdated, mistyped or from a page that has moved during the site overhaul.</p>
    <div class="pv2-actions">
      <a class="pv2-btn primary" href="/">Return home</a>
      <a class="pv2-btn secondary" href="/support-v2/">Support Center</a>
      <a class="pv2-btn secondary" href="/login-v2/">Online banking</a>
    </div>
  </div>
</section>
</main>
<?php include __DIR__ . '/../common-sections/public-v2-footer.php'; ?>
</body></html>