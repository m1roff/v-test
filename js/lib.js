function paging()
{
    function __removeAllActive()
    {
        jQuery('.order_paging.active').removeClass('active');
    }

    this.pagingClass = function()
    {
        return '.order_paging:not(.active) > a';
    }

    this.to = function(obj, action, place)
    {
        var $Obj = jQuery(obj);
        var offset = $Obj.attr('value');
        __removeAllActive();
        $Obj.parent('li').addClass('active');
        
        content2().get(action, {offset: offset}, place);
    }
    return this;
};

function content2()
{
    var main_container = '#main_container';

    var success_data_place;

    function _onSuccess(data, status, xhr)
    {
        if(!success_data_place) success_data_place = main_container;
        jQuery(success_data_place).html(data);
    }

    function _onError(xhr, statusText, errorThrown)
    {
        alert.put('#alerts_container', errorThrown, 'danger')
    }

    this.get = function(pageName, pageData, place)
        {
            if(place) success_data_place = place;
                else success_data_place = null;
            return jQuery.ajax('/getcontent.php', {
                'cache' : false,
                'dataType' : 'html',
                'method' : 'POST',
                'success' : _onSuccess,
                'error' : _onError,
                'data' : {'page_name': pageName, 'page_data': pageData}
            });
        };
    this.action = function(action, data, onSuccessFn, onErrorFn)
        {
            return jQuery.ajax('/action.php', {
                'cache' : false,
                'dataType' : 'json',
                'method' : 'POST',
                'success' : onSuccessFn,
                'error' : onErrorFn,
                'data' : {'action': action, 'data': data}
            });
        };
    return this;
};


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


