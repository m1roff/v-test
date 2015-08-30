<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/protected/_load_main_files.php');
if( !isAjaxRequest() || !userInfo() )
{
    header("HTTP/1.0 404 Not Found");
    exit;
}

// print_r($_POST);
if(empty($_POST) || empty($_POST['page_name']))
{
    showView('index');
}
else
{
    switch($_POST['page_name'])
    {
        case 'customer_orders':
            customer_show_orders();
            break;
        case 'performer_orders':
            performer_show_orders();
            break;
        default:
            header("HTTP/1.0 404 Not Found. Page name is not correct.");
            exit;
    }
}



function showView($viewName)
{
    require(dirname(__FILE__).'/protected/views/'.userInfo('type').'/'.$viewName.'.php');
}

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
