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
require "../db/db_connection_general.php";
if($_SESSION['adminLevel'] < 2)
{
    header("Location: index.php");
    exit();
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

$showAddPartyList = 0; 
$showAddCandidate = 0; 
$showModifyPartyList = 0; 
$showDeletePartylist = 0;

?>

<html>
    <head>
        <title>Candidates</title>
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
    <body class='block'>
        <?php require 'index/header.php';?>
        <div id='newAdmin' class='grid'>
            <div id='sidebar'>
                <?php
                if($showResults == 0)
                {
                    $showAddPartyList = 1;
                    echo "<a><p class='input-lrn obj-center block button' style='width:196px;text-align:center;' onclick='addPartyList()'>New Party</p></a>";
                }
                else if ($showResults == 1)
                {
                    echo "<a><p class='input-lrn obj-center block button' style='width:196px;text-align:center;background-color:#444!important'>New Party</p></a>";
                }
                ?>
                <span class='span-16px'></span>
                <a href='candidates.php'><p class='sectionList'>All</p></a>
                <?php
                $partyListId = 0;
                $partyListAbbr = "Independent";
                echo "<a href='candidates.php?partyListId=".$partyListId."'</a><p class='sectionList'> " .  $partyListAbbr . " " . ($partyListId != 0 ? "Party" : ""). "</p></a>";

                // load parties
                $sql = "SELECT * FROM `partyList` ORDER BY `partyList`.`partyListName` ASC";
                $stmt = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($stmt, $sql)) 
                { 
                    header("Location: voterslist.php?error=SQL&task=fetchPartylists");
                    exit();
                }
                else 
                {
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt); 
                    while ($row = mysqli_fetch_assoc($result)) 
                    { 
                        $partyListId = $row['partyListId'];
                        $partyListAbbr = $row['partyListAbbr'];
                        echo "<a href='candidates.php?partyListId=".$partyListId."'</a><p class='sectionList'> " .  $partyListAbbr . " Party</p></a>";
                    }
                }
                ?>
            </div>
            <div id='mainContent'>
                <?php
                    $indFix = 0;
                    $partyExist = 0;
                    if(isset($_GET['partyListId']))
                    {
                        $indFix = 1;
                        $partyListId = $_GET['partyListId'];
                    }
                    if($indFix == 1)
                    {
                        if($partyListId == 0)
                        {
                            echo "<h2>Independent</h2>";
                        }
                        else
                        {
                            $sql = "SELECT * FROM `partyList` WHERE `partyListId` = ?";
                            $stmt = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($stmt, $sql)) { 
                                header("Location: voterslist.php?error=SQL&task=fetchPartylists");
                                exit();
                            }
                            else 
                            {
                                mysqli_stmt_bind_param($stmt, "i", $_GET['partyListId']);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt); 
                                $row = mysqli_fetch_assoc($result);
                                $partyListId = $row['partyListId'];
                                $partyListAbbrHeader = $row['partyListAbbr'];
                                $partyListNameHeader = $row['partyListName'];
                                
                                echo "
                                    <h2>".$partyListNameHeader."</h2>
                                "; 
                                $partyExist = 1;
                            }
                        }
                    }
                    else
                    {
                        echo "<h2>All</h2>";
                    }
                    echo "
                        <div class='grid' id='sub-nav'>
                            
                            "; 
                            if($partyExist == 1)
                            {
                                echo "
                                <p style='margin-top:10px'>(".$partyListAbbrHeader.")</p>
                                ";
                                if($showResults == 0)
                                {
                                    $showAddCandidate = 1;
                                    $showModifyPartyList = 1;
                                    $showDeletePartylist = 1;
                                    echo "
                                    <a><p class='input-lrn obj-center block button' style='width:auto;text-align:center;' onclick='addCandidate()'>Add Candidate</p></a>
                                    <a><p class='input-lrn obj-center block button modifyButton' style='width:auto;text-align:center;' onclick='modifyPartyList()'>Modify</p></a>
                                    <a><p class='input-lrn obj-center block button deleteButton' style='width:auto;text-align:center;' onclick='deletePartyList()'>Delete</p></a>
                                    ";
                                }
                            }
                            else if ($partyExist == 0)
                            {
                                echo "<p style='margin-top:10px'>Showing ".($partyListId == 0 ? "independent" : "all")." candidates</p>";
                                if($showResults == 0)
                                {
                                    $showAddCandidate = 1;
                                    echo "<span></span><span></span><a><p class='input-lrn obj-center block button' style='width:auto;text-align:center;' onclick='addCandidate()'>Add Candidate</p></a>";
                                }
                            }
                            echo "
                        </div>
                        <span class='span-16px'></span>
                    ";
                ?>
                <div id='candidateList'>
                    <?php
                        // show candidates
                        $allCandidateCount = 0;
                        for($positionId = 1; $positionId <= 13; $positionId++)
                        {
                            // get vote method
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

                            // check for position availability
                            $loadList = 0;
                            $sql = "SELECT * FROM `positionList` WHERE `positionId` = ?";
                            $stmt = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt, $sql))
                            {
                                header("Location: candidateInfo.php?error=SQL&task=fetchPositionList");
                                exit();
                            }
                            else
                            {
                                mysqli_stmt_bind_param($stmt, "i", $positionId);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                while ($row = mysqli_fetch_assoc($result))
                                {
                                    $positionName = $row['positionName'];
                                    $votingMethod = $row['votingMethod'];

                                    if(($votingMethod == $currentVotingMethod) || $currentVotingMethod == 3)
                                    {
                                        $loadList = 1;
                                        // echo "<h3>".$positionName."</h3>";
                                    }
                                }
                            }

                            if($loadList == 1)
                            {
                                $partySpecified = 0;
                                if(isset($_GET['partyListId']) && $_GET['partyListId'] != NULL)
                                {
                                    // check if party exists
                                    $sql = "SELECT * FROM `partyList` WHERE `partyListId` = ?";
                                    $stmt = mysqli_stmt_init($conn);
                                    if(!mysqli_stmt_prepare($stmt, $sql))
                                    {
                                        header("Location: candidates.php?error=SQL&task=checkPartyExist");
                                        exit();
                                    }
                                    else
                                    {
                                        mysqli_stmt_bind_param($stmt, "i", $_GET['partyListId']);
                                        mysqli_stmt_execute($stmt);
                                        $result = mysqli_stmt_get_result($stmt);
                                        $row = mysqli_fetch_assoc($result);
                                        if($row['partyListName'] != NULL || $_GET['partyListId'] == 0)
                                        {
                                            $partySpecified = 1;
                                        }
                                    }
                                }
                                // fetch and print candidates list
                                $candidateCount = 0;
                                switch($partySpecified)
                                {
                                    case 0: $sql = "SELECT * FROM `candidatesList` WHERE `position` = ? ORDER BY `candidatesList`.`lastName` ASC";break;
                                    case 1: $sql = "SELECT * FROM `candidatesList` WHERE `position` = ? AND `partyList` = ? ORDER BY `candidatesList`.`lastName` ASC";break;
                                }
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: candidates.php?error=SQL&task=fetchCandidateList");
                                    exit();
                                }
                                else
                                {
                                    switch($partySpecified)
                                    {
                                        case 0: mysqli_stmt_bind_param($stmt, "i", $positionId);break;
                                        case 1: mysqli_stmt_bind_param($stmt, "ii", $positionId, $_GET['partyListId']);break;
                                    }
                                    
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $candidateId = $row['candidateId'];
                                        $firstName = $row['firstName'];
                                        $middleName = $row['middleName'];
                                        $lastName = $row['lastName'];
                                        $partyListId = $row['partyList'];
                                        if($row['displayPic'] == NULL)
                                        {
                                            $displayPic = "../assets/img/blank.png";
                                        }
                                        else
                                        {
                                            $displayPic = "../".$row['displayPic'];
                                        }

                                        $sql2 = "SELECT * FROM `partyList` WHERE `partyListId` = ?";
                                        $stmt2 = mysqli_stmt_init($conn);
                                        if(!mysqli_stmt_prepare($stmt2, $sql2))
                                        {
                                            header("Location: candidates.php?error=SQL&task=getPartyListName");
                                            exit();
                                        }
                                        else
                                        {
                                            mysqli_stmt_bind_param($stmt2, "i", $partyListId);
                                            mysqli_stmt_execute($stmt2);
                                            $result2 = mysqli_stmt_get_result($stmt2);
                                            if($row2 = mysqli_fetch_assoc($result2))
                                            {
                                                $partyList = $row2['partyListAbbr'];
                                            }
                                            else
                                            {
                                                $partyList = "IND";
                                            }
                                        }
                                        $candidateIdHL = 0;
                                        if(isset($_GET['candidateId']))
                                        {
                                            $candidateIdHL = $_GET['candidateId'];
                                        }
                                        // highlight if newly created
                                        if($candidateId == $candidateIdHL)
                                        {
                                            $highlight = "highlightCandidate";
                                        }
                                        else
                                        {
                                            $highlight = "";
                                        }

                                        echo "
                                        <div class='candidate ".$highlight."'>
                                            <img src='".$displayPic."'>
                                            <div>
                                                <p class='candidateName'>".$lastName.", ".$firstName." ".$middleName." ".($partySpecified == 0 ? "(".$partyList.")" : "")."</p>
                                                <p class='candidatePosition'>".$positionName."</p>
                                            </div>
                                            ";

                                            if($showResults == 0)
                                            {
                                                
                                                echo "
                                                <form action='modify.php?photo=1&partyListId=".$partyListId."&candidateId=".$candidateId."' method='post'>
                                                    <button class='candidateButton' type='submit' name='modify'><img src='../assets/img/blank.png'></button>
                                                </form>
                                                <form action='modify.php?info=1&candidateId=".$candidateId."' method='post'>
                                                    <button class='candidateButton' type='submit' name='modify'><img src='../assets/img/admin/edit.png'></button>
                                                </form>
                                                <form action='challenge.php?candidateId=".$candidateId."' method='post'>
                                                    <button class='candidateButton deleteButton' type='submit' name='delete'><img src='../assets/img/admin/android-trash.png'></button>
                                                </form>
                                                ";
                                            }
                                        echo "</div>";
                                        $candidateCount++;
                                        $allCandidateCount++;
                                    }
                                }
                            }
                        }
                        if($allCandidateCount == 0)
                        {
                            echo "<p>Returned 0 candidates. Add one by clicking the 'Add Candidate' Button.</p>";
                        }
                    ?>
                </div>
            </div>
        </div>
        <?php
        if($showAddPartyList == 1)
        {
        ?>
        <div id="addPartyList" class="page-sub-window" style="display:none">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>New Party</p>
                        <img src="../assets/img/close.png" onclick="addPartyList()">
                    </div>
                    <span class="span-16px"></span>
                    <form action="../db/db_candidates.php" method="post">
                        <?php require "form-content/partyListForm.php";?>
                        <span class="span-8px"></span>
                        <button class="input-lrn obj-center  block button" style='width:300px' type="submit" name="createPartyList">Create Partylist</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        }
        if($showAddCandidate == 1)
        {
        ?>
        <div id="addCandidate" class="page-sub-window" style="display:none">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>New Candidate</p>
                        <img src="../assets/img/close.png" onclick="addCandidate()">
                    </div>
                    <span class="span-16px"></span>

                    <form action='../db/db_candidates.php' method='post'>
                        <?php
                        echo "<input class='block obj-center input-lrn' style='width:300px' type='text' name='firstName' placeholder='First Name'>
                        <span class='span-8px'></span>
                        <input class='block obj-center input-lrn' style='width:300px' type='text' name='middleName' placeholder='Middle Name'>
                        <span class='span-8px'></span>
                        <input class='block obj-center input-lrn' style='width:300px' type='text' name='lastName' placeholder='Last Name'>
                        <span class='span-8px'></span>
                        <select class='block obj-center input-lrn' style='width:300px;height:32px;' name='partylist'>
                            <option value='0' " . ((isset($_GET['partyListId']) == 0) || (isset($partyListId)) == 0 ? " selected='selected'" : "") . ">IND</option>";
                                $sql = "SELECT * FROM `partyList` ORDER BY `partyList`.`partyListName` ASC";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: candidateInfo.php?error=SQL&task=fetchPartyList");
                                    exit();
                                }
                                else
                                {
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $partyListId = $row['partyListId'];
                                        $partyListAbbr = $row['partyListAbbr'];
                                        echo "<option value='".$partyListId."' " . (($_GET['partyListId'] == $partyListId) || (isset($partyListIdMatch) && $partyListIdMatch == $partyListId) ? " selected='selected'" : "") . ">" . $partyListAbbr."</option>";
                                    }
                                }
                            echo "
                        </select>
                        <span class='span-8px'></span>
                        <select class='block obj-center input-lrn' style='width:300px;height:32px;' name='position'>";
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
                    
                            // check for position availability as per voting method
                            for($positionId = 1; $positionId <= 13; $positionId++)
                            {
                                $sql = "SELECT * FROM `positionList` WHERE `positionId` = ?";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql))
                                {
                                    header("Location: candidateInfo.php?error=SQL&task=fetchPositionList");
                                    exit();
                                }
                                else
                                {
                                    mysqli_stmt_bind_param($stmt, "i", $positionId);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $positionName = $row['positionName'];
                                        $votingMethod = $row['votingMethod'];
                    
                                        if(($votingMethod == $currentVotingMethod) || $currentVotingMethod == 3)
                                        {
                                            echo "<option value='".$positionId."' " . (($_GET['positionId'] == $positionId) || $positionIdMatch == $positionId ? " selected='selected'" : "") . ">" . $positionName. "</option>";
                                        }
                                    }
                                }
                            }
                        echo "</select>";
                        ?>
                        <span class="span-8px"></span>
                        <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='addCandidate'>Add Candidate</button>
                    </form>

                </div>
            </div>
        </div>
        <?php
        }
        if($showModifyPartyList == 1)
        {
        ?>
        <div id="modifyPartyList" class="page-sub-window" style="display:none">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>Modify Party</p>
                        <img src="../assets/img/close.png" onclick="modifyPartyList()">
                    </div>
                    <span class="span-16px"></span>
                    <form action="../db/db_candidates.php?partyListId=<?php echo $_GET['partyListId'];?>" method="post">
                    <input class='block obj-center input-lrn' style="width:300px" type="text" name="partyListName" placeholder="Partylist Name" value='<?php echo (isset($partyListNameHeader) ? $partyListNameHeader : '');?>'>
                    <span class="span-8px"></span>
                    <input class='block obj-center input-lrn' style="width:300px" type="text" name="partyListAbbr" placeholder="Partylist Abbreviation" value='<?php echo (isset($partyListAbbrHeader) ? $partyListAbbrHeader : '');?>'>
                        <span class="span-8px"></span>
                        <button class="input-lrn obj-center  block button" style='width:300px' type="submit" name="modifyPartyList">Modify Party</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        }
        if($showDeletePartylist == 1)
        {
        ?>
        <div id="deletePartyList" class="page-sub-window" style="display:none">
            <div class="vertical-center">
                <div class="page-sub-window-content">
                    <div class="window-grid">
                        <p>Are you sure?</p>
                        <img src="../assets/img/close.png" onclick="deleteSection()">
                    </div>
                    <span class="span-4px"></span>
                    <p>This action cannot be undone.</p>
                    <span class="span-16px"></span>
                    <form action="../db/db_candidates.php?partyListId=<?php echo $_GET['partyListId'];?>" method="post">
                        <input class='block obj-center input-lrn' style="width:300px" type='password' name='passcode' placeholder='Enter password to confirm'>
                        <span class="span-8px"></span>
                        <button class="input-lrn obj-center  block button" style='width:300px' type="submit" name='deletePartyList'>Confirm Delete</button>
                    </form>
                    <span class='span-8px'></span><p class='smallWarning-subwindow'>Deleting a party won't delete candidates associated with it but instead become independent.</p>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
        <script>
            function addPartyList() {
                if (document.getElementById('addPartyList').style.display === 'none') {  
                    document.getElementById('addPartyList').style.display = 'table';
                }
                else {
                    document.getElementById('addPartyList').style.display = 'none';
                }
            }

            function addCandidate() {
                if (document.getElementById('addCandidate').style.display === 'none') {  
                    document.getElementById('addCandidate').style.display = 'table';
                }
                else {
                    document.getElementById('addCandidate').style.display = 'none';
                }
            }

            function modifyPartyList() {
                if (document.getElementById('modifyPartyList').style.display === 'none') {  
                    document.getElementById('modifyPartyList').style.display = 'table';
                }
                else {
                    document.getElementById('modifyPartyList').style.display = 'none';
                }
            }

            function deletePartyList() {
                if (document.getElementById('deletePartyList').style.display === 'none') {  
                    document.getElementById('deletePartyList').style.display = 'table';
                }
                else {
                    document.getElementById('deletePartyList').style.display = 'none';
                }
            }
        </script>  
    </body>
</html>