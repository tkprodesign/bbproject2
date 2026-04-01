<?php include('../app.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel - KYC</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
</head>
<body>
<section class="table list-of-kyc" style="padding: 100px 0">
    <div class="container">
        <h2 style="margin-bottom: 36px">KYC DATA</h2>
        <?php
// Include database connection
// require_once 'db_connection.php';

// Function to fetch KYC data by ID
function getKYCDataById($id) {
    $db = connectToDatabase();

    // Prepare and execute the query
    $stmt = $db->prepare("SELECT * FROM kyc_data WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    $kyc_data = $result->fetch_assoc();

    // Close the statement and connection
    $stmt->close();
    $db->close();

    return $kyc_data;
}

// Get the ID from the URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Fetch KYC data
$kyc_data = getKYCDataById($id);

// Function to display data
function displayData($title, $value) {
    echo '<div><span>' . htmlspecialchars($title) . ': </span><b>' . (!empty($value) ? htmlspecialchars($value) : 'Not set') . '</b></div>';
}

// Check if KYC data is found
if ($kyc_data) {
    echo '<div class="kyc-details-container">';
    displayData('ID', $kyc_data['id']);
    displayData('First Name', $kyc_data['first_name']);
    displayData('Middle Name', $kyc_data['middle_name']);
    displayData('Last Name', $kyc_data['last_name']);
    displayData('Suffix', $kyc_data['suffix']);
    displayData('Gender', $kyc_data['gender']);
    displayData('Address 1', $kyc_data['address1']);
    displayData('Address 2', $kyc_data['address2']);
    displayData('Apartment No', $kyc_data['apartment_no']);
    displayData('City', $kyc_data['city']);
    displayData('State', $kyc_data['state']);
    displayData('Phone Number', $kyc_data['phone_number']);
    displayData('Date of Birth', $kyc_data['date_of_birth']);
    displayData('Zip Code', $kyc_data['zip_code']);
    displayData('US Citizen', $kyc_data['us_citizen']);
    displayData('Dual Citizenship', $kyc_data['dual_citizenship']);
    displayData('Country of Residence', $kyc_data['country_of_residence']);
    displayData('Source of Income', $kyc_data['source_of_income']);
    displayData('Nationality', $kyc_data['nationality']);
    displayData('Email', $kyc_data['email']);
    displayData('Status', $kyc_data['status']);
    displayData('Time Uploaded', $kyc_data['time_uploaded']);
    echo '</div>';
} else {
    echo 'No KYC data found for the given ID.';
}
?>


    </div>
</section>
</body>
</html>