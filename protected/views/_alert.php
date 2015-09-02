<?php
/**
 * Вью для показа сообщений
 * Использует ф-ия {@link showAlert()}
 * @todo Требует доработки. 
 * @todo Тип сообщения
 */
?>
<?php if(!empty($messages)) : ?>
    <? for($i=0, $max=count($messages); $i<$max; ++$i) : ?>
<div class="alert alert-warning alert-dismissible" role="alert"><?=$messages[$i]?></div>
    <? endfor ?>
<? endif ?>