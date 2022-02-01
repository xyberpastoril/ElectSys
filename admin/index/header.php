<div id="bg" class="absolute"></div>
<div id="header" class="absolute"></div>
<div id="left-logo">
<?php
    if(isset($home))
    {
        echo "";
    }
    else
    {
        echo "<a href='index.php'><img class='hover-home' src='../assets/img/admin/android-back.png' style='display:block;width:28px;height:28px;padding:4px;margin-right:10px;'></a>";
    }
?>
    <a href='index.php'>
        <!--<p style='float:left;display:block;'>
            ARMS-AES <sub>v1 Alpha</sub>
        </p>-->
        <img class="block" style='height:36px;width:auto;float:left!important' src="../assets/img/logo/full_logo.png">
    </a>
</div>
<div id="pre-right-logo"><p><?php echo $_SESSION['adminUsername'] . " | Level " . $_SESSION['adminLevel'];?></p></div>
<div id="right-logo">
    <form action="../db/db_admin.php" method="post">
        <button class="input-lrn obj-center  block button" type="submit" name="logout" style="width:100px">Logout</button>
    </form>
</div>