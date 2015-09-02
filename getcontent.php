<?php
/**
 * Запрос на получение контента по определенной команде
 * При ошибке выдает 404
 * В случае удачи, возвращает HTML
 */
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/protected/_load_main_files.php');
if( !isAjaxRequest() || !userInfo() )
{
    header("HTTP/1.0 404 Not Found");
    exit;
}

if(empty($_POST) || empty($_POST['page_name']))
{
    // Страница по умолчанию в зависимости от авторизованного пользователя
    showView('index');
}
else
{
    switch($_POST['page_name'])
    {
        case 'customer_orders':
            // Показать заказы заказчику
            customer_show_orders();
            break;
        case 'performer_orders':
            // Показать заказы исполнителю
            performer_show_orders();
            break;
        case 'performer_balance':
            // Показ баланса исполнителя
            performer_balance();
            break;
        default:
            header("HTTP/1.0 404 Not Found. Page name is not correct.");
            exit;
    }
}



/**
 * Для показа вью страницы
 * Зависит от авторизованного пользователя
 * @param string $viewName Название вью
 */
function showView($viewName)
{
    require(dirname(__FILE__).'/protected/views/'.userInfo('type').'/'.$viewName.'.php');
}

/**
 * Выобрка и показ заказав заказчика
 */
function customer_show_orders()
{
    $data = db_select(
        'orders', 
        'orders.*, user.name as performer', 
        'orders.id_customer='.userInfo('id_user'), 
        'order by orders.date_created asc', 
        array(
            'user' => array(
                'on' => 'user.id_user = orders.id_performer'
            ),
        )
    );
    require(dirname(__FILE__).'/protected/views/customer/_show_orders_table.php');
}

/**
 * Выборка и показа заказов исполнителя
 */
function performer_show_orders()
{
    $data = db_select(
        'orders', 
        'orders.*', 
        'orders.status="0" AND orders.id_performer='.userInfo('id_user'), 
        'order by orders.date_created asc'
    );
    require(dirname(__FILE__).'/protected/views/performer/_show_orders_table.php');
}

/**
 * Показ баланса исполнителя
 */
function performer_balance()
{
    $ans = 0;
    $ans = userInfo('balance', true);
    echo $ans;
}