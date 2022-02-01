<?php
session_start();
session_unset();
?>

<html>
    <head>
        <title>Success!</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
    </head>
    <body class='table' style='background-color:#222'>
        <div class="vertical-center">
            <img class="block logo-128px obj-center" src="../assets/img/logo/mini_logo.png">
            <span class="span-16px"></span>
            <p style='font-size:32px;font-weight:700;'class="text-center white-text-shadow">Congratulations! You successfully installed ElectSys!</p>
            <span class='span-16px'></span>
            <p style='font-size:24px;font-weight:500;'class="text-center white-text-shadow">Would you mind to add a logo for this election?<br>Logo of your Organization/Club or School can do.</p>
            <span class='span-8px'></span>
            <?php
                // list of errors
                if(isset($_GET['error']))
                {
                    echo "<span class='span-8px'></span><p class='subWindowError' style='width:368px;margin:0 auto;'>";
                    switch($_GET['error'])
                    {
                        case "nophoto":
                            echo "There isn't any file chosen";
                        break;
                        case "invalidtype":
                            echo "Invalid type chosen. Supported types: <br>'jpg', 'jpeg', 'png', 'gif'";
                        break;
                        case "toobig":
                            echo "File is too big";
                        break;
                        case "error":
                            echo "Unknown error occurred";
                        break;
                    }
                    echo "</p><span class='span-16px'></span>";
                }
            ?>
            <form action="../db/db_setup.php" method="post" enctype="multipart/form-data">
                <input class="file block obj-center input-lrn" style='height:60px' type="file" name="file">
                <span class='span-8px'></span>
                <button class='input-lrn obj-center  block button' type="submit" name="addElectionLogo">Add Photo</button>
            </form>
            <span class='span-8px'></span>
            <!-- formerly coverphoto.php, but disabled as it was presetted to czejan's background -->
            <form action="../admin/index.php">
                <button class='input-lrn obj-center  block button' style='background-color:#777;'type="submit">Skip</button>
            </form>
        </div>
    </body>
</html>