<?php include('../app.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel - KYC</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 720px)">
</head>
<body>
<section class="table list-of-kyc" style="padding: 100px 0">
    <div class="container">
        <h2 style="margin-bottom: 36px">Profile Picture</h2>
        <?php
// Include database connection
// require_once 'db_connection.php';

// Function to fetch user data by ID
function getUserDataById($id) {
    $db = connectToDatabase();

    // Prepare and execute the query
    $stmt = $db->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    $user_data = $result->fetch_assoc();

    // Close the statement and connection
    $stmt->close();
    $db->close();

    return $user_data;
}

// Get the ID from the URL
$usage_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Fetch user data
$user_data = getUserDataById($usage_id);

// Function to display data
function displayUserData($user_data) {
    echo '<div class="user-details-container">';
    echo '<div><span>Name: </span><b>' . htmlspecialchars($user_data['name']) . '</b></div>';
    echo '<div><span>Email: </span><b>' . htmlspecialchars($user_data['email']) . '</b></div>';
    echo '<div><span>Profile Picture: </span><img src="/assets/images/profile-pictures/' . htmlspecialchars($user_data['profile_picture']) . '" alt="Profile Picture" /></div>';
    echo '</div>';
}

// Check if user data is found
if ($user_data) {
    displayUserData($user_data);
} else {
    echo 'No user data found for the given ID.';
}
?>



    </div>
</section>
</body>
</html>