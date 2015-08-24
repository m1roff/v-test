<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/protected/_load_main_files.php');
/**
 * Пока что через файл php, может придется какие-либо операции проводить
 */
if( userInfo() === false )
{
    header('HTTP/1.0 401 Unauthorized');
    header( 'Location: /signin.php', false );
    die();
}
?><!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Тестовое задание - заказы</title>
        <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>

        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    
                    <a class="navbar-brand" href="/">tOrders</a>
                </div>
                
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="/">Home</a></li>
                    </ul>
                    
                    <ul class="nav navbar-nav navbar-right">
                        <li><a ><?=userInfo('name')?></a></li>
                        <li><a href="/logout.php">Выйти</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>







        <div id="main_container" class="container">
            
        </div> <!-- /container -->







        <script src="/vendor/jquery/jquery-1.11.3.min.js"></script>
        <script src="/vendor/bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>