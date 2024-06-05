<?php
// functions.php

include 'config_incomeprops.php';

function getProperties($num_units = "all", $min_price = null, $max_price = null, $city = null) {
    global $conn;
    $properties = array();

    $query = "SELECT * FROM income_props.multifamily_20240418 WHERE province = 'Ontario'";

    if (!empty($num_units) && $num_units !== "all") { $query .= " AND num_units >= :num_units";}
    if (!empty($min_price)) { $query .= " AND Price >= :min_price";}
    if (!empty($max_price)) { $query .= " AND Price <= :max_price";}
    if (!empty($city) && strtolower($city) !== "all") { $query .= " AND city = :city";}

    $sort = isset($_POST['sort']) ? $_POST['sort'] : null;

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    if (!empty($num_units) && $num_units !== "all") { $stmt->bindParam(':num_units', $num_units, PDO::PARAM_INT);}
    if (!empty($min_price)) { $stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);}
    if (!empty($max_price)) { $stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);}
    if (!empty($city) && strtolower($city) !== "all") { $stmt->bindParam(':city', $city, PDO::PARAM_STR);}

    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $properties[] = $row;
    }

    return $properties;
}

function getPropertyDetails($address = null) {
    global $conn;
    
    if ($address) {
        $query = "SELECT * FROM income_props.multifamily_20240418 WHERE Address = :address";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
    } else {
        $query = "SELECT * FROM income_props.multifamily_20240418 WHERE province = 'Ontario' LIMIT 1";
        $stmt = $conn->prepare($query);
    }

    $stmt->execute();
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    return $property;
}

function getAddresses() {
    global $conn;
    $addressesQuery = "SELECT Address FROM income_props.multifamily_20240418 WHERE province = 'Ontario'";
    $stmt = $conn->prepare($addressesQuery);
    $stmt->execute();
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $addresses;
}

function closeConnection() {
    global $conn;
    $conn = null;
}

function getDistinctCities() {
    global $conn;
    $sql = "SELECT DISTINCT city FROM income_props.multifamily_20240418 WHERE province = 'Ontario'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $cities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cities[] = $row['city'];
    }
    return $cities;
}
?>