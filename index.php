<?php
session_start();
session_unset();
require "db/db_checker.php"; // check if database exists
require "db/db_connection_general.php"; // load databse credentials

for($checkSetting = 1; $checkSetting <= 4; $checkSetting++)
{
    switch($checkSetting)
    {
        case 1: $settingName = "voteOpen";break;
        case 2: $settingName = "school";break;
        case 3: $settingName = "organizationName";break;
        case 4: $settingName = "votingMethod";break;
    }

    $sql = "SELECT * FROM `operationSettings` WHERE `settingName` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        echo "Coding error.";
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "s", $settingName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        switch($checkSetting)
        {
            case 1: $voteOpen = $row['settingValue'];break;
            case 2: $school = $row['settingValue'];break;
            case 3: $organization = $row['settingValue'];break;
            case 4: $votingMethod = $row['settingValue'];break;
        }
    }
}

switch($votingMethod)
{
    case 1: 
        $votingText = "General Elections";
        $votingYear = (date("Y")) + 1;break;
    case 2: 
        $votingText = "Grade 7 & 11 Representative Elections";
        $votingYear = (date("Y"));break;
    case 3: 
        $votingText = "Club/Organizational Election";
        $votingYear = (date("Y"));break;
}

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
        <title>ElectSys</title>
        <link rel="stylesheet" type="text/css" href="assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="assets/css/general.css">
        <?php
            echo"
            <style>
            #bg {
                background-image:url('".$backgroundPhoto."');
            </style>
            ";
        ?>
        <link rel="shortcut icon" type="image/png" href="assets/img/logo/arms_128x128.png">
    </head>
    <body class="table">
        <div id="bg" class="absolute">
        </div>
        <div class="vertical-center">
            <img class="block logo-128px obj-center" src="<?php echo $electionLogo; ?>">
            <span class="span-16px"></span>
            <h1 class="text-center white-text-shadow"><?php echo $school; ?></h1>
            <h1 class="text-center white-text-shadow" style="font-size:25px;"><?php echo $organization; ?></h1>
            <span class="span-4px"></span>
            <p class="text-center white-text-shadow" style="font-size:21px"><?php echo $votingText . " " . $votingYear; ?></p>
            <span class="span-16px"></span>
            <?php
                if(!empty($_GET['error']))
                {
                    switch($_GET['error'])
                    {
                        case "emptyLRN":echo "<p class='error'>Enter your LRN to Vote!</p>";break;
                        case "LRNnotfound":echo "<p class='error'>LRN not found in database!</p>";break;
                        case "InvalidInput":echo "<p class='error'>Invalid LRN!</p>";break;
                        case "alreadyvoted":echo "<p class='error'>LRN already casted its vote!</p>";break;
                        case "NoSession":echo "<p class='error'>There isn't any session. Enter your LRN to Vote!</p>";break;
                    }
                    echo "<span class='span-8px'></span>";
                }
            if($voteOpen == 1)
            {
                echo"
                <form action='checklrn.php' method='post'> <!-- the login form  -->
                    <input class='block obj-center input-lrn' type='text' name='lrn' placeholder='Enter your 12-digit LRN to Vote!'>
                    <span class='span-8px'></span>
                    <button class='input-lrn obj-center  block button' type='submit' name='openballot'>Open Ballot</button>
                </form>
                ";
            }
            else
            {
                echo "
                <span class='span-16px'></span>
                <span class='span-16px'></span>
                <p class='text-center white-text-shadow'>Voting is currently closed.</p>
                <span class='span-16px'></span>
                <span class='span-16px'></span>";
            }
            ?>
            
            <span class='span-16px'></span>
            <p style="font-size:12px;" class="text-center white-text-shadow"><?php echo "ElectSys v1.2.2001 © Arms, 2019 -" . " " . date("Y"); ?></p>
        </div>
        <!--<a style='position:absolute;display:block;right:12px;top:12px;width:48px;height:48px;background-color:#eee;border-radius:1000px' href='admin/index.php'>
            <img style='height:32px;width:32px;padding:8px;' src='assets/img/admin/adminpanel.png'>
        </a>-->
        <a style='position:absolute;display:block;right:12px;top:12px;'>
            <p style='font-size:9px;font-weight:500;'>Background Photo by Sunri💖</p>
        </a>
    </body>
</html>