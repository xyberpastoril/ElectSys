<?php
session_start();
require 'db/db_connection_general.php';
if(empty($_SESSION['lrn']))
{
    header("Location: index.php?error=NoSession");
    exit();
}
// localize variables
$position = $_GET['position'];
$gradeLvl = $_SESSION['gradeLvl'];
$skipVote = 0; // used to reset position if position # is not appliable to user such as g7 not voting to g8-12 reps
if(!empty($_GET['skipAhead']))
{
    if($_GET['skipAhead'] == 1)
    {
        $_SESSION['skipAhead'] = 1;
    }
}

// get the votingMethod
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'votingMethod'";
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
    $votingMethod = $row['settingValue'];
}

// getting the actual position (double for peace officers and representatives)
if($position < 7)
    $actualPos = $position;
else if($position == 7 || $position == 8) // Peace Officers
    $actualPos = 7;
else if($position == 9 || $position == 10) // Grade 7 Reps
    $actualPos = 8;
else if($position == 11 || $position == 12) // Grade 8 Reps
    $actualPos = 9;
else if($position == 13 || $position == 14) // Grade 9 Reps
    $actualPos = 10;
else if($position == 15 || $position == 16) // Grade 10 Reps
    $actualPos = 11;
else if($position == 17 || $position == 18) // Grade 11 Reps
    $actualPos = 12;
else if($position == 19 || $position == 20) // Grade 12 Reps
    $actualPos = 13;
else if($position > 20)
{
    header("Location: review.php");
    exit();
}

// check if can vote on actualPos
$sql = "SELECT * FROM `positionList` WHERE `positionId` = ?";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql))
{
    header("Location: index.php?error=SQL&task=checkActualPos");
    exit();
}
else
{
    mysqli_stmt_bind_param($stmt, "i", $actualPos);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    //echo "actual pos: " .$actualPos;
    //echo "<br>specific level: " .$row['specificLevel'];
    //echo "<br>current level: " .$gradeLvl;
    //exit();
    if(($votingMethod == $row['votingMethod'] || $votingMethod == 3) && ($gradeLvl == $row['specificLevel'] || $row['specificLevel'] == 0))
    {
        // calculation of candidate-grid width
        $listCount = 0;
        $sql = "SELECT * FROM `candidatesList` WHERE `position` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)) 
        {
            header("Location: index.php?error=SQL");
            exit();
        }
        else 
        {
            mysqli_stmt_bind_param($stmt, "s", $actualPos);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result))
            {
                $listCount++;
            }
        }
        if($listCount == 0)
        {
            if($_SESSION['skipAhead'] == 1)
            {
                header("Location: review.php");
                exit();
            }
            else
            {
                $position++;
                header("Location: vote.php?position=".$position);
                exit();
            }
        }
        if($actualPos >= 7 && $listCount == 1)
        { // if only one candidate ran on position that's good for 2 elected candidates, skip the other one
            if($position % 2 == 0 && $_SESSION['candidates'][$position-1] != 0)
            {
                if($_SESSION['skipAhead'] == 1)
                {
                    header("Location: review.php");
                    exit();
                }
                else
                {
                    $position++;
                    header("Location: vote.php?position=".$position);
                    exit();
                }
            }
            if($position % 2 == 1 && $_SESSION['candidates'][$position+1] != 0)
            {
                if($_SESSION['skipAhead'] == 1)
                {
                    header("Location: review.php");
                    exit();
                }
                else
                {
                    $position++;
                    header("Location: vote.php?position=".$position);
                    exit();
                }
            }
        }
        // calculation of the candidate-list-block-width
        $resultWidth = ($listCount * 200) + (($listCount - 1) * 24); // resultWidth to be used on the actual block width of grid  
    }
    else
    {
        if($_SESSION['skipAhead'] == 1)
        {
            header("Location: review.php");
            exit();
        }
        else
        {
            $position++;
            header("Location: vote.php?position=".$position);
            exit();
        }
    }
}

for($checkSetting = 1; $checkSetting <= 4; $checkSetting++)
{
    switch($checkSetting)
    {
        case 1: $settingName = "voteOpen";break;
        case 2: $settingName = "school";break;
        case 3: $settingName = "organizationName";break;
        case 4: $settingName = "votingMethod";break;
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
            case 1: $voteOpen = $row['settingValue'];break;
            case 2: $school = $row['settingValue'];break;
            case 3: $organization = $row['settingValue'];break;
            case 4: $votingMethod = $row['settingValue'];break;
        }
    }
}

switch($votingMethod)
{
    case 1: 
        $votingText = "General Elections";
        $votingYear = (date("Y")) + 1;break;
    case 2: 
        $votingText = "Grade 7 & 11 Representative Elections";
        $votingYear = (date("Y"));break;
    case 3: 
        $votingText = "Club/Organizational Election";
        $votingYear = (date("Y"));break;
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
    $electionLogo = $row['settingValue'];
    if($electionLogo == NULL)
    {
        $electionLogo = "assets/img/admin/adminpanel.png";
    }
}

// get background photo link
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'backgroundPhoto'";
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
    $backgroundPhoto = $row['settingValue'];
    if($backgroundPhoto == NULL)
    {
        $backgroundPhoto = "";
    }
}
?>
<html>
    <head>
        <title>ARMS_AES</title>
        <link rel="stylesheet" type="text/css" href="assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="vote-beta.css">
        <link rel="shortcut icon" type="image/png" href="assets/img/logo/arms_128x128.png">
        <meta charset="UTF-8">
        <?php
            echo "
                <style>
                    #candidate-grid {
                        display:grid;
                        grid-template-columns:";
                        for($N=1;$N<=$listCount;$N++)
                        { // aligns profile cards to center with this adjustable css code based on number of candidates
                            if($N == $listCount)
                            {
                                echo "200px;";
                            }
                            else
                            {
                                echo "200px ";
                            }
                        }
                        
                        echo "
                        grid-gap:24px;
                    }

                    #candidate-list-block {
                        display:block;
                        margin:0 auto;
                        height:auto;
                        width:".$resultWidth.";
                    }

                    #vote-selection {
                        width:100%;
                        height:calc(100vh - 72px);
                        padding-top:72px;
                    }

                    #bg {
                        background-image:url('".$backgroundPhoto."');
                    }
                </style>
            ";
        ?>
    </head>
    <body class="block">
        <div id="bg" class="absolute"></div>
        <div id="header" class="absolute"></div>
        <div id="left-logo">
            <!--<p>ElectSys <sub>v1</sub></p>-->
            <img class="block" style='height:36px;width:auto;float:left!important' src="<?php echo $electionLogo; ?>">
            <div style='margin-left:16px;'>
                <p style='font-size:24px'><?php echo $organization; ?></p>
                <p style='font-size:12px'><?php echo $votingText . " " . $votingYear; ?></p>
            </div>
        </div>
        <div id="right-logo">
            <p style="margin-top:8px;"><?php echo "LRN: <b>".$_SESSION['lrn']."</b> | ".$_SESSION['gradeLvl']." - ".$_SESSION['sectionName'];?></p>
        </div>
        <div id="vote-selection" class="table">
            <div id="vote-selection-margin" class="vertical-center">
                <div>
                    <?php
                        $positionNumber = "";
                        // get positionName
                        $sql = "SELECT * FROM `positionList` WHERE `positionId` = ?";
                        $stmt = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt, $sql))
                        {
                            header("Location: index.php?error=SQL&task=getPositionName");
                            exit();
                        }
                        else
                        {
                            mysqli_stmt_bind_param($stmt, "i", $actualPos);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            $row = mysqli_fetch_assoc($result);
                            $positionName = $row['positionName'];
                        }
                        // get positionNumber
                        if($position >=  7)
                        {
                            if($position%2 == 1)
                            {
                                $positionNumber = "(1/2)";
                            }
                            else if ($position%2 == 0)
                            {
                                $positionNumber = "(2/2)";
                            }
                        }
                        echo "<p id='position'>".$positionName." ".$positionNumber."</p>";
                    ?>
                    <span class='span-4px'></span>
                    <?php
                        if(!empty($_GET['error']))
                        { // error messages
                            if($_GET['error'] == "duplicatevote")
                            {
                                echo "<p class='error' style='width:640px'>You can't vote a single candidate twice. Select another candidate.</p>";
                            }
                        }
                        else if ($_SESSION['candidates'][$position] != 0)
                        { // if candidate is already picked from this position
                            echo "<p class='select' style='text-align:center'>Reselect the yellowish candidate to keep vote. Otherwise, select another candidate.</p>";
                        }
                        else
                        { // if there isn't any candidate picked
                            echo "<p id='select' style='text-align:center'>Click your preferred candidate to select</p>";
                        }
                    ?>
                </div>
                <span class="span-16px"></span>
                <span class="span-8px"></span>
                <div id="candidate-list-block">
                    <div id="candidate-grid">
                        <?php
                            // load list of candidates of respective actualpos
                            $sql = "SELECT * FROM `candidatesList` WHERE `position` = ? ORDER BY `candidatesList`.`lastname` ASC";
                            $stmt = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt, $sql)) 
                            {
                                header("Location: index.php?error=SQL");
                                exit();
                            }
                            else 
                            {
                                mysqli_stmt_bind_param($stmt, "s", $actualPos);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                while ($row = mysqli_fetch_assoc($result)) 
                                {
                                    $showselected = "#";
                                    $id = $row['candidateId'];
                                    $lastname = $row['lastName'];
                                    $firstname = $row['firstName'];
                                    $party = $row['partyList'];
                                    // get partylist abbr
                                    if($party == 0)
                                    {
                                        $partyListAbbr = "IND";
                                    }
                                    else
                                    {
                                        $sql10 = "SELECT * FROM `partyList` WHERE `partyListId` = ?";
                                        $stmt10 = mysqli_stmt_init($conn);
                                        if(!mysqli_stmt_prepare($stmt10, $sql10))
                                        {
                                            header("Location: index.php?error=SQL&task=getPartyName");
                                            exit();
                                        }
                                        else
                                        {
                                            mysqli_stmt_bind_param($stmt10, "s", $party);
                                            mysqli_stmt_execute($stmt10);
                                            $result10 = mysqli_stmt_get_result($stmt10);
                                            $row10 = mysqli_fetch_assoc($result10);
                                            $partyListAbbr = $row10['partyListAbbr'];
                                        }
                                    }
                                    

                                    $profilepic = $row['displayPic'];
                                    if($profilepic == NULL)
                                    {
                                        $profilepic = "assets/img/blank.png";
                                    }
                                    $showlink = "votecheck.php?position=".$position."&value=".$id;
                                    // check if id is already chosen
                                    if($position >= 1 && $position <= 6)
                                    {
                                        if($id == $_SESSION['candidates'][$position])
                                        {
                                            $showselected = "candidate-selected";
                                        }
                                    }
                                    else if ($position >= 7 && $position <= 20)
                                    {
                                        if($position % 2 == 1 && $_SESSION['candidates'][$position+1] != $id) // odd - 7,9,11,13,15,17,19 (first)
                                        {
                                            echo "";
                                        }
                                        else if($position % 2 == 0 && $_SESSION['candidates'][$position-1] != $id) // even - 8, 10, 12, 14, 16,18,20 (second)
                                        {
                                            echo "";
                                        }
                                        else
                                        {
                                            $showselected = "candidate-selected";
                                        }

                                        if($id == $_SESSION['candidates'][$position])
                                        {
                                            $showselected = "candidate-selected candidate-selected-focus";
                                        }
                                    }
                                    
                                    // load candidate information
                                    echo "
                                        <a href='".$showlink."' class='candidate-profile ".$showselected."'>
                                            <img src='".$profilepic."'>
                                            <span class='span-16px'></span>
                                            <p class='lastname'>".strtoupper($lastname)."</p>
                                            <p class='firstname'>".strtoupper($firstname)."</p>
                                            <span class='span-16px'></span>
                                            <p class='party'>".strtoupper($partyListAbbr)."</p>
                                            <span class='span-16px'></span>
                                        </a>
                                    ";
                                }
                            }
                        ?>
                    </div>
                    <span class="span-16px"></span>
                    <span class="span-4px"></span>
                    <a href="votecheck.php?position=<?php // skip vote because you don't have someone to vote from here 
                    echo $position;?>&value=0" class="button" id="skip">Neither of these</a>
                </div>
            </div>
        </div>
    </body>
</html>