<?php
    session_start();
    $setup = 1;
    require "../db/db_checker.php"; // check if database exists
?>
<html>
    <head>
        <title>ARMS-AES Setup</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
        <style>
            .faded-label {
                font-size:14px;
                color:#ccc;
                font-weight:600;
                margin-bottom:16px;
                letter-spacing:1.0px;
            }

            #armstext {
                font-family:'productsans-regular', sans-serif!important;
                font-size:14px;
                text-align:center;
                margin:0!important;
                padding:0!important;
            }
            #arms {
                display:block;
                height:24px;
                width:auto;
                margin:2 auto;
            }
        </style>
    </head>
    <body class='table' style='background-color:#222'>
        <div class="vertical-center">
            <img class="block logo-128px obj-center" src="../assets/img/logo/mini_logo.png">
            <span class="span-16px"></span>
            <h1 class='text-center' style='font-size:60px'>ElectSys</h1>
            <span class="span-8px"></span>
            <p id='armstext' class='faded-label'>FROM</p>
            <img id='arms' src='../assets/img/logo/arms.png'>
            <span class="span-16px"></span>
            <p class='text-center'>A paperless election system that organizes high-school organizational elections.</p>
            <span class="span-8px"></span>
            <p class='text-center'>Click the button below to begin setup.</p>
            <span class="span-16px"></span>
            <form action="../db/db_setup.php" method="post">
                <button class='input-lrn obj-center  block button' type="submit" name="start">Begin Setup</button>
            </form>
        </div>
    </body>
</html> 