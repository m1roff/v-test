<?php
$_dataPerformers = getPerformers();
?>

<div class="row">
    <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#newOrder">Добавить заказ</button>
</div>

<div class="row">
    <table class="table table-striped table-hover" id="customerOrdersTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Стоимость</th>
                <th>Исполнитель</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody >
            
        </tbody>
    </table>
</div>




<div class="modal fade" id="newOrder" tabindex="-1" role="dialog" aria-labelledby="newOrderLable">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="newOrderLable">Новый заказ</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="createOrder">
                    <div class="form-group">
                        <label for="orderPerformer" class="col-sm-2 control-label">Исполнитель</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="order_performer">
                                <option value="">Выберите исполнителя</option>
                                <? for($i=0, $max=count($_dataPerformers); $i<$max; ++$i) : ?>
                                <option value="<?=$_dataPerformers[$i]['id_user']?>"><?=$_dataPerformers[$i]['name']?></option>
                                <? endfor ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="orderAmount" class="col-sm-2 control-label">Стоимость</label>
                        <div class="col-sm-3">
                            <input name="order_amount" type="text" class="form-control" id="orderAmount" placeholder="0000.00">
                        </div>
                    </div>
                </form>

                <div style="display: none;" id="newOrderLoadin">
                    <center><img src="/img/loader.gif" /></center>
                </div>
                <div id="newOrderMessages">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="btnCreateOrder">Создать</button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function(){
        content2().get('customer_orders', null, '#customerOrdersTable > tbody')
    });

    jQuery('#newOrder').on('hidden.bs.modal', function (e) {
    })

    jQuery('#btnCreateOrder').on('click', function(){
        newOrderWait('wait');
        content2().action('create_order', jQuery('#createOrder').serialize(), function(data, status, xhr){
            content2().get('customer_orders', null, '#customerOrdersTable > tbody')
            alert.put('#newOrderMessages', data.message, 'success');
            newOrderWait('complete');
            jQuery('input, select', '#createOrder').val('');
        }, function(xhr, statusText, errorThrown){
            alert.put('#newOrderMessages', errorThrown, 'danger')
            newOrderWait('complete');
        });
        return true;
    })

    function newOrderWait(action)
    {
        if(action=='wait')
        {
            jQuery('#newOrderMessages').empty();
            jQuery('.modal-footer > button').attr('disabled', true);
            jQuery('#newOrderLoadin').show();
        }
        if(action=='complete')
        {
            jQuery('.modal-footer > button').attr('disabled', false);
            jQuery('#newOrderLoadin').hide();
        }
    }
</script>