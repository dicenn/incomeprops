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

<!-- property summary section -->
<div style="flex: 1 1 auto;">
    <section class="property-details">
        <p>Price: <?= $property['Price'] ?></p>
        <p>Bedrooms: <?= $property['Bedrooms'] ?></p>
        <p>Bathrooms: <?= $property['Bathrooms'] ?></p>
        <p>Building type: <?= $property['Building type'] ?></p>
        <p>Property taxes: <?= $property['Property taxes'] ?></p>
        <p>Lot size: <?= $property['Lot size'] ?></p>
    </section>
</div>

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
    $inputs = [
        "purchasePrice" => ["label" => "Purchase Price ($)", "value" => $property['Price']],
        "downpaymentPercentage" => ["label" => "Downpayment (%)", "value" => "0.20"],
        "monthlyRent" => ["label" => "Monthly Rent ($)", "value" => $totalRent, "readonly" => true],
        "propertyTax" => ["label" => "Property Tax ($)", "value" => intval(preg_replace('/[,.]/', '', substr($property['Property taxes'], 1, 6))) , "readonly" => true],
        "appreciationPercentage" => ["label" => "Appreciation (%)", "value" => "0.06"],
        "holdingPeriod" => ["label" => "Holding Period (Years)", "value" => "5"],
        "mortgageRate" => ["label" => "Mortgage Rate (%)", "value" => "0.06"],
        "renovationCosts" => ["label" => "Renovation Costs ($)", "value" => "0"],
        "mortgageTerm" => ["label" => "Mortgage Term (Years)", "value" => "30"],
        "landTransferTaxPercentage" => ["label" => "Land Transfer Tax (%)", "value" => "0.04"],
        "monthlyCapexReserve" => ["label" => "Monthly CapEx Reserve ($)", "value" => "400"],
        "annualInsurance" => ["label" => "Annual Insurance ($)", "value" => "1500"],
        "sellingCosts" => ["label" => "Selling Costs (%)", "value" => "0.04"],
        "vacancyAllowance" => ["label" => "Vacancy Allowance (%)", "value" => "0.05"]
    ];

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
<div class="user-input-section">
    <h2>User Inputs</h2>
    <form id="financialAnalysisForm">
        <table>
            <tbody>
                <?php foreach ($inputs as $id => $details): ?>
                    <tr>
                        <td><label for="<?= $id ?>"><?= $details['label'] ?>:</label></td>
                        <td>
                            <input 
                                id="<?= $id ?>" 
                                value="<?= $details['value'] ?>"
                                <?= isset($details['readonly']) && $details['readonly'] ? "readonly" : "" ?>
                            >
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>

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