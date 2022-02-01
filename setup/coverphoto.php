<?php
session_start();
?>

<html>
    <head>
        <title>Success!</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
        <!--<meta http-equiv="refresh" content="60;URL='../admin'" />-->
    </head>
    <body class='table' style='background-color:#222'>
        <div class="vertical-center">
            <p style='font-size:32px;font-weight:700;'class="text-center white-text-shadow">Would you mind adding a background photo? </p>
            <span class='span-16px'></span>
            <p style='font-size:24px;font-weight:500;'class="text-center white-text-shadow">This will be applied to the voting interface.</p>
            <span class='span-16px'></span>
            <form action="../db/db_setup.php" method="post" enctype="multipart/form-data">
                <input class="file block obj-center input-lrn" style='height:60px' type="file" name="file">
                <span class='span-8px'></span>
                <button class='input-lrn obj-center  block button' type="submit" name="addBackgroundPhoto">Add Photo</button>
            </form>
            <span class='span-8px'></span>
            <form action="../admin/index.php">
                <button class='input-lrn obj-center  block button' style='background-color:#777;'type="submit">Skip</button>
            </form>
        </div>
    </body>
</html>