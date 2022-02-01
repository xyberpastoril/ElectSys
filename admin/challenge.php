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
// check voteOpen
$adminLevel = $_SESSION['adminLevel'];
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
    $voteOpenToggle = $row['settingValue'];
}
// check showResults (if 1 and closed, can't open until reset)
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

// for account deletion process only
if(isset($_GET['adminId']))
{
    // count
    $adminAccountsCount = 0;
    for($adminLevelCtr = 3; $adminLevelCtr > 0; $adminLevelCtr--)
    {
        $sql = "SELECT * FROM `adminAccounts` WHERE `adminLevel` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: settings.php?error=SQL&task=loadAdminLevels");
            exit();
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "i", $adminLevelCtr);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result))
            {
                $adminAccountsCount++;
            }
        }
    }
    if($adminAccountsCount <= 3 || $adminLevel < 3 || $voteOpen == 1)
    {
        header("Location: settings.php?accessdenied");
        exit();
    }
    // get account name
    $sql = "SELECT * FROM `adminAccounts` WHERE `adminId` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: settings.php?error=SQL&task=getAccountDeletedName");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "i", $_GET['adminId']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $deleteUsername = $row['username'];
    }
}

if(isset($_POST['delete']) || (isset($_GET['error']) != NULL))
{
    if(isset($_GET['candidateId']))
    {
        if($adminLevel < 2 || $showResults == 1 || $voteOpen == 1)
        {
            header("Location: candidates.php?accessDenied");
            exit();
        }
        $formTop = "<form action='../db/db_candidates.php?candidateId=".$_GET['candidateId']."' method='post'>";
        $formBottom = "<button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='deleteCandidate'>Delete Candidate</button></form>";
    }
    else if (isset($_GET['partyListId']))
    {
        if($adminLevel < 2 || $showResults == 1 || $voteOpen == 1)
        {
            header("Location: candidates.php?accessDenied");
            exit();
        }
        $formTop = "<form action='../db/db_candidates.php?partyListId=".$_GET['partyListId']."' method='post'>";
        $formBottom = "<button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='deletePartyList'>Delete Partylist</button></form><span class='span-8px'></span><p class='smallWarning-subwindow'>Deleting a party won't delete candidates associated with it but instead become independent.</p>";
    }
    else if (isset($_GET['sectionId']) && (!isset($_GET['LRN'])))
    {
        if($showResults == 1 || $voteOpen == 1)
        {
            header("Location: voterslist.php?accessDenied");
            exit();
        }
        $formTop = "<form action='../db/db_voterslist.php?sectionId=".$_GET['sectionId']."' method='post'>";
        $formBottom = "<button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='deleteSection'>Delete</button></form><span class='span-8px'></span><p class='smallWarning-subwindow'>Deleting a section also includes all LRNS within it.</p>";
    }
    else if (isset($_GET['LRN']))
    {
        $formTop = "<form action='../db/db_voterslist.php?admin=1&sectionId=".$_GET['sectionId']."&LRN=".$_GET['LRN']."' method='post'>
        <p class='smallWarning-subwindow'>You are about to delete LRN '".$_GET['LRN']."'</p><span class='span-8px'></span>";
        $formBottom = "<button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='deleteStudent'>Delete</button></form>";
    }
}
if(isset($_GET['voteOpen']))
{
    if($adminLevel < 3)
    {
        header("Location: index.php?accessDenied");
        exit();
    }
    if($showResults == 1 && $voteOpen == 0)
    {
        header("Location: settings.php?accessDenied");
        exit();
    }

    $formTop = "<form action='../db/db_admin.php?voteOpen=".$_GET['voteOpen']."&admin=".$_GET['admin']."' method='post'><p>Admin ".$_GET['admin']." of 3</p><span class='span-16px'></span>";
    $formBottom = "<button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='voteOpen'>".($_GET['voteOpen'] == 1 ? "Sign Election Open" : "Sign Election Close")."</button>" . ($_GET['voteOpen'] == 1 ? "<span class='span-8px'></span><p class='smallWarning-subwindow'>This disables you the ability to modify/delete sections, partylists, candidates and voted students. This can only be done once and can't be reopened once it's closed. Think twice.</p>" : "<span class='span-8px'></span><p class='smallWarning-subwindow'>Voting can't be reopened once it's closed. Think twice.</p>");
}
else if (isset($_GET['adminId']))
{
    if($adminLevel < 3 || $voteOpen == 1)
    {
        header("Location: settings.php?accessDenied");
        exit();
    }
    if(($_GET['adminId'] == $_SESSION['adminId'] || $_GET['adminId'] == 1) || $adminLevel < 3 || $voteOpen == 1)
    {
        header("Location: index.php?accessDenied");
        exit();
    }
    $formTop = "<form action='../db/db_admin.php?adminId=".$_GET['adminId']."' method='post'>";
    $formBottom = "<button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='deleteAdmin'>Delete</button></form>";
}
if(isset($_POST['reset']))
{
    echo "";
}
?>

<html>
    <head>
        <title>Challenge</title>
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
                        <p>Challenge</p>
                        <img src="../assets/img/close.png" onclick="<?php
                        if(isset($_GET['candidateId']))
                        {
                            echo "location.href='candidates.php'";
                        }
                        else if (isset($_GET['partyListId']))
                        {
                            echo "location.href='candidates.php?partyListId=".$_GET['partyListId']."'";
                        }
                        else if (isset($_GET['sectionId']))
                        {
                            echo "location.href='voterslist.php?sectionId=".$_GET['sectionId']."'";
                        }
                        else if (isset($_GET['voteOpen']))
                        {
                            echo "location.href='settings.php?'";
                        }
                        else if (isset($_GET['adminId']))
                        {
                            echo "location.href='settings.php?'";
                        }
                        ?>">
                    </div>
                    <span class="span-4px"></span>
                    <p>This action cannot be undone.</p>
                    <?php
                    // list of errors
                    if(isset($_GET['error']))
                    {
                        echo "<span class='span-16px'></span><p class='subWindowError'>";
                        switch($_GET['error'])
                        {
                            case "invalidPassword":
                                echo "Invalid Username or Password";
                            break;
                            case "invalidAdminPassword":
                                echo "Invalid Admin Password";
                            break;
                            case "invalidSelectedAdminPassword":
                                echo "Invalid Selected Admin Password";
                            break;
                            case "adminAlreadySigned":
                                echo "Admin Already Signed";
                            break;
                        }
                        echo "</p>";
                    }
                    ?>
                    <span class="span-16px"></span>
                    <?php
                        $_SESSION['adminSign'][3] = array("","","");
                        if(isset($_GET['voteOpen']))
                        {
                            echo  
                            $formTop . "
                            <input class='block obj-center input-lrn' style='width:300px' type='text' name='username' placeholder='Username'><span class='span-8px'></span>
                            <input class='block obj-center input-lrn' style='width:300px' type='password' name='passcode' placeholder='Enter password to confirm'><span class='span-8px'></span>"
                            . $formBottom;
                        }
                        else if (isset($_GET['adminId']))
                        {
                            echo
                            $formTop . "
                                <p>You are about to delete '".$deleteUsername."' administrative account. Enter your password to continue.</p>
                                <span class='span-8px'></span>
                                <input class='block obj-center input-lrn' style='width:300px' type='password' name='passcode' placeholder='Enter password to confirm'><span class='span-8px'></span>
                                <span class='span-8px'></span>
                            " . $formBottom;
                        }
                        else if ( isset($_POST['reset']) || ( isset($_GET['error']) && isset($_GET['resetApplication']) ) )
                        {
                            echo "
                            <form action='../db/db_admin.php' method='post'>
                                <p>Select data to be deleted.</p>
                                <span class='span-8px'></span>
                                <input id='checkbox1' type='checkbox' name='truncateData[]' value='candidates' checked='checked' disabled><label for='checkbox1'>Candidates & Vote Results</label><br>
                                <input id='checkbox4' type='checkbox' name='truncateData[]' value='partylists'><label for='checkbox4'>Partylists</label><br>
                                <input id='checkbox2' type='checkbox' name='truncateData[]' value='voters'><label for='checkbox2'>Voters</label><br>
                                <span class='span-8px'></span>
                                <input class='block obj-center input-lrn' style='width:300px' type='password' name='passcode' placeholder='Enter password to confirm'><span class='span-8px'></span>
                                <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='resetApp'>Reset Application</button>
                            </form>
                            ";
                            
                        }
                        else
                        {
                            echo  
                            $formTop . "
                            <input class='block obj-center input-lrn' style='width:300px' type='password' name='passcode' placeholder='Enter password to confirm'><span class='span-8px'></span>"
                            . $formBottom;
                        }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>