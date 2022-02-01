<?php
session_start();
if(isset($adminAction) == "AddAdmin")
{
    $sql = "INSERT INTO `adminAccounts` (username, passcode, adminLevel) VALUES (?, ?, ?)";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: " . $errorLocation . "&task=addAdmin");
        exit();
    }
    else 
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param($stmt, "ssi", $username, $hashedPassword, $adminLevel);
        mysqli_stmt_execute($stmt);
    }
}
else if (isset($_POST['login']))
{
    require "db_connection_general.php";
    $username = $_POST['username'];
    $passcode = $_POST['password'];

    // error checking
    if(empty($username) || empty($passcode))
    {
        header("Location: ../admin/index.php?error=emptyFields");
        exit();
    }
    else
    {
        $sql = "SELECT * FROM `adminAccounts` WHERE `username` = ?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) { 
            header("Location: ../admin/index.php?error=sql&code=1");
            exit();
        }
        else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt); 
            if ($row = mysqli_fetch_assoc($result)) { 
                $passcodeCheck = password_verify($passcode, $row['passcode']);
                if ($passcodeCheck == false) { 
                    header("Location: ../admin/index.php?error=invalidPassword&username=" . $username);
                    exit();
                }
                else if ($passcodeCheck == true) { 
                    $_SESSION['adminId'] = $row['adminId'];
                    $_SESSION['adminUsername'] = $row['username'];
                    $_SESSION['adminLevel'] = $row['adminLevel']; 
                    $_SESSION['modifyToken'] = 0;
                    header("Location: ../admin/index.php?login=1"); 
                    exit(); 
                }
                else {
                    header("Location: ../admin/index.php?error=invalidPassword&username=" . $username);
                    exit(); 
                }
            }
            else {
                header("Location: ../admin/index.php?error=noAdmin");
                exit();
            }
        }
    }
}
else if (isset($_POST['logout']))
{
    session_unset();
    header("Location: ../admin/index.php?logout=1");
    exit();
}
else if (isset($_POST['voteOpen']))
{
    require "db_connection_general.php";
    $voteOpen = $_GET['voteOpen'];  
    $admin = $_GET['admin'];
    $showResults = 1;
    $settingName = "voteOpen";
    $failLink = "../admin/challenge.php?voteOpen=".$voteOpen."&admin=".$admin;

    if($admin >= 2)
    {
        for($adminSignCount = $admin-1;$adminSignCount >= 0;$adminSignCount--)
        {
            if($_POST['username'] == $_SESSION['adminSign'][$adminSignCount])
            {
                header("Location: ".$failLink."&error=adminAlreadySigned");
                exit();
            }
        }
    }

    // verify password of logged in user
    require "db_verify.php";
    if($passcodeCheck == false)
    {
        header("Location: ".$failLink."&error=invalidPassword");
        exit();
    }
    else if ($passcodeCheck == true)
    {
        if($admin == 3)
        {
            $sql = "UPDATE `operationSettings` SET `settingValue` = ? WHERE `operationSettings`.`settingName` = ?";
            $stmt = mysqli_stmt_init($conn);
            if(!mysqli_stmt_prepare($stmt, $sql))
            {
                header("Location: ".$failLink."&error=SQL&task=updateVoteStatus");
                exit();
            }
            else 
            {
                mysqli_stmt_bind_param($stmt, "is", $voteOpen, $settingName);
                mysqli_stmt_execute($stmt);
            }

            // update showResults value
            $settingName = "showResults";
            $sql = "UPDATE `operationSettings` SET `settingValue` = ? WHERE `operationSettings`.`settingName` = ?";
            $stmt = mysqli_stmt_init($conn);
            if(!mysqli_Stmt_prepare($stmt, $sql))
            {
                header("Location: ".$failLink."&error=SQL&task=updateVoteStatus2");
                exit();
            }
            else
            {
                mysqli_stmt_bind_param($stmt, "is", $showResults, $settingName);
                mysqli_stmt_execute($stmt);
            }

            header("Location: ../admin/settings.php?voteOpen=".$voteOpen);
            exit();
        }
        else
        {
            $_SESSION['adminSign'][$admin-1] = $_POST['username'];
            header("Location: ../admin/challenge.php?voteOpen=".$voteOpen."&admin=".++$admin);
            exit();
        }
    }
}
else if (isset($_POST['addAdminSettings']))
{
    require 'db_connection_general.php';
    $username = $_POST['username'];
    $password = $_POST['password'];
    $repeatPassword = $_POST['repeatPassword'];
    $passcode = $_POST['passcode']; //adminPassword

    $usernameLength = strlen($username);
    $passwordLength = strlen($password);

    // check admin password
    $failLink = "../admin/create.php?newAdmin=1";
    require 'db_verify.php';
    if ($passcodeCheck == false) 
    { 
        header("Location: ".$failLink."&error=invalidAdminPassword&code=1");
        exit();
    }
    else if ($passcodeCheck == true) 
    {
        if(empty($username) || empty($password) || empty($repeatPassword))
        {
            header("Location: ".$failLink."&error=emptyFields&admin=".$admin);
            exit();
        }
        else if ($usernameLength < 5)
        {
            header("Location: ".$failLink."&error=shortUsername&admin=".$admin);
            exit();
        }
        else if (!preg_match("/^[a-zA-Z0-9_.]*$/", $username))
        {
            header("Location: ".$failLink."&error=invalidUsername&admin=".$admin);
            exit();
        }
        else if ($passwordLength < 8)
        {
            header("Location: ".$failLink."&error=shortPassword&admin=".$admin);
            exit();
        }
        else if ($password != $repeatPassword)
        {
            header("Location: ".$failLink."&error=unmatchedPassword&admin=".$admin);
            exit();
        }

        // check for duplicates
        $sql = "SELECT * FROM `adminAccounts` WHERE `username` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            exit("error. checking of admin username duplicates.");
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if($row = mysqli_fetch_assoc($result))
            {
                header("Location: ".$failLink."&error=adminTaken");
                exit();
            } 
        }

        // no errors?
        $errorLocation = $failLink;
        $adminAction = "AddAdmin";
        $adminLevel = $_POST['adminLevel'];
        require 'db_admin.php';
    
        header("Location: ../admin/settings.php?newAdminSuccess=1");
        exit();
    }
}
else if (isset($_POST['changePassword']))
{
    require 'db_connection_general.php';
    $password = $_POST['password'];
    $repeatPassword = $_POST['repeatPassword'];
    $passcode = $_POST['passcode']; //adminPassword
    $adminId = $_SESSION['adminId'];

    $passwordLength = strlen($password);
    // check admin password
    $failLink = "../admin/modify.php?changePassword=1";
    $_SESSION['modifyToken'] = 1;
    require 'db_verify.php';
    if ($passcodeCheck == false) 
    { 
        header("Location: ".$failLink."&error=invalidAdminPassword&code=1");
        exit();
    }
    else if ($passcodeCheck == true) 
    {
        if(empty($password) || empty($repeatPassword))
        {
            header("Location: ".$failLink."&error=emptyFields&admin=".$admin);
            exit();
        }
        else if ($passwordLength < 8)
        {
            header("Location: ".$failLink."&error=shortPassword&admin=".$admin);
            exit();
        }
        else if ($password != $repeatPassword)
        {
            header("Location: ".$failLink."&error=unmatchedPassword&admin=".$admin);
            exit();
        }
        // no errors?
        $errorLocation = $failLink;

        $sql = "UPDATE `adminAccounts` SET `passcode` = ? WHERE `adminAccounts`.`adminId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: " . $errorLocation . "&task=addAdmin");
            exit();
        }
        else 
        {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $adminId);
            mysqli_stmt_execute($stmt);
        }
    
        header("Location: ../admin/settings.php?changePasswordSuccess=1");
        exit();
    }
}
else if (isset($_POST['changeAdminLevel']))
{
    require 'db_connection_general.php';
    $adminLevel = $_POST['adminLevel'];
    $passcode = $_POST['passcode']; //adminPassword
    $changeAdminId = $_GET['adminId'];

    // check admin password
    $failLink = "../admin/modify.php?adminId=".$changeAdminId;
    require 'db_verify.php';
    if ($passcodeCheck == false) 
    { 
        header("Location: ".$failLink."&error=invalidAdminPassword&code=1");
        exit();
    }
    else if ($passcodeCheck == true) 
    {
        $sql = "UPDATE `adminAccounts` SET `adminLevel` = ? WHERE `adminAccounts`.`adminId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: ".$failLink."&error=SQL&task=updateDetails");
            exit();
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "ii", $adminLevel, $changeAdminId);
            mysqli_stmt_execute($stmt);
            header("Location: ../admin/settings.php?modifyAdmin=1&adminId=".$changeAdminId);
            exit();
        }
    }
}
else if (isset($_POST['deleteAdmin']))
{
    require 'db_connection_general.php';
    $deletePasscode = $_POST['passcode2'];
    $deleteAdminId = $_GET['adminId'];
    $failLink = "../admin/challenge.php?adminId=".$deleteAdminId;
    // check admin password
    require 'db_verify.php';
    if ($passcodeCheck == false) 
    { 
        header("Location: ".$failLink."&error=invalidAdminPassword&code=1");
        exit();
    }
    else if ($passcodeCheck == true) 
    {
        
        $sql = "DELETE FROM `adminaccounts` WHERE `adminaccounts`.`adminId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: ".$failLink."&error=SQL&task=deleteAccount");
            exit();        
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "i", $deleteAdminId);
            mysqli_stmt_execute($stmt);
            header("Location: ../admin/settings.php?deleteAccount=1");
            exit();
        }

    }
}
else if (isset($_POST['modifyElectionLogo']))
{
    // unlink past
    $failLink = "../admin/settings.php?";

    require "db_connection_general.php";
    // delete old photo
    $sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'electionLogo'";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../sqlerror.php?task=deleteOldPhoto&fromPage=modify.php?photo=1&partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']);
        exit();
    }
    else
    {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $oldDisplayPic = "../".$row['settingValue'];
        if($oldDisplayPic != "../assets/img/schoolLogo.png")
        { // customized, will be changed on official build for commercial use
            if(!unlink($oldDisplayPic)) {
                echo "Error deleting old profile pic";
            }
        }
    }

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
                    header("Location: ../admin/settings.php?error=SQL&task=uploadLogoLink");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "s", $file_destination_db);
                    mysqli_stmt_execute($stmt);
                    // header("Location: ../setup/coverphoto.php");
                    header("Location: ../admin/settings.php?modifyElectionLogo=1");
                    exit();
                }
            }
            else
            {
                header("Location: ../admin/settings.php?error=toobig");
                exit();
                // echo "Your file is too big";
            }
        }
        else
        {
            header("Location: ../admin/settings.php?error=error");
            exit();
        }
    }
    else
    {
        if($file_name == "") {
            header("Location: ../admin/settings.php?error=nophoto");
            exit();
        }
        else {
            header("Location: ../admin/settings.php?error=invalidtype");
            exit();
        }
    }
}
else if (isset($_POST['defaultElectionLogo']))
{
    // unlink past
    $failLink = "../admin/settings.php?";

    require "db_connection_general.php";
    // delete old photo
    $sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'electionLogo'";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../sqlerror.php?task=deleteOldPhoto&fromPage=modify.php?photo=1&partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']);
        exit();
    }
    else
    {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $oldDisplayPic = "../".$row['settingValue'];
    }
    // use default logo
    $file_destination_db = "assets/img/schoolLogo.png";
    require "db_connection_general.php";

    $sql = "UPDATE `operationSettings` SET `settingValue` = ? WHERE `settingName` = 'electionLogo'";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/settings.php?error=SQL&task=uploadLogoLink");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "s", $file_destination_db);
        mysqli_stmt_execute($stmt);

        // delete old pic
        if($oldDisplayPic != "../assets/img/schoolLogo.png")
        { // customized, will be changed on official build for commercial use
            if(!unlink($oldDisplayPic)) {
                echo "Error deleting old profile pic";
            }
        }

        header("Location: ../admin/settings.php?defaultElectionLogo=1");
        exit();
    }
}
else if (isset($_POST['modifyOrganization']))
{
    require "db_connection_general.php";
    $organization = $_POST['organization'];
    $settingName = "organizationName";

    // error handling
    if(empty($organization))
    {
        header("Location: ../admin/settings.php?error=emptyFields");
        exit();
    }

    $sql = "UPDATE `operationSettings` SET `settingValue` = ? WHERE `settingName` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/settings.php?error=SQL&task=modifyOrganization");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "ss", $organization, $settingName);
        mysqli_stmt_execute($stmt);
        header("Location: ../admin/settings.php?modifyOrganization=1");
        exit();
    }
}
else if (isset($_POST['modifyVotingMethod']))
{
    require "db_connection_general.php";
    $votingMethod = $_POST['votingMethod'];
    $settingName = "votingMethod";

    $sql = "UPDATE `operationSettings` SET `settingValue` = ? WHERE `settingName` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/settings.php?error=SQL&task=modifyVotingMethod");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "ss", $votingMethod, $settingName);
        mysqli_stmt_execute($stmt);
        header("Location: ../admin/settings.php?modifyVotingMethod=1");
        exit();
    }
}
else if (isset($_POST['resetApp']))
{
    require 'db_connection_general.php';
    $passcode = $_POST['passcode'];
    $failLink = "../admin/challenge.php?resetApplication=1";
    // check admin password
    require 'db_verify.php';
    if ($passcodeCheck == false) 
    { 
        header("Location: ".$failLink."&error=invalidAdminPassword&code=1");
        exit();
    }
    else if ($passcodeCheck == true) 
    {
        if(isset($_POST['truncateData']))
        {
            if (is_array($_POST['truncateData']) || is_object($_POST['truncateData']))
            {
                foreach ($_POST['truncateData'] as $value)
                {
                    switch($value)
                    {
                        case "partylists":
                            $sql = "TRUNCATE `partylist`";
                            $stmt = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt, $sql))
                            {
                                exit("error. truncate partylists");
                            }
                            else
                            {
                                //echo "truncate partylist<br>";continue;
                                mysqli_stmt_execute($stmt);
                            }
                        break;
                        case "voters":
                            $sql = "TRUNCATE `votersList`";
                            $stmt = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt, $sql))
                            {
                                exit("error. truncate voters");
                            }
                            else
                            {
                                //echo "truncate voterslist<br>";continue;
                                mysqli_stmt_execute($stmt);
                            }
                        break;
                    }
                }
            }
        }

        // truncate candidatesList (required)
        $sql = "TRUNCATE `candidateslist`";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            exit("error. truncate candidates");
        }
        else
        {
            //echo "truncate candidates<br>";
            mysqli_stmt_execute($stmt);
        }
        
        // truncate voteResults (required)
        $sql = "TRUNCATE `voteResults`";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            exit("error. truncate voteResults");
        }
        else
        {
            //echo "truncate voteResults<br>";
            mysqli_stmt_execute($stmt);
        }

        // update operationSettings
        $settingName = "showResults";
        $sql = "UPDATE `operationSettings` SET `settingValue` = 0 WHERE `settingName` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            exit("error. update showResults");
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "s", $settingName);
            mysqli_stmt_execute($stmt);
        }

        // remove candidates Folder
        function delete_files($target) {
            if(is_dir($target)){
                $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

                foreach( $files as $file ){
                    delete_files( $file );      
                }

                rmdir( $target );
            } elseif(is_file($target)) {
                unlink( $target );  
            }
        }

        delete_files('../assets/img/candidates/');
        mkdir("../assets/img/candidates/");

        /* 
        * php delete function that deals with directories recursively
        */
        
        

        header("Location: ../admin/settings.php#otherGenInfo?resetApp=1");
        exit();
    }
}