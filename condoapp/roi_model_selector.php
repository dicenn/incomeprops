<?php
// Include your database configuration
include 'config_condoapp.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to get distinct projects, models, and units
function getDistinctProjectModelUnit() {
    global $conn;
    $query = "SELECT DISTINCT project, model, unit FROM condo_app.precon_cashflows_20231102_v10";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getIndex($project, $model, $unit) {
    global $conn;
    $query = "SELECT * FROM condo_app.precon_cashflows_20231021_v8 WHERE project = ? AND model = ? AND unit = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $project, $model, $unit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row ? array_search($row, $data) : null;
}

if (isset($_GET['get_index'])) {
    $project = $_GET['project'];
    $model = $_GET['model'];
    $unit = $_GET['unit'];
    $index = getIndex($project, $model, $unit);
    echo json_encode(['index' => $index]);
    exit();
}

$data = getDistinctProjectModelUnit();
?>

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


</script>