<?php
if(isset($_POST['castvote']))
{
    session_start();
    require 'db/db_connection_general.php';
    // localize variables
    $sectionId = $_SESSION['sectionId'];
    $sectionVote = "section_".$sectionId;
    $gradeLvl = $_SESSION['gradeLvl'];
    $lrn = $_SESSION['lrn'];
    // loop for adding votes
    for ($N=1;$N<=20;$N++)
    {
        
        $id = $_SESSION['candidates'][$N]; // get id from candidates array
        // get current tally for voteresults
        $sql = "SELECT * FROM `voteResults` WHERE candidateId = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)) 
        {
            header("Location: index.php?error=SQL&code=3");
            exit();
        }
        else 
        {
            if($id == 0 || $id == NULL)
            {
                continue;
            }
            else
            {
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $tally = $row[$sectionVote];
                $tally++;
                // update voteresults
                $sql = "UPDATE `voteResults` SET `" . $sectionVote . "` = ? WHERE `candidateId` = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql))
                {
                    header("Location: index.php?error=SQL&code=4");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "ii", $tally, $id);
                    mysqli_stmt_execute($stmt);
                }
            }
        }
        // get current tally for totalvotes
        $sql = "SELECT * FROM `candidatesList` WHERE candidateId = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)) 
        {
            header("Location: index.php?error=SQL&code=5");
            exit();
        }
        else 
        {
            if($id == 0 || $id == NULL)
            {
                continue;
            }
            else
            {
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $tally = $row['totalVotes'];
                $tally++;
                // update voteresults
                $sql = "UPDATE `candidatesList` SET `totalVotes` = ? WHERE `candidateId` = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql))
                {
                    header("Location: index.php?error=SQL&code=6");
                    exit();
                }
                else
                {
                    mysqli_stmt_bind_param($stmt, "ii", $tally, $id);
                    mysqli_stmt_execute($stmt);
                }
            }
        }
    }
    $voted = 1; // will mark the user voted for this election, which means, he/she wont be able to vote again till next year if still a student of the school (this will update the database)
    $sql = "UPDATE `votersList` SET `voted`= ? WHERE `LRN`= ?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)) 
    {
        header("Location: index.php?error=SQL&code=7");
        exit();
    }
    else 
    {
        mysqli_stmt_bind_param($stmt, "ii", $voted, $lrn);
        mysqli_stmt_execute($stmt);
    }
    header("Location: success.php");
    exit();
}
else
{
    header("Location: index.php?error=NoSession");
    exit();
}