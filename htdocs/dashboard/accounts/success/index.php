<?php
include('../../app.php');
$account_number;
if (!isset($_GET['nos'])) {
    header('Location: ../');
    exit();
} else {
    $nos = $_GET['nos'];
    $account_number = (int)substr($nos, 0, -1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
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
<section class="account-success">
    <div class="content">
        <img src="/assets/images/dashboard/success.ico">
        <p>Congratulations, a new account has been created with account number <strong><?php echo $account_number; ?></strong>.</p>
        <a href="../" class="cta">View Details</a>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>