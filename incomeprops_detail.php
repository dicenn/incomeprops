<?php
    include 'config.php';

    // Turn on error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Determine which address to fetch
    if (isset($_GET['address'])) {
        $address = mysqli_real_escape_string($conn, $_GET['address']);
        $query = "SELECT * FROM income_props.toronto_duplexes_lean_v8 WHERE Address = '$address'";
    } else {
        // Default to first entry if address is not provided
        $query = "SELECT * FROM income_props.toronto_duplexes_lean_v8 LIMIT 1";
    }

    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $property = mysqli_fetch_assoc($result);

    if (!$property) {
        die("Property not found.");
    }

    mysqli_close($conn);

    $bedroomTypes = [
        "3" => "3 bdrms",
        "2" => "2 bdrms",
        "1" => "1 bdrms",
        "0" => "Bachelors"
    ];

?>

<h1><?= $property['Address'] ?> - <?= $property['Community name'] ?></h1>

<!-- Using Flexbox to display the photo and details side by side -->
<div style="display: flex; gap: 2rem; align-items: flex-start;">
    <div class="photos-section" style="flex: 0 0 auto; max-width: 300px;"> <!-- Make photo smaller and restrict width -->
        <img id="currentPhoto" src="<?= $property['Pic1'] ?>" style="width: 100%; height: auto;">
        <div style="text-align: center; margin-top: 1rem;">
            <button onclick="prevPhoto()">Prev</button>
            <button onclick="nextPhoto()">Next</button>
        </div>
    </div>

    <div style="flex: 1 1 auto;">
        <section class="property-details">
            <p>Price: <?= $property['Price'] ?></p>
            <p>Bedrooms: <?= $property['Bedrooms'] ?></p>
            <p>Bathrooms: <?= $property['Bathrooms'] ?></p>
            <p>Building type: <?= $property['Building type'] ?></p>
            <p>Property taxes: <?= $property['Property taxes'] ?></p>
            <p>Lot size: <?= $property['Lot size'] ?></p>
        </section>

        <table>
            <tr>
                <th>Unit Type</th>
                <th># of Units</th>
                <th>Rent per Unit</th>
                <th>Total Rent</th>
            </tr>

            <?php 
            $totalUnits = 0;
            $totalRent = 0;

            foreach ($bedroomTypes as $key => $label): 
                $unitsValue = $property[$key . '_bedroom_units'];
                $rentValue = $property[$key . '_bedroom_rent'];

                if ($unitsValue != 0) {
                    $totalUnits += $unitsValue;
                    $totalRent += $unitsValue * $rentValue;
                    $formattedRentValue = $rentValue;
                    $formattedTotalRent = $unitsValue * $rentValue;
                } else {
                    $formattedRentValue = "";
                    $formattedTotalRent = "";
                }
                ?>

                <tr class="rent-row">
                    <td><?= $label ?></td>
                    <td><input type="number" class="units" value="<?= $unitsValue ?>" onchange="updateTotal()"></td>
                    <td><input type="number" class="rent-per-unit" value="<?= $formattedRentValue ?>" onchange="updateTotal()"></td>
                    <td class="total-rent"><?= $formattedTotalRent ?></td>
                </tr>

            <?php endforeach; ?>

            <!-- Totals Row -->
            <tr>
                <td><strong>Total</strong></td>
                <td><strong id="totalUnits"><?= $totalUnits ?></strong></td>
                <td></td>
                <td><strong id="totalRent"><?= number_format($totalRent, 2) ?></strong></td>
            </tr>
        </table>

<!-- Revert to Defaults Button -->
<button onclick="revertToDefaults()">Revert to Defaults</button>

<script>
    function revertToDefaults() {
        // Here, you'd ideally make an AJAX request to your server to fetch the default property values
        // Once retrieved, you can then repopulate your table rows with the original values
        // For now, as a placeholder:
        alert("This will revert to original values from the database.");
    }
</script>

    </div>
</div>

<script>
    let currentPhotoIndex = 0;  // Start with 0 since JavaScript arrays are 0-indexed
    let photos = [
        "<?= $property['Pic1'] ?>",
        "<?= $property['Pic2'] ?>",
        "<?= $property['Pic3'] ?>",
        "<?= $property['Pic4'] ?>",
        "<?= $property['Pic5'] ?>",
    ];
</script>

<section class="description">
    <p><?= $property['Description'] ?></p>
</section>

<!-- <script>
    function updateTotal() {
        // Logic to compute total based on input values
    }
</script> -->


<style>
    .input-group {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .input-group label,
    .input-group input {
        flex: 1;
    }
</style>

<div class="user-input-section">
    <h2>User Inputs</h2>
    <form id="financialAnalysisForm">
        <label for="purchasePrice">Purchase Price ($):</label>
        <input type="number" id="purchasePrice" value="<?= $property['Price'] ?>"><br>

        <label for="downpaymentPercentage">Downpayment (%):</label>
        <input type="number" id="downpaymentPercentage" value="20"><br>

        <!-- This value will be populated by JavaScript from the total rent calculation -->
        <label for="monthlyRent">Monthly Rent ($):</label>
        <input type="number" id="monthlyRent" readonly><br>

        <label for="appreciationPercentage">Appreciation (%):</label>
        <input type="number" id="appreciationPercentage" value="6"><br>

        <label for="holdingPeriod">Holding Period (Years):</label>
        <input type="number" id="holdingPeriod" value="5"><br>

        <label for="mortgageRate">Mortgage Rate (%):</label>
        <input type="number" id="mortgageRate" value="6"><br>

        <label for="renovationCosts">Renovation Costs ($):</label>
        <input type="number" id="renovationCosts" value="0"><br>

        <label for="mortgageTerm">Mortgage Term (Years):</label>
        <input type="number" id="mortgageTerm" value="30"><br>

        <label for="landTransferTaxPercentage">Land Transfer Tax (%):</label>
        <input type="number" id="landTransferTaxPercentage" value="4"><br>

        <label for="monthlyCapexReserve">Monthly CapEx Reserve ($):</label>
        <input type="number" id="monthlyCapexReserve" value="400"><br>

        <label for="annualInsurance">Annual Insurance ($):</label>
        <input type="number" id="annualInsurance" value="1500"><br>

        <label for="sellingCosts">Selling Costs (%):</label>
        <input type="number" id="sellingCosts" value="4"><br>

        <button type="button" onclick="performCalculations()">Calculate</button>
    </form>
</div>


<script src="rent_calc.js"></script>
<script src="photo_nav.js"></script>
<script src="cash_flow_calc.js">
    window.onload = function() {
        calculateCashFlowAnalysis();
    }
    const inputFields = [
    'purchasePrice', 
    'downpaymentPercentage', 
    'monthlyRent', 
    'appreciationPercentage', 
    'holdingPeriod', 
    'mortgageRate', 
    'renovationCosts', 
    'mortgageTerm', 
    'landTransferTaxPercentage', 
    'monthlyCapexReserve', 
    'annualInsurance', 
    'sellingCosts'
    ];

    inputFields.forEach(fieldId => {
        document.getElementById(fieldId).addEventListener('change', calculateCashFlowAnalysis);
    });
</script>

<section class="cash-flow-analysis">
    <h2>Cash Flow Analysis</h2>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Year 1</th>
                <th>Year 2 - [holding period - 1]</th>
                <th>Year [holding period]</th>
            </tr>
        </thead>
        <tbody id="cashFlowAnalysisBody">
            <!-- Rows will be filled dynamically -->
        </tbody>
    </table>
</section>
