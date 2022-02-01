<?php 
    echo "
    <input class='block obj-center input-lrn' style='width:300px' type='text' name='firstName' placeholder='First Name' value='". (isset($firstName) ? $firstName : "")."'>
    <span class='span-8px'></span>
    <input class='block obj-center input-lrn' style='width:300px' type='text' name='middleName' placeholder='Middle Name' value='". (isset($middleName) ? $middleName : "")."'>
    <span class='span-8px'></span>
    <input class='block obj-center input-lrn' style='width:300px' type='text' name='lastName' placeholder='Last Name' value='". (isset($lastName) ? $lastName : "")."'>
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
    // current picture and uploader (TO BE ADDED)