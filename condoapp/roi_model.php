<?php
// Include your database configuration
include 'config_condoapp.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getData() {
    global $conn;
    $query = "SELECT * FROM condo_app.precon_cashflows_20231102_v10";
    $result = mysqli_query($conn, $query);
    $cashflows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Loop through each field in the row
        foreach ($row as $key => $value) {
            // Try to decode each field as JSON
            $decodedValue = json_decode($value, true);
            
            // Check if json_decode was successful and if it's not null
            if (json_last_error() == JSON_ERROR_NONE && $decodedValue !== null) {
                $row[$key] = $decodedValue;
            }
            // If it's not JSON or null, the value remains as is
        }
        $cashflows[] = $row;
    }
    return $cashflows;
}

// Fetch the distinct projects, models, and units
$cashflows = getData();

if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    $json_data = json_encode($cashflows);

    if (json_last_error() == JSON_ERROR_NONE) {
        echo $json_data;
    } else {
        echo json_encode(array('error' => 'JSON encoding failed: ' . json_last_error_msg()));
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Investment Metrics</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body style="display: none;">

    <h1>Investment Metrics</h1>

    <!-- Include the dropdowns file -->
    <?php include 'roi_model_selector.php'; ?>

    <!-- Investment Metrics -->
    <div id="metrics">
        <!-- <h2>Investment Metrics</h2> -->
        <p>Annualized return (IRR): <span id="irr">0%</span></p>
        <!-- <p>Cash-on-Cash: <span id="cashOnCash">0%</span></p>
        <p>NOI: <span id="noi">$0</span></p>
        <p>Monthly Rent: <span id="monthlyRent">$0</span></p>
        <p>Monthly Net Income: <span id="monthlyNetIncome">$0</span></p>
        <p>Cap Rate: <span id="capRate">0%</span></p>
        <p>Total Investment: <span id="totalInvestment">$0</span></p> -->
    </div>

    <!-- Value Sliders -->
    <div id="sliders">
        <h2>Value Sliders</h2>
        
        <label for="appreciation">Appreciation %:</label>
        <input type="range" id="appreciation" min="-4" max="16" step="1">
        <div id="appreciationValue">%</div>
        
        <label for="holdingPeriod">Holding Period:</label>
        <input type="range" id="holdingPeriod" min="0" max="120" step="1">
        <div id="holdingPeriodValue"></div>

        <label for="rent">Rent:</label>
        <input type="range" id="rent" step="100">
        <div id="rentValue">$</div>
    </div>

    <script>

        $(document).ready(function() {
            console.log("Document is ready");

            // Fetch data from the PHP backend
            $.get('http://localhost/incomeprops/condoapp/roi_model.php?json=true', function(cashflows) {
            console.log("Received cashflows: ", cashflows);

            const occupancyIndex = cashflows[0].occupancy_index;
            const rentArray = cashflows[0].rent;
            const initialRent = rentArray[occupancyIndex + 2];

            console.log("Occupancy Index: ", occupancyIndex);
            console.log("Initial Rent: ", Math.round(initialRent / 100) * 100);

            // Calculate minimum rent as half of initialRent, rounded down to nearest hundred

            // Initialize sliders based on database values
            $('#holdingPeriod').attr('min', occupancyIndex);
            $('#holdingPeriod').attr('max', 360);
            $('#holdingPeriod').attr('step', 12);
            $('#holdingPeriod').val(occupancyIndex);
            $('#rent').attr('min', Math.round(initialRent * 0.5 / 100) * 100);
            $('#rent').attr('max', Math.round(initialRent * 1.5 / 100) * 100);
            $('#rent').attr('step', 100); // Ensure this step value aligns with your initialRent
            $('#rent').val(Math.round(initialRent / 100) * 100);
            console.log("Rent slider initial value set to: ", $('#rent').val());
            $('#rentValue').text('$' + Math.round(initialRent / 100) * 100);
            console.log("Rent text initial value set to: ", $('#rentValue').text());
            $('#appreciation').attr('min', -4);
            $('#appreciation').attr('max', 16);
            $('#appreciation').val(6); // Set default value for appreciation
            $('#appreciationValue').text('6%'); // Set default value for appreciation

            // Set initial values for holding period and dates derived from it
            updateHoldingPeriodValues(occupancyIndex, cashflows);

            // Unhide the entire page content after it's fully prepared
            $('body').show();

            // Assuming cashflows, holdingPeriod are available in your script
            console.log("Deposits: ", cashflows[0].deposits);
            console.log("Closing Costs: ", cashflows[0].closing_costs);
            console.log("Mortgage Payments: ", cashflows[0].mortgage_payment);
            console.log("Rental Net Income: ", cashflows[0].rental_net_income);
            console.log("Holding Period: ", parseInt($('#holdingPeriod').val()) + 1);

            let netCashFlows = calculateNetCashFlows(cashflows[0].deposits, cashflows[0].closing_costs, cashflows[0].mortgage_payment, cashflows[0].rental_net_income, parseInt($('#holdingPeriod').val()) + 3, parseInt($('#appreciation').val()) / 100 , cashflows[0].mortgage_principal, cashflows[0].price, cashflows[0].selling_costs, cashflows[0].mortgage_payments_year);

            let correspondingDatesArray = cashflows[0].corresponding_date.slice(0, parseInt($('#holdingPeriod').val()) + 3);
            let correspondingDates = correspondingDatesArray.map(dateStr => new Date(dateStr));

            let xirr = calculateXIRR(netCashFlows, correspondingDates)
            console.log(netCashFlows);
            console.log(xirr);
            document.getElementById('irr').textContent = (xirr).toFixed(1) + '%';
            
            // Event listener for sliders
            $('#holdingPeriod, #appreciation, #rent').on('input', function() {
                const holdingPeriod = $('#holdingPeriod').val();
                const appreciation = $('#appreciation').val();
                let rent = $('#rent').val(); 

                // Now 'rent' represents the current slider value and can be used to update the display
                $('#rentValue').text('$' + rent);

                console.log("Slider Values - Holding Period: ", holdingPeriod, " Appreciation: ", appreciation, " Rent: ", rent);

                // Update the displayed values for Appreciation and Rent
                $('#appreciationValue').text(appreciation + '%');
                $('#rentValue').text('$' + rent);

                // Update values based on holding period
                updateHoldingPeriodValues(holdingPeriod, cashflows);
                netCashFlows = calculateNetCashFlows(cashflows[0].deposits, cashflows[0].closing_costs, cashflows[0].mortgage_payment, cashflows[0].rental_net_income, parseInt($('#holdingPeriod').val()) + 3, parseInt($('#appreciation').val()) / 100 , cashflows[0].mortgage_principal, cashflows[0].price, cashflows[0].selling_costs, cashflows[0].mortgage_payments_year);

                correspondingDatesArray = cashflows[0].corresponding_date.slice(0, parseInt($('#holdingPeriod').val()) + 3);
                correspondingDates = correspondingDatesArray.map(dateStr => new Date(dateStr));

                xirr = calculateXIRR(netCashFlows, correspondingDates)
                console.log(netCashFlows);
                console.log(xirr);
                document.getElementById('irr').textContent = (xirr).toFixed(1) + '%';
            });

        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("AJAX request failed: ", textStatus, errorThrown);
            // Consider showing an error message or handling the failure
        });
        });

        function updateHoldingPeriodValues(holdingPeriod, cashflows) {
            if (holdingPeriod == cashflows[0].occupancy_index) {
                $('#holdingPeriodValue').text('At occupancy');
            } else {
                const yearsPostOccupancy = Math.floor((holdingPeriod - cashflows[0].occupancy_index) / 12);
                $('#holdingPeriodValue').text(yearsPostOccupancy + ' years post-occupancy');
            }
        }

        function calculateNetCashFlows(deposits, closingCosts, mortgagePayments, rentalNetIncome, holdingPeriod, appreciationRate, mortgagePrincipal, price, selling_costs, mortgage_payments_year) {
            let netCashFlows = [];
            console.log("Initial netCashFlows length:", netCashFlows.length); // Should always be 0

            for (let i = 0; i < holdingPeriod; i++) {
                let netCashFlow = -(deposits[i] || 0) - (closingCosts[i] || 0) - (mortgagePayments[i] || 0) + (rentalNetIncome[i] || 0);
                netCashFlows.push(netCashFlow);
                console.log(`Period ${i+1}: Net Cash Flow = ${netCashFlow}`);
            }

            console.log("netCashFlows length after loop:", netCashFlows.length); // Check length after filling the array

        
            console.log("price: ", price, typeof price)
            console.log("appreciationRate: ", appreciationRate, typeof appreciationRate)
            console.log("holdingPeriod: ", holdingPeriod, typeof holdingPeriod)            

            // 1. Calculate the sale price of the property at the end of the holding period
            const salePrice = price * Math.pow((1 + appreciationRate / mortgage_payments_year), holdingPeriod);
            console.log("salePrice: ", salePrice, typeof salePrice)

            // 2. Calculate the selling costs
            console.log("selling_costs: ", selling_costs, typeof salePrice)
            const sellingCosts = salePrice * selling_costs;
            console.log("sellingCosts: ", sellingCosts)

            // 3. Calculate the amount remaining on the mortgage
            // Assuming the holding_period is less than the length of the deposits and mortgage_principal arrays
            const depositsSum = deposits.slice(0, holdingPeriod + 1).reduce((a, b) => a + b, 0);
            const principalSum = mortgagePrincipal.slice(0, holdingPeriod).reduce((a, b) => a + b, 0);
            const mortgageRemaining = (price - depositsSum) - principalSum;

            console.log("depositsSum: ", depositsSum, typeof depositsSum)
            console.log("principalSum: ", principalSum, typeof principalSum)
            console.log("mortgageRemaining: ", mortgageRemaining, typeof mortgageRemaining)

            const last_cashflow_addition = (salePrice - sellingCosts - mortgageRemaining)
            console.log("last_cashflow_addition: ", last_cashflow_addition, typeof last_cashflow_addition)

            console.log("netCashFlows.length: ", netCashFlows.length, typeof netCashFlows.length)
            console.log("holdingPeriod: ", holdingPeriod, typeof holdingPeriod)

            console.log("pre-lastcashflow: ", netCashFlows[holdingPeriod - 1], typeof netCashFlows[holdingPeriod - 1])
            netCashFlows[holdingPeriod - 1] += last_cashflow_addition;
            console.log("lastCashFlow: ", netCashFlows[holdingPeriod - 1])

            console.log("Final netCashFlows length:", netCashFlows.length); // Final check before returning
        
            return netCashFlows;
        }

        // First, make sure to have finance.js included in your project.
        // You can include it via a <script> tag in your HTML or by installing it via npm if you're using Node.js.

        function calculateXIRR(netCashFlows, correspondingDateStrings) {
        // Convert the date strings to Date objects
        let correspondingDates = correspondingDateStrings.map(dateString => new Date(dateString));

        // Assuming finance.js is included and Finance is available in the global scope
        let finance = new Finance();

        // Calculate XIRR
        // The XIRR function in finance.js expects the dates to be passed as actual Date objects, not strings
        let xirrValue = finance.XIRR(netCashFlows, correspondingDates);

        // Return the XIRR result
        return xirrValue;
        }

    </script>

    <script src="https://cdn.jsdelivr.net/npm/financejs@4.1.0/finance.js"></script>

</body>
</html>
