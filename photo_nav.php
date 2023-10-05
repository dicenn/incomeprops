<?php echo "Inside photo_nav.php"; ?>

<?php
    echo "Testing PHP execution"
    include 'queries.php';

    $address = isset($_GET['address']) ? $_GET['address'] : null;
    $property = getPropertyDetails($address);
    // var_dump($property);

    // if (!$property) {
    //     die("Property not found.");
    // }

    $addresses = getAddresses();
    closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page Title Here</title>
    <!-- You can include other meta tags, CSS links, etc. here -->
</head>
<body>

<script>
    console.log("Pic1: ", "<?= $property['Pic1'] ?>");
    console.log("Pic2: ", "<?= $property['Pic2'] ?>");
    console.log("Pic3: ", "<?= $property['Pic3'] ?>");
    console.log("Pic4: ", "<?= $property['Pic4'] ?>");
    console.log("Pic5: ", "<?= $property['Pic5'] ?>");

    let currentPhotoIndex = 0;  
    let photos = [
        "<?php echo $property['Pic1']; ?>",
        "<?php echo $property['Pic2']; ?>",
        "<?php echo $property['Pic3']; ?>",
        "<?php echo $property['Pic4']; ?>",
        "<?php echo $property['Pic5']; ?>",
    ];

    function nextPhoto() {
        if (currentPhotoIndex < photos.length - 1) {  
            currentPhotoIndex++;
            document.getElementById('currentPhoto').src = photos[currentPhotoIndex];
        }
    }

    function prevPhoto() {
        if (currentPhotoIndex > 0) { 
            currentPhotoIndex--;
            document.getElementById('currentPhoto').src = photos[currentPhotoIndex];
        }
    }
</script>

</body>
</html>
