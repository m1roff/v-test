<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/protected/_load_main_files.php');
if( !isAjaxRequest() || !userInfo() || empty($_POST) || empty($_POST['action']))
{
    header("HTTP/1.0 404 Not Found");
    exit;
}
header('Content-type:application/json;charset=utf-8');

if(isset($_POST['data']))
{
    $_d = urldecode($_POST['data']);
    if($_d)
    {
        parse_str( urldecode($_POST['data']), $_POST['data']);
        unset($_d);
    }
}

switch ($_POST['action']) {
    case 'create_order':
        order_create($_POST['data']);
        exit;

    case 'finish_order':
        order_finish($_POST['data']);
        exit;
    
    default:
        header("HTTP/1.0 404 Not Found");
        exit;
}



/**
 * @return mixed
 */
function order_create($data)
{
    if( userInfo('type') != 'customer' )
    {
        header("HTTP/1.0 405 Only for customers.");
        return false;
    }
    if(empty($data['order_performer']))
    {
        header("HTTP/1.0 406 Performer is not selected.");
        return false;
    }
    if(empty($data['order_amount']))
    {
        header("HTTP/1.0 406 Amount is not specified.");
        return false;
    }
    elseif(!is_numeric($data['order_amount']))
    {
        header("HTTP/1.0 406 Amount is not numeric.");
        return false;
    }
    // var_dump($return, json_encode($return));
    $_set = array(
        'id_customer'  => userInfo('id_user'),
        'id_performer' => $data['order_performer'],
        'amount'       => $data['order_amount'],
        'status'       => 0,
    );
    $_res = db_insert('orders', $_set, true);
    $return = array('message'=>'Заказ #'.$_res.' удачно создан.');
    echo json_encode($return);
    return true;
}

function order_finish($data)
{
    db_transaction_start();
    // TODO: Пока что не известно что выводить
    $return = [];
    $upd = [
        'orders.status' => '1'
    ];
    $_res = db_update('orders', $upd, 'orders.id_performer=:idUser AND orders.status = "0" AND orders.id_orders=:idOrders', [':idUser'=>userInfo('id_user'), ':idOrders'=>$data['ofin']] );
    if($_res)
    {
        db_transaction_commit();
    }
    else 
    {
        
        $return['message'] = db_get_error('orders');
        header("HTTP/1.0 406 ".$return['message']);
        db_transaction_rollback();
    }
    echo json_encode($return);
    return true;
}