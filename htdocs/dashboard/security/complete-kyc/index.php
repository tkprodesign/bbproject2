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
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<section class="add-account">
    <div class="container">
        <form action="" method="post" enctype="multipart/form-data">
            <?php if ($user_kyc_level == 2) {
                $kyc_required = '';
            } else {
                $kyc_required = 'required';
            }
             ?>
            <h2>Your Information</h2>
            <div class="input-box">
                <label>Identification Photograph: <span>*</span></label>
                <input type="file" name="profile_picture" accept="image/*">
            </div>
            <div class="input-box">
                <label>First name:<span>*</span></label>
                <input type="text" name="first_name" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>Middle name:</label>
                <input type="text" name="middle_name">
            </div>
            <div class="input-box">
                <label>Last name:<span>*</span></label>
                <input type="text" name="last_name" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>Suffix:<span>*</span></label>
                <select name="suffix">
                    <option value="Mr">Mr</option>
                    <option value="Mrs">Mrs</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="input-box">
                <label>Gender:<span>*</span></label>
                <select name="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Others">Others</option>
                    <option value="Not specified">Not specified</option>
                </select>
            </div>
            <div class="input-box">
                <label>Address line 1:<span>*</span></label>
                <input type="text" name="address1" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>Addres line 2:</label>
                <input type="text" name="address2">
            </div>
            <div class="input-box">
                <label>Apartment #, Unit #, etc</label>
                <input type="text" name="apartment_no">
            </div>
            <div class="input-box">
                <label>City:<span>*</span></label>
                <input type="text" name="city" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>State/Province:<span>*</span></label>
                <input type="text" name="state" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>Phone:<span>*</span></label>
                <input type="text" name="phone_number" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>Date of birth:<span>*</span></label>
                <input type="date" name="date_of_birth" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>Zip code:<span>*</span></label>
                <input type="text" name="zip_code" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>Us citizen:<span>*</span></label>
                <select name="us_citizen">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            <div class="input-box">
                <label>Dual citizenship:<span>*</span></label>
                <select name="dual_citizenship">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            <div class="input-box">
                <label>Country of residence:<span>*</span></label>
                <input type="text" name="country_of_residence" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <label>Source of income:<span>*</span></label>
                <select name="source_of_income">
                    <option value="Investments">Investments</option>
                    <option value="Salary">Salary</option>
                    <option value="Business">Business</option>
                    <option value="Freelancing">Freelancing</option>
                    <option value="Rental Income">Rental Income</option>
                    <option value="Dividends">Dividends</option>
                    <option value="Interest">Interest</option>
                    <option value="Gifts">Gifts</option>
                    <option value="Family">Family</option>
                    <option value="Government">Government</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="input-box">
                <label>Nationality:<span>*</span></label>
                <input type="text" name="nationality" <?php echo $kyc_required; ?>>
            </div>
            <div class="input-box">
                <button type="submit" name="submit_kyc_data" value="kyc-verification">Submit KYC Verification</button>
                <a href="/dashboard">Cancel</a>
            </div>
        </form>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>