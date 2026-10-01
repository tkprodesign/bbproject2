<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT id,user_email,title,body,notification_type,is_read,created_at FROM notifications ORDER BY id DESC LIMIT 100");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();
cpv2Start('Communications','communications');
?>
<section class="op-heading"><div><span class="op-kicker">CUSTOMER COMMUNICATIONS</span><h1>Notification operations</h1><p>Send structured in-app notices and review recent customer-facing notification records.</p></div></section>
<div class="op-two">
<section class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">RECENT MESSAGES</span><h2>Notification log</h2></div></div>
<div class="op-list"><?php foreach($rows as $r):?><article><span class="material-symbols-rounded">notifications</span><div><strong><?php echo htmlspecialchars($r['title']); ?></strong><small><?php echo htmlspecialchars($r['user_email'].' · '.$r['notification_type'].' · '.date('M d, Y H:i',strtotime($r['created_at']))); ?></small><small><?php echo htmlspecialchars(mb_strimwidth($r['body'],0,120,'…')); ?></small></div><span class="op-status <?php echo (int)$r['is_read']===1?'approved':'pending'; ?>"><?php echo (int)$r['is_read']===1?'Read':'Unread'; ?></span></article><?php endforeach;?><?php if(empty($rows)):?><div class="op-alert error">No notifications recorded.</div><?php endif;?></div>
</section>
<aside class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">SEND NOTICE</span><h2>In-app notification</h2></div></div>
<form method="post" class="op-form"><?php echo cpv2CsrfInput(); ?>
<label><span>Customer email</span><input type="email" name="email" required></label>
<label><span>Notification type</span><select name="type"><option>Operations</option><option>Account</option><option>Transfer</option><option>Security</option><option>KYC</option></select></label>
<label><span>Title</span><input type="text" name="title" maxlength="190" required></label>
<label><span>Message</span><textarea name="body" required></textarea></label>
<button class="op-btn primary" type="submit" name="cpv2_send_notification" value="1">Send notification</button>
</form></aside>
</div>
<?php cpv2End(); ?>