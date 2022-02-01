<?php

    require "db_connection_general.php";

    $sql = "DROP DATABASE `arms_aes`";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql))
    {
        echo "error sql";
        exit();
    }
    else
    {
        mysqli_stmt_execute($stmt);
        header("Location: ../index.php");
        exit();
    }