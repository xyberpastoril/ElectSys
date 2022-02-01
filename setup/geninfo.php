<?php
    session_start();
    if(empty($_SESSION['setup']))
    {
        header("Location: index.php");
        exit();
    }
?>
<html>
    <head>
        <title>Fill in General Info</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
    </head>
    <body class='table' style='background-color:#222'>
        <div class="vertical-center">
            <p style='font-size:32px;font-weight:700;'class="text-center white-text-shadow">Enter Required Information</p>
            <span class='span-8px'></span>
            <?php
                // list of errors
                if(isset($_GET['error']))
                {
                    echo "<span class='span-8px'></span><p class='subWindowError' style='width:368px;margin:0 auto;'>";
                    switch($_GET['error'])
                    {
                        case "emptyFields":
                            echo "Fill in all fields";
                        break;
                        case "unmatchedPassword":
                            echo "Passwords do not match";
                        break;
                        case "shortPassword":
                            echo "Password is too short";
                        break;
                        case "sameCredentialsAdmin":
                            echo "Username already exists";
                        break;
                        case "shortUsername":
                            echo "Username too short";
                        break;
                        case "invalidUsername":
                            echo "Invalid Username";
                        break;
                    }
                    echo "</p><span class='span-16px'></span>";
                }
            ?>
            <form action="../db/db_setup.php" method="post">
                <!-- hidden school -->
                <input class='block obj-center input-lrn' type="hidden" name="school" placeholder="School" value="Plaridel National High School">
                <!--<span class='span-8px'></span>-->
                <input class='block obj-center input-lrn' type="text" name="organization" placeholder="Organization">
                <span class='span-8px'></span>
                <select class='block obj-center input-lrn' style="width:384px;height:32px;"name="votingMethod">
                    <option value='1'>SSG General Elections (except Grade 7 & Grade 11 Reps.)</option>
                    <option value='2'>SSG Gr. 7 & 11 Representative Elections</option>
                    <option value='3'>Organization/Club Election (All Positions)</option>
                </select>
                <span class='span-8px'></span>
                <button class='input-lrn obj-center  block button' type="submit" name="addInfo">Submit</button>
            </form>
        </div>
    </body>
</html>