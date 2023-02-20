/**
 * @brief 
 * @note id filter-brand*, filter-attr*, filter-price*, filter-order*, filter-by*
 * @param object config:lightClassName,descClassName,ascClassName
 */
function searchFilter(config)
{
    _self = this;
    var lightClassName = config && config.lightClassName ? config.lightClassName : 'current';
    var descClassName  = config && config.descClassName  ? config.descClassName  : 'desc';
    var ascClassName   = config && config.ascClassName   ? config.ascClassName   : 'asc';

    //jsurl
    var searchObj = {};
    var attrObj   = {};
    var searchArray = decodeURIComponent(window.location.search.substring(1)).split("&");
    for(var searchStr in searchArray)
    {
        if(searchArray[searchStr].indexOf('=') !== -1)
        {
            var tempObj = searchArray[searchStr].split('=');

            if(tempObj[1])
            {
                if(tempObj[0].indexOf('attr') !== -1)
                {
                    var attrId = tempObj[0].match(/\d+/);
                    attrObj[attrId] = tempObj[1];
                }
                searchObj[tempObj[0]] = tempObj[1];
            }
        }
    }

	//
	if(searchObj.brand)
	{
	    $('[id ^= "filter-brand"]').removeClass(lightClassName);
	    $('#filter-brand'+searchObj.brand).addClass(lightClassName);
	}

	//
	for(var attrId in attrObj)
	{
	    $('[id ^= "filter-attr'+attrId+'"]').removeClass(lightClassName);
	    $('#filter-attr'+attrId+attrObj[attrId]).addClass(lightClassName);
	}

	//
	if(searchObj.min_price && searchObj.max_price)
	{
	    var priceId = searchObj.min_price+"-"+searchObj.max_price;
	    $('[id ^= "filter-price"]').removeClass(lightClassName);
	    $('#filter-price'+priceId).addClass(lightClassName);

    	$('input[name="min_price"]').val(parseFloat(searchObj.min_price));
    	$('input[name="max_price"]').val(parseFloat(searchObj.max_price));
	}

	//
	if(searchObj.order)
	{
	    $('#filter-order'+searchObj.order).addClass(lightClassName);
	    if(searchObj.by == 'desc')
	    {
	        $('#filter-by'+searchObj.order).removeClass(ascClassName);
	        $('#filter-by'+searchObj.order).addClass(descClassName);
	    }
	    else
	    {
	        $('#filter-by'+searchObj.order).removeClass(descClassName);
	        $('#filter-by'+searchObj.order).addClass(ascClassName);
	    }
	}

    //URL
	this.link = function(param)
	{
	    for(var i in param)
	    {
	        if(i == 'order')
	        {
	            searchObj['by'] = searchObj[i] == param[i] && searchObj['by'] == 'asc' ? 'desc' : 'asc';
	        }
	        searchObj[i] = param[i];
	    }

	    if(window.location.search)
	    {
	        window.location.href = window.location.href.replace(window.location.search,"?"+jQuery.param(searchObj));
	    }
	    else
	    {
	        window.location.href = window.location.href+"?"+jQuery.param(searchObj);
	    }
	}
}