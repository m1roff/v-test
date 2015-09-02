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
    $_page_data = array();
    if(!empty($_POST['page_data'])) $_page_data = $_POST['page_data'];
    switch($_POST['page_name'])
    {
        case 'customer_orders':
            // Показать заказы заказчику
            customer_show_orders($_page_data);
            break;
        case 'customer_pagination':
            customer_pagination();
            break;
        case 'performer_orders':
            // Показать заказы исполнителю
            performer_show_orders($_page_data);
            break;
        case 'performer_pagination':
            performer_pagination();
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
 * @param Array $data Дополнительные данные
 */
function customer_show_orders(Array $data=array())
{
    $limit = $GLOBALS['pagination']['customer']['limit'];
    $offset = 0;
    if(isset($data['offset']))
    {
        $offset = $limit*$data['offset'];
    }

    $data = db_select(
        'orders', 
        'orders.*, user.name as performer', 
        'orders.id_customer='.userInfo('id_user'), 
        'order by orders.date_created asc, orders.id_orders asc limit '.$offset.','.$limit, 
        array(
            'user' => array(
                'on' => 'user.id_user = orders.id_performer'
            ),
        )
    );
    require(dirname(__FILE__).'/protected/views/customer/_show_orders_table.php');
}

/**
 * Пагинация для Заказчика
 */
function customer_pagination()
{
    $total = 0;
    $limit = $GLOBALS['pagination']['customer']['limit'];
    $_res = db_select_row('orders', 'count(*) count', 'orders.id_customer='.userInfo('id_user'));
    if(!empty($_res))
    {
        $total = (int)$_res['count'];
    }
    require(dirname(__FILE__).'/protected/views/_pagination.php');
}

/**
 * Выборка и показа заказов исполнителя
 * @param Array $data Дополнительные данные
 */
function performer_show_orders(Array $data=array())
{
    $limit = $GLOBALS['pagination']['performer']['limit'];
    $offset = 0;
    if(isset($data['offset']))
    {
        $offset = $limit*$data['offset'];
    }
    $data = db_select(
        'orders', 
        'orders.*', 
        'orders.status="0" AND orders.id_performer='.userInfo('id_user'), 
        'order by orders.date_created asc, orders.id_orders asc limit '.$offset.','.$limit
    );
    require(dirname(__FILE__).'/protected/views/performer/_show_orders_table.php');
}

/**
 * Пагинация для исполнителя
 */
function performer_pagination()
{
    $total = 0;
    $limit = $GLOBALS['pagination']['performer']['limit'];
    $_res = db_select_row('orders', 'count(*) count', 'orders.status="0" AND orders.id_performer='.userInfo('id_user'));
    if(!empty($_res))
    {
        $total = (int)$_res['count'];
    }
    require(dirname(__FILE__).'/protected/views/_pagination.php');
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