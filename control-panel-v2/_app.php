<?php
require_once __DIR__ . '/../control-panel/app.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['cpv2_csrf'])) $_SESSION['cpv2_csrf']=bin2hex(random_bytes(24));

function cpv2CsrfInput(): string {
    return '<input type="hidden" name="cpv2_csrf" value="'.htmlspecialchars((string)$_SESSION['cpv2_csrf'],ENT_QUOTES,'UTF-8').'">';
}
function cpv2Verify(): void {
    $token=(string)($_POST['cpv2_csrf']??'');
    if($token===''||!hash_equals((string)($_SESSION['cpv2_csrf']??''),$token)){http_response_code(419);exit('Session validation failed.');}
}
function cpv2Flash(string $kind,string $message): void { $_SESSION['cpv2_flash'][$kind]=$message; }
function cpv2TakeFlash(string $kind): ?string { $v=$_SESSION['cpv2_flash'][$kind]??null;unset($_SESSION['cpv2_flash'][$kind]);return is_string($v)?$v:null; }
function cpv2Go(string $url): never { header('Location: '.$url);exit; }

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['cpv2_transfer_decision'])){
    cpv2Verify();$id=(int)($_POST['transaction_id']??0);$decision=(string)($_POST['decision']??'');
    if($id<=0||!in_array($decision,['Successful','Failed'],true)){cpv2Flash('error','Invalid transfer decision.');cpv2Go('/control-panel-v2/transfers/');}
    $db=connectToDatabase();
    $stmt=$db->prepare("SELECT user_email,transaction_id FROM transactions WHERE id=? AND type='Transfer' LIMIT 1");$stmt->bind_param('i',$id);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
    if($row){
        $stmt=$db->prepare("UPDATE transactions SET status=?,posted_at=NOW(),value_date=COALESCE(value_date,CURDATE()) WHERE id=?");$stmt->bind_param('si',$decision,$id);$stmt->execute();$stmt->close();
        createUserNotification($db,$row['user_email'],'Transfer '.$decision,'Transfer '.$row['transaction_id'].' status is now '.$decision.'.','Transfer','/dashboard-v3/transactions/detail/?ref='.urlencode($row['transaction_id']));
        cpv2Flash('success','Transfer status updated.');
    }else cpv2Flash('error','Transfer not found.');
    $db->close();cpv2Go('/control-panel-v2/transfers/');
}

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['cpv2_kyc_decision'])){
    cpv2Verify();$id=(int)($_POST['kyc_id']??0);$decision=(string)($_POST['decision']??'');$note=trim((string)($_POST['note']??''));
    if($id<=0||!in_array($decision,['Approved','Rejected','Pending'],true)){cpv2Flash('error','Invalid KYC decision.');cpv2Go('/control-panel-v2/kyc/');}
    $db=connectToDatabase();$stmt=$db->prepare("SELECT email FROM kyc_data WHERE id=? LIMIT 1");$stmt->bind_param('i',$id);$stmt->execute();$stmt->bind_result($email);$found=$stmt->fetch();$stmt->close();
    if($found){
        $stmt=$db->prepare("UPDATE kyc_data SET status=?,description=? WHERE id=?");$stmt->bind_param('ssi',$decision,$note,$id);$stmt->execute();$stmt->close();
        createUserNotification($db,$email,'Identity review updated','Your identity review status is now '.$decision.'.','KYC','/dashboard-v3/identity/');
        cpv2Flash('success','KYC status updated.');
    }else cpv2Flash('error','KYC record not found.');
    $db->close();cpv2Go('/control-panel-v2/kyc/detail/?id='.$id);
}

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['cpv2_send_notification'])){
    cpv2Verify();$email=trim((string)($_POST['email']??''));$title=trim((string)($_POST['title']??''));$body=trim((string)($_POST['body']??''));$type=trim((string)($_POST['type']??'Operations'));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||$title===''||$body===''){cpv2Flash('error','Complete all notification fields.');cpv2Go('/control-panel-v2/communications/');}
    $db=connectToDatabase();createUserNotification($db,$email,$title,$body,$type,'/dashboard-v3/notifications/');$db->close();cpv2Flash('success','In-app notification queued for the customer.');cpv2Go('/control-panel-v2/communications/');
}

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['cpv2_support_phone'])){
    cpv2Verify();$phone=trim((string)($_POST['support_phone']??''));
    if($phone===''){cpv2Flash('error','Enter a support phone number.');cpv2Go('/control-panel-v2/settings/');}
    $db=connectToDatabase();$stmt=$db->prepare("INSERT INTO dynamic_data (name,value) VALUES ('phone_number',?) ON DUPLICATE KEY UPDATE value=VALUES(value)");$stmt->bind_param('s',$phone);$stmt->execute();$stmt->close();$db->close();cpv2Flash('success','Support phone updated.');cpv2Go('/control-panel-v2/settings/');
}
