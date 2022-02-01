<?php
    $admin = 1; // to change directory level for db check
    require "../db/db_checker.php"; // check if database exists
    session_start();
    // check if admin logged in
    if(empty($_SESSION['adminId']))
    {
        require "index/login.php";
        exit();
    }

    require "../db/db_connection_general.php";

    $sectionId = $_GET['sectionId'];
    $adminLevel = $_SESSION['adminLevel'];
?>

<html>
    <head>
        <title>Add Students</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
        <style>
            .vertical-center {
                width:100vw;
                height:100vh;
            }
        </style>
    </head>
    <body>
        <div id="addStudents" class="page-sub-window limitedWindowSize">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>Add Students</p>
                        <img src="../assets/img/close.png" onclick="location.href='voterslist.php?sectionId=<?php echo $_GET['sectionId']; ?>'">
                    </div>
                    <span class="span-16px"></span>
                    <form action="../db/db_voterslist.php" method="post">
                        <?php
                        if(isset($_POST['initAddStudents']) || isset($_GET['studentLimit']))
                        {
                            if(isset($_POST['voters']))
                            {
                                $studentLimit = $_POST['voters'];
                            }
                            if(isset($_GET['studentLimit']))
                            {
                                $studentLimit = $_GET['studentLimit'];
                            }
                            if(isset($_POST['LRN']))
                            {
                                $LRNtemp = $_POST['LRN'];
                            }
                            else
                            {
                                for($count = 0; $count < $studentLimit; $count++)
                                {
                                    $LRNtemp[$count] = "";
                                }
                            }
                            echo "
                            <input type='hidden' name='studentLimit' value='".$studentLimit."'>
                            <input type='hidden' name='sectionId' value='".$sectionId."'>
                            ";
                            if(empty($studentLimit))
                            {
                                header("Location: voterslist.php?sectionId=".$sectionId."&error=emptyFields");
                                exit();
                            }
                            else if (!preg_match("/^[1-9][0-9]*$/", $studentLimit))
                            {
                                header("Location: voterslist.php?sectionId=".$sectionId."&error=invalidinput");
                                exit();
                            }
                            for ($studentCount = 0; $studentCount < $studentLimit; $studentCount++)
                            {
                                echo "
                                <div class='indexNumber grid'>
                                    <p>".($studentCount+1)."</p>
                                    <input class='block obj-center input-lrn' style='width:auto;text-align:left' type='text' name='LRN[".$studentCount."]' value='".$LRNtemp[$studentCount]."'>
                                </div>";
                            }
                        }

                        // check showResults (if 1, disable critical features)
                        $sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'showResults'";
                        $stmt = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt, $sql))
                        {
                            header("Location: settings.php?error=SQL&task=fetchShowResultsToggle");
                            exit();
                        }
                        else
                        {
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            $row = mysqli_stmt_get_result($stmt);
                            $row = mysqli_fetch_assoc($result);
                            $showResults = $row['settingValue'];
                        }

                        if($showResults == 1)
                        {
                            echo "
                            <span class='span-16px'></span>
                            <p>As a security measure, you need to retype your password.</p>
                            ";
                            // list of errors
                            if(isset($_GET['error']))
                            {
                                echo "<span class='span-16px'></span><p class='subWindowError'>";
                                switch($_GET['error'])
                                {
                                    case "invalidPassword":
                                        echo "Invalid Password";
                                    break;
                                }
                                echo "</p>";
                            }
                            echo "
                            <span class='span-8px'></span>
                            <input class='block obj-center input-lrn' style='width:300px;text-align:left' type='password' name='passcode'>
                            <span class='span-8px'></span>";
                        }
                        ?>
                        <button class='input-lrn obj-center  block button' style='width:300px' type="submit" name='addVoterBulk'>Add Students</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>