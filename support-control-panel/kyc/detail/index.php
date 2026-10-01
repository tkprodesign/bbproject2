<?php
require_once __DIR__ . '/../../_app.php';
require_once __DIR__ . '/../../_layout.php';
$id=(int)($_GET['id']??0);$db=connectToDatabase();
$stmt=$db->prepare("SELECT * FROM kyc_data WHERE id=? LIMIT 1");$stmt->bind_param('i',$id);$stmt->execute();$row=$stmt->get_result()->fetch_assoc()?:null;$stmt->close();$db->close();
if(!$row)cpv2Go('/support-control-panel/kyc/');
cpv2Start('KYC Record','kyc');
?>
<section class="op-heading"><div><span class="op-kicker">IDENTITY REVIEW</span><h1><?php echo htmlspecialchars(trim($row['first_name'].' '.$row['last_name'])); ?></h1><p><?php echo htmlspecialchars($row['email']); ?> · Record #<?php echo (int)$row['id']; ?></p></div><a class="op-btn secondary" href="/support-control-panel/kyc/">Back to queue</a></section>
<div class="op-two">
<section class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">SUBMITTED PROFILE</span><h2>Identity information</h2></div><span class="op-status <?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></div>
<dl class="op-detail">
<div><dt>First name</dt><dd><?php echo htmlspecialchars($row['first_name']); ?></dd></div><div><dt>Middle name</dt><dd><?php echo htmlspecialchars($row['middle_name']?:'—'); ?></dd></div>
<div><dt>Last name</dt><dd><?php echo htmlspecialchars($row['last_name']); ?></dd></div><div><dt>Date of birth</dt><dd><?php echo htmlspecialchars($row['date_of_birth']); ?></dd></div>
<div><dt>Gender</dt><dd><?php echo htmlspecialchars($row['gender']?:'—'); ?></dd></div><div><dt>Phone</dt><dd><?php echo htmlspecialchars($row['phone_number']); ?></dd></div>
<div><dt>Occupation</dt><dd><?php echo htmlspecialchars($row['occupation']?:'—'); ?></dd></div><div><dt>Source of income</dt><dd><?php echo htmlspecialchars($row['source_of_income']); ?></dd></div>
<div><dt>Nationality</dt><dd><?php echo htmlspecialchars($row['nationality']); ?></dd></div><div><dt>Country of residence</dt><dd><?php echo htmlspecialchars($row['country_of_residence']); ?></dd></div>
<div><dt>U.S. citizen</dt><dd><?php echo htmlspecialchars($row['us_citizen']?:'—'); ?></dd></div><div><dt>Dual citizenship</dt><dd><?php echo htmlspecialchars($row['dual_citizenship']?:'—'); ?></dd></div>
<div class="wide"><dt>Residential address</dt><dd><?php echo htmlspecialchars(trim($row['address1'].' '.$row['address2'].' '.$row['apartment_no'].', '.$row['city'].', '.$row['state'].' '.$row['zip_code'])); ?></dd></div>
<div><dt>Submitted</dt><dd><?php echo htmlspecialchars($row['time_uploaded']); ?></dd></div><div><dt>Existing review note</dt><dd><?php echo htmlspecialchars($row['description']?:'—'); ?></dd></div>
</dl></section>
<aside class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">DECISION</span><h2>Review outcome</h2></div></div>
<form method="post" class="op-form"><?php echo cpv2CsrfInput(); ?><input type="hidden" name="kyc_id" value="<?php echo (int)$row['id']; ?>">
<label><span>Decision</span><select name="decision" required><option value="Pending" <?php echo $row['status']==='Pending'?'selected':''; ?>>Pending</option><option value="Approved" <?php echo $row['status']==='Approved'?'selected':''; ?>>Approved</option><option value="Rejected" <?php echo $row['status']==='Rejected'?'selected':''; ?>>Rejected</option></select></label>
<label><span>Review note</span><textarea name="note" placeholder="Reason or internal/customer-facing context"><?php echo htmlspecialchars($row['description']??''); ?></textarea></label>
<button class="op-btn primary" type="submit" name="cpv2_kyc_decision" value="1">Save decision</button>
</form></aside>
</div>
<?php cpv2End(); ?>