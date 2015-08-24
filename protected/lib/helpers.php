<?php

function showAlert($messages)
{
    if(empty($messages)) return false;
    if( !is_array($messages) )
    {
        $messages = [$messages];
    }
    include(dirname(__FILE__).'/../views/_alert.php');
}

function userInfo($row=null)
{
    if(!isset($_SESSION['user'])) return false;
    if( $row!==null )
    {
        if(isset($_SESSION['user'][$row])) return $_SESSION['user'][$row];
        return false;
    }

    return $_SESSION['user'];
}

/**
 * Для авторизации
 * @param Array $postData Данные из глобальной переменной $_POST
 * @return mixed true - если все удачно, array в случае ошибки
 */
function authUser($postData)
{
    if( userInfo() !== false ) return true;

    if( empty($postData['login']) || empty($postData['passwd']) )
    {
        $_errors = [];
        if( empty($postData['login']) )
        {
            $_errors[] = 'Не указан логин';
        }
        if( empty($postData['passwd']) )
        {
            $_errors[] = 'Не указан пароль';
        }
        return $_errors;
    }
    if ( !__try_auth_user($postData['login'], $postData['passwd']) )
    {
        return ['Логин или пароль неверный.'];
    }
    return true;
}















/**
 * Проверка, есть ли пользователь в БД
 * Для авторизации
 * @param string $user
 * @param string $passwd
 * @return bool
 */
function __try_auth_user($user, $passwd)
{
    $_user = db_select_row('user', '*', '`login`="'.__db_strip($user).'"');
    if( empty($_user) || !password_verify($passwd, $_user['password']) ) 
    {
        return false;
    }
    unset($_user['password']);
    $_SESSION['user'] = $_user;
    return true;
}

