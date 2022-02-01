<?php
    session_start();
    $admin = 1; // to change directory level for db check
    require '../db/db_checker.php'; // check if database exists
    require '../db/db_connection_general.php';
    // remove token
    $_SESSION['modifyToken'] = 0;
    // check if admin logged in
    if(empty($_SESSION['adminId']))
    {
        require "index/login.php";
        exit();
    }
// check voteOpen
$sql = "SELECT * FROM `operationSettings` WHERE `settingName` = 'voteOpen'";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql))
{
    header("Location: settings.php?error=SQL&task=fetchVoteOpenToggle");
    exit();
}
else
{
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $voteOpen = $row['settingValue'];
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

$showAddStudents = 0;
$showAddSection = 0;
$showModifySection = 0;
$showDeleteSection = 0;

?>

<html>
    <head>
        <title>Admin Panel</title>
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
        <?php require "index/header.php";?>
        <div id="newAdmin" class="grid">
            <div id="sidebar">
                <?php
                if($showResults == 0)
                {
                    $showAddSection = 1;
                    echo "<a><p class='input-lrn obj-center block button' style='width:196px;text-align:center;' onclick='addSection()'>Add Section</p></a>";
                }
                else if ($showResults == 1)
                {
                    echo "<a><p class='input-lrn obj-center block button' style='width:196px;text-align:center;background-color:#444!important'>Add Section</p></a>";   
                }
                ?>
                <span class="span-16px"></span>
                <?php
                    // load sections per grade level
                    $sectionCount = 0;
                    $currentSection = 0;
                    if(isset($_GET['sectionId']))
                    {
                        $currentSection = $_GET['sectionId'];
                    }
                    for($gradeLevel = 7; $gradeLevel <= 12; $gradeLevel++)
                    {
                        $sql = "SELECT * FROM `sectionsList` WHERE `gradeLevel` = ? ORDER BY `sectionsList`.`sectionName` ASC";
                        $stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt, $sql)) { 
                            header("Location: voterslist.php?error=sql&code=1");
                            exit();
                        }
                        else {
                            mysqli_stmt_bind_param($stmt, "i", $gradeLevel);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt); 
                            while ($row = mysqli_fetch_assoc($result)) { 
                                $sectionId = $row['sectionId'];
                                $sectionName = $row['sectionName'];
                                echo "<a href='voterslist.php?sectionId=".$sectionId."'</a><p class='sectionList ".($currentSection == $sectionId ? "selectedSection" : "")."'>" . $gradeLevel . " - " . $sectionName . "</p></a>";
                                $sectionCount++;
                            }
                        }
                    }
                    if($sectionCount == 0)
                    {
                        echo "<p>No sections added.</p>";
                    }
                ?>
            </div>
            <div id="mainContent">
            <?php
                if(!isset($_GET['sectionId']))
                {
                    echo "<p>No section selected.</p>";
                }
                else if ($_GET['sectionId'] != NULL)
                {
                    // get name
                    $sql = "SELECT * FROM `sectionsList` WHERE `sectionId` = ?";
                    $stmt = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($stmt, $sql)) { 
                        header("Location: voterslist.php?error=sql&code=1");
                        exit();
                    }
                    else {
                        mysqli_stmt_bind_param($stmt, "i", $_GET['sectionId']);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt); 
                        if ($row = mysqli_fetch_assoc($result))
                        {
                            $voterCount = 0;
                            $sectionName = $row['sectionName'];
                            $gradeLevel = $row['gradeLevel'];
                            // get number of students within a section
                            $sql = "SELECT * FROM `votersList` WHERE `sectionId` = ? ORDER BY `votersList`.`LRN` ASC";
                            $stmt = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($stmt, $sql)) { 
                                header("Location: voterslist.php?error=sql&code=1");
                                exit();
                            }
                            else {
                                mysqli_stmt_bind_param($stmt, "i", $_GET['sectionId']);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt); 
                                while ($row = mysqli_fetch_assoc($result)) 
                                {$voterCount++;}
                            }
                            echo "
                            <h2>".$gradeLevel." - ". $sectionName."</h2>
                            <div class='grid' id='sub-nav'>
                                <p style='margin-top:10px'>Showing ".$voterCount." results</p>
                                ";

                                if($showResults == 0)
                                {
                                    $showAddStudents = 1;
                                    $showModifySection = 1;
                                    $showDeleteSection = 1;
                                    echo "
                                    <a><p class='input-lrn obj-center block button' style='width:auto;text-align:center;' onclick='addStudents()'>Add Student</p></a>
                                    <a><p class='input-lrn obj-center block button modifyButton' style='width:auto;text-align:center;' onclick='modifySection()'>Modify</p></a>
                                    <a><p class='input-lrn obj-center block button deleteButton' style='width:auto;text-align:center;' onclick='deleteSection()'>Delete</p></a>";
                                }
                                if($showResults == 1)
                                {
                                    if($voteOpen == 1)
                                    {
                                        $showAddStudents = 1;
                                        echo "
                                        <span></span><span></span>
                                        <a><p class='input-lrn obj-center block button' style='width:auto;text-align:center;' onclick='addStudents()'>Add Student</p></a>
                                        ";
                                    }
                                }
                            echo "
                            </div>
                            <span class='span-16px'></span>
                            ";
                        }
                    }
                    
                    if(isset($sectionName))
                    {
                        echo "
                        <div class='grid' id='students'>
                        ";
                        $sql = "SELECT * FROM `votersList` WHERE `sectionId` = ? ORDER BY `votersList`.`LRN` ASC";
                        $stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt, $sql)) { 
                            header("Location: voterslist.php?error=sql&code=1");
                            exit();
                        }
                        else {
                            mysqli_stmt_bind_param($stmt, "i", $_GET['sectionId']);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt); 
                            while ($row = mysqli_fetch_assoc($result)) { 
                                $LRN = $row['LRN'];
                                $voted = $row['voted'];
                                if($showResults == 0 && $voteOpen == 0)
                                {
                                    echo "
                                    <div class='student-lrn grid'>
                                        <p style='margin-top:6px;'>" . $LRN . "</p>
                                        " . ($voted == 0 ? "
                                            <form action='../db/db_voterslist.php?LRN=".$LRN."&sectionId=".$_GET['sectionId']."' method='post'>
                                                <button class='deleteButtonHover' style='background-color:#440909;padding:4px 8px;' type='submit' name='deleteStudent'><img src='../assets/img/admin/android-trash.png'></button>
                                            </form>
                                        " : "<img style='margin-left:10px;margin-top:3px' src='../assets/img/admin/android-checkmark.png'>") . "
                                    </div>
                                    ";
                                }
                                else if ($showResults == 1 && $voteOpen == 1)
                                {
                                    echo "
                                    <div class='student-lrn grid'>
                                        <p style='margin-top:6px;'>" . $LRN . "</p>
                                        " . ($voted == 0 ? "
                                            <form action='challenge.php?LRN=".$LRN."&sectionId=".$_GET['sectionId']."' method='post'>
                                                <button class='deleteButtonHover' style='background-color:#440909;padding:4px 8px;' type='submit' name='delete'><img src='../assets/img/admin/android-trash.png'></button>
                                            </form>
                                        " : "<img style='margin-left:10px;margin-top:3px' src='../assets/img/admin/android-checkmark.png'>") . "
                                    </div>
                                    ";
                                }
                                else if ($showResults == 1 && $voteOpen == 0)
                                {
                                    echo "
                                    <div class='student-lrn grid'>
                                        <p style='margin-top:6px;'>" . $LRN . "</p>
                                        " . ($voted == 0 ? "<img style='margin-left:10px;margin-top:3px' src='../assets/img/admin/close.png'>" : "<img style='margin-left:10px;margin-top:3px' src='../assets/img/admin/android-checkmark.png'>") . "
                                    </div>
                                    ";
                                }
                            }
                            echo "</div>";
                        }
                        if($voterCount == 0)
                        {
                            echo "<p>Empty section</p>";
                        }
                    }
                }
            ?>
            </div>
        </div>

        <!-- window -->
        <?php
        if($showAddSection == 1)
        { 
        ?>
        <div id='addSection' class='page-sub-window' style='display:none'>
            <div class='vertical-center'>
                <div class='page-sub-window-content'>
                    <div class='window-grid'>
                        <p>Create New Section</p>
                        <img src='../assets/img/close.png' onclick='addSection()'>
                    </div>
                    <span class='span-16px'></span>
                    <form action='../db/db_voterslist.php' method='post'>
                        <?php require 'form-content/sectionForm.php';?>
                        <span class='span-8px'></span>
                        <button class='input-lrn obj-center  block button' style='width:300px'type='submit' name='createsection'>Create Section</button>
                    </form>
                </div>
            </div>
        </div>
        <?php } 
        if($showAddStudents == 1)
        {
        ?>
        <div id='addStudents' class='page-sub-window' style='display:none'>
            <div class='vertical-center'>
                <div class='page-sub-window-content'>
                    <div class='window-grid'>
                        <p>How many?</p>
                        <img src='../assets/img/close.png' onclick='addStudents()'>
                    </div>
                    <span class='span-16px'></span>
                    <form action='addStudents.php?sectionId=<?php echo $_GET['sectionId'];?>' method='post'>
                    <select class='block obj-center input-lrn' style='width:300px;height:32px;' name='voters'>
                        <?php
                            for($no=1;$no<=50;$no++)
                            {
                                echo "<option value='".$no."'>" . $no . "</option>";
                            }
                        ?>
                    </select>
                        <!--<input type='text' name='voters' placeholder='How many'>-->
                        <span class='span-8px'></span>
                        <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='initAddStudents'>Open Student Registar</button>
                    </form>
                </div>
            </div>
        </div>
        <?php 
        }
        if($showModifySection == 1)
        {
        ?>
        <div id='modifySection' class='page-sub-window' style='display:none'>
            <div class='vertical-center'>
                <div class='page-sub-window-content'>
                    <div class='window-grid'>
                        <p>Modify Section</p>
                        <img src='../assets/img/close.png' onclick='modifySection()'>
                    </div>
                    <span class='span-16px'></span>
                    <form action='../db/db_voterslist.php' method='post'>
                        <input type='hidden' name='sectionId' value='<?php echo $_GET['sectionId'];?>'>
                        <input class='block obj-center input-lrn' style='width:300px'type='text' name='sectionName' placeholder='Section Name' value="<?php echo (isset($sectionNameInput) ? $sectionNameInput : (isset($sectionName) ? "$sectionName" : ""));?>">
                        <span class='span-8px'></span>
                        <select class='block obj-center input-lrn' style='width:300px;height:32px;'name='gradeLevel'>
                            <?php
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
                        <span class="span-8px"></span>
                        <button class="input-lrn obj-center  block button" style="width:300px"type="submit" name="modifySection">Modify Section</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        }
        if($showDeleteSection == 1)
        {
        ?>
        <div id='deleteSection' class='page-sub-window' style='display:none'>
            <div class='vertical-center'>
                <div class='page-sub-window-content'>
                    <div class='window-grid'>
                        <p>Are you sure?</p>
                        <img src='../assets/img/close.png' onclick='deleteSection()'>
                    </div>
                    <span class='span-4px'></span>
                    <p>This action cannot be undone.</p>
                    <span class='span-16px'></span>
                    <form action='../db/db_voterslist.php?sectionId=<?php echo $_GET['sectionId'];?>' method='post'>
                        <input class='block obj-center input-lrn' style='width:300px' type='password' name='passcode' placeholder='Enter password to confirm'>
                        <span class='span-8px'></span>
                        <button class='input-lrn obj-center  block button' style='width:300px' type='submit' name='deleteSection'>Confirm Delete</button>
                    </form>
                    <span class='span-8px'></span>
                    <p class='smallWarning-subwindow'>Deleting a section also includes all LRNS within it.</p>
                </div>
            </div>
        </div>
        <?php } ?>
        <script>
            function addSection() {
                if (document.getElementById('addSection').style.display === 'none') {  
                    document.getElementById('addSection').style.display = 'table';
                }
                else {
                    document.getElementById('addSection').style.display = 'none';
                }
            }

            function addStudents() {
                if (document.getElementById('addStudents').style.display === 'none') {  
                    document.getElementById('addStudents').style.display = 'table';
                }
                else {
                    document.getElementById('addStudents').style.display = 'none';
                }
            }

            function modifySection() {
                if (document.getElementById('modifySection').style.display === 'none') {  
                    document.getElementById('modifySection').style.display = 'table';
                }
                else {
                    document.getElementById('modifySection').style.display = 'none';
                }
            }

            function deleteSection() {
                if (document.getElementById('deleteSection').style.display === 'none') {  
                    document.getElementById('deleteSection').style.display = 'table';
                }
                else {
                    document.getElementById('deleteSection').style.display = 'none';
                }
            }
        </script>   
    </body>
</html>