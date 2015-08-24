#!/usr/bin/php
<?php

$_help = ['-h'];
if( $argc < 2 || in_array($argv, $_help) )
{
    __show_help();
    die();
}

$_command = isset($argv[1]) ? $argv[1] : null;

switch($_command)
{
    default:
        __show_message("Указанная команда не доступна!");
        __show_help();
        break;
    case 'adduser':
        add_user();
        break;
}






function __show_help()
{
    global $argc, $argv;
    echo "\e[0;33m";
    include(dirname(__FILE__).'/views/help.php');
    echo "\e[0m";
}

function __show_message($msg, $type='error')
{
    $_strStart = null;
    switch($type)
    {
        case 'error':
            $_strStart = "\e[0;31m[error]";
            break;
        case 'success':
            $_strStart = "\e[0;32m[success]";
            break;
    }
    if( is_array($msg) )
    {
        echo "{$_strStart}:\n";
        for($i=0, $max=count($msg); $i<$max; ++$i)
        {
            echo "\t {$msg[$i]}\n";
        }
        echo "\e[0m\n\n";
    }
    else
    {
        echo "{$_strStart}: {$msg}\e[0m\n\n";
    }
}

function add_user()
{
    global $argc, $argv;
    require_once(dirname(__FILE__).'/../lib/db.php');

    $_login    = isset($argv[2]) ? $argv[2] : null;
    $_passwd   = isset($argv[3]) ? $argv[3] : null;
    $_type     = isset($argv[4]) ? $argv[4] : null;
    $_username = isset($argv[5]) ? $argv[5] : $_login;

    $types = ['admin', 'customer', 'performer'];
    
    $_err = [];
    if(empty($_login))
    {
        $_err[] = 'Укажите пользователя';
    }

    if(empty($_passwd))
    {
        $_err[] = 'Укажите пароль для пользователя';
    }

    if(empty($_type))
    {
        $_err[] = 'Укажите тип пользователя';
    }
    elseif( !in_array($_type, $types) )
    {
        $_err[] = 'Укажите правильный типа пользователя. (Доступные: "'.implode('", "',$types).'")';
    }

    if( !empty($_err) )
    {
        __show_message($_err);
    }
    else
    {
        $_setData = [
            'login'    => $_login,
            'password' => password_hash($_passwd, PASSWORD_BCRYPT),
            'name'     => $_username,
            'type'     => $_type,
        ];
        $res = db_insert('user', $_setData);
        if( !$res )
        {
            __show_message(db_get_error('user'));
            die();
        }

        __show_message('User "'.$_login.'" created.', 'success');
        
    }
}










