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
        <title>Modify</title>
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
                        <p>Modify 
                            <?php
                            echo (isset($_GET['LRN']) ? "Student" : "");
                            echo (isset($_GET['sectionId']) ? "Section" : "");
                            echo (isset($_GET['candidateId']) ? "Candidate" : "");
                            echo (isset($_GET['partyListId']) && isset($_GET['candidate']) ? "Party" : "");
                            echo (isset($_GET['changePassword'])) ? "Password" : "";
                            echo (isset($_GET['adminId'])) ? "Admin" : "";
                            ?>

                        </p>
                        <img src="../assets/img/close.png" onclick=location.href='<?php 
                        echo (isset($_GET['LRN']) ? "voterslist.php?sectionId=".$sectionId : "");
                        echo (isset($_GET['sectionId']) ? "voterslist.php?sectionId=".$_GET['sectionId'] : "");
                        echo (isset($_GET['candidateId']) && !isset($_GET['partyListId']) ? "candidates.php" : "");
                        echo (isset($_GET['partyListId']) ? "candidates.php?partyListId=".$_GET['partyListId'] : "");
                        echo (isset($_GET['changePassword'])) ? "settings.php" : "";
                        echo (isset($_GET['adminId'])) ? "settings.php" : "";
                        ?>'>
                        <!-- make back button dynamic depending on modify isset -->
                    </div>
                    <?php
                    // list of errors
                    if(isset($_GET['error']))
                    {
                        echo "<span class='span-16px'></span><p class='subWindowError'>";
                        switch($_GET['error'])
                        {
                            case "duplicateSection":
                                echo "Section in that Grade Level already exists";
                            break;
                            case "duplicateName":
                                echo "Candidate already exists";
                            break;
                            case "positionLimit":
                                echo "Limit reached for position of party";
                            break;
                            case "duplicatePartyList":
                                echo "Partylist already exists";
                            break;
                            case "invalidAdminPassword":
                                echo "Invalid Admin Password";
                            break;
                        }
                        echo "</p>";
                    }
                    ?>
                    <span class="span-16px"></span>
                    <?php
                        if($_SESSION['modifyToken'] == 1 || isset($_POST['modify']))
                        {
                            if(isset($_GET['sectionId']))
                            {
                                // anti-backdoor protection
                                if($showResults == 1 || $voteOpen == 1)
                                {
                                    header("Location: voterslist.php?accessDenied");
                                    exit();
                                }
                                // check if exists
                                $sql = "SELECT * FROM `sectionsList` WHERE `sectionId` = ?";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: ../admin/voterslist.php?error=SQL&task=getSectionId");
                                    exit();
                                }
                                else
                                {
                                    mysqli_stmt_bind_param($stmt, "i", $_GET['sectionId']);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $sectionId = $row['sectionId'];
                                        $sectionNameInput = $row['sectionName'];
                                        $gradeLevel = $row['gradeLevel'];
                                    }
                                }
                                if(empty($sectionId))
                                {
                                    header("Location: ../admin/voterslist.php");
                                    exit();
                                }
                                echo"
                                <form action='../db/db_voterslist.php' method='post'>
                                    <input type='hidden' name='sectionId' value='".$sectionId."'>
                                    "; require "form-content/sectionForm.php";
                                    echo "
                                    <span class='span-8px'></span>
                                    <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='modifySection'>Modify Section</button>
                                </form>
                                ";
                            }
                            else if(isset($_GET['candidateId']) && isset($_GET['info']))
                            {
                                // anti-backdoor protection
                                if($adminLevel < 2 || $showResults == 1 || $voteOpen == 1)
                                {
                                    header("Location: voterslist.php?accessDenied");
                                    exit();
                                }
                                // check if exists
                                $sql = "SELECT * FROM `candidatesList` WHERE `candidateId` = ?";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: ../admin/candidates.php?error=SQL&task=getSectionId");
                                    exit();
                                }
                                else
                                {
                                    mysqli_stmt_bind_param($stmt, "i", $_GET['candidateId']);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $firstName = $row['firstName'];
                                        $middleName = $row['middleName'];
                                        $lastName = $row['lastName'];
                                        $positionIdMatch = $row['position'];
                                        $partyListIdMatch = $row['partyList'];
                                    }
                                }
                                if(empty($positionIdMatch))
                                {
                                    header("Location: ../admin/candidates.php");
                                    exit();
                                }
                                echo "
                                <form action='../db/db_candidates.php?candidateId=".$_GET['candidateId']."' method='post'>";
                                    require "form-content/candidateForm.php";
                                    echo "
                                    <span class='span-8px'></span>
                                    <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='modifyCandidate'>Modify Candidate</button>
                                </form>
                                "; 
                            }
                            else if(isset($_GET['candidateId']) && isset($_GET['photo']))
                            {
                                // anti-backdoor protection
                                if($adminLevel < 2 || $showResults == 1 || $voteOpen == 1)
                                {
                                    header("Location: voterslist.php?accessDenied");
                                    exit();
                                }
                                // check if exists
                                $sql = "SELECT * FROM `candidatesList` WHERE `candidateId` = ?";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: ../admin/candidates.php?error=SQL&task=getSectionId");
                                    exit();
                                }
                                else
                                {
                                    mysqli_stmt_bind_param($stmt, "i", $_GET['candidateId']);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    if ($row = mysqli_fetch_assoc($result))
                                    {
                                        $firstName = $row['firstName'];
                                        $middleName = $row['middleName'];
                                        $lastName = $row['lastName'];
                                        $positionIdMatch = $row['position'];
                                        $partyListIdMatch = $row['partyList'];
                                        if($row['displayPic'] == NULL)
                                        {
                                            $displayPic = "../assets/img/blank.png";
                                        }
                                        else
                                        {
                                            $displayPic = "../".$row['displayPic'];
                                        }
                                    }
                                }
                                if(empty($positionIdMatch))
                                {
                                    header("Location: ../admin/candidates.php");
                                    exit();
                                }
                                echo "
                                <p>Current Display Photo of<br>'".$lastName.", ".$firstName." ".$middleName."'</p>
                                <span class='span-8px'></span>
                                <img style='width:300px;height:300px;' src='".$displayPic."'>
                                <span class='span-8px'></span>
                                ";
                                if($row['displayPic'] != NULL)
                                {
                                    echo "
                                    <form action='../db/db_candidates.php?partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']."' method='post' enctype='multipart/form-data'>
                                        <input class='file block obj-center input-lrn' style='height:60px;width:300px' type='file' name='file'>
                                        <span class='span-8px'></span>
                                        <button style='width:300px' class='input-lrn obj-center  block button' type='submit' name='modifyDisplayPhoto'>Change Photo</button>
                                    </form>
                                    <span class='span-8px'></span>
                                    <form action='../db/db_candidates.php?partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']."' method='post'>
                                        <button style='width:300px;' class='input-lrn obj-center block button deleteButton' type='submit' name='removeDisplayPhoto'>Remove Photo</button>
                                    </form>
                                    ";
                                }
                                else
                                {
                                    echo "
                                    <form action='../db/db_candidates.php?partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']."' method='post' enctype='multipart/form-data'>
                                        <input class='file block obj-center input-lrn' style='height:60px;width:300px' type='file' name='file'>
                                        <span class='span-8px'></span>
                                        <button style='width:300px' class='input-lrn obj-center  block button' type='submit' name='addDisplayPhoto'>Add Photo</button>
                                    </form>
                                    ";
                                }
                            }
                            else if (isset($_GET['partyListId']))
                            {
                                // anti-backdoor protection
                                if($adminLevel < 2 || $showResults == 1 || $voteOpen == 1)
                                {
                                    header("Location: voterslist.php?accessDenied");
                                    exit();
                                }
                                // check if exists
                                $sql = "SELECT * FROM `partyList` WHERE `partyListId` = ?";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: ../admin/candidates.php?error=SQL&task=fetchPartyData");
                                    exit();
                                }
                                else
                                {
                                    mysqli_stmt_bind_param($stmt, "i", $_GET['partyListId']);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    if ($row = mysqli_fetch_assoc($result))
                                    {
                                        $partyListNameMatch = $row['partyListName'];
                                        $partyListAbbrMatch = $row['partyListAbbr'];
                                    }
                                }
                                if(empty($partyListNameMatch))
                                {
                                    header("Location: ../admin/candidates.php");
                                    exit();
                                }
                                echo "
                                <form action='../db/db_candidates.php?partyListId=".$_GET['partyListId']."' method='post'>";
                                    require "form-content/partyListForm.php";
                                    echo "
                                    <span class='span-8px'></span>
                                    <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='modifyPartyList'>Modify Party</button>
                                </form>
                                "; 
                            }
                            else if (isset($_GET['adminId']))
                            {
                                // anti-backdoor protection
                                if($adminLevel < 3)
                                {
                                    header("Location: voterslist.php?accessDenied");
                                    exit();
                                }
                                $username = NULL;
                                $adminLevel = NULL;
                                // check if exists
                                $sql = "SELECT * FROM `adminAccounts` WHERE `adminId` = ?";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: ../admin/settings.php?error=SQL&task=fetchAdminData");
                                    exit();
                                }
                                else
                                {
                                    mysqli_stmt_bind_param($stmt, "i", $_GET['adminId']);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    if ($row = mysqli_fetch_assoc($result))
                                    {
                                        $username = $row['username'];
                                        $adminLevel = $row['adminLevel'];
                                    }
                                }
                                if(empty($adminLevel))
                                {
                                    header("Location: ../admin/candidates.php");
                                    exit();
                                }
                                echo "
                                    <form action='../db/db_admin.php?adminId=".$_GET['adminId']."' method='post'>
                                        <p>You're modifying details for '".$username."'</p>
                                        <span class='span-4px'></span>
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
                                        <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='changeAdminLevel'>Modify Details</button>
                                    </form>
                                ";
                            }
                            else if (isset($_GET['changePassword']))
                            {
                                echo "
                                <form action='../db/db_admin.php' method='post'>
                                    <p>Enter Current Password:</p>
                                    <span class='span-4px'></span>
                                    <input class='block obj-center input-lrn' style='width:300px' type='password' name='passcode' placeholder='Enter Current Password'>
                                    <span class='span-8px'></span>
                                    <p>Enter New Password:</p>
                                    <span class='span-4px'></span>
                                    <input class='block obj-center input-lrn' style='width:300px' type='password' name='password' placeholder='New Password'>
                                    <span class='span-8px'></span>
                                    <input class='block obj-center input-lrn' style='width:300px' type='password' name='repeatPassword' placeholder='Repeat New Password'>
                                    <span class='span-8px'></span>
                                    <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='changePassword'>Change Password</button>
                                </form>
                                ";
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>