<?php include('../../app.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
    <!-- Smartsupp Live Chat script -->
<script type="text/javascript">
var _smartsupp = _smartsupp || {};
_smartsupp.key = '598556b50d3bd3f128d2c9362dccbf940458225d';
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>
<noscript> Powered by <a href=“https://www.smartsupp.com” target=“_blank”>Smartsupp</a></noscript>
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<section class="add-account">
    <div class="container">
        <!-- <a href="#" class="manage-accounts">Manage Accounts</a> -->
        <?php
            if (isset($_POST['upload_picture'])) {
                // Collect form data
                $user_name = htmlspecialchars($_POST['user_name']);
                $user_email = htmlspecialchars($_POST['user_email']);
                $profile_picture = $_FILES['profile_picture'];

                // exit();

                // Validate the uploaded file
                if ($profile_picture['error'] === UPLOAD_ERR_OK) {
                    // Get the file name and its extension
                    $file_name = basename($profile_picture['name']);
                    $file_tmp_name = $profile_picture['tmp_name'];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $file_name_without_ext = pathinfo($file_name, PATHINFO_FILENAME);

                    // Generate a unique file name to avoid conflicts
                    $unique_file_name = $file_name_without_ext . '_' . time() . '.' . $file_ext;

                    // Set the target directory and file path
                    $target_dir = '/assets/images/profile-pictures/';
                    $target_file = $_SERVER['DOCUMENT_ROOT'] . $target_dir . $unique_file_name;

                    // Move the uploaded file to the target directory
                    if (move_uploaded_file($file_tmp_name, $target_file)) {
                        // exit();
                        // Update the database with the new profile picture name
                        $db = connectToDatabase();
                        $stmt = $db->prepare("UPDATE users SET profile_picture = ? WHERE email = ?");
                        $stmt->bind_param("ss", $unique_file_name, $user_email);
                        $stmt->execute();

                        // Close the statement and database connection
                        $stmt->close();
                        $db->close();

                        // Redirect to the dashboard
                        // header("Location: /dashboard");
                        exit();
                    } else {
                        echo "Sorry, there was an error uploading your file.";
                    }
                } else {
                    echo "No file was uploaded or there was an upload error.";
                }
            }
        ?>
<form action="" method="post" enctype="multipart/form-data">
    <h2>Upload Image For Verification</h2>
    <div class="input-box">
        <label>Name</label>
        <input type="text" name="user_name" readonly value="<?php echo htmlspecialchars($user_name); ?>" class="dark-bg">
    </div>
    <div class="input-box">
        <label>Email</label>
        <input type="text" name="user_email" readonly value="<?php echo htmlspecialchars($user_email); ?>" class="dark-bg">
    </div>
    <div class="input-box">
        <label>Image</label>
        <input type="file" name="profile_picture"   class="dark-bg">
    </div>
    <div class="input-box">
        <button type="submit" name="upload_picture" accept="image/*" value="1">Upload Picture</button>
    </div>
</form>


    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>