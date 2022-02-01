<?php
$admin = 1; // to change directory level for db check
require "../db/db_checker.php"; // check if database exists
session_start();
// remove token
$_SESSION['modifyToken'] = 0;
$showResults = 0;
// check if admin logged in
if(empty($_SESSION['adminId']))
{
    require "index/login.php";
    exit();
}

$_SESSION['adminSign'][1] = NULL;
$_SESSION['adminSign'][2] = NULL;
$_SESSION['adminSign'][3] = NULL;
require "../db/db_connection_general.php";

$adminLevel = $_SESSION['adminLevel'];

// check voteOpen (if 1, disable critical features)
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'voteOpen'";
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
    $voteOpenToggle = $row['settingValue'];
}

$showAddAdmin = 0;
$showModifyOrganization = 0;
$showModifyVotingMethod = 0;

for($checkSetting = 1; $checkSetting <= 4; $checkSetting++)
{
    switch($checkSetting)
    {
        case 1: $settingName = "votingMethod";break;
        case 2: $settingName = "school";break;
        case 3: $settingName = "organizationName";break;
        case 4: $settingName = "electionLogo";break;
    }

    $sql = "SELECT * FROM `operationSettings` WHERE `settingName` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        echo "Coding error.";
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "s", $settingName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        switch($checkSetting)
        {
            case 1: $currentVotingMethod = $row['settingValue'];break;
            case 2: $school = $row['settingValue'];break;
            case 3: $organization = $row['settingValue'];break;
            case 4: $electionLogo = $row['settingValue'];break;
        }
    }
}
if($electionLogo == NULL)
{
    $electionLogo = "../assets/img/schoolLogo.png";
}

// only if voting not opened

// change name of school
// change name of organization
// change voting method
// change logo

// year is based on voting method

// year+1 for ssg general
// same year for the rest of voting methods

?>

<html>
    <head>
        <title>Admin Panel</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
        <style>
            .vertical-center {
                width:100%;
                height:calc(100vh - 72px);
                padding-top:72px;
            }
        </style>
    </head>
    <body class='block' id='vote' <?php 
    if(isset($_GET['voteOpen']) && $_GET['voteOpen'] == 0)
    {
        echo "onload='print()'";
    }
    ?>>
        <?php require 'index/header.php';?>
        <div id='newAdminSettings' class='block'>
            <div id='mainContentSettings'>
            <?php
                // load vote open module (at least level 3)
                if($adminLevel >= 3)
                {
                    echo "<h2 id='admin' style='font-size:32px'>Voting System</h2>
                    <span class='span-16px'></span>";
                    $votingSystemErrorCount = 0;
                    // check if at least a section exist
                    $sql = "SELECT * FROM `sectionsList`";
                    $stmt = mysqli_stmt_init($conn);
                    if(!mysqli_stmt_prepare($stmt, $sql))
                    {
                        header("Location: challenge.php?error=SQL&task=checkSectionExist");
                        exit();
                    }
                    else
                    {
                        $sectionCount = 0;
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        while ($row = mysqli_fetch_assoc($result))
                        {
                            $sectionCount++;
                            $sql2 = "SELECT * FROM `votersList` WHERE `sectionId` = ?";
                            $stmt2 = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt2, $sql2))
                            {
                                header("Location: challenge.php?error=SQL&task=checkSectionStudents");
                                exit();
                            }
                            else
                            {
                                $voterCount = 0;
                                mysqli_stmt_bind_param($stmt2, "i", $row['sectionId']);
                                mysqli_stmt_execute($stmt2);
                                $result2 = mysqli_stmt_get_result($stmt2);
                                while($row2 = mysqli_fetch_assoc($result2))
                                {
                                    $voterCount++;
                                }
                                if($voterCount == 0)
                                {
                                    echo "<p>ERROR: Section ".$row['sectionName']." of Grade ". $row['gradeLevel'] . " doesn't have any students. Add at least one or delete section completely.</p><br>";
                                    $votingSystemErrorCount++;
                                }
                            }
                        }
                        if ($sectionCount == 0)
                        {
                            echo "<p>ERROR: At least 1 section required with at least 1 student for voting system to work.</p><br>";
                            $votingSystemErrorCount++;
                        }
                    }
                    // check if there's at least one candidate
                    $sql = "SELECT * FROM `candidatesList`";
                    $stmt = mysqli_stmt_init($conn);
                    if(!mysqli_stmt_prepare($stmt, $sql))
                    {
                        header("Location: challenge.php?error=SQL&task=checkCandidatesList");
                        exit();
                    }
                    else
                    {
                        $candidateCount = 0;
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_Get_Result($stmt);
                        while ($row = mysqli_fetch_assoc($result))
                        {
                            $candidateCount++;
                        }
                        if ($candidateCount == 0)
                        {
                            echo "<p>ERROR: At least 1 candidate required for voting system to work.</p><br>";
                            $votingSystemErrorCount++;
                        }
                    }
                    if($votingSystemErrorCount == 0)
                    {
                        // settingValue (voteOpen) used at the very top
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
                        if(($showResults == 0 && $voteOpenToggle == 0) || ($showResults == 1 && $voteOpenToggle == 1))
                        {
                            if($voteOpenToggle == 0)
                            {
                                echo "
                                <p>When opening the voting proper, make sure to agree on the following restrictions:<br><span class='span-8px'></span>
                                * Disabled Creation/Modification/Deletion of Party<br>
                                * Disabled Creation/Modification/Deletion of Candidate<br>
                                * Disabled Creation/Modification/Deletion of Section<br>
                                <span class='span-16px'></span>
                                You are only allowed to do the following (but requires 2 admins to enter their passwords):<br>
                                <span class='span-8px'></span>
                                * Ability to Delete unvoted students<br>
                                * Ability to Add students (in case of mistakenly typed LRN throughout encoding)<br>
                                <span class='span-16px'></span>
                                This action cannot be undone. If everything is ready, have the other 2 admins with you to sign your respective passwords to officially open the election.
                                </p>
                                ";
                            }
                            else if ($voteOpenToggle == 1)
                            {
                                echo "
                                <p>If everyone has voted or exceeded the time allotted for election, you're free to close the voting proper.<br><span class='span-16px'></span>
                                This action cannot be undone. If everything is settled out, have the other 2 admins with you to sign your respective passwords to officially close the election.
                                </p>
                                ";
                            }
                            echo "<span class='span-16px'></span>
                            <form action='challenge.php?voteOpen=".($voteOpenToggle == 0 ? 1 : 0)."&admin=1' method='post'>
                                <button class='input-lrn block button' style='width:300px' type='submit' name='setting'>".($voteOpenToggle == 0 ? "Vote Open" : "Vote Close")."</button>
                            </form>
                            ";
                        }
                        else if ($showResults == 1 && $voteOpenToggle == 0)
                        {
                            echo "
                                <p>Voting System is officially closed.</p>
                                <span class='span-16px'></span>
                                <form action='results.php' method='post'>
                                <button class='input-lrn block button' style='width:300px' type='submit'>See Results</button>
                                </form>
                                <span class='span-8px'></span>
                                <button onclick='print()' class='input-lrn block button' style='width:300px' type='submit'>Print Results</button>
                                <iframe style='display:none' id='printpreview' name='printpreview' src='results3.php'>
                                </iframe>
                                ";
                        }
                        
                    }
                    echo "<span class='span-16px'></span><span class='span-16px'></span>";
                }
                // load admin management system
                if($adminLevel >= 1)
                {
                    echo "
                    <h2 style='font-size:32px'>Admins</h2>
                    <div class='grid' id='sub-nav'>
                        <p style='margin-top:10px'>Showing all existing admins.</p>
                        <span></span>
                        <span></span>";
                        if($voteOpenToggle == 0)
                        {
                            $showAddAdmin = 1;
                            echo "
                            <a><p class='input-lrn obj-center block button' style='width:auto;text-align:center;' onclick='addAdmin()'>Add Admin</p></a>";
                        }
                        echo"
                    </div>
                    <span class='span-16px'></span>
                    ";
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
                    //show
                    if($_SESSION['adminId'] == 1)
                    {
                        $adminLevelCtr = 4;
                    }
                    else
                    {
                        $adminLevelCtr = 3;
                    }
                    for($adminLevelCtr; $adminLevelCtr > 0; $adminLevelCtr--)
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
                                $highlight = 0;
                                if($_SESSION['adminId'] == $row['adminId'])
                                {
                                    $highlight = 1;
                                }
                                echo "
                                <div class='block admin-block ".($highlight == 1 ? "highlightCandidate" : "")."'>
                                    <div class='sub-nav-admins grid'>
                                        <p style='font-size:24px;font-weight:700;margin-top:6px;'>".$row['username']." (".$row['adminLevel'].")
                                        ";
                                        if($adminAccountsCount <= 3)
                                        {
                                            echo "<p> </p>";
                                        }
                                        // modify
                                        if($voteOpenToggle == 1 && $_SESSION['adminId'] != $row['adminId'] && $adminAccountsCount > 3)
                                        {
                                            echo "<p> </p>";
                                        }
                                        if($_SESSION['adminId'] == $row['adminId'])
                                        {
                                            if($adminAccountsCount > 3)
                                            {
                                                echo "<p> </p>";
                                            }
                                            echo "
                                            <a><p class='input-lrn obj-center block button' style='width:190px;text-align:center;' onclick='changePassword()'>Change Password</p></a>";
                                        }
                                        else if ($_SESSION['adminLevel'] >= 3)
                                        {
                                            echo "
                                            <form action='modify.php?adminId=".$row['adminId']."' method='post'>
                                                <button class='input-lrn obj-center block button' style='text-align:center;width:190px' type='submit' name='modify'>Modify Account</button>
                                            </form>";
                                        }
                                        // delete
                                        if($_SESSION['adminId'] != $row['adminId'] && $adminLevel >= 3 && $adminAccountsCount > 3 && $voteOpenToggle != 1)
                                        {
                                            echo "
                                            <a href='challenge.php?adminId=".$row['adminId']."'><p class='input-lrn obj-center block button' style='width:190px;text-align:center;'>Delete Account</p></a>";
                                        }
                                    echo "
                                    </div>
                                </div>";
                            }
                        }
                    }
                }
                // load general info
                if($adminLevel >= 3)
                {   
                    // reworked settings general info
                    echo "<span class='span-16px'></span>
                    <div style='display:grid;grid-template-columns:256px auto;grid-gap:32px'>
                        <div id='electionLogo'>
                            <h2 style='font-size:32px;text-align:center'>Election Logo</h2>
                            <span class='span-16px'></span>
                            <img style='width:256px;height:256px' src='../".$electionLogo."'>
                            <span class='span-16px'></span>
                            ";
                            if($voteOpenToggle == 0)
                            {
                                $showModifyElectionLogo = 1;
                                
                                // list of errors
                                if(isset($_GET['error']))
                                {
                                    echo "<p class='subWindowError'>";
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
                
                                echo "
                                <form action='../db/db_admin.php' method='post' enctype='multipart/form-data'>
                                    <input class='file block obj-center input-lrn' style='height:60px;width:256px' type='file' name='file'>
                                    <span class='span-8px'></span>
                                    <button style='width:256px;text-align:center;' class='input-lrn obj-center block button' type='submit' name='modifyElectionLogo'>Change Logo</button>
                                </form>";
                                if($electionLogo != "assets/img/schoolLogo.png")
                                {
                                    echo "<span class='span-8px'></span>
                                    <form action='../db/db_admin.php' method='post'>
                                    <button style='width:256px;text-align:center;background-color:#444!important' class='input-lrn obj-center block button' type='submit' name='defaultElectionLogo'>Use Default</button>
                                    </form>";
                                }
                            }
                            echo "
                        </div>
                        <div id='otherGenInfo'>
                            <h2 style='font-size:32px'>General Information:</h2>
                            <span class='span-16px'></span>
                            <div style='display:grid;grid-template-columns:auto 200px;grid-gap:16px'>
                                <div>
                                    <h2 style='font-size:32px'>School Name</h2>
                                    <p style='margin-top:0px'>".$school." (Pre-installed)</p>
                                </div>
                                ";
                                
                                // removed modify option as it was only pre-installed exclusively for plaridel nhs for now
        
                                echo "
                            </div>
                            <span class='span-16px'></span>
                            ";
        
                            // organization
                            echo "<span class='span-16px'></span>
                            
                            <div style='display:grid;grid-template-columns:auto 200px;grid-gap:16px'>
                                <div>
                                    <h2 style='font-size:32px'>Organization Name</h2>
                                    <p style='margin-top:0px'>".$organization."</p>
                                </div>";
                                
                                if($voteOpenToggle == 0)
                                {
                                    $showModifyOrganization = 1;
                                    echo "
                                    <a><p class='input-lrn obj-center block button' style='width:auto;text-align:center;' onclick='modifyOrganization()'>Rename</p></a>";
                                }
                                echo "
                                </div>
                            <span class='span-16px'></span>
                            <span class='span-16px'></span>";
                            

                            // load addt setting change if ever wala mn char (voting method)
                            echo "
                            
                            <div style='display:grid;grid-template-columns:auto 200px;grid-gap:16px'>
                                <div>
                                    <h2 style='font-size:32px'>Voting Method</h2>
                                    <p style='margin-top:0px'>";
                                    
                                    switch($currentVotingMethod)
                                    {
                                    case 1: echo "General Elections (except Grade 7 & 11 Representatives)";
                                    break;
                                    case 2: echo "Grade 7 & 11 Representative Elections";
                                    break;
                                    case 3: echo "Organization/Club Election";
                                    break;
                                    
                                    }
                                    echo "</p>
                                </div>
                                ";
                                if($candidateCount == 0)
                                {
                                    $showModifyVotingMethod = 1;
                                    echo "<a><p class='input-lrn obj-center block button' style='width:auto;text-align:center;' onclick='modifyVotingMethod()'>Modify</p></a>";
                                }
                                echo "                   
                            </div>
                            <span class='span-16px'></span>
                            <span class='span-16px'></span>";    

                            echo "

                            <h2 style='font-size:32px'>Admins Contributed to this Application:</h2>
                            <span class='span-8px'></span>

                            <p>Voting Background Photo (Pre-installed) by</p>
                            <span class='span-8px'></span>
                            <h2 style='font-size:24px'>Sunri💖</h2>
                            <p>Local Digital Artist</p>
                            <span class='span-16px'></span>

                            <p>Web Application Programmed by</p>
                            <span class='span-8px'></span>
                            <h2 style='font-size:24px'>cyboryan</h2>
                            <p><b>Graeme Xyber Pastoril</b> | PNHS Alumnus 2019</p>
                            <span class='span-16px'></span>
                        </div>
                    </div>
                    <span class='span-16px'></span>
                    <span class='span-16px'></span>
                    ";
                }
                if($adminLevel < 3)
                {
                    echo "
                    <span class='span-16px'></span>
                    <h2 style='font-size:32px'>Admins Contributed to this Application:</h2>
                    <span class='span-8px'></span>

                    <p>Voting Background Photo (Pre-installed) by</p>
                    <span class='span-8px'></span>
                    <h2 style='font-size:24px'>Czejan Rae Tabaranza</h2>
                    <p>11 - Algorithmians @ PNHS</p>
                    <span class='span-16px'></span>

                    <p>Web Application Programmed by</p>
                    <span class='span-8px'></span>
                    <h2 style='font-size:24px'>Graeme Xyber Pastoril</h2>
                    <p>BSCS-1 @ VSU | PNHS Alumnus 2019</p>
                    <span class='span-16px'></span>
                    ";
                }

                ?>
                

                <!--<p>Special Thanks to</p>
                <span class='span-8px'></span>
                <h2 style='font-size:24px'>Adriano Pelicano</h2>
                <p>Head Teacher IV | School Head</p>
                <span class='span-8px'></span>
                <h2 style='font-size:24px'>Renato Nunez</h2>
                <p>ComLab - 1 Operator</p>
                <span class='span-8px'></span>
                <h2 style='font-size:24px'>Jose Alfred Paculanang</h2>
                <p>SSG Adviser</p>
                <span class='span-8px'></span>
                <h2 style='font-size:24px'>SSG and SSG COMELEC Batch 2019-2020</h2>
                <span class='span-8px'></span>
                <h2 style='font-size:24px'>ArmsTech Team</h2>-->
                <span class='span-16px'></span>
                <span class='span-16px'></span>
                <?php
                    if($voteOpenToggle == 0 && $showResults == 1)
                    {
                        echo "
                        <h2 style='font-size:32px'>Done Election? Time to reset things!</h2>
                        <span class='span-8px'></span>
                        <form action='challenge.php?resetApplication=1&admin=1' method='post'>
                            <p>You will choose which information to delete/retain. General Information will remain and can be changed afterwards in this page.</p>
                            <span class='span-16px'></span>
                            <button class='input-lrn block button deleteButton' style='width:300px' type='submit' name='reset'>Reset Application</button>
                        </form>
                        <span class='span-16px'></span>
                        ";
                    }
                ?>
                <p style="font-size:12px;"><?php echo "ElectSys © Arms, 2019 -" . " " . date("Y"); ?></p>
            </div>
        </div>
        <div id="changePassword" class="page-sub-window" style="display:none">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>Change Password</p>
                        <img src="../assets/img/close.png" onclick="changePassword()">
                    </div>
                    <span class="span-16px"></span>
                    <form action="../db/db_admin.php" method="post">
                        <p>Enter Current Password:</p>
                        <span class='span-4px'></span>
                        <input class='block obj-center input-lrn' style="width:300px" type="password" name="passcode" placeholder="Enter Current Password">
                        <span class='span-8px'></span>
                        <p>Enter New Password:</p>
                        <span class='span-4px'></span>
                        <input class='block obj-center input-lrn' style="width:300px" type="password" name="password" placeholder="New Password">
                        <span class='span-8px'></span>
                        <input class='block obj-center input-lrn' style="width:300px" type="password" name="repeatPassword" placeholder="Repeat New Password">
                        <span class='span-8px'></span>
                        <button class='input-lrn obj-center  block button' style="width:300px" type="submit" name="changePassword">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- window -->
        <?php 
        if($showModifyVotingMethod == 1)
        {
        ?>
        <div id="modifyVotingMethod" class="page-sub-window" style="display:none">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>Modify Voting Method</p>
                        <img src="../assets/img/close.png" onclick="modifyVotingMethod()">
                    </div>
                    <span class="span-16px"></span>
                    <form action='../db/db_admin.php' method='post'>
                        <select class='block obj-center input-lrn' style="width:300px;height:32px;" name="votingMethod">
                            <option value='1' <?php $currentVotingMethod == 1 ? "selected='selected'" : "" ?> >SSG General Elections (except Grade 7 & Grade 11 Reps.)</option>
                            <option value='2' <?php $currentVotingMethod == 2 ? "selected='selected'" : "" ?> >SSG Gr. 7 & 11 Representative Elections</option>
                            <option value='3' <?php $currentVotingMethod == 3 ? "selected='selected'" : "" ?> >Organization/Club Election (All Positions)</option>
                        </select>
                        <span class='span-8px'></span>
                        <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='modifyVotingMethod'>Modify Voting Method</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        }
        if($showModifyOrganization == 1)
        {
        ?>
        <div id="modifyOrganization" class="page-sub-window" style="display:none">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>Rename Organization</p>
                        <img src="../assets/img/close.png" onclick="modifyOrganization()">
                    </div>
                    <span class="span-16px"></span>
                    <form action='../db/db_admin.php' method='post'>
                        <input class='block obj-center input-lrn' style='width:300px' type='text' name='organization' placeholder='Organization' value='<?php echo $organization; ?>'>
                        <span class='span-8px'></span>
                        <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='modifyOrganization'>Rename Organization</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        }
        if($showAddAdmin == 1)
        {
        ?>
        <div id="addAdmin" class="page-sub-window" style="display:none">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>Add Admin</p>
                        <img src="../assets/img/close.png" onclick="addAdmin()">
                    </div>
                    <span class="span-16px"></span>
                    <form action="../db/db_admin.php" method="post">
                        <input class='block obj-center input-lrn' style="width:300px" type="text" name="username" placeholder="Username">
                        <span class='span-8px'></span>
                        <input class='block obj-center input-lrn' style="width:300px" type="password" name="password" placeholder="Password">
                        <span class='span-8px'></span>
                        <input class='block obj-center input-lrn' style="width:300px" type="password" name="repeatPassword" placeholder="Repeat Password">
                        <span class='span-8px'></span>
                        <select class='block obj-center input-lrn' style="width:300px;height:32px;"name="adminLevel">
                            <option value='3'>Level 3</option>
                            <option value='2' selected='selected'>Level 2</option>
                            <option value='1'>Level 1</option>
                        </select>
                        <span class='span-16px'></span>
                        <p>For security purposes, please let the currently logged admin enter his/her password:</p>
                        <span class='span-16px'></span>
                        <input class='block obj-center input-lrn' style="width:300px" type="password" name="passcode" placeholder="Current Admin Password">
                        <span class='span-8px'></span>
                        <button class='input-lrn obj-center  block button' style="width:300px" type="submit" name="addAdminSettings">Add Admin</button>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
        <script>
            function addAdmin() {
                if (document.getElementById('addAdmin').style.display === 'none') {  
                    document.getElementById('addAdmin').style.display = 'table';
                }
                else {
                    document.getElementById('addAdmin').style.display = 'none';
                }
            }
            function changePassword() {
                if (document.getElementById('changePassword').style.display === 'none') {  
                    document.getElementById('changePassword').style.display = 'table';
                }
                else {
                    document.getElementById('changePassword').style.display = 'none';
                }
            }
            
            function modifyOrganization() {
                if (document.getElementById('modifyOrganization').style.display === 'none') {  
                    document.getElementById('modifyOrganization').style.display = 'table';
                }
                else {
                    document.getElementById('modifyOrganization').style.display = 'none';
                }
            }
            function modifyVotingMethod() {
                if (document.getElementById('modifyVotingMethod').style.display === 'none') {  
                    document.getElementById('modifyVotingMethod').style.display = 'table';
                }
                else {
                    document.getElementById('modifyVotingMethod').style.display = 'none';
                }
            }
            function print() {
            window.frames["printpreview"].focus();
            window.frames["printpreview"].print();
            
        }
        </script>
    </body>
</html>