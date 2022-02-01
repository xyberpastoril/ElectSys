<?php
    session_start();
    if(empty($_SESSION['setup']))
    {
        header("Location: index.php");
        exit();
    }

    if(isset($_GET['admin']))
    {
        switch($_GET['admin'])
        {
            case 1: $rank = "1";break;
            case 2: $rank = "2";break;
            case 3: $rank = "3";break;
        }
    }
?>
<html>
    <head>
        <title>Create Admin</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
    </head>
    <body class='table' style='background-color:#222'>
        <div class="vertical-center">
            <p style='font-size:32px;font-weight:700;'class="text-center white-text-shadow">Add Admin # <?php echo $rank . " (Level ".($_GET['admin'] == 1 ? "3" : "2").")"; ?> </p>
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
            <form action="../db/db_setup.php?admin=<?php echo $_GET['admin'];?>" method="post">
                <input class='block obj-center input-lrn' type="text" name="username" placeholder="Username">
                <span class='span-8px'></span>
                <input class='block obj-center input-lrn' type="password" name="password" placeholder="Password">
                <span class='span-8px'></span>
                <input class='block obj-center input-lrn' type="password" name="repeatPassword" placeholder="Repeat Password">
                <span class='span-8px'></span>
                <button class='input-lrn obj-center  block button'type="submit" name="addAdmin">Add Admin</button>
            </form>
        </div>
    </body>
</html>