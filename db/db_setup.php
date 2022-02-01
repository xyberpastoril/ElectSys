<?php
session_start();
if(isset($_POST['start']))
{ // start
    $_SESSION['setup'] = 1;
    // initalize three admins placeholders
    $_SESSION['username'][3] = array("","","");
    $_SESSION['password'][3] = array("","","");

    header("Location: ../setup/createadmin.php?admin=1");
    exit();
}
else if (isset($_POST['addAdmin']))
{   // processing step 1 input
    $username = $_POST['username'];
    $password = $_POST['password'];
    $admin = $_GET['admin'];
    $repeatPassword = $_POST['repeatPassword'];

    $usernameLength = strlen($username);
    $passwordLength = strlen($password);

    // error handling
    if(empty($username) || empty($password) || empty($repeatPassword))
    {
        header("Location: ../setup/createadmin.php?error=emptyFields&admin=".$admin);
        exit();
    }
    else if ($usernameLength < 5)
    {
        header("Location: ../setup/createadmin.php?error=shortUsername&admin=".$admin);
        exit();
    }
    else if (!preg_match("/^[a-zA-Z0-9_.]*$/", $username))
    {
        header("Location: ../setup/createadmin.php?error=invalidUsername&admin=".$admin);
        exit();
    }
    else if ($passwordLength < 8)
    {
        header("Location: ../setup/createadmin.php?error=shortPassword&admin=".$admin);
        exit();
    }
    else if ($password != $repeatPassword)
    {
        header("Location: ../setup/createadmin.php?error=unmatchedPassword&admin=".$admin);
        exit();
    }
    else if ($admin > 1)
    {
        if ($admin > 2)
        {
            if($username == $_SESSION['username'][1])
            {
                header("Location: ../setup/createadmin.php?error=sameCredentialsAdmin&admin=".$admin);
                exit();
            }
            else if ($username == $_SESSION['username'][0])
            {
                header("Location: ../setup/createadmin.php?error=sameCredentialsAdmin&admin=".$admin);
                exit();
            }
        }
        else if ($admin > 1)
        {
            if($username == $_SESSION['username'][0])
            {
                header("Location: ../setup/createadmin.php?error=sameCredentialsAdmin&admin=".$admin);
                exit();
            }
        }
    }
    // no errors?
    $_SESSION['username'][$admin-1] = $username;
    $_SESSION['password'][$admin-1] = $password;

    if($admin < 3)
    {
        header("Location: ../setup/createadmin.php?admin=".++$admin);
        exit();
    }
    else
    {
        header("Location: ../setup/geninfo.php");
        exit();
    }
}
else if (isset($_POST['addInfo']))
{   // processing step 2 input
    $school = $_POST['school'];
    $organization = $_POST['organization'];
    $votingMethod = $_POST['votingMethod'];

    // error handling
    if(empty($school) || empty($organization) || empty($votingMethod))
    {
        header("Location: ../setup/geninfo.php?error=emptyFields");
        exit();
    }

    $_SESSION['school'] = $school;
    $_SESSION['organization'] = $organization;
    $_SESSION['votingMethod'] = $votingMethod;

    header("Location: ../setup/review.php");
    exit();
}
else if (isset($_POST['install']))
{
    // create database
    require "db_connection_setup.php";
    $sql = "CREATE DATABASE `arms_aes`";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../setup/review.php?error=SQL&code=setup1");
        exit();
    }
    else
    {
        mysqli_stmt_execute($stmt);
    }

    require "db_connection_general.php";

    // create tables
    for($tableCtr = 1; $tableCtr <= 9; $tableCtr++)
    {
        switch($tableCtr)
        {
            case 1:
                $sql = 
                "CREATE TABLE `adminAccounts` (
                    adminId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) NOT NULL,
                    passcode LONGTEXT NOT NULL,
                    adminLevel INT NOT NULL
                )";
                break;
            case 2:
                $sql = 
                "CREATE TABLE `sectionsList` (
                    sectionId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    sectionName VARCHAR(255),
                    gradeLevel INT
                )";
                break;
            case 3:
                $sql = 
                "CREATE TABLE `votersList` (
                    LRN BIGINT NOT NULL PRIMARY KEY,
                    sectionId INT,
                    voted INT
                )";
                break;
            case 4:
                $sql = 
                "CREATE TABLE `positionList` (
                    positionId INT PRIMARY KEY,
                    positionName VARCHAR(255),
                    votingMethod INT,
                    specificLevel INT,
                    candidatesPerParty INT
                )";
                break; 
            case 5:
                $sql = 
                "CREATE TABLE `partyList` (
                    partyListId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    partyListName VARCHAR(255),
                    partyListAbbr VARCHAR(255),
                    partyListPic TEXT,
                    partyListTheme VARCHAR(6)
                )";
                break; 
            case 6:
                $sql = 
                "CREATE TABLE `candidatesList` (
                    candidateId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    firstName VARCHAR(255) NOT NULL,
                    middleName VARCHAR(255),
                    lastName VARCHAR(255),
                    position INT,
                    partyList INT,
                    displayPic TEXT,
                    totalVotes INT NOT NULL
                )";
                break;
            case 7:
                $sql = 
                "CREATE TABLE `voteResults` (
                    candidateId INT NOT NULL AUTO_INCREMENT PRIMARY KEY
                )";
                break;
            case 8:
                $sql =
                "CREATE TABLE `operationSettings` (
                    settingName TEXT,
                    settingValue TEXT
                )";
                break;
            case 9:
                $sql = 
                "CREATE TABLE `menuList` (
                    pageName VARCHAR(255),
                    pageLink TEXT,
                    iconPic TEXT,
                    adminLevel INT
                )";
                break;
        }
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)) 
        {
            header("Location: ../setup/review.php?error=SQL&code=setup2&tableCtr=".$tableCtr);
            exit();
        }
        else 
        {
            mysqli_stmt_execute($stmt);
        }
    }
    // Add Admins
    $adminAction = "AddAdmin";
    $errorLocation = "../setup/review.php?error=SQL&code=setup3";
    for($adminNo = 1; $adminNo <= 4; $adminNo++)
    {
        switch($adminNo)
        {
            case 1: $username = "arms";$password = "armstech19";$adminLevel = 4;break;
            case 2: $username = $_SESSION['username'][0];$password = $_SESSION['password'][0];$adminLevel = 3;break;
            case 3: $username = $_SESSION['username'][1];$password = $_SESSION['password'][1];$adminLevel = 2;break;
            case 4: $username = $_SESSION['username'][2];$password = $_SESSION['password'][2];$adminLevel = 2;break;
        }
        require "db_admin.php";
    }

    // Add Positions
    for($positionNo = 1; $positionNo <= 13; $positionNo++)
    {
        switch($positionNo)
        {
            case 1: 
                $positionName = "President";
                $votingMethod = 1;
                $specificLevel = 0;
                $candidatesPerParty = 1;
                break;
            case 2: 
                $positionName = "Vice-President";
                $votingMethod = 1;
                $specificLevel = 0;
                $candidatesPerParty = 1;
                break;
            case 3: 
                $positionName = "Secretary";
                $votingMethod = 1;
                $specificLevel = 0;
                $candidatesPerParty = 1;
                break;
            case 4: 
                $positionName = "Treasurer";
                $votingMethod = 1;
                $specificLevel = 0;
                $candidatesPerParty = 1;
                break;
            case 5: 
                $positionName = "Auditor";
                $votingMethod = 1;
                $specificLevel = 0;
                $candidatesPerParty = 1;
                break;
            case 6: 
                $positionName = "Public Information Officer";
                $votingMethod = 1;
                $specificLevel = 0;
                $candidatesPerParty = 1;
                break;
            case 7: 
                $positionName = "Peace Officer";
                $votingMethod = 1;
                $specificLevel = 0;
                $candidatesPerParty = 2;
                break;
            case 8: 
                $positionName = "Grade 7 Representative";
                $votingMethod = 2;
                $specificLevel = 7;
                $candidatesPerParty = 2;
                break;
            case 9: 
                $positionName = "Grade 8 Representative";
                $votingMethod = 1;
                switch($_SESSION['votingMethod'])
                {
                    case 1: $specificLevel = 7;break;
                    case 3: $specificLevel = 8;break;
                }
                $candidatesPerParty = 2;
                break;
            case 10: 
                $positionName = "Grade 9 Representative";
                $votingMethod = 1;
                switch($_SESSION['votingMethod'])
                {
                    case 1: $specificLevel = 8;break;
                    case 3: $specificLevel = 9;break;
                }
                $candidatesPerParty = 2;
                break;
            case 11: 
                $positionName = "Grade 10 Representative";
                $votingMethod = 1;
                switch($_SESSION['votingMethod'])
                {
                    case 1: $specificLevel = 9;break;
                    case 3: $specificLevel = 10;break;
                }
                $candidatesPerParty = 2;
                break;
            case 12: 
                $positionName = "Grade 11 Representative";
                $votingMethod = 2;
                $specificLevel = 11;
                $candidatesPerParty = 2;
                break;
            case 13: 
                $positionName = "Grade 12 Representative";
                $votingMethod = 1;
                switch($_SESSION['votingMethod'])
                {
                    case 1: $specificLevel = 11;break;
                    case 3: $specificLevel = 12;break;
                }
                $candidatesPerParty = 2;
                break;
        }

        $sql = "INSERT INTO `positionList` (positionId, positionName, votingMethod, specificLevel, candidatesPerParty) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: ../setup/review.php?error=SQL&code=setup4");
            exit();
        }
        else 
        {
            mysqli_stmt_bind_param($stmt, "isiii", $positionNo, $positionName, $votingMethod, $specificLevel, $candidatesPerParty);
            mysqli_stmt_execute($stmt);
        }
    }

    // Operation Settings
    for($operationNo = 1; $operationNo <= 7; $operationNo++)
    {
        switch($operationNo)
        {
            case 1: 
                $settingName = "voteOpen"; 
                $settingValue = 0;
            break;
            case 2: 
                $settingName = "votingMethod"; 
                $settingValue = $_SESSION['votingMethod'];
            break;
            case 3:
                $settingName = "organizationName"; 
                $settingValue = $_SESSION['organization'];
            break;
            case 4: 
                $settingName = "school"; 
                $settingValue = $_SESSION['school'];
            break;
            case 5: 
                $settingName = "electionLogo"; 
                $settingValue = "assets/img/schoolLogo.png";
            break;
            case 6: 
                $settingName = "backgroundPhoto"; 
                $settingValue = "assets/img/bg.jpg";
            break;
            case 7:
                $settingName = "showResults";
                $settingValue = 0;
            break;
        }

        $sql = "INSERT INTO `operationSettings` (settingName, settingValue) VALUES (?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: ../setup/review.php?error=SQL&code=setup4");
            exit();
        }
        else 
        {
            mysqli_stmt_bind_param($stmt, "ss", $settingName, $settingValue);
            mysqli_stmt_execute($stmt);
        }
    }

    // Admin Menu List
    for($menuNo = 1; $menuNo <= 3; $menuNo++)
    {
        switch($menuNo)
        {
            case 1: 
                $pageName = "Voters List";
                $pageLink = "voterslist.php";
                $iconPic = "../assets/img/admin/voters.png";
                $adminLevel = 1;
                break;
            case 2:
                $pageName = "Candidates";
                $pageLink = "candidates.php";
                $iconPic = "../assets/img/admin/candidates.png";
                $adminLevel = 2;
                break;
            case 3:
                $pageName = "Results";
                $pageLink = "results.php";
                $iconPic = "../assets/img/admin/results.png";
                $adminLevel = 1;
                break;
            /*case 4:
                $pageName = "Settings";
                $pageLink = "settings.php";
                $iconPic = "assets/img/menu/settings.png";
                $adminLevel = 1;
                break;*/
        }

        $sql = "INSERT INTO `menuList` (pageName, pageLink, iconPic, adminLevel) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: ../setup/review.php?error=SQL&code=setup4");
            exit();
        }
        else 
        {
            mysqli_stmt_bind_param($stmt, "sssi", $pageName, $pageLink, $iconPic, $adminLevel);
            mysqli_stmt_execute($stmt);
        }
    }

    header("Location: ../setup/success.php");
    exit();
}
else if (isset($_POST['addElectionLogo']))
{
    require "db_connection_general.php";
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmpname = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    $file_type = $file['type'];
    $file_ext = explode('.', $file_name);
    $file_actual_ext = strtolower(end($file_ext));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if(in_array($file_actual_ext, $allowed)) 
    {
        if($file_error === 0) 
        {
            if ($file_size < 1000000) 
            {
                $file_name_new = uniqid('', true). ".".$file_actual_ext;
                $file_destination = '../assets/img/admin/'.$file_name_new;
                $file_destination_db = 'assets/img/admin/'.$file_name_new;
                move_uploaded_file($file_tmpname, $file_destination);
                $sql = "UPDATE `operationSettings` SET `settingValue` = ? WHERE `settingName` = 'electionLogo'";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql))
                {
                    header("Location: ../setup/success.php?error=SQL&task=uploadLogoLink");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "s", $file_destination_db);
                    mysqli_stmt_execute($stmt);
                    // header("Location: ../setup/coverphoto.php");
                    header("Location: ../admin/index.php?addLogo=1");
                    exit();
                }
            }
            else
            {
                header("Location: ../setup/success.php?error=toobig");
                exit();
                // echo "Your file is too big";
            }
        }
        else
        {
            header("Location: ../setup/success.php?error=error");
            exit();
        }
    }
    else
    {
        if($file_name == "") {
            header("Location: ../setup/success.php?error=nophoto");
            exit();
        }
        else {
            header("Location: ../setup/success.php?error=invalidtype");
            exit();
        }
    }
}
else if (isset($_POST['addBackgroundPhoto']))
{
    require "db_connection_general.php";
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmpname = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    $file_type = $file['type'];
    $file_ext = explode('.', $file_name);
    $file_actual_ext = strtolower(end($file_ext));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if(in_array($file_actual_ext, $allowed)) 
    {
        if($file_error === 0) 
        {
            if ($file_size < 100000000) 
            {
                $file_name_new = uniqid('', true). ".".$file_actual_ext;
                $file_destination = '../assets/img/admin/'.$file_name_new;
                $file_destination_db = 'assets/img/admin/'.$file_name_new;
                move_uploaded_file($file_tmpname, $file_destination);
                $sql = "UPDATE `operationSettings` SET `settingValue` = ? WHERE `settingName` = 'backgroundPhoto'";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql))
                {
                    header("Location: ../setup/coverphoto.php?error=SQL&task=uploadLogoLink");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "s", $file_destination_db);
                    mysqli_stmt_execute($stmt);
                    header("Location: ../admin/index.php");
                    exit();
                }
            }
            else
            {
                header("Location: ../setup/coverphoto.php?error=toobig");
                exit();
                // echo "Your file is too big";
            }
        }
        else
        {
            header("Location: ../setup/coverphoto.php?error=error");
            exit();
        }
    }
    else
    {
        if($file_name == "") {
            header("Location: ../setup/coverphoto.php?error=nophoto");
            exit();
        }
        else {
            header("Location: ../setup/coverphoto.php?error=invalidtype");
            exit();
        }
    }
}