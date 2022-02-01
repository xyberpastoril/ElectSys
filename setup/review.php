<?php
    session_start();
    if(empty($_SESSION['setup']))
    {
        header("Location: index.php");
        exit();
    }

    switch($_SESSION['votingMethod'])
    {
    case 1: $votingMethod = "SSG General Elections (except G7 & G11 Reps)";break;
    case 2: $votingMethod = "SSG Grade 7 & Grade 11 Representative Elections";break;
    case 3: $votingMethod = "Organization/Club Election";break;
    }
?>
<html>
    <head>
        <title>Review Details</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
    </head>
    <body class='table' style='background-color:#222'>
        <div class='vertical-center'>
            <p style='font-size:32px;font-weight:700;'class="text-center white-text-shadow">Review Information</p>
            <span class='span-16px'></span>
            <p style='font-size:27px;font-weight:500;'class="text-center white-text-shadow">Admins</p>
            <span class='span-16px'></span>
            <p class="text-center white-text-shadow">
            <?php 
            for($N=0;$N<3;$N++)
            {
                echo $_SESSION['username'][$N] . "<br>";
            }
            ?></p>
            <span class='span-16px'></span>
            <p style='font-size:27px;font-weight:500;'class="text-center white-text-shadow">General Info</p>
            <span class='span-16px'></span>
            <p class="text-center white-text-shadow">School: <?php echo $_SESSION['school'];?></p>
            <span class='span-4px'></span>
            <p class="text-center white-text-shadow">Organization: <?php echo $_SESSION['organization'];?></p>
            <span class='span-4px'></span>
            <p class="text-center white-text-shadow">Voting Method: <?php echo $votingMethod;?></p>
            <span class='span-16px'></span>
            <form action="../db/db_setup.php" method="post">
                <button class='input-lrn obj-center  block button' type="submit" name="install">Begin Installation</button>
            </form>
        </div>
    </body>
</html>