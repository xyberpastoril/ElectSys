<?php

$adminId = $_SESSION['adminId'];
$passcode = $_POST['passcode'];

if(isset($_GET['voteOpen']))
{
    $sql = "SELECT * FROM `adminAccounts` WHERE `username` = ?";
    $adminId = $_POST['username'];
    
    if($adminId == "arms")
    {
        if($adminId != $_SESSION['adminUsername'])
        {
            header("Location: ".$failLink."&error=invalidPassword");
            exit;
        }
    }
}
else
{
    $sql = "SELECT * FROM `adminAccounts` WHERE `adminId` = ?";
}

$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) { 
    header("Location: ".$failLink."&error=SQL&task=verifyAdminForTask");
    exit();
}
else {
    mysqli_stmt_bind_param($stmt, "s", $adminId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt); 
    $row = mysqli_fetch_assoc($result);
    $passcodeCheck = password_verify($passcode, $row['passcode']);
}