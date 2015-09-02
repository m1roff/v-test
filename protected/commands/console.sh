#!/usr/bin/php
<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

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
    case 'passwd':
        change_pass();
        break;
    case 'fillorders':
        fillorders();
        break;
}






function __show_help()
{
    global $argc, $argv;
    echo "\e[0;33m";
    include(dirname(__FILE__).'/views/help.php');
    echo "\e[0m";
}

function __show_message($msg, $type='error', $prefix=true)
{
    $_strStart = null;
    switch($type)
    {
        case 'error':
            $_strStart = "\e[0;31m";
            if($prefix) $_strStart .= '[error]: ';
            break;
        case 'success':
            $_strStart = "\e[0;32m";
            if($prefix) $_strStart .= '[success]: ';
            break;
        case 'info':
            $_strStart = "\e[0;36m";
            if($prefix) $_strStart .= '[info]: ';
            break;
    }
    if( is_array($msg) )
    {
        echo "{$_strStart}\n";
        for($i=0, $max=count($msg); $i<$max; ++$i)
        {
            echo "\t {$msg[$i]}\n";
        }
        echo "\e[0m\n\n";
    }
    else
    {
        echo "{$_strStart}{$msg}\e[0m\n\n";
    }
}

function fillorders()
{
    global $argc, $argv;
    require_once(dirname(__FILE__).'/../lib/db.php');
    $_maxamount = 1000000; // Страховка
    $_amount = isset($argv[2]) ? (int)$argv[2] : null;
    if(empty($_amount))
    {
        __show_message('Укажите необходимое кол-во заказов.');
        exit;
    }
    elseif($_amount > $_maxamount)
    {
        __show_message('Укажите меньшее кол-во заказов. (макс.'.$_maxamount.')');
        exit;
    }

    $_performers = db_select('user','id_user', 'type="performer"');
    if(empty($_performers))
    {
        __show_message('Не найдены исполнители.');
        exit;
    }

    $_customers = db_select('user','id_user', 'type="customer"');
    if(empty($_customers))
    {
        __show_message('Не найдены заказчики.');
        exit;
    }

    __show_message('создадим '.$_amount.'шт. заказа.', 'info');
    for($i=0; $i<$_amount; ++$i)
    {
        $_set = array(
            'id_customer'  => $_customers[array_rand($_customers,1)]['id_user'],
            'id_performer' => $_performers[array_rand($_performers,1)]['id_user'],
            'amount'       => rand(10,99999),
            'status'       => 0,
        );
        $_res = db_insert('orders', $_set);
        if(!$_res)
        {
            __show_message(db_get_error('orders'));
            exit;
        }
        else
        {
            __show_message('Заказ '.($i+1).' создан.','info');
        }
    }
    __show_message('все создано.', 'info');
}

function change_pass()
{
    global $argc, $argv;
    require_once(dirname(__FILE__).'/../lib/db.php');
    $_login = isset($argv[2]) ? $argv[2] : null;
    $_passwd   = isset($argv[3]) ? $argv[3] : null;

    $_err = [];
    if(empty($_login))
    {
        $_err[] = 'Укажите пользователя';
    }

    if(empty($_passwd))
    {
        $_err[] = 'Укажите пароль для пользователя';
    }

    if( !empty($_err) )
    {
        __show_message($_err);
    }
    else
    {
        $_upd = array(
            'password' => password_hash($_passwd, PASSWORD_BCRYPT),
        );
        $res = db_update('user', $_upd, 'login="'.__db_strip('user', $_login).'"');
        if( !$res )
        {
            __show_message(db_get_error('user'));
            die();
        }

        __show_message('Password of user "'.$_login.'" updated.', 'success');
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










