<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';

$profile=v3Profile($user_email,$user_name);
$notifications=v3Notifications($user_email,100);
$unread=count(array_filter($notifications,fn($n)=>(int)$n['is_read']===0));

v3PageStart('Notifications','notifications',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
  <div><span class="v3-kicker">MESSAGE CENTER</span><h1>Notifications</h1><p>Account, transaction and security notices associated with your banking relationship.</p></div>
  <?php if($unread>0): ?><form method="post"><?php echo v3CsrfInput(); ?><button class="v3-secondary-btn" type="submit" name="v3_mark_notifications_read" value="1"><span class="material-symbols-rounded">done_all</span>Mark all read</button></form><?php endif; ?>
</section>
<section class="v3-panel">
  <div class="v3-section-head"><div><span class="v3-kicker">INBOX</span><h2><?php echo $unread; ?> unread</h2></div></div>
  <div class="v3-notification-list">
    <?php foreach($notifications as $n): ?>
    <article class="<?php echo (int)$n['is_read']===0?'unread':''; ?>">
      <span class="v3-list-icon"><span class="material-symbols-rounded"><?php echo stripos($n['notification_type'],'security')!==false?'shield':'notifications'; ?></span></span>
      <div>
        <div class="v3-notification-title"><strong><?php echo htmlspecialchars($n['title']); ?></strong><?php if((int)$n['is_read']===0): ?><b>NEW</b><?php endif; ?></div>
        <p><?php echo htmlspecialchars($n['body']); ?></p>
        <small><?php echo htmlspecialchars(date('M d, Y · H:i',strtotime((string)$n['created_at']))); ?> · <?php echo htmlspecialchars($n['notification_type']); ?></small>
      </div>
      <?php if(!empty($n['action_url'])): ?><a href="<?php echo htmlspecialchars($n['action_url']); ?>"><span class="material-symbols-rounded">arrow_forward</span></a><?php endif; ?>
    </article>
    <?php endforeach; ?>
    <?php if(empty($notifications)): ?><div class="v3-empty">No notifications yet.</div><?php endif; ?>
  </div>
</section>
<?php v3PageEnd(); ?>