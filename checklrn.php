<?php
if(isset($_POST['openballot']))
{
    // if this page is accessed by the button at index.php
    session_start();
    require 'db/db_connection_general.php';
    $lrn = $_POST['lrn'];

    // check if server still accepting votes?
    $sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'voteOpen'";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: index.php?error=SQL&task=checkVoteOpen");
        exit();
    }
    else
    {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        if($row['settingValue'] == 0)
        {
            header("Location: index.php?error=votingClosed");
            exit();
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

    // error checking
    if(empty($lrn))
    {
        header("Location: index.php?error=emptyLRN");
        exit();
    }
    else if (strlen($lrn) != 12)
    {
        header("Location: index.php?error=InvalidInput");
        exit();
    }
    // no errors?
    $sql = "SELECT * FROM `votersList` WHERE `lrn` = ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql))
    {
        header("Location: index.php?error=SQL&task=checklrn");
        exit();
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "s", $lrn);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if($row = mysqli_fetch_assoc($result))
        {
            $_SESSION['lrn'] = $row['LRN'];
            $_SESSION['sectionId'] = $row['sectionId'];
            $_SESSION['voted'] = $row['voted'];
        }
        // error checking
        if($row['LRN'] == NULL)
        {
            session_unset();
            header("Location: index.php?error=LRNnotfound");
            exit();
        }
        else if ($_SESSION['voted'] == 1)
        {
            session_unset();
            header("Location: index.php?error=alreadyvoted");
            exit();
        }
        // no errors?
        $sectionId = $_SESSION['sectionId'];
        $sql = "SELECT * FROM `sectionsList` WHERE `sectionId` = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql))
        {
            header("Location: index.php?error=SQL&task=loadBallot");
            exit();
        }
        else
        {
            mysqli_stmt_bind_param($stmt, "s", $sectionId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $_SESSION['gradeLvl'] = $row['gradeLevel'];
            $_SESSION['sectionName'] = $row['sectionName'];
            // error checking
            if($votingMethod == 2)
            {
                if($_SESSION['gradeLvl'] != 7 && $_SESSION['gradeLvl'] != 11)
                {
                    header("Location: index.php?error=ineligible");
                    exit();
                }
            }
        }
        // session settings
        $_SESSION['skipAhead'] = 0; // if true, skip ahead to review instead of going to next position
        $_SESSION['candidates'] = array(); // set of values from [1] to [20] (0 set as 0 as it won't be included either)
        for($N=1;$N<=20;$N++)
        { // setting 16 values to zero rather than remaining null as it returns an error and some confusion
            $_SESSION['candidates'][$N] = 0;
        }
        // redirects to voting proper
        header("Location: vote.php?position=1");
        exit();
    }
}
else
{
    header("Location: index.php");
    exit();
}