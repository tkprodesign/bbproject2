<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';

$profile=v3Profile($user_email,$user_name);
$error=null;

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['v3_profile_picture_upload'])){
    v3VerifyPost();

    if(!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error']!==UPLOAD_ERR_OK){
        $error='Select a valid JPG or PNG image.';
    }else{
        $file=$_FILES['profile_picture'];
        $extension=strtolower(pathinfo((string)$file['name'],PATHINFO_EXTENSION));
        $allowed=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png'];
        $imageInfo=@getimagesize($file['tmp_name']);

        if($imageInfo===false){
            $error='The selected file is not a valid image.';
        }elseif($file['size']>2*1024*1024){
            $error='The image must be 2 MB or smaller.';
        }elseif(!isset($allowed[$extension]) || ($imageInfo['mime']??'')!==$allowed[$extension]){
            $error='Only JPG, JPEG and PNG images are accepted.';
        }else{
            $targetDir=dirname(__DIR__,2).'/dashboard/security/complete-kyc/uploads/';
            if(!is_dir($targetDir) && !mkdir($targetDir,0755,true)){
                $error='The profile-image storage directory is not available.';
            }elseif(!is_writable($targetDir)){
                $error='The profile-image storage directory is not writable.';
            }else{
                $fileName=uniqid('profile_',true).'.'.$extension;
                $targetFile=$targetDir.$fileName;
                if(!move_uploaded_file($file['tmp_name'],$targetFile)){
                    $error='The image could not be uploaded.';
                }else{
                    $db=connectToDatabase();
                    $stmt=$db->prepare("UPDATE users SET profile_picture=? WHERE email=?");
                    $stmt->bind_param('ss',$fileName,$user_email);
                    $ok=$stmt->execute();
                    $stmt->close();

                    if($ok){
                        recordSecurityEvent($db,$user_email,'Profile Photo Updated','Online banking profile picture updated');
                        createUserNotification($db,$user_email,'Profile photo updated','Your online banking profile photo was updated.','Profile','/dashboard-v3/profile/');
                    }
                    $db->close();

                    if($ok){
                        v3PostMessage('success','Profile photo updated.');
                        v3Redirect('/dashboard-v3/profile/');
                    }else{
                        @unlink($targetFile);
                        $error='The profile record could not be updated.';
                    }
                }
            }
        }
    }
}

v3PageStart('Profile Picture','profile',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
  <div><span class="v3-kicker">PROFILE</span><h1>Profile picture</h1><p>Upload a clear image for your online banking profile. This is separate from identity/KYC verification.</p></div>
  <a class="v3-secondary-btn" href="/dashboard-v3/profile/">Back to profile</a>
</section>

<div class="v3-two-col">
  <section class="v3-panel">
    <?php if($error): ?><div class="v3-alert error"><span class="material-symbols-rounded">error</span><div><?php echo htmlspecialchars($error); ?></div></div><?php endif; ?>

    <div class="v3-profile-hero" style="margin-bottom:20px">
      <?php if($user_profile_picture && $user_profile_picture!=='nil'): ?>
        <img src="/dashboard/security/complete-kyc/uploads/<?php echo htmlspecialchars($user_profile_picture); ?>" alt="Current profile picture">
      <?php else: ?>
        <span class="v3-profile-avatar"><?php echo htmlspecialchars(strtoupper(substr($profile['name'],0,1))); ?></span>
      <?php endif; ?>
      <div><span class="v3-kicker">CURRENT PROFILE</span><h2><?php echo htmlspecialchars($profile['name']); ?></h2><p><?php echo htmlspecialchars($profile['occupation']); ?></p></div>
    </div>

    <form method="post" enctype="multipart/form-data" class="v3-form">
      <?php echo v3CsrfInput(); ?>
      <label><span>Select image</span><input type="file" name="profile_picture" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required></label>
      <p class="v3-form-note">JPG, JPEG or PNG only. Maximum file size: 2 MB.</p>
      <button class="v3-primary-btn" type="submit" name="v3_profile_picture_upload" value="1">Update profile picture</button>
    </form>
  </section>

  <aside class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">PHOTO GUIDANCE</span><h2>Use a clear profile image.</h2></div></div>
    <div class="v3-list">
      <article class="v3-list-item"><span class="v3-list-icon"><span class="material-symbols-rounded">face</span></span><div><strong>Clearly visible</strong><small>Use a recent image where your face is easy to see.</small></div></article>
      <article class="v3-list-item"><span class="v3-list-icon"><span class="material-symbols-rounded">crop</span></span><div><strong>Simple framing</strong><small>A head-and-shoulders image works best in the banking interface.</small></div></article>
      <article class="v3-list-item"><span class="v3-list-icon"><span class="material-symbols-rounded">verified_user</span></span><div><strong>Not a KYC document</strong><small>This image does not replace identity-verification requirements.</small></div></article>
    </div>
  </aside>
</div>
<?php v3PageEnd(); ?>