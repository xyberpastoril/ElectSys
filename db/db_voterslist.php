<html>
    <head>
        <title>Add Students</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/newAdmin.css">
        <style>
            .vertical-center {
                width:100vw;
                height:100vh;
            }
        </style>
    </head>
    <body>

<?php
session_start();
if(isset($_POST['createsection']))
{
    require "db_connection_general.php";

    $sectionName = $_POST['sectionName'];
    $gradeLevel = $_POST['gradeLevel'];

    if(empty($sectionName) || empty($gradeLevel))
    {
        header("Location: ../admin/create.php?newsection&error=emptyFields");
        exit();
    }

    $duplicateCount = 0;
    // check for duplicates
    $sql = "SELECT * FROM `sectionsList` WHERE `sectionName` = ? AND `gradeLevel` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/create.php?newsection&error=SQL&task=checkduplicateSection");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "si", $sectionName, $gradeLevel);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
        {
            $duplicateCount++;
        }
        if($duplicateCount > 0)
        {
            header("Location: ../admin/create.php?newsection&error=duplicateSection");
            exit();
        }
    }

    // create section
    $sql = "INSERT INTO `sectionsList` (sectionName, gradeLevel) VALUES (?, ?)";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)) 
    {
        header("Location: ../admin/create.php?newsection&error=SQL&task=createsection");
        exit();
    }
    else 
    {
        mysqli_stmt_bind_param($stmt, "si", $sectionName, $gradeLevel);
        mysqli_stmt_execute($stmt);
    }

    // get id
    $sql = "SELECT * FROM `sectionsList` WHERE `sectionName` = ? AND `gradeLevel` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/create.php?newsection&error=SQL&task=getSectionId");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "si", $sectionName, $gradeLevel);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
        {
            $sectionId = $row['sectionId'];
        }
    }

    // add column to vote results
    $sql = "ALTER TABLE `voteResults` ADD `section_" . $sectionId . "` INT NOT NULL AFTER `candidateId`;
    ";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)) 
    {
        header("Location: ../admin/create.php?newsection&error=SQL&task=createsection");
        exit();
    }
    else 
    {
        mysqli_stmt_execute($stmt);
    }

    header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&success=createsection");
    exit();
}
else if (isset($_POST['addvoter']))
{
    require "db_connection_general.php";

    $sectionId = $_POST['sectionId'];
    $LRN = $_POST['LRN'];

    if(empty($LRN) || empty($sectionId))
    {
        header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&error=emptyFields");
        exit();
    }
    else if (strlen($LRN) != 12 || (!preg_match("/^[1-9][0-9]*$/", $LRN)))
    {
        header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&error=invalidLRN");
        exit();
    }

    $duplicateCount = 0;
    // check for duplicate on specific section
    $sql = "SELECT * FROM `votersList` WHERE `LRN` = ? AND `sectionId` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&error=SQL&task=checkduplicateVoter");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "si", $LRN, $sectionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
        {
            $duplicateCount++;
        }
        if($duplicateCount > 0)
        {
            header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&error=LRNonSection");
            exit();
        }
    }
    // check for duplicates on other sections
    $sql = "SELECT * FROM `votersList` WHERE `LRN` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&error=SQL&task=checkduplicateVoter");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "s", $LRN);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
        {
            $duplicateCount++;
        }
        if($duplicateCount > 0)
        {
            header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&error=LRNonOtherSection");
            exit();
        }
    }
    // insert student
    $voted = 0;
    $sql = "INSERT INTO `votersList` (LRN, sectionId, voted) VALUES (?, ?, ?)";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)) 
    {
        header("Location: ../admin/voterslist.php?error=SQL&task=addVoter");
        exit();
    }
    else 
    {
        mysqli_stmt_bind_param($stmt, "sii", $LRN, $sectionId, $voted);
        mysqli_stmt_execute($stmt);
    }
    header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&addVoter=1");
    exit();
}
else if (isset($_POST['modifySection']))
{
    require "db_connection_general.php";

    $sectionId = $_POST['sectionId'];
    $sectionName = $_POST['sectionName'];
    $gradeLevel = $_POST['gradeLevel'];

    if(empty($sectionName) || empty($gradeLevel))
    {
        $_SESSION['modifyToken'] = 1;
        header("Location: ../admin/modify.php?sectionId=".$sectionId."&error=emptyFields");
        exit();
    }

    $duplicateCount = 0;
    // check for duplicates
    $sql = "SELECT * FROM `sectionsList` WHERE `sectionName` = ? AND `gradeLevel` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        $_SESSION['modifyToken'] = 1;
        header("Location: ../admin/modify.php?sectionId=".$sectionId."&error=SQL&task=checkDuplicateSection");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "si", $sectionName, $gradeLevel);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
        {
            $duplicateCount++;
        }
        if($duplicateCount > 0)
        {
            $_SESSION['modifyToken'] = 1;
            header("Location: ../admin/modify.php?sectionId=".$sectionId."&error=duplicateSection");
            exit();
        }
    }

    // update
    $sql = "UPDATE `sectionslist` SET `sectionName`= ?, `gradeLevel` = ? WHERE `sectionId` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        $_SESSION['modifyToken'] = 1;
        header("Location: ../admin/modify.php?sectionId=".$sectionId."&error=SQL&task=modifySection");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "sii", $sectionName, $gradeLevel, $sectionId);
        mysqli_stmt_execute($stmt);
    }

    header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&modifySection=1");
    exit();
}
else if (isset($_POST['deleteStudent']))
{
    require "db_connection_general.php";
    $sectionId = $_GET['sectionId'];
    $LRN = $_GET['LRN'];

    if(isset($_GET['admin']))
    {
        $failLink = "../admin/challenge.php?LRN=".$LRN."&sectionId=".$sectionId;
        // verify password of logged in user
        require "db_verify.php";
        if ($passcodeCheck == false) 
        { 
            header("Location: ".$failLink."&error=invalidPassword&code=1");
            exit();
        }
        else if ($passcodeCheck == true)
        {
            echo"";
        }
    }

    // delete student
    $sql = "DELETE FROM `voterslist` WHERE `voterslist`.`LRN` = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) { 
        header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&error=sql&task=deleteStudent");
        exit();
    }
    else {
        mysqli_stmt_bind_param($stmt, "s", $LRN);
        mysqli_stmt_execute($stmt);
    }

    header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&deleteStudent=1");
    exit();
}
else if (isset($_POST['deleteSection']))
{
    require "db_connection_general.php";
    $sectionId = $_GET['sectionId'];
    $failLink = "../admin/challenge.php?sectionId=".$sectionId;

    // verify password of logged in user
    require "db_verify.php";
    if ($passcodeCheck == false) 
    { 
        header("Location: ".$failLink."&error=invalidPassword&code=1");
        exit();
    }
    else if ($passcodeCheck == true)
    {
        // delete section
        $sql = "DELETE FROM `sectionslist` WHERE `sectionslist`.`sectionId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) { 
            header("Location: ".$failLink."&error=sql&task=deleteSection");
            exit();
        }
        else {
            mysqli_stmt_bind_param($stmt, "i", $sectionId);
            mysqli_stmt_execute($stmt);
        }

        // delete contents
        $sql = "DELETE FROM `voterslist` WHERE `voterslist`.`sectionId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) { 
            header("Location: ".$failLink."&error=sql&task=deleteVoters");
            exit();
        }
        else {
            mysqli_stmt_bind_param($stmt, "i", $sectionId);
            mysqli_stmt_execute($stmt);
        }

        // delete column
        $sql = "ALTER TABLE `voteresults` DROP `section_".$sectionId."`;";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) { 
            header("Location: ".$failLink."&error=sql&task=deleteSection");
            exit();
        }
        else {
            mysqli_stmt_execute($stmt);
        }
    }
    else
    {
        header("Location: ".$failLink."&error=invalidPassword&code=2");
        exit();   
    }

    header("Location: ../admin/voterslist.php?deleteSection=1");
    exit();
}
else if (isset($_POST['addVoterBulk']))
{
    require "db_connection_general.php";
    $failed = 0;
    $completed = 0;
    $studentLimit = $_POST['studentLimit'];
    $sectionId = $_POST['sectionId'];
    $LRN = $_POST['LRN'];
    $errorLRN = array($studentLimit);
    $errorReason = array($studentLimit);

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

    if ($showResults == 1)
    {
        // check for password verification
        $failLink = "../admin/addStudents.php?studentLimit=".$studentLimit."&sectionId=".$sectionId;

        // verify password of logged in user
        require "db_verify.php";
        if ($passcodeCheck == false) 
        { 
            header("Location: ".$failLink."&error=invalidPassword&code=1");
            exit();
        }
        else if ($passcodeCheck == true)
        {
            echo "";
        }
    }

    if(empty($sectionId))
    {
        header("Location: ../admin/addStudents.php?sectionId=".$sectionId."&error=emptySectionId");
        exit();
    }

    for($studentCount = 0; $studentCount < $studentLimit; $studentCount++)
    {
        if(empty($LRN[$studentCount]))
        {
            $errorLRN[$failed] = 0;
            $errorReason[$failed] = ($studentCount+1) . ": Blank LRN";
            $failed++;
            continue; // skip to next value
        }
        if ((!preg_match("/^[1-9][0-9]*$/", $LRN[$studentCount])))
        {
            $errorLRN[$failed] = $LRN[$studentCount];
            $errorReason[$failed] = ($studentCount+1) . ": " . $errorLRN[$failed] . " : Invalid LRN";
            $failed++;
            continue;
        }
        if (strlen($LRN[$studentCount]) != 12)
        {
            $errorLRN[$failed] = $LRN[$studentCount];
            $errorReason[$failed] = ($studentCount+1) . ": " . $errorLRN[$failed] . " : Invalid LRN";
            $failed++;
            continue;
        }
        $duplicateCount = 0;
        // check for duplicate on specific section
        $sql = "SELECT * FROM `votersList` WHERE `LRN` = ? AND `sectionId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            $errorLRN[$failed] = $LRN[$studentCount];
            $errorReason[$failed] = ($studentCount+1) . ": " . $errorLRN[$failed] . " : Code Error - Checking duplicate on current section";
            $failed++;
            continue;
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "si", $LRN[$studentCount], $sectionId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result))
            {
                $duplicateCount++;
            }
            if($duplicateCount > 0)
            {
                $errorLRN[$failed] = $LRN[$studentCount];
                $errorReason[$failed] = ($studentCount+1) . ": " . $errorLRN[$failed] . " : Duplicate found on current section";
                $failed++;
                continue;
            }
        }
        $duplicateCount = 0;
        // check for duplicates on other sections
        $sql = "SELECT * FROM `votersList` WHERE `LRN` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            $errorLRN[$failed] = $LRN[$studentCount];
            $errorReason[$failed] = ($studentCount+1) . ": " . $errorLRN[$failed] . " : Code Error - Checking duplicate on current section";
            $failed++;
            continue;
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "s", $LRN[$studentCount]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result))
            {
                $duplicateCount++;
                $idSection = $row['sectionId'];
                $sql5 = "SELECT * FROM `sectionsList` WHERE `sectionId` = ?";
                $stmt5 = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt5, $sql5))
                {
                    $errorLRN[$failed] = $LRN[$studentCount];
                    $errorReason[$failed] = ($studentCount+1) . ": " . $errorLRN[$failed] . " : Code Error - Checking Section Name of Duplicate LRN";
                    $failed++;
                    continue;
                }
                else
                {
                    mysqli_stmt_bind_param($stmt5, "i", $idSection);
                    mysqli_stmt_execute($stmt5);
                    $result5 = mysqli_stmt_get_result($stmt5);
                    $row5 = mysqli_fetch_assoc($result5);
                    $levelGrade = $row5['gradeLevel'];
                    $nameSection = $row5['sectionName'];
                }
            }
            if($duplicateCount > 0)
            {
                $errorLRN[$failed] = $LRN[$studentCount];
                $errorReason[$failed] = ($studentCount+1) . ": " . $errorLRN[$failed] . " : Duplicate found on other section (" . $levelGrade . " - " . $nameSection . ")";
                $failed++;
                continue;
            }
        }
        // insert student
        $voted = 0;
        $sql = "INSERT INTO `votersList` (LRN, sectionId, voted) VALUES (?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)) 
        {
            $errorLRN[$failed] = $LRN[$studentCount];
            $errorReason[$failed] = ($studentCount+1) . ": " . $errorLRN[$failed] . " : Code Error - Add LRN to Database";
            $failed++;
            continue;
        }
        else 
        {
            mysqli_stmt_bind_param($stmt, "sii", $LRN[$studentCount], $sectionId, $voted);
            mysqli_stmt_execute($stmt);
        }
        $completed++;
    }
    if($failed == 0 && $completed > 0)
    {
        header("Location: ../admin/voterslist.php?sectionId=".$sectionId."&addvoter=1&success=".$completed);
    }
    else if ($failed > 0 && $completed == 0)
    {
        echo "
            <div id='addStudents' class='page-sub-window limitedWindowSize'>
                <div class='vertical-center'>
                    <div class='page-sub-window-content'>
                        <div class='window-grid'>
                            <p>Status</p>
                            <a href='../admin/voterslist.php?sectionId=".$sectionId."'><img src='../assets/img/close.png'></a>
                        </div>
                        <span class='span-16px'></span>
                        <p>All unsuccessful. Returned " . $failed . " error(s).</p>
        ";
    }
    else if ($failed > 0 && $completed > 0)
    {
        echo "
        <div id='addStudents' class='page-sub-window limitedWindowSize'>
            <div class='vertical-center'>
                <div class='page-sub-window-content'>
                    <div class='window-grid'>
                        <p>Status</p>
                        <a href='../admin/voterslist.php?sectionId=".$sectionId."'><img src='../assets/img/close.png'></a>
                    </div>
                    <span class='span-16px'></span>
                    <p>Successfully added " . $completed . "student(s), but returned " . $failed . " error(s).</p>
        ";
    }
    if($failed > 0)
    {
        echo "
        <span class='span-8px'></span>
        <div class='window-grid'>
            <p>List of Errors</p>
            <span></span>
        </div>
        <span class='span-8px'></span>
        ";
        echo "<form action='../admin/addStudents.php?sectionId=".$sectionId."' method='post'>";
        echo "<input type='hidden' name='voters' value='".($failed)."'>";
        for($count = 0; $count < count($errorLRN); $count++)
        {
            // if($errorLRN[$count] == 0)break;
            echo "<p>" . $errorReason[$count] . "</p>";
            echo "<input type='hidden' name='LRN[]' value='".$errorLRN[$count]."'>";
        }
        echo "<span class='span-16px'></span>";
        echo "<button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='initAddStudents'>Modify Errors</button></form>";
    }
    echo "
            </div>
        </div>
    </div>
    ";
}