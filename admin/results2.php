<?php
$admin = 1; // to change directory level for db check
require "../db/db_checker.php"; // check if database exists
session_start();
// remove token
$_SESSION['modifyToken'] = 0;
// check if admin logged in
if(empty($_SESSION['adminId']))
{
    require "index/login.php";
    exit();
}
$view = "";
$viewlevel = "";
if(isset($_GET['view']))
{
    switch($_GET['view'])
    {
        case "overall": $view = "overall";break;
        case "level": $view = "level";break;
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
                    default: $viewlevel = 0;break;
                }
            }
            break;
    }
}
require "../db/db_connection_general.php";

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

for ($posNo = 1; $posNo <= 13; $posNo++)
{
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
        if(($row['votingMethod'] == $currentVotingMethod) || $currentVotingMethod == 3)
        {
            // check if there are any candidates within
            $sql4 = "SELECT * FROM `candidateslist` WHERE `position` = ?";
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
                    continue; // skip to next
                }
                 
            }

            $posName = $row['positionName'];
            echo "<h3>".$posName."</h3>";
            switch($view)
            {
            case "section":
                echo "
                <table>
                <tr>
                    <th style='text-align:left;'>Candidate Name</th>
                    <th style='text-align:left;'>Party</th>
                ";
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
                                <th style='text-align:left;'>".$gradeLevel . " - " . $sectionName."</th>
                                ";
                            }
                        }
                    }
                }
                echo "</tr>";
                break;
            case "level":
                echo "
                <table>
                <tr>
                    <th style='text-align:left;'>Candidate Name</th>
                    <th style='text-align:left;'>Party</th>
                    <th style='text-align:left;'>Grade 7</th>
                    <th style='text-align:left;'>Grade 8</th>
                    <th style='text-align:left;'>Grade 9</th>
                    <th style='text-align:left;'>Grade 10</th>
                    <th style='text-align:left;'>Grade 11</th>
                    <th style='text-align:left;'>Grade 12</th>
                </tr>
                ";
                break;
            case "overall":
            default:
                echo "
                <table>
                <tr>
                    <th style='text-align:left;'>Candidate Name</th>
                    <th style='text-align:left;'>Party</th>
                    <th style='text-align:left;'>Tally</th>
                </tr>
                ";
            }

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
                    // echo first parts of table
                    echo "
                    <tr>
                        <td>".$lastName.", ".$firstName." " . $middleName ."</td>
                        <td>".$partyListAbbr."</td>
                    ";

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
                            else
                            {
                                mysqli_stmt_bind_param($stmt3, "i", $gradeLevel);
                                mysqli_stmt_execute($stmt3);
                                $result3 = mysqli_stmt_get_result($stmt3);
                                while ($row3 = mysqli_fetch_assoc($result3))
                                {
                                    $sectionId = $row3['sectionId'];
                                    $sectionColumn = "section_".$sectionId;
                                    switch($view)
                                    {
                                    case "section":
                                        if(($viewlevel == $gradeLevel) || $viewlevel == 0)
                                        {
                                            echo "
                                            <td>".$row2[$sectionColumn]."</td>";
                                            break;
                                        }
                                    }
                                    $resultTally[$gradeLevel-6] += $row2[$sectionColumn];
                                }
                                $overallTally += $resultTally[$gradeLevel-6];
                            }
                            switch($view)
                            {
                            case "level":
                                echo "
                                <td>".$resultTally[$gradeLevel-6]."</td>";
                                break;
                            }
                        }
                        switch($view)
                        {
                        case "overall":
                            echo "
                                <td>".$overallTally."</td>
                            </tr>";
                            break;
                        case "level":
                            echo "</tr>";
                            break;
                        case "section":
                            echo "</tr>";
                            break;
                        default:
                            echo "
                                <td>".$overallTally."</td>
                            </tr>";
                            break;
                        }
                    }
                }
            }
            echo "</table>";
        }
    }
}
