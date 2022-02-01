<?php
session_start();
require 'db/db_connection_general.php';
if(empty($_SESSION['lrn']))
{
    header("Location: index.php?error=NoSession");
    exit();
}
// localize variables
$gradeLvl = $_SESSION['gradeLvl'];

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
        <?php
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
            // get result width for candidate list block, based on votingmethod
            if($votingMethod == 1) $resultWidth = (140*5)+(8*4);
            if($votingMethod == 2) $resultWidth = (140*2)+8;
            if($votingMethod == 3) $resultWidth = (140*5)+(8*4);
            
            echo "
                <style>
                    #candidate-grid {
                        display:grid;
                        grid-template-rows:auto;
                        grid-gap:8px;
                        grid-template-columns:";
                        
                        if($votingMethod == 1 || $votingMethod == 3)
                        {
                            for($N=0;$N<5;$N++)
                            {
                                echo "140px ";
                            }
                        }
                        else if ($votingMethod == 2)
                        {
                            for($N=0;$N<2;$N++)
                            {
                                echo "140px ";
                            }
                        }
                        echo ";
                        
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
    <body>
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
                    <p id="position">Review Ballot</p>
                    <span class="span-4px"></span>
                    <p id="select">If you need changes, you can always go back to the specific position by clicking it.</p>
                </div>
                <span class="span-8px"></span>
                <div id="candidate-list-block" class="block">
                    <div id="candidate-grid" class="grid">
                        <?php
                            for($N=1;$N<=20;$N++)
                            { // for each position, get positionName and profile card of picked candidate of respective position #
                                switch($N)
                                { // identify actualPos
                                    case 1: $actualPos = 1;$positionNumber = "";break;
                                    case 2: $actualPos = 2;$positionNumber = "";break;
                                    case 3: $actualPos = 3;$positionNumber = "";break;
                                    case 4: $actualPos = 4;$positionNumber = "";break;
                                    case 5: $actualPos = 5;$positionNumber = "";break;
                                    case 6: $actualPos = 6;$positionNumber = "";break;
                                    case 7: $actualPos = 7;$positionNumber = "1";break;
                                    case 8: $actualPos = 7;$positionNumber = "2";break;
                                    case 9: $actualPos = 8;$positionNumber = "1";break;
                                    case 10: $actualPos = 8;$positionNumber = "2";break;
                                    case 11: $actualPos = 9;$positionNumber = "1";break;
                                    case 12: $actualPos = 9;$positionNumber = "2";break;
                                    case 13: $actualPos = 10;$positionNumber = "1";break;
                                    case 14: $actualPos = 10;$positionNumber = "2";break;
                                    case 15: $actualPos = 11;$positionNumber = "1";break;
                                    case 16: $actualPos = 11;$positionNumber = "2";break;
                                    case 17: $actualPos = 12;$positionNumber = "1";break;
                                    case 18: $actualPos = 12;$positionNumber = "2";break;
                                    case 19: $actualPos = 13;$positionNumber = "1";break;
                                    case 20: $actualPos = 13;$positionNumber = "2";break;
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
                                    if(($votingMethod == $row['votingMethod'] || $votingMethod == 3) && ($gradeLvl == $row['specificLevel'] || $row['specificLevel'] == 0))
                                    {
                                        $positionName = $row['positionName'];
                                    }
                                    else
                                    {
                                        continue;
                                    }
                                }
                                // get candidate value
                                $value = $_SESSION['candidates'][$N];
                                $sql = "SELECT * FROM `candidatesList` WHERE `candidateId` = ?";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: index.php?error=SQL");
                                    exit();
                                }
                                else
                                {
                                    mysqli_stmt_bind_param($stmt, "s", $value);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    $row = mysqli_fetch_assoc($result);
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
                                    if($lastname == NULL)
                                    { // if profile card is blank because he/she didn't voted someone
                                        $lastname = "-";
                                        $firstname = "-";
                                        $party = "-";
                                        $profilepic = "assets/img/blank.png";
                                    }
                                    $showlink = "vote.php?position=".$N."&skipAhead=1"; // link to go back for review, with skipAhead this time to go back directly here after clarifying vote
                                    if($N <= 20)
                                    { // print out candidate card of its respective grade lvl reps if $N which is posNoLoop and $gradelvl are true
                                        echo "
                                        <a href='".$showlink."' class='candidate-profile-review'>
                                            <img src='".$profilepic."'>
                                            <span class='span-4px'></span>
                                            <p class='party-review'>".$positionName." ".$positionNumber."</p>
                                            <span class='span-4px'></span>
                                            <p class='lastname-review'>".strtoupper($lastname)."</p>
                                            <p class='firstname-review'>".strtoupper($firstname)."</p>
                                            <span class='span-4px'></span>
                                            <p class='party-review'>".strtoupper($partyListAbbr)."</p>
                                            <span class='span-4px'></span>
                                        </a>
                                        ";
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
                <span class="span-8px"></span>
                <form action="castvote.php" method="post">
                    <button class="input-lrn obj-center  block button" type="submit" name="castvote">Cast Vote</button>
                </form>
            </div>
        </div>
    </body>
</html>