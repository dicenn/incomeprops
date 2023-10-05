<!DOCTYPE html>
<html lang="en">
<head>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet"> -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Properties</title>
    <!-- CSS styles -->
    <link rel="stylesheet" type="text/css" href="ip_styles.css">
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

<!-- user filter header -->
<div class="filters-band">
    <form method="post" class="filters-form">
        <div class="filter-item">
            <input type="text" name="num_units" class="filter-input" value="<?= isset($_POST['num_units']) ? htmlspecialchars($_POST['num_units']) : '' ?>">
            <label>Min # of Units</label>
        </div>
        <div class="filter-item">
            <input type="text" name="min_price" class="filter-input" value="<?= isset($_POST['min_price']) ? htmlspecialchars($_POST['min_price']) : '' ?>">
            <label>Min Price</label>
        </div>
        <div class="filter-item">
            <input type="text" name="max_price" class="filter-input" value="<?= isset($_POST['max_price']) ? htmlspecialchars($_POST['max_price']) : '' ?>">
            <label>Max Price</label>
        </div>
        <div class="filter-item">
            <select name="city" class="filter-input">
                <option value="all" <?= (isset($_POST['city']) && $_POST['city'] == 'all') ? 'selected' : '' ?>>All</option>
                <?php foreach($citiesList as $cityItem): ?>
                    <option value="<?= $cityItem ?>" <?= (isset($_POST['city']) && $_POST['city'] == $cityItem) ? 'selected' : '' ?>><?= $cityItem ?></option>
                <?php endforeach; ?>
            </select>
            <label>City</label>
        </div>
        <div class="filter-item filter-submit">
            <input type="submit" class="filter-input" value="Filter Properties">
        </div>
        <button type="button" class="filter-clear" onclick="window.location.href='http://localhost:8888/incomeprops/incomeprops_landing.php'">Clear Filters</button>
    </form>
</div>

<!-- show the number of properties returned from the database -->
<?php
    echo '<div class="results-count">Total Properties Found: ' . count($properties) . '</div>';
?>

<!-- sort the page by highest to lowest price, etc... ; to be developd later-->
<!-- <label for="sort">Sort By:</label>
<select name="sort" id="sort">
    <option value="price_high_low">Price: High to Low</option>
    <option value="price_low_high">Price: Low to High</option>
    <option value="return_high_low">Annual Return: High to Low</option>
    <option value="return_low_high">Annual Return: Low to High</option>
</select> -->

<!-- set up an array that is passed to the calculatePropertyMetrics function which calculates the invesmment summary stats -->
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

<!-- link to packages and files that the landing page deoends on -->
<script src="https://cdn.jsdelivr.net/npm/financejs@4.1.0/finance.js"></script>
<script src="cash_flow_calc.js"></script>
<script src="listener.js"></script>

</body>
</html>
