<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property cash flow analysis</title>
    <!-- CSS styles -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> <!-- bootstrap required for page layout -->
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

<div class="header-band">
    <div class="site-name">
        <a href="http://localhost/incomeprops/incomeprops_landing.php" class="site-name-link">incomeprops.ca</a>
    </div>
    <div class="nav-links">
        <a href="#">Properties</a>
        <a href="#">Blog</a>
        <a href="#">About us</a>
        <a href="#">Sign up</a>
        <a href="#">Login</a>
    </div>
</div>

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

<div class="container">
    <div class="row">

        <!-- photos section -->
        <div class="col-lg-6">
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
        </div>

        <div class="col-lg-6">
            <div class="d-flex flex-column flex-lg-row">
                <div>
                    <div class="property-price-cashflow">
                        $<?= number_format($property['Price']) ?>
                    </div>
                    <div class="property-address-cashflow">
                        <?= $property['Address'] ?>
                    </div>
                </div>
                <div class="ml-lg-3 mt-3 mt-lg-0"> <!-- Added margin-top for mobile views -->
                    <button class="button-common agent-button">Speak to an Agent</button>
                </div>
            </div>

            <!-- set up fields / rows for the user inputs and cash flow analysis tables -->
            <?php
                $analysisItems = [
                    "initialInvestment" => "Initial Investment",
                    "mortgageExpenses" => "Mortgage Expenses",
                    "expenses" => "Other expenses",
                    "income" => "Rental income",
                    "proceedsFromSale" => "Proceeds from Sale",
                    "netCashFlow" => "Net Cash Flow"
                ];
            ?>

            <div class="row mt-4">
                <div class="col-md-6">
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
                </div>

            <!-- set the initial values of the user inputs tables -->
            <?php
                $inputs = [
                    "purchasePrice" => ["label" => "Purchase Price ($)", "value" => $property['Price']],
                    "downpaymentPercentage" => ["label" => "Downpayment (%)", "value" => "20"],
                    "monthlyRent" => ["label" => "Monthly Rent ($)", "value" => $totalRent, "readonly" => true],
                    "propertyTax" => ["label" => "Property Tax ($)", "value" => $property['Property_taxes'], "readonly" => true],
                    "appreciationPercentage" => ["label" => "Appreciation (%)", "value" => "6"],
                    "holdingPeriod" => ["label" => "Holding Period (Years)", "value" => "5"],
                    "mortgageRate" => ["label" => "Mortgage Rate (%)", "value" => "6"],
                    "renovationCosts" => ["label" => "Renovation Costs ($)", "value" => "0"],
                    "mortgageTerm" => ["label" => "Mortgage Term (Years)", "value" => "30"],
                    "landTransferTaxPercentage" => ["label" => "Land Transfer Tax (%)", "value" => "4"],
                    "monthlyCapexReserve" => ["label" => "Monthly CapEx Reserve ($)", "value" => "400"],
                    "annualInsurance" => ["label" => "Annual Insurance ($)", "value" => "1500"],
                    "sellingCosts" => ["label" => "Selling Costs (%)", "value" => "4"],
                    "vacancyAllowance" => ["label" => "Vacancy Allowance (%)", "value" => "5"]
                ];
            ?>

                <div class="col-md-6">
                    <!-- the key inputs table -->
                    <form id="financialAnalysisFormKeyInputs">
                        <table class="investment-summary-cashflow">
                            <thead>
                                <tr>
                                    <th colspan="2">Key inputs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><label for="purchasePrice"><?= $inputs['purchasePrice']['label'] ?>:</label></td>
                                    <td><input class="input-light-blue" id="purchasePrice" value="<?= $inputs['purchasePrice']['value'] ?>"></td>
                                </tr>
                                <tr>
                                    <td><label for="downpaymentPercentage"><?= $inputs['downpaymentPercentage']['label'] ?>:</label></td>
                                    <td><input class="input-light-blue" id="downpaymentPercentage" value="<?= $inputs['downpaymentPercentage']['value'] ?>"></td>
                                </tr>
                                <tr>
                                    <td><label for="appreciationPercentage"><?= $inputs['appreciationPercentage']['label'] ?>:</label></td>
                                    <td><input class="input-light-blue" id="appreciationPercentage" value="<?= $inputs['appreciationPercentage']['value'] ?>"></td>
                                </tr>
                                <tr>
                                    <td><label for="holdingPeriod"><?= $inputs['holdingPeriod']['label'] ?>:</label></td>
                                    <td><input class="input-light-blue" id="holdingPeriod" value="<?= $inputs['holdingPeriod']['value'] ?>"></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex flex-column flex-lg-row">
                        <div>
                            <table class="rent-table">
                                <thead>
                                    <tr>
                                        <th>Unit Type</th>
                                        <th># of Units</th>
                                        <th>Rent <span class="sub-heading">(per Unit)</span></th>
                                        <th>Total Rent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $totalUnits = 0; $totalRent = 0; ?>

                                    <tr class="rent-row">
                                        <td>3 bdrms</td>
                                        <td><input type="number" class="units" value="<?= $property['3_bedroom_units'] ?: '' ?>"></td>
                                        <td><input type="number" class="rent-per-unit" value="<?= $property['3_bedroom_units'] ? $property['3_bedroom_rent'] : '' ?>"></td>
                                        <td class="total-rent"><?= ($totalRent += $property['3_bedroom_units'] * $property['3_bedroom_rent']) ?: '' ?></td>
                                    </tr>

                                    <tr class="rent-row">
                                        <td>2 bdrms</td>
                                        <td><input type="number" class="units" value="<?= $property['2_bedroom_units'] ?: '' ?>"></td>
                                        <td><input type="number" class="rent-per-unit" value="<?= $property['2_bedroom_units'] ? $property['2_bedroom_rent'] : '' ?>"></td>
                                        <td class="total-rent"><?= ($totalRent += $property['2_bedroom_units'] * $property['2_bedroom_rent']) ?: '' ?></td>
                                    </tr>

                                    <tr class="rent-row">
                                        <td>1 bdrms</td>
                                        <td><input type="number" class="units" value="<?= $property['1_bedroom_units'] ?: '' ?>"></td>
                                        <td><input type="number" class="rent-per-unit" value="<?= $property['1_bedroom_units'] ? $property['1_bedroom_rent'] : '' ?>"></td>
                                        <td class="total-rent"><?= ($totalRent += $property['1_bedroom_units'] * $property['1_bedroom_rent']) ?: '' ?></td>
                                    </tr>

                                    <tr class="rent-row">
                                        <td>Bachelors</td>
                                        <td><input type="number" class="units" value="<?= $property['0_bedroom_units'] ?: '' ?>"></td>
                                        <td><input type="number" class="rent-per-unit" value="<?= $property['0_bedroom_units'] ? $property['0_bedroom_rent'] : '' ?>"></td>
                                        <td class="total-rent"><?= ($totalRent += $property['0_bedroom_units'] * $property['0_bedroom_rent']) ?: '' ?></td>
                                    </tr>

                                    <!-- Totals Row -->
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td><strong id="totalUnits"><?= $totalUnits ?></strong></td>
                                        <td></td>
                                        <td><strong id="totalRentDisplay"></strong></td>
                                        <input type="hidden" id="monthlyRent" value="0">
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="ml-lg-3 mt-3 mt-lg-0"> <!-- margin-top for mobile views -->
                            <button id="recalculateButton" class="button-common agent-button">Recalculate</button>
                            <p id="errorMessage" style="color: #cc0000; font-size: 0.8rem;"></p>
                            <button id="restore-button" class="button-common restore-button">Restore defaults</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-12">

            <!-- tabs for financial analysis and property detail sections -->
            <div class="tabs">
                <button class="tablink" onclick="openContent(event, 'FinancialAnalysis')" id="defaultOpen">Financial Analysis</button>
                <button class="tablink" onclick="openContent(event, 'PropertyDetails')">Property Details</button>
            </div>

            <div id="FinancialAnalysis" class="tabcontent">
                <div class="row">
                    <!-- cash flow analysis table on the left -->
                    <div class="col-lg-6 col-12">
                        <div class="cashflow-analysis-section">
                            <table>
                                <thead id="cashflow-thead">
                                    <tr>
                                        <th>Cash flow analysis</th>
                                        <th>Year 0</th>

                                        <?php if ($inputs["holdingPeriod"]["value"] < 3): ?>
                                            <th>Year 1 - <?= ($inputs["holdingPeriod"]["value"] - 1) ?> <span class="cashflow-sub-heading">(annually)</span></th>
                                        <?php endif; ?>

                                        <?php if ($inputs["holdingPeriod"]["value"] == 3): ?>
                                            <th>Year 2</th>
                                        <?php endif; ?>

                                        <?php if ($inputs["holdingPeriod"]["value"] >= 3): ?>
                                            <th>Year 1 - <?= ($inputs["holdingPeriod"]["value"] - 1) ?> <span class="cashflow-sub-heading">(annually)</span></th>
                                            <th>Year <?= $inputs["holdingPeriod"]["value"] ?></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody id="cashflow-tbody">
                                    <!-- Cash Outflows Row -->
                                    <tr class="cashflow-highlight">
                                        <td>Cash outflows</td>
                                        <td id="outflowsYear0"></td>
                                        <td id="outflowsYear1"></td>
                                        <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                                            <td id="outflowsYearN"></td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Initial Investment Row -->
                                    <tr class="indented-row">
                                        <td>Initial Investment</td>
                                        <td id="initialInvestmentYear0"></td>
                                        <td id="initialInvestmentYear1"></td>
                                        <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                                            <td id="initialInvestmentYearN"></td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Mortgage Expenses Row -->
                                    <tr class="indented-row">
                                        <td>Mortgage Expenses</td>
                                        <td id="mortgageExpensesYear0"></td>
                                        <td id="mortgageExpensesYear1"></td>
                                        <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                                            <td id="mortgageExpensesYearN"></td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Other Expenses Row -->
                                    <tr class="indented-row">
                                        <td>Other expenses</td>
                                        <td id="expensesYear0"></td>
                                        <td id="expensesYear1"></td>
                                        <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                                            <td id="expensesYearN"></td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Cash Inflows Row -->
                                    <tr class="cashflow-highlight">
                                        <td>Cash inflows</td>
                                        <td id="inflowsYear0"></td>
                                        <td id="inflowsYear1"></td>
                                        <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                                            <td id="inflowsYearN"></td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Rental Income Row -->
                                    <tr class="indented-row">
                                        <td>Rental income</td>
                                        <td id="incomeYear0"></td>
                                        <td id="incomeYear1"></td>
                                        <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                                            <td id="incomeYearN"></td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Proceeds from Sale Row -->
                                    <tr class="indented-row">
                                        <td>Proceeds from Sale</td>
                                        <td id="proceedsFromSaleYear0"></td>
                                        <td id="proceedsFromSaleYear1"></td>
                                        <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                                            <td id="proceedsFromSaleYearN"></td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Net Cash Flow Row -->
                                    <tr>
                                        <td>Net Cash Flow</td>
                                        <td id="netCashFlowYear0"></td>
                                        <td id="netCashFlowYear1"></td>
                                        <?php if ($inputs["holdingPeriod"]["value"] >= 2): ?>
                                            <td id="netCashFlowYearN"></td>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-6 col-12 mt-4 mt-lg-0">
                        <form id="financialAnalysisFormOtherInputs">
                            <table class="investment-summary-cashflow">
                                <thead>
                                    <tr>
                                        <th colspan="2">Other inputs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- <tr>
                                        <td><label for="monthlyRent"><?= $inputs['monthlyRent']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="monthlyRent" value="<?= $inputs['monthlyRent']['value'] ?>" readonly></td>
                                    </tr> -->
                                    <tr>
                                        <td><label for="propertyTax"><?= $inputs['propertyTax']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="propertyTax" value="<?= $inputs['propertyTax']['value'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td><label for="mortgageRate"><?= $inputs['mortgageRate']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="mortgageRate" value="<?= $inputs['mortgageRate']['value'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="renovationCosts"><?= $inputs['renovationCosts']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="renovationCosts" value="<?= $inputs['renovationCosts']['value'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="mortgageTerm"><?= $inputs['mortgageTerm']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="mortgageTerm" value="<?= $inputs['mortgageTerm']['value'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="landTransferTaxPercentage"><?= $inputs['landTransferTaxPercentage']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="landTransferTaxPercentage" value="<?= $inputs['landTransferTaxPercentage']['value'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="monthlyCapexReserve"><?= $inputs['monthlyCapexReserve']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="monthlyCapexReserve" value="<?= $inputs['monthlyCapexReserve']['value'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="annualInsurance"><?= $inputs['annualInsurance']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="annualInsurance" value="<?= $inputs['annualInsurance']['value'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="sellingCosts"><?= $inputs['sellingCosts']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="sellingCosts" value="<?= $inputs['sellingCosts']['value'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="vacancyAllowance"><?= $inputs['vacancyAllowance']['label'] ?>:</label></td>
                                        <td><input class="input-light-blue" id="vacancyAllowance" value="<?= $inputs['vacancyAllowance']['value'] ?>"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>

            <div id="PropertyDetails" class="tabcontent">
                <table class="investment-summary-cashflow">
                    <tr>
                        <td>Bedrooms</td>
                        <td><input type="text" value="<?= $property['Bedrooms'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Bathrooms</td>
                        <td><input type="text" value="<?= $property['Bathrooms'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>When Listed</td>
                        <td><input type="text" value="<?= $property['listingCardTagLabel'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>MLS Number</td>
                        <td><input type="text" value="<?= $property['MLS_number'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Agent</td>
                        <td><input type="text" value="<?= $property['Agent'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Agent Phone Number</td>
                        <td><input type="text" value="<?= $property['Agent_phone_number'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Building Type</td>
                        <td><input type="text" value="<?= $property['Building_type'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Stories</td>
                        <td><input type="text" value="<?= $property['Storys'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Community Name</td>
                        <td><input type="text" value="<?= $property['Community_name'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Property Taxes</td>
                        <td><input type="text" value="<?= $property['Property_taxes'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Lot Size</td>
                        <td><input type="text" value="<?= $property['Lot_size'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Number of Units</td>
                        <td><input type="text" value="<?= $property['num_units'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Above Grade Bedrooms</td>
                        <td><input type="text" value="<?= $property['Above_grade_bedrooms'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Above Grade Bathrooms</td>
                        <td><input type="text" value="<?= $property['Above_grade_bathrooms'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Basement Features</td>
                        <td><input type="text" value="<?= $property['Basement_features'] ?: '' ?>"></td>
                    </tr>
                    <tr>
                        <td>Parking Spaces</td>
                        <td><input type="text" value="<?= $property['Parking_spaces'] ?: '' ?>"></td>
                    </tr>
                </table>

                <p>Description: <?= htmlspecialchars($property['Description']) ?: 'N/A' ?></p>

                <div class="brokerage-section">
                    <p>Brokerage: <?= htmlspecialchars($property['Brokerage']) ?: 'N/A' ?></p>
                    <img src="<?= $property['Brokerage_logo'] ?: 'path/to/default/logo.png' ?>" alt="Brokerage Logo" class="brokerage-logo">
                    <img src="<?= $property['Listing_agent_photo'] ?: 'path/to/default/agent/photo.png' ?>" alt="Listing Agent Photo" class="listing-agent-photo">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- calling the js scripts and functions needed to update the user inputs, cash flow ananlysis and investment summary tables -->
<script src="https://cdn.jsdelivr.net/npm/financejs@4.1.0/finance.js"></script>
<script src="listener.js"></script>
<script src="ip_functions.js"></script>
<!-- <script src="cashflow_coladj.js"></script>
<script src="photo_nav.js"></script> -->

</body>
</html>