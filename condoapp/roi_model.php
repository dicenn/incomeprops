<?php
// Include your database configuration
include 'config_condoapp.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to get distinct projects, models, and units
function getDistinctProjectModelUnit() {
    global $conn;
    $query = "SELECT DISTINCT project, model, unit FROM condo_app.precon_cashflows_20231021_v7";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getData() {
    global $conn;
    $query = "SELECT * FROM condo_app.precon_cashflows_20231021_v7";
    $result = mysqli_query($conn, $query);
    $cashflows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Loop through each field in the row
        foreach ($row as $key => $value) {
            // Try to decode each field as JSON
            $decodedValue = json_decode($value, true);
            
            // Check if json_decode was successful and if it's actually an array (not a number or string)
            if (json_last_error() == JSON_ERROR_NONE && is_array($decodedValue)) {
                $row[$key] = $decodedValue;
            }
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
<body>

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
        <input type="range" id="appreciation" min="-4" max="16" step="1" value="6">
        <div id="appreciationValue">6%</div>
        
        <label for="holdingPeriod">Holding Period:</label>
        <input type="range" id="holdingPeriod" min="0" max="360" step="1" value="0">
        <div id="holdingPeriodValue">0 Years, 0 Months</div>
        
        <label for="rent">Rent:</label>
        <input type="range" id="rent" min="0" max="4000" step="100" value="2000">
        <div id="rentValue">$2000</div>
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
            console.log("Document is ready");  // Debug line

            // Fetch data from the PHP backend
            $.get('http://localhost/incomeprops/condoapp/roi_model.php?json=true', function(cashflows) {
                console.log("Received cashflows: ", cashflows);  // Debug line

                const occupancyIndex = cashflows[0].occupancy_index;
                const rentArray = JSON.parse(cashflows[0].rent);
                const initialRent = rentArray[occupancyIndex];

                console.log("Occupancy Index: ", occupancyIndex);  // Debug line
                console.log("Initial Rent: ", initialRent);  // Debug line

                // Initialize sliders based on database values
                $('#holdingPeriod').val(occupancyIndex);
                $('#rent').attr('min', initialRent * 0.5);
                $('#rent').attr('max', initialRent * 2);
                $('#rent').val(initialRent);
                $('#rentValue').text('$' + initialRent);

                // Event listener for sliders
                $('#holdingPeriod, #appreciation, #rent').on('input', function() {
                    const holdingPeriod = $('#holdingPeriod').val();
                    const appreciation = $('#appreciation').val();
                    const rent = $('#rent').val();

                    console.log("Slider Values - Holding Period: ", holdingPeriod, " Appreciation: ", appreciation, " Rent: ", rent);  // Debug line

                    // Update the displayed values
                    $('#appreciationValue').text(appreciation + '%');
                    const years = Math.floor(holdingPeriod / 12);
                    const months = holdingPeriod % 12;
                    $('#holdingPeriodValue').text(years + ' Years, ' + months + ' Months');
                    $('#rentValue').text('$' + rent);
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.log("AJAX request failed: ", textStatus, errorThrown);  // Debug line
            });
        });
    </script>

</body>
</html>
