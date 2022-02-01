<style>

    @media print {
        .pagebreak { page-break-before: always; } /* page-break-after works, as well */
    }

    p {
        font-family:"Century Gothic";
    }
    
    h2 {
        margin-bottom:4px!important;
        margin-top:0px!important;
        font-family:"Century Gothic";
    }

    h3 {
        margin-bottom:2px!important;
        margin-top:2px!important;
        font-family:"Century Gothic";
    }
    table {
        border:0px solid #000;
        border-collapse:collapse;
    }
    td {
        padding:2px;
        border-collapse:collapse;
        margin:0;
        border:1px solid #000;
    }
    th {
        border:1px solid #000;
        padding:2px;
        font-family:"Century Gothic";
    }
    tr {
        font-family:"Century Gothic";
    }
    .name {
        width:180px;
    }
    .party {
        width:84px;
    }
    .tally p {
        text-align:center;
    }
    .highlight {
        font-weight:700;
    }
    #overall {
        display:grid;
        grid-template-columns:320px 320px;
        grid-gap:10px;
    }
</style>
<?php
session_start();
$admin = 1;
require '../db/db_checker.php';
require '../db/db_connection_general.php';

for($checkSetting = 2; $checkSetting <= 3; $checkSetting++)
{
    switch($checkSetting)
    {
        case 2: $settingName = "school";break;
        case 3: $settingName = "organizationName";break;
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
            case 2: $school = $row['settingValue'];break;
            case 3: $organization = $row['settingValue'];break;
        }
    }
}

// get election logo link
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'electionLogo'";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql))
{
    header("Location: index.php?error=SQL&task=checkVotingMethod");
    exit();
}
else
{
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $electionLogo = "../".$row['settingValue'];
    if($electionLogo == NULL)
    {
        $electionLogo = "../assets/img/admin/schoolLogo.png";
    }
}

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
switch($currentVotingMethod)
{
case 1: 
    $votingText = "General Elections";
    $votingYear = (date("Y")) + 1;break;
case 2: 
    $votingText = "Grade 7 & 11 Representative Elections";
    $votingYear = (date("Y")) + 1;break;
case 3: 
    $votingText = "Club/Organizational Election";
    $votingYear = (date("Y")) + 1;break;
}

echo "
<div style='display:grid;grid-template-columns:84px auto;grid-gap:16px'>
    <img style='margin-top:16px;width:84px;height:84px' src='".$electionLogo."'>
    <div>
        <p>".$school."<br>
        ".$organization."<br>
        ".($organization == "Supreme Student Government" ? "Commission on Elections" : "")."<br>
        ".$votingText." for S.Y. " . date("Y") . " - " . $votingYear . "</p>
    </div>
</div>
";

for($N=1;$N<=5;$N++)
{
    switch($N)
    {
    case 1: // overall           
        $posCheck = 0;
        $view = "overall";$officialTitle = "Official Overall Results";
        echo "<h2>".$officialTitle."</h2>";
        echo "<div id='overall' class='grid'>";
        for($posNo = 1;$posNo <= 14; $posNo++)
        {
            $rank = 0;
            if($posNo == 14)
            {  
                if($posCheck % 2 == 1)
                {
                    echo "<div>";
                    echo "<br>";
                    echo "<br>";
                    echo "<p style='margin:0px;padding-left:144px;'>_________________________</p>";
                    echo "<p style='margin:0px;padding-left:144px;'>COMELEC Chairman</p>";
                    echo "<br>";
                    echo "<br>";
                    echo "<p style='color:#999;margin:0;font-size:11px;padding-left:24px;'><i>This document is computer-generated. Powered by ARMS_AES.</i></p>";
                    echo "</div>";
                }
                else
                {
                    echo "<div>";
                    echo "<br>";
                    echo "<br>";
                    echo "<p style='margin:0px;'>_________________________</p>";
                    echo "<p style='margin:0px'>".($organization == "Supreme Student Government" ? "COMELEC Chairman": "")."</p>";
                    echo "<br>";
                    echo "<br>";
                    echo "<p style='color:#999;margin:0;font-size:11px;'><i>This document is computer-generated. Powered by ARMS_AES.</i></p>";
                    echo "</div>";
                }
            }
            else
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
                    $candidatesPerParty = $row['candidatesPerParty'];
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
                        echo "<div><h3>".$posName."</h3>";
                        echo "
                        <table>
                        <tr>
                            <th class='name' style='text-align:left;'>Candidate Name</th>
                            <th class='party' style='text-align:left;'>Party</th>
                            <th style='text-align:left;'>Tally</th>
                        </tr>
                        ";
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
                                $rank++;
                                $candidateId = $row['candidateId'];
                                $firstName = $row['firstName'];
                                $middleName = $row['middleName'];
                                $lastName = $row['lastName'];
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
                                // echo first parts of table
                                echo "
                                <tr>
                                    <td class='".($rank <= $candidatesPerParty ?"highlight" : "")."'>".$lastName.", ".$firstName." " . $middleName ."</td>
                                    <td class='".($rank <= $candidatesPerParty ?"highlight" : "")."'>".$partyListAbbr."</td>
                                    <td class='tally ".($rank <= $candidatesPerParty ?"highlight" : "")."'><p>".$totalVotes."</p></td>
                            </tr>
                                ";
                            }
                            echo "</table></div>";
                            $posCheck++;
                        }
                    }
                }
            }
        }
        echo "</div>";
    break;
    case 2: // by level
    break; 
        $view = "level";$officialTitle = "Official Results by Level";
        echo "<h2>".$officialTitle."</h2>";
        for($posNo = 1;$posNo <= 13; $posNo++)
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
                    echo "
                    <table>
                    <tr>
                        <th class='name' style='text-align:left;'>Candidate Name</th>
                        <th class='party' style='text-align:left;'>Party</th>
                        <th style='text-align:left;'>Grade 7</th>
                        <th style='text-align:left;'>Grade 8</th>
                        <th style='text-align:left;'>Grade 9</th>
                        <th style='text-align:left;'>Grade 10</th>
                        <th style='text-align:left;'>Grade 11</th>
                        <th style='text-align:left;'>Grade 12</th>
                    </tr>
                    ";
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
                            $rank++;
                            $candidateId = $row['candidateId'];
                            $firstName = $row['firstName'];
                            $middleName = $row['middleName'];
                            $lastName = $row['lastName'];
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
                            // echo first parts of table
                            echo "
                            <tr>
                                <td class='".($rank <= $candidatesPerParty ?"highlight" : "")."'>".$lastName.", ".$firstName." " . $middleName ."</td>
                                <td class='".($rank <= $candidatesPerParty ?"highlight" : "")."'>".$partyListAbbr."</td>
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
                                                $resultTally[$gradeLevel-6] += $row2[$sectionColumn];
                                            }
                                            $overallTally += $resultTally[$gradeLevel-6];
                                        }
                                        echo "
                                        <td class='tally ".($rank <= $candidatesPerParty ?"highlight" : "")."'><p>".$resultTally[$gradeLevel-6]."</p></td>";
                                    }
                                }
                            echo "</tr>";
                        }
                        echo "</table>";
                    }
                }
            }
        }
    break;
    case 5: // section (by level) - looped 6 times
    
        echo "<div class='pagebreak'></div>";
        $view = "section";$officialTitle = "Official Results by Section";
        for($gradeLevelCtr = 7; $gradeLevelCtr <= 12; $gradeLevelCtr++)
        {
            if ($currentVotingMethod == 1 || $currentVotingMethod == 3 || ($currentVotingMethod == 2 && ($gradeLevelCtr == 7 || $gradeLevelCtr == 11)))
                echo "";
            else
                continue;

            $viewlevel = $gradeLevelCtr;
            echo "<br><h2>".$officialTitle." (Grade ".$gradeLevelCtr.")</h2>";
            for($posNo = 1;$posNo <= 13; $posNo++)
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
                        echo "
                        <table>
                        <tr>
                            <th class='name' style='text-align:left;'>Candidate Name</th>
                            <th class='party' style='text-align:left;'>Party</th>";

                            $sql3 = "SELECT * FROM `sectionsList` WHERE `gradeLevel` = ? ORDER BY `sectionsList`.`sectionName` ASC";
                            $stmt3 = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt3, $sql3))
                            {
                                header("Location: ../admin/results.php?error=SQL&task=getSectionIdforHeader");
                                exit();
                            }
                            else
                            {
                                mysqli_stmt_bind_param($stmt3, "i", $viewlevel);
                                mysqli_stmt_execute($stmt3);
                                $result3 = mysqli_stmt_get_result($stmt3);
                                while ($row3 = mysqli_fetch_assoc($result3))
                                {
                                    $sectionName = $row3['sectionName'];
                                    echo"
                                    <th style='text-align:left;'>".$sectionName."</th>
                                    ";
                                }
                            }

                            echo "
                        </tr>
                        ";
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
                                $rank++;
                                $candidateId = $row['candidateId'];
                                $firstName = $row['firstName'];
                                $middleName = $row['middleName'];
                                $lastName = $row['lastName'];
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
                                // echo first parts of table
                                echo "
                                <tr>
                                    <td class='".($rank <= $candidatesPerParty ?"highlight" : "")."'>".$lastName.", ".$firstName." " . $middleName ."</td>
                                    <td class='".($rank <= $candidatesPerParty ?"highlight" : "")."'>".$partyListAbbr."</td>
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
                                                            <td class='tally ".($rank <= $candidatesPerParty ?"highlight" : "")."'><p>".$row2[$sectionColumn]."</p></td>";
                                                            break;
                                                        }
                                                    }
                                                    $resultTally[$gradeLevel-6] += $row2[$sectionColumn];
                                                }
                                                $overallTally += $resultTally[$gradeLevel-6];
                                            }
                                        }
                                    }
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                    }
                }
            }
        }
    break;
    case 4: // elected officers list
        echo "
        <div class='pagebreak'></div>
        <div style='display:grid;grid-template-columns:84px auto;grid-gap:16px'>
            <img style='margin-top:16px;width:84px;height:84px' src='".$electionLogo."'>
            <div>
                <span style='display:block;width:auto;height:4px'></span>
                <p>".$school."<br>
                ".$organization."<br>
                ".($organization == "Supreme Student Government" ? "Commission on Elections" : "")."<br>
                ".$votingText." for S.Y. " . date("Y") . " - " . $votingYear . "</p>
            </div>
        </div>
        <br>
        ";
        $view = "overall";$officialTitle = "Newly Elected Officers";
        echo "<h2>".$officialTitle."</h2>";
        // print list of new officers
        for($posNo = 1;$posNo <= 13; $posNo++)
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
                    echo "<div><h3>".$posName."</h3>";
                    echo "
                    
                    ";
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
                            $rank++;
                            if($rank > $candidatesPerParty)
                            {
                                continue;
                            }
                            $candidateId = $row['candidateId'];
                            $firstName = $row['firstName'];
                            $middleName = $row['middleName'];
                            $lastName = $row['lastName'];
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
                            // echo first parts of table
                            echo "
                                <p style='margin-left:20px;margin-bottom:0px;margin-top:0px;'>".$lastName.", ".$firstName." " . $middleName ." | ".$partyListAbbr."</td>
                            ";
                        }
                        echo "</div>";
                    }
                }
            }
        }
    echo "</div>";
    echo "<br>";
    echo "<br>";
    echo "<p style='margin:0px'>_________________________</p>";
    echo "<p style='margin:0px'>".($organization == "Supreme Student Government" ? "COMELEC Chairman": "")."</p>";
    echo "<br>";
    echo "<br>";
    echo "<p style='color:#999;margin:0;font-size:11px'><i>This document is computer-generated. Powered by ARMS_AES.</i></p>";
    break;
    }
}