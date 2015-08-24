<?php
session_start();
/**
 * Авторизация
 * 
 * Инфо:
 *  Защита от перехвата не предусмотрена.
 *  Ее можно решить с помоьщю https 
 *  или примерно как тут оипсано http://habrahabr.ru/post/85698/
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/protected/_load_main_files.php');
$_authUser = false;
if(!empty($_POST))
{
    $_authUser = authUser($_POST);
    if( $_authUser === true )
    {
        header( 'Location: /');
    }
}

?><!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Авторизация</title>
        <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="/css/signin.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <form class="form-signin" action="/signin.php" method="POST">
                <label for="login" class="sr-only">Ваш логин</label>
                <input value="<?=isset($_POST['login']) ? $_POST['login'] : null ?>" type="text" name="login" id="login" class="form-control" placeholder="Ваш логин" required autofocus>
            
                <label for="passwd" class="sr-only">Ваш пароль</label>
                <input type="password" name="passwd" id="passwd" class="form-control" placeholder="Ваш пароль" required>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Авторизоваться</button>
                <br/>
                <?showAlert($_authUser)?>
            </form>



        </div>



    </body>
</html>
