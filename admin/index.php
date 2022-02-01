<?php
$admin = 1; // to change directory level for db check
$home = 1;
session_start();
require "../db/db_checker.php"; // check if database exists
// remove token
$_SESSION['modifyToken'] = 0;
if(empty($_SESSION['adminId']))
{
    require "index/login.php";
    exit();
    
}
else
{
    require "index/menu.php";
    exit();
}