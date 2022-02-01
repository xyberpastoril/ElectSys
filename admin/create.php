<?php
$admin = 1; // to change directory level for db check
require '../db/db_checker.php';
session_start();
// check if admin logged in
if (empty($_SESSION['adminId']))
{
    require 'index/login.php';
    exit();
}
require '../db/db_connection_general.php';
$adminLevel = $_SESSION['adminLevel'];
// check voteOpen
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'voteOpen'";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql))
{
    header("Location: settings.php?error=SQL&task=fetchVoteOpenToggle");
    exit();
}
else
{
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $voteOpen = $row['settingValue'];
}
// check showResults
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'showResults'";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql))
{
    header("Location: settings.php?error=SQL&task=fetchVoteOpenToggle");
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
?>
<html>
    <head>
        <title>Create</title>
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
    <div id='addStudents' class='page-sub-window limitedWindowSize'>
        <div class='vertical-center'>
            <div class='page-sub-window-content'>
                <div class='window-grid'>
                    <p>New
                        <?php
                        echo (isset($_GET['partyListId'])) ? "Candidate" : "";
                        echo (isset($_GET['newparty'])) ? "Party" : "";
                        echo (isset($_GET['newsection'])) ? "Section" : "";
                        echo (isset($_GET['newAdmin'])) ? "Admin" : "";
                        echo (isset($_GET['candidateId'])) ? " Picture" : "";
                        ?>
                    </p>
                    <img src='../assets/img/close.png' onclick=location.href='<?php 
                        echo (isset($_GET['partyListId']) ? "candidates.php?partyListId=".$_GET['partyListId'] : "");
                        echo (isset($_GET['candidateId']) ? "candidates.php?partyListId=".$_GET['partyListId'] : "");
                        echo (isset($_GET['newparty']) ? "candidates.php" : "");
                        echo (isset($_GET['newsection']) ? "voterslist.php" : "");
                        echo (isset($_GET['newAdmin'])) ? "settings.php" : "";
                    ?>'>
                </div>
                <?php
                    // list of errors
                    if(isset($_GET['error']))
                    {
                        echo "<span class='span-16px'></span><p class='subWindowError'>";
                        switch($_GET['error'])
                        {
                            case "emptyFields":
                                echo "Fill in all fields";
                            break;
                            case "duplicateName":
                                echo "Candidate already exists";
                            break;
                            case "duplicatePartyList":
                                echo "Partylist already exists";
                            break;
                            case "duplicateSection":
                                echo "Section already exists";
                            break;
                            case "positionLimit":
                                echo "Limit reached for position of party";
                            break;
                            case "invalidAdminPassword":
                                echo "Invalid Admin Password";
                            break;
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
                            case "adminTaken":
                                echo "Username already taken.";
                            break;
                        }
                        echo "</p>";
                    }
                ?>
                <span class="span-16px"></span>
                <?php
                    if(isset($_GET['partyListId']) && !isset($_GET['candidateId']))
                    {
                        // check admin level
                        if($adminLevel < 2 || $voteOpen == 1 || $showResults == 1)
                        {
                            header("Location: candidates.php?accessDenied");
                            exit();
                        }
                        // check if exist
                        $sql = 'SELECT * FROM `partylist` WHERE `partyListID` = ?';
                        $stmt = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt, $sql))
                        {
                            header("Location: candidates.php?error=SQL&task=checkPartyListExistence");
                            exit();
                        }
                        else
                        {
                            mysqli_stmt_bind_param($stmt, "i", $_GET['partyListId']);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            if($row = mysqli_fetch_assoc($result))
                            {
                                $partyListNameMatch = $row['partyListName'];
                                $partyListAbbrMatch = $row['partyListAbbr'];
                            }
                        }
                        if(empty($partyListNameMatch))
                        {
                            $partyListNameMatch = "Independent";
                            $partyListAbbrMatch = "IND";
                        }
                        echo "
                        <form action='../db/db_candidates.php?partyListId=".$_GET['partyListId']."' method='post'>";
                            require 'form-content/candidateForm.php';
                            echo "
                            <span class='span-8px'></span>
                            <button class='input-lrn obj-center block button' style='width:300px' type='submit' name='addCandidate'>Add Candidate</button>
                        </form>    
                        ";
                    }
                    else if (isset($_GET['candidateId']))
                    {
                        // check admin level
                        if($adminLevel < 2 || $voteOpen == 1 || $showResults == 1)
                        {
                            header("Location: candidates.php?accessDenied");
                            exit();
                        }
                        echo "
                        <form action='../db/db_candidates.php?partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']."' method='post' enctype='multipart/form-data'>
                            <input class='file block obj-center input-lrn' style='height:60px;width:300px' type='file' name='file'>
                            <span class='span-8px'></span>
                            <button style='width:300px' class='input-lrn obj-center  block button' type='submit' name='addDisplayPhoto'>Add Photo</button>
                        </form>
                        <span class='span-8px'></span>
                        <form action='candidates.php?newCandidate=1&partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']."' method='post'>
                            <button style='width:300px;background-color:#333!important' class='input-lrn obj-center block button' type='submit'>Skip</button>
                        </form>";
                    }
                    else if (isset($_GET['newparty']))
                    {
                        // check admin level
                        if($adminLevel < 2 || $showResults == 1 || $voteOpen == 1)
                        {
                            header("Location: candidates.php?accessDenied");
                            exit();
                        }
                        echo "
                        <form action='../db/db_candidates.php' method='post'>";
                            require "form-content/partyListForm.php";
                            echo "
                            <span class='span-8px'></span>
                            <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='createPartyList'>Create Partylist</button>
                        </form>
                        ";
                    }
                    else if (isset($_GET['newsection']))
                    {
                        // anti-backdoor protection
                        if($showResults == 1 || $voteOpen == 1)
                        {
                            header("Location: voterslist.php?accessDenied");
                            exit();
                        }
                        echo "
                        <form action='../db/db_voterslist.php' method='post'>";
                            require "form-content/sectionForm.php";
                            echo "
                            <span class='span-8px'></span>
                            <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='createsection'>Create Section</button>
                        </form>
                        ";
                    }
                    else if (isset($_GET['newAdmin']))
                    {
                        // check admin level
                        if($adminLevel < 2 || $voteOpen == 1)
                        {
                            header("Location: settings.php?accessDenied");
                            exit();
                        }
                        echo "
                        <form action='../db/db_admin.php' method='post'>
                            <input class='block obj-center input-lrn' style='width:300px' type='text' name='username' placeholder='Username'>
                            <span class='span-8px'></span>
                            <input class='block obj-center input-lrn' style='width:300px' type='password' name='password' placeholder='Password'>
                            <span class='span-8px'></span>
                            <input class='block obj-center input-lrn' style='width:300px' type='password' name='repeatPassword' placeholder='Repeat Password'>
                            <span class='span-8px'></span>
                            <select class='block obj-center input-lrn' style='width:300px;height:32px;'name='adminLevel'>
                                <option value='3'>Level 3</option>
                                <option value='2' selected='selected'>Level 2</option>
                                <option value='1'>Level 1</option>
                            </select>
                            <span class='span-16px'></span>
                            <p>For security purposes, please let the currently logged admin enter his/her password:</p>
                            <span class='span-16px'></span>
                            <input class='block obj-center input-lrn' style='width:300px' type='password' name='passcode' placeholder='Current Admin Password'>
                            <span class='span-8px'></span>
                            <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='addAdminSettings'>Add Admin</button>
                        </form>
                        ";
                    }
                ?>
            </div>
        </div>
    </div>
</html>