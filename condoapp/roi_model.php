<?php
// Include your database configuration
include 'config_condoapp.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to get distinct projects, models, and units
function getDistinctProjectModelUnit() {
    global $conn;
    $query = "SELECT DISTINCT project, model, unit FROM condo_app.precon_cashflows_20231021_v8";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getData() {
    global $conn;
    $query = "SELECT * FROM condo_app.precon_cashflows_20231021_v8";
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
$data = getDistinctProjectModelUnit();
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

    <!-- Dropdowns for Project, Model, and Unit -->
    <div>
        <label for="project">Select Project:</label>
        <select id="project">
            <!-- Options will be populated dynamically -->
        </select>
    </div>

    <div>
        <label for="model">Select Model:</label>
        <select id="model">
            <!-- Options will be populated dynamically -->
        </select>
    </div>

    <div>
        <label for="unit">Select Unit:</label>
        <select id="unit">
            <!-- Options will be populated dynamically -->
        </select>
    </div>

    <!-- Investment Metrics -->
    <div id="metrics">
        <h2>Investment Metrics</h2>
        <p>IRR: <span id="irr">0%</span></p>
        <p>Cash-on-Cash: <span id="cashOnCash">0%</span></p>
        <p>NOI: <span id="noi">$0</span></p>
        <p>Monthly Rent: <span id="monthlyRent">$0</span></p>
        <p>Monthly Net Income: <span id="monthlyNetIncome">$0</span></p>
        <p>Cap Rate: <span id="capRate">0%</span></p>
        <p>Total Investment: <span id="totalInvestment">$0</span></p>
    </div>

    <!-- Value Sliders -->
    <div id="sliders">
        <h2>Value Sliders</h2>
        
        <label for="appreciation">Appreciation %:</label>
        <input type="range" id="appreciation" min="-4" max="16" step="1">
        <div id="appreciationValue">%</div>
        
        <label for="holdingPeriod">Holding Period:</label>
        <input type="range" id="holdingPeriod" min="0" max="360" step="1">
        <div id="holdingPeriodValue"></div>

        <label for="rent">Rent:</label>
        <input type="range" id="rent" step="100">
        <div id="rentValue">$</div>
    </div>

    <script>
        const data = <?php echo json_encode($data); ?>;

        const projectDropdown = $('#project');
        const modelDropdown = $('#model');
        const unitDropdown = $('#unit');

        // Populate the project dropdown initially
        const projects = [...new Set(data.map(item => item.project))];
        projects.forEach(project => {
            projectDropdown.append(new Option(project, project));
        });

        // Event listener for project dropdown change
        projectDropdown.change(function() {
            const selectedProject = $(this).val();

            // Filter models based on the selected project
            const filteredModels = [...new Set(data.filter(item => item.project === selectedProject).map(item => item.model))];
            modelDropdown.empty();
            filteredModels.forEach(model => {
                modelDropdown.append(new Option(model, model));
            });

            // Trigger a change event to update the unit dropdown
            modelDropdown.trigger('change');
        });

        // Event listener for model dropdown change
        modelDropdown.change(function() {
            const selectedProject = projectDropdown.val();
            const selectedModel = $(this).val();

            // Filter units based on the selected project and model
            const filteredUnits = [...new Set(data.filter(item => item.project === selectedProject && item.model === selectedModel).map(item => item.unit))];
            unitDropdown.empty();
            filteredUnits.forEach(unit => {
                unitDropdown.append(new Option(unit, unit));
            });
        });

        // Trigger an initial change event to populate the model and unit dropdowns
        projectDropdown.trigger('change');

        $(document).ready(function() {
            console.log("Document is ready");

            // Fetch data from the PHP backend
            $.get('http://localhost/incomeprops/condoapp/roi_model.php?json=true', function(cashflows) {
            console.log("Received cashflows: ", cashflows);

            const occupancyIndex = cashflows[0].occupancy_index;
            const rentArray = cashflows[0].rent;
            const initialRent = rentArray[occupancyIndex];

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
    </script>

</body>
</html>
