<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/protected/_load_main_files.php');

if( userInfo() !== false )
{
    unset($_SESSION['user']);
}

header( 'Location: /signin.php');