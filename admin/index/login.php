<?php
// get election logo link
require '../db/db_connection_general.php    ';
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
?>

<html>
    <head>
        <title>Admin Login</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
    </head>
    <body class='table'>
        <div id="bg" class="absolute">
        </div>
        <!--<a style='position:absolute;display:block;right:12px;top:12px;width:48px;height:48px;background-color:#eee;border-radius:1000px' href='../index.php'>
            <img style='height:32px;width:32px;padding:8px;' src='../assets/img/admin/vote.png'>
        </a>-->
        <div class="vertical-center">
            <img class="block logo-128px obj-center" src="../assets/img/logo/mini_logo.png">
            <span class="span-16px"></span>
            <h1 class="text-center white-text-shadow">ElectSys Admin</h1>
            <span class="span-4px"></span>
            <p class="text-center white-text-shadow">Enter your credentials below to login.</p>
            <span class="span-16px"></span>
            <?php
                /*if(!empty($_GET['error']))
                {
                    switch($_GET['error'])
                    {
                        case "LRNnotfound":echo "<p class='error'>LRN not found in database.</p>";break;
                        case "InvalidInput":echo "<p class='error'>Invalid Input!</p>";break;
                        case "alreadyvoted":echo "<p class='error'>You already had voted this election!</p>";break;
                        case "NoSession":echo "<p class='error'>There isn't any session. Enter your LRN to Vote!</p>";break;
                    }
                    echo "<span class='span-8px'></span>";
                }
            */
            // form to be filled up then checked up by a bunch of codes for the LRN confirming attendance for election?>
            <form action="../db/db_admin.php" method="post">
                <input class="block obj-center input-lrn" type="text" name="username" placeholder="Username">
                <span class="span-8px"></span>
                <input class="block obj-center input-lrn" type="password" name="password" placeholder="Password">
                <span class="span-8px"></span>
                <button class="input-lrn obj-center  block button"type="submit" name="login">Login</button>
            </form>
            <span class='span-16px'></span>
            <p style="font-size:12px;" class="text-center white-text-shadow"><?php echo "ElectSys v1.2.2001 © Arms, 2019 -" . " " . date("Y"); ?></p>
        </div>
    </body>
</html>