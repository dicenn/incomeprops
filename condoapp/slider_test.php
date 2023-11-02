<?php
// Include your database configuration
include 'config_condoapp.php';

function getInitialRentAndIndex() {
    global $conn;
    $query = "SELECT occupancy_index, rent FROM condo_app.precon_cashflows_20231021_v8 LIMIT 1"; // Modify the query to suit your needs
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $occupancyIndex = $row['occupancy_index'];
        $rentArray = json_decode($row['rent']); // Assuming 'rent' is a JSON array
        $initialRent = $rentArray[$occupancyIndex]; // Getting rent based on occupancy index
        return $initialRent;
    }
    return null;
}

$initialRent = getInitialRentAndIndex();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slider Test</title>
</head>
<body>
    <h2>Slider Test</h2>

    <label for="rentSlider">Rent:</label>
    <input type="range" id="rentSlider" min="1000" max="3000" step="100">
    <div id="rentValue">$<?php echo $initialRent; ?></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var rentSlider = document.getElementById('rentSlider');
            var rentValue = document.getElementById('rentValue');

            // Set initial rent value from PHP
            var initialRent = <?php echo $initialRent; ?>;
            rentSlider.value = initialRent;
            rentValue.textContent = '$' + initialRent;

            // Event listener for the slider
            rentSlider.addEventListener('input', function() {
                var sliderValue = this.value;
                rentValue.textContent = '$' + sliderValue;

                // Logging slider values as the listener fires
                console.log("Slider Value: ", sliderValue);
                console.log("Text Underneath: ", rentValue.textContent);
            });

            // Logging initial values
            console.log("Initial Rent from DB: ", initialRent);
            console.log("Slider Initial Value: ", rentSlider.value);
        });
    </script>
</body>
</html>
