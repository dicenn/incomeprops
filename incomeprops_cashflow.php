<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property cash flow analysis</title>
    <!-- CSS styles -->
    <link rel="stylesheet" type="text/css" href="ip_styles.css">
</head>
<body data-page="cashflow">

<!-- connection to mysql and initial queries used to populate values from the database -->
<?php
    include 'queries.php';

    $address = isset($_GET['address']) ? $_GET['address'] : null;
    $property = getPropertyDetails($address);

    if (!$property) {
        die("Property not found.");
    }

    $addresses = getAddresses();
    closeConnection();
?>

<!-- define the variables needed for the photo navigation -->
<script>
    let currentPhotoIndex = 0;  
        let photos = [
            "<?php echo $property['Pic1']; ?>",
            "<?php echo $property['Pic2']; ?>",
            "<?php echo $property['Pic3']; ?>",
            "<?php echo $property['Pic4']; ?>",
            "<?php echo $property['Pic5']; ?>",
        ];
</script>

<!-- photos section -->
<!-- photos section -->
<div class="photos-section">
    <img id="currentPhoto" src="<?= $property['Pic1'] ?>">
    
    <!-- Property details overlay -->
    <div class="property-info">
        <span><?= $property['num_units'] ?> Units</span> |
        <span><?= $property['Bedrooms'] ?> Bedrooms</span> |
        <span><?= $property['Bathrooms'] ?> Bathrooms</span>
    </div>

    <button class="photo-nav prev" onclick="prevPhoto()">&#8592;</button>
    <button class="photo-nav next" onclick="nextPhoto()">&#8594;</button>
</div>

<div class="property-price-cashflow">
    $<?= number_format($property['Price']) ?>
</div>
<div class="property-address-cashflow">
    <?= $property['Address'] ?>
</div>
<button class="agent-button">Speak to an Agent</button>

<!-- units + rent table -->
<table>
    <tr>
        <th>Unit Type</th>
        <th># of Units</th>
        <th>Rent per Unit</th>
        <th>Total Rent</th>
    </tr>

    <?php $totalUnits = 0; $totalRent = 0; ?>

    <tr class="rent-row">
        <td>3 bdrms</td>
        <td><input type="number" class="units" value="<?= $property['3_bedroom_units'] ?: '' ?>" onchange="updateCashFlowAnalysisTable()"></td>
        <td><input type="number" class="rent-per-unit" value="<?= $property['3_bedroom_units'] ? $property['3_bedroom_rent'] : '' ?>" onchange="updateCashFlowAnalysisTable()"></td>
        <td class="total-rent"><?= ($totalRent += $property['3_bedroom_units'] * $property['3_bedroom_rent']) ?: '' ?></td>
    </tr>

    <tr class="rent-row">
        <td>2 bdrms</td>
        <td><input type="number" class="units" value="<?= $property['2_bedroom_units'] ?: '' ?>" onchange="updateCashFlowAnalysisTable()"></td>
        <td><input type="number" class="rent-per-unit" value="<?= $property['2_bedroom_units'] ? $property['2_bedroom_rent'] : '' ?>" onchange="updateCashFlowAnalysisTable()"></td>
        <td class="total-rent"><?= ($totalRent += $property['2_bedroom_units'] * $property['2_bedroom_rent']) ?: '' ?></td>
    </tr>

    <tr class="rent-row">
        <td>1 bdrms</td>
        <td><input type="number" class="units" value="<?= $property['1_bedroom_units'] ?: '' ?>" onchange="updateCashFlowAnalysisTable()"></td>
        <td><input type="number" class="rent-per-unit" value="<?= $property['1_bedroom_units'] ? $property['1_bedroom_rent'] : '' ?>" onchange="updateCashFlowAnalysisTable()"></td>
        <td class="total-rent"><?= ($totalRent += $property['1_bedroom_units'] * $property['1_bedroom_rent']) ?: '' ?></td>
    </tr>

    <tr class="rent-row">
        <td>Bachelors</td>
        <td><input type="number" class="units" value="<?= $property['0_bedroom_units'] ?: '' ?>" onchange="updateCashFlowAnalysisTable()"></td>
        <td><input type="number" class="rent-per-unit" value="<?= $property['0_bedroom_units'] ? $property['0_bedroom_rent'] : '' ?>" onchange="updateCashFlowAnalysisTable()"></td>
        <td class="total-rent"><?= ($totalRent += $property['0_bedroom_units'] * $property['0_bedroom_rent']) ?: '' ?></td>
    </tr>

    <!-- Totals Row -->
    <tr>
        <td><strong>Total</strong></td>
        <td><strong id="totalUnits"><?= $totalUnits ?></strong></td>
        <td></td>
        <td><strong id="totalRent"><?= number_format($totalRent, 2) ?></strong></td>
    </tr>
</table>

<!-- set up fields / rows for the user inputs and cash flow analysis tables -->
<?php
    $analysisItems = [
        "initialInvestment" => "Initial Investment",
        "mortgageExpenses" => "Mortgage Expenses",
        "expenses" => "Expenses",
        "income" => "Income",
        "netIncome" => "Net Income",
        "proceedsFromSale" => "Proceeds from Sale",
        "netCashFlow" => "Net Cash Flow"
    ];
?>

<!-- investment summary table -->
<table id="investment-summary-cashflow">
    <thead>
        <tr>
            <th colspan="2">Investment Summary</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Initial Investment</td>
            <td id="initialInvestmentValue">-</td>
        </tr>
        <tr>
            <td>Annual Return</td>
            <td id="irrValue">-</td>
        </tr>
        <tr>
            <td>Cap Rate</td>
            <td id="capRateValue">-</td>
        </tr>
        <tr>
            <td>Monthly Cash Flow</td>
            <td id="monthlyCashFlowValue">-</td>
        </tr>
        <tr>
            <td>Annual NOI</td>
            <td id="noiValue">-</td>
        </tr>
    </tbody>
</table>

<!-- the user inputs table -->
<form id="financialAnalysisFormKeyInputs">
    <table class="investment-summary-cashflow">
        <thead>
            <tr>
                <th colspan="2">Key inputs</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><label for="purchasePrice">Purchase Price ($):</label></td>
                <td><input class="input-light-blue" id="purchasePrice" value="<?= $property['Price'] ?>"></td>
            </tr>
            <tr>
                <td><label for="downpaymentPercentage">Downpayment (%):</label></td>
                <td><input class="input-light-blue" id="downpaymentPercentage" value="0.20"></td>
            </tr>
            <tr>
                <td><label for="appreciationPercentage">Appreciation (%):</label></td>
                <td><input class="input-light-blue" id="appreciationPercentage" value="0.06"></td>
            </tr>
            <tr>
                <td><label for="holdingPeriod">Holding Period (Years):</label></td>
                <td><input class="input-light-blue" id="holdingPeriod" value="5"></td>
            </tr>
        </tbody>
    </table>
</form>

<form id="financialAnalysisFormOtherInputs">
    <table class="investment-summary-cashflow">
        <thead>
            <tr>
                <th colspan="2">Key inputs</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><label for="monthlyRent">Monthly Rent ($):</label></td>
                <td><input class="input-light-blue" id="monthlyRent" value="<?= $totalRent ?>" readonly></td>
            </tr>
            <tr>
                <td><label for="propertyTax">Property Tax ($):</label></td>
                <td><input class="input-light-blue" id="propertyTax" value="<?= intval(preg_replace('/[,.]/', '', substr($property['Property taxes'], 1, 6))) ?>" readonly></td>
            </tr>
            <tr>
                <td><label for="mortgageRate">Mortgage Rate (%):</label></td>
                <td><input class="input-light-blue" id="mortgageRate" value="0.06"></td>
            </tr>
            <tr>
                <td><label for="renovationCosts">Renovation Costs ($):</label></td>
                <td><input class="input-light-blue" id="renovationCosts" value="0"></td>
            </tr>
            <tr>
                <td><label for="mortgageTerm">Mortgage Term (Years):</label></td>
                <td><input class="input-light-blue" id="mortgageTerm" value="30"></td>
            </tr>
            <tr>
                <td><label for="landTransferTaxPercentage">Land Transfer Tax (%):</label></td>
                <td><input class="input-light-blue" id="landTransferTaxPercentage" value="0.04"></td>
            </tr>
            <tr>
                <td><label for="monthlyCapexReserve">Monthly CapEx Reserve ($):</label></td>
                <td><input class="input-light-blue" id="monthlyCapexReserve" value="400"></td>
            </tr>
            <tr>
                <td><label for="annualInsurance">Annual Insurance ($):</label></td>
                <td><input class="input-light-blue" id="annualInsurance" value="1500"></td>
            </tr>
            <tr>
                <td><label for="sellingCosts">Selling Costs (%):</label></td>
                <td><input class="input-light-blue" id="sellingCosts" value="0.04"></td>
            </tr>
            <tr>
                <td><label for="vacancyAllowance">Vacancy Allowance (%):</label></td>
                <td><input class="input-light-blue" id="vacancyAllowance" value="0.05"></td>
            </tr>
        </tbody>
    </table>
</form>

<!-- the cah flow analysis table -->
<div class="cashflow-analysis-section">
    <h2>Cash Flow Analysis</h2>
    <table>
        <thead id="cashflow-thead">
            <tr>
                <th>Item</th>
                <th>Year 0</th>

                <?php if ($inputs["holdingPeriod"]["value"] < 3): ?>
                    <th>Year 1</th>
                <?php endif; ?>

                <?php if ($inputs["holdingPeriod"]["value"] == 3): ?>
                    <th>Year 2</th>
                <?php endif; ?>

                <?php if ($inputs["holdingPeriod"]["value"] >= 3): ?>
                    <th>Year 1 - <?= ($inputs["holdingPeriod"]["value"] - 1) ?> (annually)</th>
                    <th>Year <?= $inputs["holdingPeriod"]["value"] ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody id="cashflow-tbody">
            <?php foreach ($analysisItems as $key => $label): ?>
                <tr>
                    <td><?= $label ?></td>
                    <td id="<?= $key ?>Year0"></td>
                    <td id="<?= $key ?>Year1"></td>

                    <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                        <td id="<?= $key ?>YearN"></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<!-- calling the js scripts and functions needed to update the user inputs, cash flow ananlysis and investment summary tables -->
<script src="https://cdn.jsdelivr.net/npm/financejs@4.1.0/finance.js"></script>
<script src="listener.js"></script>
<script src="cash_flow_calc.js"></script>
<script src="cashflow_coladj.js"></script>
<script src="photo_nav.js"></script>

</body>
</html>