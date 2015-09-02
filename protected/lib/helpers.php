<?php
/**
 * Набор вспомогательный ф-ий
 */



/**
 * Проверка запроса на !|ajax
 * @return bool
 */
function isAjaxRequest()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
}

/**
 * Показ сообщений (алерт)
 * @todo Тип сообщения
 * @param mixed Сообщение. Так же принимает индексированный массив
 */
function showAlert($messages)
{
    if(empty($messages)) return false;
    if( !is_array($messages) )
    {
        $messages = [$messages];
    }
    include(dirname(__FILE__).'/../views/_alert.php');
}

/**
 * Получить информацию о текущем (авторизованном) пользователе
 * Первое значение устанавливается в {@link __try_auth_user()}
 * @param string $row Если необходимо получить конкретное поле
 * @param bool $force Обновить информаицю перед получаением. Используется только вмете с $row
 * @return mixed
 */
function userInfo($row=null, $force=false)
{
    if(!isset($_SESSION['user'])) return false;
    if( $row!==null )
    {
        if(isset($_SESSION['user'][$row])) 
        {
            if($force===true)
            {
                $_res = (db_select_row('user', $row,'id_user='.$_SESSION['user']['id_user']));
                if($_res)
                {
                    $_SESSION['user'][$row] = $_res[$row];
                }
            }
            return $_SESSION['user'][$row];
        }
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
 * Получить всех исполнителей
 * @return Array
 */
function getPerformers()
{
    $get = db_select('user', '*', 'type="performer"');
    return $get;
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
    $_user = db_select_row('user', '*', '`login`="'.__db_strip('user',$user).'"');
    if( empty($_user) || !password_verify($passwd, $_user['password']) ) 
    {
        return false;
    }
    unset($_user['password']);
    $_SESSION['user'] = $_user;
    return true;
}

