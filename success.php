<?php
session_start();
if (empty($_SESSION['lrn'])) {
    header("Location: index.php");
    exit();
}
$lrn = $_SESSION['lrn'];
session_unset(); // session unset after lrn variable localized, this page will automatically refresh in 3 seconds

require 'db/db_connection_general.php';

// get election logo link
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'electionLogo'";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql))
{
    header("Location: index.php?error=SQL&task=checkVotingMethod");
    exit();
}
else
{
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $electionLogo = $row['settingValue'];
    if($electionLogo == NULL)
    {
        $electionLogo = "assets/img/admin/adminpanel.png";
    }
}

// get background photo link
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'backgroundPhoto'";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql))
{
    header("Location: index.php?error=SQL&task=checkVotingMethod");
    exit();
}
else
{
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $backgroundPhoto = $row['settingValue'];
    if($backgroundPhoto == NULL)
    {
        $backgroundPhoto = "";
    }
}
?>

<html>
    <head>
        <title>ARMS_AES</title>
        <link rel="stylesheet" type="text/css" href="assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="assets/css/general.css">
        <link rel="shortcut icon" type="image/png" href="assets/img/logo/arms_128x128.png">
        <?php
            echo"
            <style>
            #bg {
                background-image:url('".$backgroundPhoto."');
            </style>
            ";
        ?>
        <meta http-equiv="refresh" content="3;url=index.php" />
    </head>
    <body class="table">
        <div id="bg" class="absolute">
        </div>
        <div class="vertical-center">
            <img class="block logo-128px obj-center" src="<?php echo $electionLogo?>">
            <span class="span-16px"></span>
            <p class="text-center candidate-name white-text-shadow">You have successfully voted!</p>
            <span class="span-4px"></span>
            <p class="text-center candidate-party white-text-shadow">LRN: <?php echo $lrn; ?></p>
            <span class="span-16px"></span>
            <p class="text-center white-text-shadow">Redirecting in 3 sec. Not working? Click <a href="index.php">here</a></p>
        </div>
    </body>

</html>