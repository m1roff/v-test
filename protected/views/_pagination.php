<?php
/**
 * Вью для показа пагинации на страницах
 */
if(!isset($limit))
{
    header("HTTP/1.0 400 Pagination: limit is not defined.");
    exit;
}

if(!isset($total))
{
    header("HTTP/1.0 400 Pagination: total is not defined.");
    exit;
}
$max=ceil($total/$limit);

if($max>1)
{
    ?><nav>
        <ul class="pagination">
            <? for($i=0; $i<$max; ++$i) : ?>
                <li class="order_paging<?=$i==0 ? ' active' : null?>"><a value="<?=($i)?>"><?=($i+1)?></a></li>
            <? endfor ?>
        </ul>
    </nav><?
}