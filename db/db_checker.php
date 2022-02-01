<?php

require "db_connection_setup.php";

$dbname='arms_aes';
if (empty (mysqli_fetch_array(mysqli_query($conn,"SHOW DATABASES LIKE '$dbname'")))) 
{ // "DB not exist"
    if (isset($admin))
    {
        header("Location: ../setup/index.php");
        exit();
    }
    else if (isset($setup))
    {
        echo "";
    }
    else
    {
        header("Location: setup/index.php");
        exit();
    }
}
else
{
    if(isset($setup))
    {
        header("Location: ../index.php");
        exit();
    }
}
mysqli_close($conn);
?>