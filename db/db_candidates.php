<?php
session_start();
if(isset($_POST['createPartyList']))
{
    require "db_connection_general.php";
    $partyListName = $_POST['partyListName'];
    $partyListAbbr = $_POST['partyListAbbr'];

    if(empty($partyListName) || empty($partyListAbbr))
    {
        header("Location: ../admin/create.php?newparty=1&error=emptyFields");
        exit();
    }
    
    // check for duplicates
    $duplicateCount = 0;
    $sql = "SELECT * FROM `partyList` WHERE `partyListName` = ? OR `partyListAbbr` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/create.php?newparty=1&error=SQL&task=checkduplicatePartylist");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "ss", $partyListName, $partyListAbbr);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
        {
            $duplicateCount++;
        }
        if($duplicateCount > 0)
        {
            header("Location: ../admin/create.php?newparty=1&error=duplicatePartyList");
            exit();
        }
    }

    // create party
    $sql = "INSERT INTO `partylist` (partyListName, partyListAbbr) VALUES (?, ?)";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)) 
    {
        header("Location: ../admin/create.php?newparty=1&error=SQL&task=createpartylist");
        exit();
    }
    else 
    {
        mysqli_stmt_bind_param($stmt, "ss", $partyListName, $partyListAbbr);
        mysqli_stmt_execute($stmt);
    }

    // get party id
    $sql = "SELECT * FROM `partyList` WHERE `partyListName` = ? AND `partyListAbbr` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/create.php?newparty=1&error=SQL&task=checkduplicatePartylist");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "ss", $partyListName, $partyListAbbr);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $partyListId = $row['partyListId'];
    }

    header("Location: ../admin/candidates.php?partyListId=".$partyListId."&addpartyList=1");
    exit();
}
else if (isset($_POST['addCandidate']))
{
    require "db_connection_general.php";
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $partyList = $_POST['partylist'];
    $position = $_POST['position'];

    if(empty($firstName) || empty($lastName))
    {
        header("Location: ../admin/create.php?partyListId=".$partyList."&error=emptyFields");
        exit();
    }
    //else if (!preg_match('/^[a-z ]+$/i', $firstName) || (!preg_match('/^[a-z ]+$/i', $middleName) && $middleName != NULL) || !preg_match('/^[a-z ]+$/i', $lastName))
    //{
    //    header("Location: ../admin/create.php?partyListId=".$partyList."&error=invalidName");
    //    exit();
    //}

    // check for name duplicates
    $sql = "SELECT * FROM `candidatesList` WHERE `firstName` = ? AND `middleName` = ? AND `lastName` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/create.php?partyListId=".$partyList."&error=SQL&task=checkNameDuplicates");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "sss", $firstName, $middleName, $lastName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if($row = mysqli_fetch_assoc($result))
        {
            header("Location: ../admin/create.php?partyListId=".$partyList."&error=duplicateName");
            exit();
        }
    }

    // check if somebody own position on partylist
    if($partyList != 0)
    {
        // check position limit
        $sql = "SELECT * FROM `positionList` WHERE `positionId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: ../admin/create.php?partyListId=".$partyList."&error=SQL&task=fetchPositionLimit");
            exit();
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "i", $position);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if($row = mysqli_fetch_assoc($result))
            {
                $positionLimit = $row['candidatesPerParty'];
            }
        }

        // check if somebody owns position on party and if exceeded limit
        $sql = "SELECT * FROM `candidatesList` WHERE `position` = ? AND `partyList` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: ../admin/create.php?partyListId=".$partyList."&error=SQL&task=checkPositionParty");
            exit();
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "ii", $position, $partyList);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $candidateCount = 0;
            for ($candidateCount;$row = mysqli_fetch_assoc($result);$candidateCount++){echo "";}
            if($candidateCount >= $positionLimit)
            {
                header("Location: ../admin/create.php?partyListId=".$partyList."&error=positionLimit");
                exit();
            }
        }
    }

    // no errors?
    $sql = "INSERT INTO `candidatesList` (`firstName`, `middleName`, `lastName`, `position`, `partyList`) VALUES (?,?,?,?,?)";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/create.php?partyListId=".$partyList."&error=SQL&task=addCandidate");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "sssii", $firstName, $middleName, $lastName, $position, $partyList);
        mysqli_stmt_execute($stmt);
    }

    // get candidate id
    $sql = "SELECT * FROM `candidatesList` WHERE `firstName` = ? AND `middleName` = ? AND `lastName` = ? AND `position` = ? AND `partyList` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../admin/create.php?partyListId=".$partyList."&error=SQL&task=getCandidateIdforResults");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "sssii", $firstName, $middleName, $lastName, $position, $partyList);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if($row = mysqli_fetch_assoc($result))
        {
            if(!($firstName == $row['firstName'] && $middleName == $row['middleName'] && $lastName == $row['lastName'] && $position == $row['position'] && $partyList == $row['partyList']))
            {
                header("Location: ../admin/create.php?partyListId=".$partyList."&addCandidate=1&error=addCandidateIdtoResults1");
                exit();
            }
            else
            {
                $candidateId = $row['candidateId'];
                $sql = "INSERT INTO `voteResults` (`candidateId`) VALUES (?)";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql))
                {
                    header("Location: ../admin/create.php?partyListId=".$partyList."&error=SQL&task=addCandidateIdtoResults2");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "i", $candidateId);
                    mysqli_stmt_execute($stmt);
                }
            }
        }
    }

    header("Location: ../admin/create.php?partyListId=".$partyList."&addCandidate=1&candidateId=".$candidateId);
    exit();
}
else if (isset($_POST['modifyCandidate']))
{
    require "db_connection_general.php";
    $candidateId = $_GET['candidateId'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];

    if(empty($firstName) || empty($lastName))
    {
        $_SESSION['modifyToken'] = 1;
        header("Location: ../admin/modify.php?info=1&candidateId=".$candidateId."&error=emptyFields");
        exit();
    }
    //else if (!preg_match('/^[a-z ]+$/i', $firstName) || (!preg_match('/^[a-z ]+$/i', $middleName) && $middleName != NULL) || !preg_match('/^[a-z ]+$/i', $lastName))
    //{
    //    $_SESSION['modifyToken'] = 1;
    //    header("Location: ../admin/modify.php?info=1&candidateId=".$candidateId."&error=invalidName");
    //    exit();
    //}

    // check for name duplicates
    $sql = "SELECT * FROM `candidatesList` WHERE `firstName` = ? AND `middleName` = ? AND `lastName` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        $_SESSION['modifyToken'] = 1;
        header("Location: ../admin/modify.php?info=1&candidateId=".$candidateId."&error=SQL&task=checkNameDuplicates");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "sss", $firstName, $middleName, $lastName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        if($row['candidateId'] != $candidateId && $row['candidateId'] != NULL)
        {
            $_SESSION['modifyToken'] = 1;
            header("Location: ../admin/modify.php?info=1&candidateId=".$candidateId."&error=duplicateName");
            exit();
        }

        $partyList = $_POST['partylist'];
        $position = $_POST['position'];
        //echo $partyList . " | ".  $position;exit(); // 1 & 2

        // check if somebody own position on partylist
        if($partyList != 0)
        {
            // check position limit
            $sql = "SELECT * FROM `positionList` WHERE `positionId` = ?";
            $stmt = mysqli_stmt_init($conn);
            if(!mysqli_stmt_prepare($stmt, $sql))
            {
                $_SESSION['modifyToken'] = 1;
                header("Location: ../admin/modify.php?info=1&candidateId=".$candidateId."&error=SQL&task=fetchPositionLimit");
                exit();
            }
            else
            {
                mysqli_stmt_bind_param($stmt, "i", $position);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if($row = mysqli_fetch_assoc($result))
                {
                    $positionLimit = $row['candidatesPerParty'];
                }

                // check if somebody owns posiion on party and if exceed limit
                $sql = "SELECT * FROM `candidatesList` WHERE `position` = ? AND `partyList` = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql))
                {
                    $_SESSION['modifyToken'] = 1;
                    header("Location: ../admin/modify.php?info=1&candidateId=".$candidateId."&error=SQL&task=checkPositionParty");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "ii", $position, $partyList);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $candidateCount = 0;
                    for($candidateCount;$row = mysqli_fetch_assoc($result);$candidateCount++)
                    {
                        if(($row['firstName'] == $firstName && $row['middleName'] == $middleName && $row['lastName'] == $lastName) && $row['position'] == $position && $row['partyList'] == $partyList)
                        {
                            $candidateCount=0;
                            break;
                        }
                    }
                    if($candidateCount >= $positionLimit)
                    {
                        $_SESSION['modifyToken'] = 1;
                        header("Location: ../admin/modify.php?info=1&candidateId=".$candidateId."&error=positionLimit");
                        exit();
                    }
                }
            }
        }
        // no errors?
        $sql = "UPDATE `candidatesList` SET `firstName` = ?, `middleName` = ?, `lastName` = ?, `position` = ?, `partyList` = ? WHERE `candidatesList`.`candidateId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            $_SESSION['modifyToken'] = 1;
            header("Location: ../admin/modify.php?info=1&candidateId=".$candidateId."&error=SQL&task=modifyCandidate");
            exit();
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "sssiii", $firstName, $middleName, $lastName, $position, $partyList, $candidateId);
            mysqli_stmt_execute($stmt);
        }

        header("Location: ../admin/candidates.php?partyListId=".$partyList."&modifyCandidate=`1");
        exit();
    }
}
else if (isset($_POST['deleteCandidate']))
{
    require "db_connection_general.php";
    $candidateId = $_GET['candidateId'];
    $failLink = "../admin/challenge.php?candidateId=".$candidateId;

    // verify password of logged in user
    require "db_verify.php";
    if ($passcodeCheck == false) 
    { 
        header("Location: ".$failLink."&error=invalidPassword&code=1");
        exit();
    }
    else if ($passcodeCheck == true) 
    { 
        // delete candidate
        for($sqlCount = 1; $sqlCount <= 2; $sqlCount++)
        {
            switch($sqlCount)
            {
                case 1: $sql = "DELETE FROM `candidatesList` WHERE `candidatesList`.`candidateId` = ?";break;
                case 2: $sql = "DELETE FROM `voteResults` WHERE `voteResults`.`candidateId` = ?";break;
            }
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) { 
                header("Location: ".$failLink."&error=sql&task=deleteCandidate&sqlCount=".$sqlCount);
                exit();
            }
            else {
                mysqli_stmt_bind_param($stmt, "i", $candidateId);
                mysqli_stmt_execute($stmt);
            }
        }
    }
    else {
        header("Location: ".$failLink."&error=invalidPassword&code=2");
        exit(); 
    }

    header("Location: ../admin/candidates.php?deleteCandidate=1");
    exit();
}
else if (isset($_POST['modifyPartyList']))
{
    require "db_connection_general.php";
    $partyListId = $_GET['partyListId'];
    $partyListName = $_POST['partyListName'];
    $partyListAbbr = $_POST['partyListAbbr'];

    if(empty($partyListName) || empty($partyListAbbr))
    {
        $_SESSION['modifyToken'] = 1;
        header("Location: ../admin/candidates.php?error=emptyFields");
        exit();
    }
    
    // check for duplicates
    $duplicateCount = 0;
    $sql = "SELECT * FROM `partyList` WHERE `partyListName` = ? OR `partyListAbbr` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        $_SESSION['modifyToken'] = 1;
        header("Location: ../admin/candidates.php?error=SQL&task=checkduplicatePartylist");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "ss", $partyListName, $partyListAbbr);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
        {
            if($partyListId != $row['partyListId'])
            {
                $duplicateCount++;
            }
        }
        if($duplicateCount > 0)
        {
            $_SESSION['modifyToken'] = 1;
            header("Location: ../admin/modify.php?partyListId=".$partyListId."&error=duplicatePartyList");
            exit();
        }
    }

    // no errors?
    $sql = "UPDATE `partylist` SET `partyListName` = ?, `partyListAbbr` = ? WHERE `partylist`.`partyListId` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        $_SESSION['modifyToken'] = 1;
        header("Location: ../admin/modify.php?partyListId=".$partyListId."&error=SQL&task=modifyPartyList");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "sss", $partyListName, $partyListAbbr, $partyListId);
        mysqli_stmt_execute($stmt);
    }

    header("Location: ../admin/candidates.php?partyListId=".$partyListId."&modifyPartyList=1");
    exit();
}
else if (isset($_POST['deletePartyList']))
{
    require "db_connection_general.php";
    $partyListId = $_GET['partyListId'];
    $partyListAfter = 0;
    $failLink = "../admin/challenge.php?partyListId=".$partyListId;

    // verify password of logged in user
    require "db_verify.php";
    if ($passcodeCheck == false) 
    { 
        header("Location: ".$failLink."&error=invalidPassword&code=1");
        exit();
    }
    else if ($passcodeCheck == true) 
    { 
        // delete candidate
        for($sqlCount = 1; $sqlCount <= 2; $sqlCount++)
        {
            switch($sqlCount)
            {
                case 1: $sql = "DELETE FROM `partyList` WHERE `partyList`.`partyListId` = ?";break;
                case 2: $sql = "UPDATE `candidatesList` SET `partyList` = ? WHERE `candidatesList`.`partyList` = ?";break;
            }
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) { 
                header("Location: ".$failLink."&error=sql&task=deleteCandidate&sqlCount=".$sqlCount);
                exit();
            }
            else {
                switch($sqlCount)
                {
                    case 1: mysqli_stmt_bind_param($stmt, "i", $partyListId);break;
                    case 2: mysqli_stmt_bind_param($stmt, "ii", $partyListAfter, $partyListId);break;
                }
                mysqli_stmt_execute($stmt);
            }
        }
    }
    else {
        header("Location: ".$failLink."&error=invalidPassword&code=2");
        exit(); 
    }


    header("Location: ../admin/candidates.php?deletePartyList=1");
    exit();
}
else if (isset($_POST['addDisplayPhoto']))
{
    $candidateId = $_GET['candidateId'];
    $partyListId = $_GET['partyListId'];
    $failLink = "../admin/create.php?partyListId=".$partyListId."&candidateId=".$candidateId;

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
                $file_destination = '../assets/img/candidates/'.$file_name_new;
                $file_destination_db = 'assets/img/candidates/'.$file_name_new;
                move_uploaded_file($file_tmpname, $file_destination);
                $sql = "UPDATE `candidatesList` SET `displayPic` = ? WHERE `candidateId` = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql))
                {
                    header("Location: ".$failLink."&error=SQL&task=uploadLogoLink");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "si", $file_destination_db, $candidateId);
                    mysqli_stmt_execute($stmt);
                    header("Location: ../admin/candidates.php?partyListId=".$partyListId."&candidateId=".$candidateId."&addCandidate=1");
                    exit();
                }
            }
            else
            {
                header("Location: ".$failLink."&error=toobig");
                exit();
                // echo "Your file is too big";
            }
        }
        else
        {
            header("Location: ".$failLink."&error=error");
            exit();
        }
    }
    else
    {
        if($file_name == "") {
            header("Location: ".$failLink."&error=nophoto");
            exit();
        }
        else {
            header("Location: ".$failLink."&error=invalidtype");
            exit();
        }
    }
}
else if (isset($_POST['modifyDisplayPhoto']))
{
    $candidateId = $_GET['candidateId'];
    $partyListId = $_GET['partyListId'];
    $failLink = "../admin/create.php?partyListId=".$partyListId."&candidateId=".$candidateId;

    require "db_connection_general.php";
    // delete old photo
    $sql = "SELECT * FROM `candidatesList` WHERE `candidateId` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../sqlerror.php?task=deleteOldPhoto&fromPage=modify.php?photo=1&partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']);
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "i", $candidateId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $oldDisplayPic = "../".$row['displayPic'];
    }
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
                $file_destination = '../assets/img/candidates/'.$file_name_new;
                $file_destination_db = 'assets/img/candidates/'.$file_name_new;
                move_uploaded_file($file_tmpname, $file_destination);
                $sql = "UPDATE `candidatesList` SET `displayPic` = ? WHERE `candidateId` = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql))
                {
                    header("Location: ".$failLink."&error=SQL&task=uploadLogoLink");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "si", $file_destination_db, $candidateId);
                    mysqli_stmt_execute($stmt);

                    if(!unlink($oldDisplayPic)) {
                        echo "Error deleting old profile pic";
                    }

                    header("Location: ../admin/candidates.php?partyListId=".$partyListId."&candidateId=".$candidateId."&addCandidate=1");
                    exit();
                }
            }
            else
            {
                header("Location: ".$failLink."&error=toobig");
                exit();
                // echo "Your file is too big";
            }
        }
        else
        {
            header("Location: ".$failLink."&error=error");
            exit();
        }
    }
    else
    {
        if($file_name == "") {
            header("Location: ".$failLink."&error=nophoto");
            exit();
        }
        else {
            header("Location: ".$failLink."&error=invalidtype");
            exit();
        }
    }
}
else if (isset($_POST['removeDisplayPhoto']))
{
    $candidateId = $_GET['candidateId'];
    $partyListId = $_GET['partyListId'];
    $failLink = "../admin/create.php?partyListId=".$partyListId."&candidateId=".$candidateId;

    require "db_connection_general.php";
    // delete old photo
    $sql = "SELECT * FROM `candidatesList` WHERE `candidateId` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ../sqlerror.php?task=deleteOldPhoto&fromPage=modify.php?photo=1&partyListId=".$_GET['partyListId']."&candidateId=".$_GET['candidateId']);
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "i", $candidateId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $oldDisplayPic = "../".$row['displayPic'];
        if(!unlink($oldDisplayPic)) {
            echo "Error deleting old profile pic";
            exit();
        }
    }
    $file_destination_db = NULL;
    $sql = "UPDATE `candidatesList` SET `displayPic` = ? WHERE `candidateId` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: ".$failLink."&error=SQL&task=uploadLogoLink");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "si", $file_destination_db, $candidateId);
        mysqli_stmt_execute($stmt);
        header("Location: ../admin/candidates.php?partyListId=".$partyListId."&candidateId=".$candidateId."&addCandidate=1");
        exit();
    }
}