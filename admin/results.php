<?php
session_start();
$admin = 1; // to change directory level for db check
require "../db/db_checker.php"; // check if database exists
// remove token
$_SESSION['modifyToken'] = 0;
require "../db/db_connection_general.php";
// check if admin logged in
if(empty($_SESSION['adminId']))
{
    require "index/login.php";
    exit();
}
$view = "";
$viewlevel = "";
$tallyColumn = 0;

// check vote method
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'votingMethod'";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql))
{
    header("Location: candidateInfo.php?error=SQL&task=checkVotingMethod");
    exit();
}
else
{
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $currentVotingMethod = $row['settingValue'];
}

if(isset($_GET['view']))
{
    switch($_GET['view'])
    {
    case "overall": $view = "overall";$tallyColumn = 1;break;
    case "level": 
        switch($currentVotingMethod)
        {
        case 1:
        case 3:
            $view = "level";$tallyColumn = 6;break;
        break;
        
        case 2:
            $view = "level";$tallyColumn = 2;break;
        break;
        }
    break;
    case "section": 
        $view = "section";
        if(isset($_GET['level']))
        {
            switch($_GET['level'])
            {
                case 7: $viewlevel = 7;break;
                case 8: $viewlevel = 8;break;
                case 9: $viewlevel = 9;break;
                case 10: $viewlevel = 10;break;
                case 11: $viewlevel = 11;break;
                case 12: $viewlevel = 12;break;
                default: $viewlevel = 7;break;
            }
            // get tally columns:
            $sql = "SELECT * FROM `sectionsList` WHERE `gradeLevel` = ?";
            $stmt = mysqli_stmt_init($conn);
            if(!mysqli_stmt_prepare($stmt, $sql))
            {
                header("Location: results.php?error=SQL&task=getTallyColumns");
                exit();
            }
            else
            {
                mysqli_stmt_bind_param($stmt, "i", $viewlevel);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result))
                {
                    $tallyColumn++;
                }
            }
        }
        else
        {
            $viewlevel = 7;
            // get tally columns:
            $sql = "SELECT * FROM `sectionsList` WHERE `gradeLevel` = ?";
            $stmt = mysqli_stmt_init($conn);
            if(!mysqli_stmt_prepare($stmt, $sql))
            {
                header("Location: results.php?error=SQL&task=getTallyColumns");
                exit();
            }
            else
            {
                mysqli_stmt_bind_param($stmt, "i", $viewlevel);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result))
                {
                    $tallyColumn++;
                }
            }
        }
        break;
    default:$view = "overall";$tallyColumn = 1;break;
    }
}
else
{
    $tallyColumn = 1;
    $view = "overall";
}
// get number of voters (overall and level)
{
    $votedNo = 1;
    switch($view)
    {
    case "section":
        $votedCasted = 0;
        $totalVoters = 0;
        $votedNo = 1;
        $sql = "SELECT * FROM `sectionsList` WHERE `gradeLevel` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            echo "error sql task get sections list for getting number of voters";
            exit();
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "i", $viewlevel);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result))
            {
                $sectionId = $row['sectionId'];
                $sql2 = "SELECT * FROM `votersList` WHERE `sectionId` = ? AND `voted` = ?";
                $stmt2 = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt2, $sql2))
                {
                    echo "error sql task get number of voters (casted) from sectionId";
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt2, "ii", $sectionId, $votedNo);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_store_result($stmt2);
                    $votedCasted = $votedCasted + mysqli_stmt_num_rows($stmt2);
                }
                $sql2 = "SELECT * FROM `votersList` WHERE `sectionId` = ?";
                $stmt2 = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt2, $sql2))
                {
                    echo "error sql task get number of voters from sectionId";
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt2, "i", $sectionId);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_store_result($stmt2);
                    $totalVoters = $totalVoters + mysqli_stmt_num_rows($stmt2);
                }
            }
        }
    break;

    case "level":
    case "overall":
    default:
        // votes casted
        $sql = "SELECT * FROM `votersList` WHERE `voted` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            echo "error sql task get votes casted";
            exit();
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "i", $votedNo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $votedCasted = mysqli_stmt_num_rows($stmt);
        }
        // total voters
        $sql = "SELECT * FROM `votersList`";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            echo "error sql task get total voters";
            exit();
        }
        else
        {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $totalVoters = mysqli_stmt_num_rows($stmt);
        }
    break;
    }
}

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

?>
<html>
    <head>
        <title>Results</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
        <?php
            echo "
            <style>
                .vertical-center {
                    width:100%;
                    height:calc(100vh - 72px);
                    padding-top:72px;
                }

                .candidateResultsBlock {
                    display:block;
                    width:auto;
                    height:auto;
                    background-color:#444;
                    border-radius:8px;
                    padding:12px;
                    margin-bottom:8px;
                }
                .candidateResults {
                    grid-template-columns:36px 36px auto ";
                    for($tallyColumnsCtr = 0;$tallyColumnsCtr < $tallyColumn;$tallyColumnsCtr++)
                    {
                        echo "80px ";
                    }
                    echo ";
                    grid-gap:16px;
                }
                .candidateResultsBlock .displayPic
                {
                    width:36px;
                    height:36px;
                    border-radius:1000px;
                }
                .candidateResultsBlock .name
                {
                    font-size:26px;
                    font-weight:800;
                }
                .candidateResultsBlock .party
                {
                    font-size:14px;
                    font-weight:500;
                }
                .candidateResultsBlock .number
                {
                    font-size:27px;
                    text-align:center;
                    margin-top:8px;
                }
                .candidatePosition
                {
                    font-size:28px;
                    margin-bottom:16px;
                    margin-top:16px;
                    display:block;
                    font-weight:700;
                }
                .elected {
                    background-color:rgba(4, 138, 69, 0.431)!important;
                }   
            </style>
            ";
        ?>
    </head>
    <body>
        <?php require 'index/header.php';?>
        <div id='newAdmin' class='grid'>
            <div id='sidebar'>
            <?php
            switch($currentVotingMethod)
            {
                case 1:
                case 3:
                    echo "<a href='results.php?view=overall'</a><p class='sectionList'>Overall Results</p></a>";
                    echo "<a href='results.php?view=level'</a><p class='sectionList'>Results by Level</p></a>";
                    echo "<a></a><p class='sectionList' style='background-color:#333!important'>Results by Section of Level</p></a>";
                    echo "<a href='results.php?view=section&level=7'</a><p class='sectionList'>Grade 7</p></a>";
                    echo "<a href='results.php?view=section&level=8'</a><p class='sectionList'>Grade 8</p></a>";
                    echo "<a href='results.php?view=section&level=9'</a><p class='sectionList'>Grade 9</p></a>";
                    echo "<a href='results.php?view=section&level=10'</a><p class='sectionList'>Grade 10</p></a>";
                    echo "<a href='results.php?view=section&level=11'</a><p class='sectionList'>Grade 11</p></a>";
                    echo "<a href='results.php?view=section&level=12'</a><p class='sectionList'>Grade 12</p></a>";
                break;

                case 2:
                    echo "<a href='results.php?view=overall'</a><p class='sectionList'>Overall Results</p></a>";
                    echo "<a href='results.php?view=level'</a><p class='sectionList'>Results by Level</p></a>";
                    echo "<a></a><p class='sectionList' style='background-color:#333!important'>Results by Section of Level</p></a>";
                    echo "<a href='results.php?view=section&level=7'</a><p class='sectionList'>Grade 7</p></a>";
                    echo "<a href='results.php?view=section&level=11'</a><p class='sectionList'>Grade 11</p></a>";
                break;

            }
            ?>
            </div>
            <div id='mainContent'>
                <?php 
                $official = "";
                date_default_timezone_set('Asia/Taipei');
                if($showResults == 1 && $voteOpen == 0)
                {
                    $official = "Official ";
                    $officialTime = "";
                }   
                else
                {
                    $official = "";
                    $officialTime = "as of ".date("h:i:sa")." | ";
                }
                switch($view)
                {
                case "level":
                    echo "<h2>".$official."Results by Grade Level</h2>";
                    echo "<p>".$officialTime."".$votedCasted." out of ".$totalVoters." votes casted</p>";
                break;

                case "section":
                    echo "<h2>".$official."Results for Grade ".$viewlevel."</h2>";
                    echo "<p>".$officialTime."".$votedCasted." out of ".$totalVoters." votes casted</p>";
                break;
                
                case "overall":
                default:
                    echo "<h2>".$official."Total Results</h2>";
                    echo "<p>".$officialTime."".$votedCasted." out of ".$totalVoters." votes casted</p>";
                break;
                }
                ?>
                <span class='span-16px'></span>
                <!-- table header -->
                <div class='candidateResultsBlock'>
                    <div class='candidateResults grid'>
                        <p class='text-center'>Rank</p>
                        <span></span>
                        <p>Name & Party</p>
                        <?php
                        switch($view)
                        {
                            case "section":
                                for($gradeLevel = 7; $gradeLevel <= 12; $gradeLevel++)
                                {
                                    if(($viewlevel == $gradeLevel) || $viewlevel == 0)
                                    {
                                        $sql3 = "SELECT * FROM `sectionsList` WHERE `gradeLevel` = ? ORDER BY `sectionsList`.`sectionName` ASC";
                                        $stmt3 = mysqli_stmt_init($conn);
                                        if(!mysqli_stmt_prepare($stmt3, $sql3))
                                        {
                                            header("Location: ../admin/results.php?error=SQL&task=getSectionIdforHeader");
                                            exit();
                                        }
                                        else
                                        {
                                            mysqli_stmt_bind_param($stmt3, "i", $gradeLevel);
                                            mysqli_stmt_execute($stmt3);
                                            $result3 = mysqli_stmt_get_result($stmt3);
                                            while ($row3 = mysqli_fetch_assoc($result3))
                                            {
                                                $sectionName = $row3['sectionName'];
                                                echo"
                                                <p class='text-center'>".$sectionName."</p>
                                                ";
                                            }
                                        }
                                    }
                                } 
                            break;
                            case "level":
                                switch($currentVotingMethod)
                                {
                                case 1:
                                case 3:
                                    echo "
                                    <p class='text-center'>Grade 7</p>
                                    <p class='text-center'>Grade 8</p>
                                    <p class='text-center'>Grade 9</p>
                                    <p class='text-center'>Grade 10</p>
                                    <p class='text-center'>Grade 11</p>
                                    <p class='text-center'>Grade 12</p>
                                    ";
                                break;

                                case 2:
                                    echo "
                                    <p class='text-center'>Grade 7</p>
                                    <p class='text-center'>Grade 11</p>
                                    ";
                                }
                                
                            break;
                            case "overall":
                            default:
                                echo "<p class='text-center'>Total Votes</p>";
                            break;
                        }
                        ?>
                        
                    </div>
                </div>
                <?php
                for($posNo = 1; $posNo <= 13; $posNo++)
                {
                    $rank = 0;
                    // get position name;
                    $sql = "SELECT * FROM `positionList` WHERE `positionId` = ?";
                    $stmt = mysqli_stmt_init($conn);
                    if(!mysqli_stmt_prepare($stmt, $sql))
                    {
                        header("Location: ../admin/results.php?error=SQL&task=getPositionName");
                        exit();
                    }
                    else
                    {
                        mysqli_stmt_bind_param($stmt, "i", $posNo);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($result);
                        $candidatesPerParty = $row['candidatesPerParty'];
                        if(($row['votingMethod'] == $currentVotingMethod) || $currentVotingMethod == 3)
                        {
                            // check if there are any candidates within
                            $sql4 = "SELECT * FROM `candidatesList` WHERE `position` = ?";
                            $stmt4 = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt4, $sql4))
                            {
                                header("Location: ../admin/results.php?error=SQL&task=checkCandidates");
                                exit();
                            }
                            else
                            {
                                $count = 0;
                                mysqli_stmt_bind_param($stmt4, "i", $posNo);
                                mysqli_stmt_execute($stmt4);
                                $result4 = mysqli_stmt_get_result($stmt4);
                                while($row4 = mysqli_fetch_assoc($result4))
                                {
                                    $count++;
                                }
                                if($count == 0)
                                {
                                    continue; // skip to next position
                                }
                            }
                            echo "<p class='candidatePosition'>".$row['positionName']."</p>";
                            // print candidates
                            $sql = "SELECT * FROM `candidatesList` WHERE `position` = ? ORDER BY `candidatesList`.`totalVotes` DESC";
                            $stmt = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt, $sql))
                            {
                                header("Location: ../admin/results.php?error=SQL&task=fetchCandidateInfo");
                                exit();
                            }
                            else
                            {
                                mysqli_stmt_bind_param($stmt, "i", $posNo);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                while ($row = mysqli_fetch_assoc($result))
                                {
                                    $candidateId = $row['candidateId'];
                                    $firstName = $row['firstName'];
                                    $middleName = $row['middleName'];
                                    $lastName = $row['lastName'];
                                    $displayPic = $row['displayPic'];
                                    if($displayPic == NULL)
                                    {
                                        $displayPic = "assets/img/blank.png";
                                    }
                                    $totalVotes = $row['totalVotes'];
                                    $partyList = $row['partyList'];
                                    if($partyList == 0)
                                    {
                                        $partyListAbbr = "IND";
                                    }
                                    else
                                    {
                                        $sql2 = "SELECT * FROM `partyList` WHERE `partyListId` = ?";
                                        $stmt2 = mysqli_stmt_init($conn);
                                        if(!mysqli_stmt_prepare($stmt2, $sql2))
                                        {
                                            header("Location: ../admin/results.php?error=SQL&task=getPartyName");
                                            exit();
                                        }
                                        else
                                        {
                                            mysqli_stmt_bind_param($stmt2, "i", $partyList);
                                            mysqli_stmt_execute($stmt2);
                                            $result2 = mysqli_stmt_get_result($stmt2);
                                            $row2 = mysqli_fetch_assoc($result2);
                                            $partyListAbbr = $row2['partyListAbbr'];
                                        }
                                    }
                                    // check limit for elected candidates identification (based on rank)
                                    $elected = "";
                                    if($rank < $candidatesPerParty)
                                        $elected = "elected";

                                    // echo first parts of table
                                    echo "
                                    <div class='candidateResultsBlock ".$elected."'>
                                        <div class='candidateResults grid'>
                                            <p style='font-weight:700' class='number'>".++$rank."</p>
                                            <img class='displayPic' src='../".$displayPic."'>
                                            <div>
                                                <p class='name'>".$lastName.", ".$firstName." ".$middleName."</p>
                                                <p class='party'>".$partyListAbbr."</p>
                                            </div>
                                            ";

                                            switch($view)
                                            {
                                                case "level":
                                                case "section":
                                                    $sql2 = "SELECT * FROM `voteResults` WHERE `candidateId` = ?";
                                                    $stmt2 = mysqli_stmt_init($conn);
                                                    if(!mysqli_stmt_prepare($stmt2, $sql2))
                                                    {
                                                        header("Location: ../admin/results.php?error=SQL&task=fetchCandidateResult&candidateId=".$candidateId."&posNo=".$posNo);
                                                        exit();
                                                    }
                                                    else
                                                    {
                                                        mysqli_stmt_bind_param($stmt2, "i", $candidateId);
                                                        mysqli_stmt_execute($stmt2);
                                                        $result2 = mysqli_stmt_get_result($stmt2);
                                                        $row2 = mysqli_fetch_assoc($result2);
                                                        // get results
                                                        $resultTally = array();
                                                        $overallTally = 0;
                                                        for($N=1;$N<=6;$N++) // set offsets
                                                        {
                                                            $resultTally[$N] = 0;
                                                        }
                                                        for($gradeLevel = 7; $gradeLevel <= 12; $gradeLevel++)
                                                        {
                                                            
                                                            
                                                            
                                                            $sql3 = "SELECT * FROM `sectionsList` WHERE `gradeLevel` = ? ORDER BY `sectionsList`.`sectionName` ASC";
                                                            $stmt3 = mysqli_stmt_init($conn);
                                                            if(!mysqli_stmt_prepare($stmt3, $sql3))
                                                            {
                                                                header("Location: ../admin/results.php?error=SQL&task=getSectionIdforResults");
                                                                exit();
                                                            }
                                                            else if ($currentVotingMethod == 1 || $currentVotingMethod == 3 || ($currentVotingMethod == 2 && ($gradeLevel == 7 || $gradeLevel == 11)))
                                                            {
                                                                mysqli_stmt_bind_param($stmt3, "i", $gradeLevel);
                                                                mysqli_stmt_execute($stmt3);
                                                                $result3 = mysqli_stmt_get_result($stmt3);
                                                                while ($row3 = mysqli_fetch_assoc($result3))
                                                                {
                                                                    $sectionId = $row3['sectionId'];
                                                                    $sectionColumn = "section_".$sectionId;
                                                                    $resultTally[$gradeLevel-6] += $row2[$sectionColumn];
                                                                    switch($view)
                                                                    {
                                                                    case "section":
                                                                        if(($viewlevel == $gradeLevel) || $viewlevel == 0)
                                                                        {
                                                                            echo "
                                                                            <p class='number'>".$row2[$sectionColumn]."</p>";
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                                $overallTally += $resultTally[$gradeLevel-6];
                                                                switch($view)
                                                                {
                                                                    case "level":
                                                                        echo "<p class='number'>".$resultTally[$gradeLevel-6]."</p>";
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    echo "";
                                                break;
                                                case "overall":
                                                default:
                                                    echo "<p class='number'>".$totalVotes."</p>";
                                                break;
                                            }
                                            echo "
                                        </div>
                                    </div>
                                    ";
                                }
                            }
                        }
                    }
                }
                echo "<span class='span-16px'></span>";
                ?>
                <!-- sample -->
                <!--<p class='candidatePosition'>President</p>
                <div class='candidateResultsBlock'>
                    <div class='candidateResults grid'>
                        <p style='font-weight:700' class='number'>1</p>
                        <img class='displayPic' src='../assets/img/blank.png'>
                        <div>
                            <p class='name'>Pastoril, Graeme Xyber N.</p>
                            <p class='party'>IND</p>
                        </div>
                        <p class='number'>11</p>
                    </div>
                </div> -->
            </div>
        </div>
    </body>
</html>