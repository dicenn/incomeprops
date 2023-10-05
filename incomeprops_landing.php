<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Properties</title>
    <!-- CSS styles -->
    <style>
        .site-name-link {
            text-decoration: none; /* Removes underline */
            color: inherit; /* Uses the parent color, making it consistent with the rest of the header */
            cursor: pointer; /* Optional: Just to indicate it's clickable */
        }

        .property {
            width: 220px;
            margin: 20px;  /* Increased margin */
            box-shadow: 0px 0px 15px 2px rgba(0, 0, 0, 0.3); /* More distinct shadow */
            background-color: #f4faff;
            display: inline-block;
            vertical-align: top;
            overflow: hidden;
            position: relative;
            border-radius: 5px; /* Slightly rounded corners */
        }

        .property-address {
            position: absolute;
            bottom: 0;  /* This will now hug the bottom of the container */
            left: 0;  /* This will now hug the left of the container */
            right: 0;  /* This will ensure it stretches across the full width, hugging the right side */
            padding: 3px 3px;  /* Padding for the text, 5px top-bottom and 10px left-right */
            background: rgba(10, 50, 86, 0.45);  /* dark bluish-grey with 70% opacity */
            /* background: linear-gradient(to top, rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.6) 55%, rgba(0, 0, 0, 0.3) 75%, transparent 90%);  Gradient for readability */
            color: white;
            font-size: 0.75em;  /* Smaller font size */
            z-index: 2;
        }

        .property-image {
            width: 100%;
            height: 200px;
            background-size: cover;  /* This will ensure that the image covers the entire div */
            background-position: top left; /* This will start the image from the top left */
            background-repeat: no-repeat; /* Ensures the image doesn't repeat if it's too small */
            position: relative;  /* Makes this a positioning context for its children */
            z-index: 1;
        }

        .property-details {
            padding: 5px;
        }

        .property-details p {
            margin: 2px 0;
        }

        .property-price {
            font-size: 1.4em;  /* Slightly bigger */
            font-weight: bold; /* Bolder */
            color: #13426c;
            /* margin-bottom: 10px; */
        }

        .property-stats {
            font-size: 0.9em;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .values-row, .labels-row {
            display: flex;
            justify-content: space-between;
            width: 100%; 
            background-color: #ffffff; /* white background */
            border-top: 1px solid #ccc; /* darker gray top border */
            padding: 5px 0; /* adding some padding for visual appeal */
        }

        .metric-value, .metric-label {
            flex: 1; /* ensures equal spacing */
            text-align: center; /* centers the content */
        }

        .metric-label {
            font-size: 10px;
        }
        /* Reset default padding and margin */
        body, html, div {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
        }

        .header-band {
            background-color: #18466f;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100vw; /* ensure it takes the full viewport width */
        }

        .header-band .site-name {
            color: white;
            font-weight: 700;
        }

        .header-band .nav-links a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
        }

        .header-band .nav-links a:hover {
            text-decoration: underline;
        }

        .icon-row {
            display: flex; 
            align-items: center;
            margin-bottom: 10px;
            margin-top: 10px;
        }

        .icon-detail {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 15px;
        }

        .icon-detail img {
            width: 20px;
            height: auto;
        }

        .icon-label {
            font-size: 10px;
            text-align: center;
            margin-top: 2px;
        }

        .icon-value {
            font-size: 1.1em;
            margin-left: 5px; 
        }

    </style>
</head>
<body data-page="landing">

<!-- site header -->
<div class="header-band">
    <div class="site-name">
        <a href="http://localhost:8888/incomeprops/incomeprops_landing.php" class="site-name-link">incomeprops.ca</a>
    </div>
    <div class="nav-links">
        <a href="#">Properties</a>
        <a href="#">Blog</a>
        <a href="#">About us</a>
        <a href="#">Sign up</a>
        <a href="#">Login</a>
    </div>
</div>

<!-- getting needed data from the database and populating some invesmtnet summary data -->
<?php
    include 'queries.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $num_units = isset($_POST['num_units']) ? $_POST['num_units'] : "all";
        $min_price = isset($_POST['min_price']) ? $_POST['min_price'] : null;
        $max_price = isset($_POST['max_price']) ? $_POST['max_price'] : null;
        $city = isset($_POST['city']) ? $_POST['city'] : null;
    
        $properties = getProperties($num_units, $min_price, $max_price, $city);
    } else {
        $properties = getProperties();
    }    

    $citiesList = getDistinctCities();
    closeConnection();
?>

<form method="post">
    Min # of Units: <input type="text" name="num_units" value="<?= isset($_POST['num_units']) ? htmlspecialchars($_POST['num_units']) : '' ?>"><br>
    Min Price: <input type="text" name="min_price" value="<?= isset($_POST['min_price']) ? htmlspecialchars($_POST['min_price']) : '' ?>"><br>
    Max Price: <input type="text" name="max_price" value="<?= isset($_POST['max_price']) ? htmlspecialchars($_POST['max_price']) : '' ?>"><br>
    City: <select name="city">
        <option value="all">All</option>
        <?php foreach($citiesList as $cityItem): ?>
            <option value="<?= $cityItem ?>" <?= (isset($_POST['city']) && $_POST['city'] == $cityItem) ? 'selected' : '' ?>><?= $cityItem ?></option>
        <?php endforeach; ?>
    </select><br>
    <input type="submit" value="Filter Properties">
</form>


<script>
    let propertiesArray = <?php echo json_encode($properties); ?>;
</script>

<!-- listing out the properties -->
<div class="properties-list">
    <?php 
        foreach ($properties as $property):
            $encodedAddress = urlencode($property['Address']);
            // echo "<script>console.log('PHP encoded: $encodedAddress');</script>";
    ?>
        <div class="property" data-address="<?= urlencode($property['Address']) ?>">
            <!-- Make the entire image container a link -->
            <a href="incomeprops_cashflow.php?address=<?= urlencode($property['Address']) ?>" class="property-link">
                <div class="property-image" style="background-image: url('<?= $property['Pic1'] ?>');">
                    <div class="property-address">
                        <?= ucwords(strtolower($property['Address'])) ?>
                    </div>
                </div>
            </a>
            <div class="property-details">
                <p class="property-price">$<?= number_format($property['Price']) ?></p>
                <div class="icon-row">
                    <!-- Bedrooms -->
                    <div class="icon-detail">
                        <img src="icons/bed.png" alt="Bedroom Icon">
                        <span class="icon-label">Bedrooms</span>
                    </div>
                    <span class="icon-value"><?= str_replace(' ','',$property['Bedrooms']) ?></span>
                    
                    <!-- Spacer -->
                    <div style="width: 20px;"></div>
                    
                    <!-- Bathrooms -->
                    <div class="icon-detail">
                        <img src="icons/bath.png" alt="Bathroom Icon">
                        <span class="icon-label">Bathrooms</span>
                    </div>
                    <span class="icon-value"><?= str_replace(' ','',$property['Bathrooms']) ?></span>
                </div>
                <div class="property-stats">
                    <div class="values-row">
                        <span class="metric-value annualized-return"><?= $property['AnnualizedReturn'] ?>%</span>
                        <span class="metric-value cap-rate"><?= $property['CapRate'] ?>%</span>
                        <span class="metric-value monthly-cash-flow">$<?= number_format($property['MonthlyCashFlow']) ?></span>
                    </div>
                    <div class="labels-row">
                        <span class="metric-label">Annualized Return</span>
                        <span class="metric-label">Cap Rate</span>
                        <span class="metric-label">Monthly Cash Flow</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/financejs@4.1.0/finance.js"></script>
<script src="cash_flow_calc.js"></script>
<script src="listener.js"></script>

</body>
</html>
