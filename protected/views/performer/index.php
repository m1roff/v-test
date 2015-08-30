
<div class="row">
    <table class="table table-striped table-hover" id="performerOrdersTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Стоимость</th>
                <th>Дата</th>
                <th>Дейстие</th>
            </tr>
        </thead>
        <tbody >
            
        </tbody>
    </table>
</div>


<script type="text/javascript">
    jQuery(document).ready(function(){
        content.get('performer_orders', null, '#performerOrdersTable > tbody')

    });

     jQuery(document).on('click', 'button[ofin]',function(){
        var ofin = jQuery(this).attr('ofin');
        var line = jQuery('tr[line="'+ofin+'"]');

        // forWait('wait', ofin);
        jQuery('#alerts_container').empty();
        jQuery(line).addClass('info');
        jQuery('button[ofin="'+ofin+'"]', line).attr('disabled', true).after('<img ofin-loader="'+ofin+'" src="/img/loader_25.gif"/>');


        content.action('finish_order', {ofin:ofin}, function(data, status, xhr){

        }, function(xhr, statusText, errorThrown){
            alert.put('#alerts_container', errorThrown, 'danger');
        });
        return true;
    });

     function forWait(action, ofin)
     {
        

        if(action=='wait')
        {
            jQuery('#alerts_container').empty();
            jQuery(line).addClass('info');
            jQuery('button[ofin="'+ofin+'"]', line).attr('disabled', true).after('<img ofin-loader="'+ofin+'" src="/img/loader_25.gif"/>');
        }

        if(action=='complete')
        {
            jQuery('img[ofin-loader="'+ofin+'"]').remove();
        }
     }
</script>