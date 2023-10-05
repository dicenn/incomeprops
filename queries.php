<?php
// functions.php

include 'config.php';

function getProperties($num_units = "all", $min_price = null, $max_price = null, $city = null) {
    global $conn;
    $properties = array();

    $query = "SELECT * FROM income_props.toronto_duplexes_lean_v8 WHERE 1=1";  // "WHERE 1=1" is a trick to avoid dealing with leading "AND"s

    if (!empty($num_units) && $num_units !== "all") { $query .= " AND num_units >= '$num_units'";}
    if (!empty($min_price)) { $query .= " AND Price >= $min_price";}
    if (!empty($max_price)) {$query .= " AND Price <= $max_price";}
    if (!empty($city) && strtolower($city) !== "all") {$query .= " AND city = '$city'";}

    $sort = isset($_POST['sort']) ? $_POST['sort'] : null;
    
    // if ($sort) {
    //     switch ($sort) {
    //         case 'price_high_low': $query .= " ORDER BY Price DESC"; break;
    //         case 'price_low_high': $query .= " ORDER BY Price ASC"; break;
    //         // case 'return_high_low': $query .= " ORDER BY annual_return DESC"; break;
    //         // case 'return_low_high': $query .= " ORDER BY annual_return ASC"; break;
    //     }
    // }    

    // echo $query;

    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $properties[] = $row;
    }

    return $properties;
}

function getPropertyDetails($address = null) {
    global $conn;
    
    if ($address) {
        $address = mysqli_real_escape_string($conn, $address);
        $query = "SELECT * FROM income_props.toronto_duplexes_lean_v8 WHERE Address = '$address'";
    } else {
        $query = "SELECT * FROM income_props.toronto_duplexes_lean_v8 LIMIT 1";
    }

    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $property = mysqli_fetch_assoc($result);

    return $property;
}

function getAddresses() {
    global $conn;
    $addressesQuery = "SELECT Address FROM income_props.toronto_duplexes_lean_v8";
    $addressesResult = mysqli_query($conn, $addressesQuery);
    $addresses = mysqli_fetch_all($addressesResult, MYSQLI_ASSOC);

    return $addresses;
}

function closeConnection() {
    global $conn;
    mysqli_close($conn);
}

function getDistinctCities() {
    global $conn;
    $sql = "SELECT DISTINCT city FROM income_props.toronto_duplexes_lean_v8";
    $result = $conn->query($sql);
    $cities = [];
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
    return $cities;
}

?>