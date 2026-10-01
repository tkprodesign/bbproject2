<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT name,value,updated_at FROM dynamic_data ORDER BY name");
$dynamic=$res?$res->fetch_all(MYSQLI_ASSOC):[];
$tableCounts=[];
foreach(['users','accounts','transactions','fx_trades','beneficiaries','notifications','support_cases','support_case_messages','security_events','kyc_data'] as $table){$r=$db->query("SELECT COUNT(*) FROM `".$table."`");$tableCounts[$table]=(int)($r?$r->fetch_row()[0]:0);}
$db->close();$phone=getSupportPhoneNumber();
cpv2Start('Settings','settings');
?>
<section class="op-heading"><div><span class="op-kicker">SYSTEM SETTINGS</span><h1>Operational settings</h1><p>Maintain shared site settings and review the core database modules currently in use.</p></div></section>
<div class="op-two">
<section class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">SITE CONTACT</span><h2>Support phone</h2></div></div>
<form method="post" class="op-form"><?php echo cpv2CsrfInput(); ?><label><span>Published support phone</span><input type="text" name="support_phone" value="<?php echo htmlspecialchars($phone); ?>" required></label><button class="op-btn primary" type="submit" name="cpv2_support_phone" value="1">Update phone</button></form>
<div class="op-panel-head" style="margin-top:24px"><div><span class="op-kicker">DYNAMIC DATA</span><h2>Configured keys</h2></div></div>
<div class="op-list"><?php foreach($dynamic as $d):?><article><span class="material-symbols-rounded">database</span><div><strong><?php echo htmlspecialchars($d['name']); ?></strong><small><?php echo htmlspecialchars($d['value']!==''?$d['value']:'No public value'); ?> · Updated <?php echo htmlspecialchars($d['updated_at']); ?></small></div></article><?php endforeach;?></div>
</section>
<aside class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">DATA MODULES</span><h2>Database footprint</h2></div></div>
<div class="op-list"><?php foreach($tableCounts as $name=>$count):?><article><span class="material-symbols-rounded">table_chart</span><div><strong><?php echo htmlspecialchars($name); ?></strong><small><?php echo (int)$count; ?> records</small></div></article><?php endforeach;?></div>
</aside></div>
<?php cpv2End(); ?>