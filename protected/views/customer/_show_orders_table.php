<?
$_dataCount = count($data);
if( $_dataCount == 0 )
{
    ?><tr class="danger"><td colspan="4"><center>Нет заказов</center></td></tr><?
}
else
{

    for($i=0; $i<$_dataCount; ++$i)
    {
        $_tr_class = $data[$i]['status']==1 ? 'success' : 'warning';
        ?><tr class="<?=$_tr_class?>">
            <td><?=$data[$i]['id_orders']?></td>
            <td><?=$data[$i]['amount']?></td>
            <td><?=$data[$i]['performer']?></td>
            <td><?=$data[$i]['date_created']?></td>
        </tr><?
    }
}