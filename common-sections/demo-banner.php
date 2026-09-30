<?php
if (!defined('VELMORA_DEMO_BANNER_RENDERED')) {
    define('VELMORA_DEMO_BANNER_RENDERED', true);
?>
<div id="velmora-demo-banner" role="status" aria-label="Demo environment">DEMO</div>
<style>
#velmora-demo-banner{
    position:fixed;top:14px;right:14px;z-index:2147483647;
    background:#ff001e;color:#fff;border:2px solid rgba(255,255,255,.95);
    border-radius:999px;padding:10px 18px;
    font:900 13px/1 Arial,Helvetica,sans-serif;letter-spacing:.18em;
    box-shadow:0 10px 28px rgba(150,0,18,.38),0 0 0 4px rgba(255,0,30,.14);
    pointer-events:none;user-select:none;
}
@media(max-width:720px){
    #velmora-demo-banner{top:10px;right:10px;padding:9px 14px;font-size:12px}
}
</style>
<?php } ?>
