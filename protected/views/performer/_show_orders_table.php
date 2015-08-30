<?php
$_dataCount = count($data);
if( $_dataCount == 0 )
{
    ?><tr class="danger"><td colspan="4"><center>Нет заказов</center></td></tr><?
}
else
{
    for($i=0; $i<$_dataCount; ++$i)
    {
        ?><tr line="<?=$data[$i]['id_orders']?>">
            <td><?=$data[$i]['id_orders']?></td>
            <td><?=$data[$i]['amount']?></td>
            <td><?=$data[$i]['date_created']?></td>
            <td>
                <button type="button" class="btn btn-info btn-sm" ofin="<?=$data[$i]['id_orders']?>">выполнить</button>
            </td>
        </tr><?
    }
}

?>
<script type="text/javascript">
    
</script>