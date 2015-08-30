var content = function()
{
    var main_container = '#main_container';

    var success_data_place;

    function _onSuccess(data, status, xhr)
    {
        console.log('_onSuccess:', data, status, xhr);

        if(!success_data_place) success_data_place = main_container;

        jQuery(success_data_place).html(data);
    }

    function _onError(xhr, statusText, errorThrown)
    {
        console.log('_onError:', xhr, statusText, errorThrown);
        alert.put('#alerts_container', errorThrown, 'danger')
    }

    return {
        get : function(pageName, pageData, place)
        {
            if(place) success_data_place = place;
            // jQuery.ajax('/getcontent.php', pageData, _onSuccess, 'html');
            jQuery.ajax('/getcontent.php', {
                'cache' : false,
                'dataType' : 'html',
                'method' : 'POST',
                'success' : _onSuccess,
                'error' : _onError,
                'data' : {'page_name': pageName, 'page_data': pageData}
            });
        },
        action : function(action, data, onSuccessFn, onErrorFn)
        {
            return jQuery.ajax('/action.php', {
                'cache' : false,
                'dataType' : 'json',
                'method' : 'POST',
                'success' : onSuccessFn,
                'error' : onErrorFn,
                'data' : {'action': action, 'data': data}
            });
        }
    };
}();


var alert = function()
{
    return {
        put : function(place, mgs, type)
        {
            // success | info | warning | danger
            if(!type) type='info';
            jQuery(place).append(
                '<div class="alert alert-'+type+'" role="alert">'
                    +'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
                +mgs
                +'</div>'
            );
        }
    }
}();