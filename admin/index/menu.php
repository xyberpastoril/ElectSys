<html>
    <head>
        <title>Admin Panel</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/resetter.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/general.css">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin.css">
        <link rel="shortcut icon" type="image/png" href="../assets/img/logo/arms_128x128.png">
        <style>
            .vertical-center {
                width:100%;
                height:calc(100vh - 72px);
                padding-top:72px;
            }
        </style>
    </head>
    <body class='table'>
        <?php require "index/header.php";?>
        <div class="vertical-center">
            <div id="menu" class="grid">
                <?php
                    require "../db/db_connection_general.php";
                    // check operationSettings to whether show fucking results page
                    $settingName = "showResults";
                    $sql = "SELECT * FROM `operationSettings` WHERE `settingName` = ?";
                    $stmt = mysqli_stmt_init($conn);
                    if(!mysqli_stmt_prepare($stmt, $sql))
                    {
                        header("Location: index.php?error=SQL&task=checkShowResults");
                        exit();
                    }
                    else
                    {
                        mysqli_stmt_bind_param($stmt, "s", $settingName);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($result);
                        $showResults = $row['settingValue'];
                    }

                    // load menu list
                    for($level = 1; $level <= $_SESSION['adminLevel']; $level++)
                    {
                        $sql = "SELECT * FROM `menuList` WHERE `adminLevel` = ?";
                        $stmt = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt, $sql))
                        {
                            header("Location: index.php?error=SQL&code=1");
                            exit();
                        }
                        else
                        {
                            mysqli_stmt_bind_param($stmt, "i", $level);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            while ($row = mysqli_fetch_assoc($result))
                            {
                                $pageName = $row['pageName'];
                                $pageLink = $row['pageLink'];
                                $iconPic = $row['iconPic'];

                                if($pageName == "Results" && $showResults == 0)
                                {
                                    continue;
                                }
                                echo "
                                <a href='".$pageLink."' class='menu-content'>
                                    <img src='".$iconPic."'>
                                    <p>".$pageName."</p>
                                </a>
                                ";
                            }
                        }
                    }
                    // load settings page
                    $pageName = "Settings";
                    $pageLink = "settings.php";
                    $iconPic = "../assets/img/admin/adminpanel.png";
                    echo "
                    <a href='".$pageLink."' class='menu-content'>
                        <img src='".$iconPic."'>
                        <p>".$pageName."</p>
                    </a>
                    ";
                ?>
            </div>
        </div>
    </body>
</html>