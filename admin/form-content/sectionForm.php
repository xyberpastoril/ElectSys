<input class='block obj-center input-lrn' style="width:300px"type="text" name="sectionName" placeholder="Section Name" value="<?php echo (isset($sectionNameInput) ? $sectionNameInput : "");?>">
<span class="span-8px"></span>
<select class='block obj-center input-lrn' style="width:300px;height:32px;"name="gradeLevel">
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

        for($GL=7;$GL<=12;$GL++)
        {
            if($votingMethod == 2 && ($GL == 7 || $GL == 11))
            {
                echo "<option value='".$GL."' " . ($gradeLevel == $GL ? " selected='selected'" : "") . ">Grade " . $GL . "</option>";
            }
            if ($votingMethod == 1 || $votingMethod == 3)
            {
                echo "<option value='".$GL."' " . ($gradeLevel == $GL ? " selected='selected'" : "") . ">Grade " . $GL . "</option>";
            }
        }
    ?>
</select>