<?php include('../app.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Control Panel - Transactions</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 720px)">
</head>
<body>
<section class="table list-of-kyc">
    <div class="container">
        <h2>List of KYC</h2>
        <?php
            // Connect to the database
            $db = connectToDatabase();

            // Query to select data from the kyc_data table
            $query = "SELECT * FROM kyc_data ORDER BY id DESC"; // Adjust ORDER BY as needed
            $result = $db->query($query);
            ?>

            <table>
                <thead>
                    <tr>
                        <td>ID</td>
                        <td>First Name</td>
                        <td>Last Name</td>
                        <td>Email</td>
                        <td>Status</td>
                        <td>Date of Birth</td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars(date('d, F Y', strtotime($row['date_of_birth']))); ?></td>
                                <td><a href="/control-panel/kyc/?id=<?php echo htmlspecialchars($row['id']); ?>">View Full Details</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // Close the database connection
            $db->close();
        ?>

    </div>
</section>
</body>
</html>