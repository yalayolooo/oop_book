<?php
try {
    $venuemapper = new VenueMapper();
    $venus = $venuemapper->finAll();
} catch (\Exception) {
    include('error.php');
    exit(0);
}

// Далее идет страница по умолчанию
?>

<html>
<head>
<title>Список заведений</title>
</head>
<body>
<h1>Список заведений</h1>
<?php foreach ($venues as $venue) { ?>
<?php print $venue->getName(); ?><br />
<?php } ?>
</body>
</html>