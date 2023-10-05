<?php
// functions.php

include 'config.php';

function getProperties() {
    global $conn;
    $properties = array();
    $query = "SELECT * FROM income_props.toronto_duplexes_lean_v8";
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

?>