//
function initProductTable()
{
	//
	var goodsHeadHtml = template.render('goodsHeadTemplate',{'templateData':[]});
	$('#goodsBaseHead').html(goodsHeadHtml);

	//
	var goodsRowHtml = template.render('goodsRowTemplate',{'templateData':[[]]});
	$('#goodsBaseBody').html(goodsRowHtml);
}

//
function selSpec(obj)
{
	var specIsHere    = getIsHereSpec();
	var specValueData = specIsHere.specValueData;
	var specData      = specIsHere.specData;

	//
	var jsonData = $(obj).find("option:selected").val();
	if(!jsonData)
	{
		return;
	}
	var json = $.parseJSON(jsonData);

	//
	if(specValueData[json.name])
	{
		for(var k in specValueData[json.name])
		{
			if(specValueData[json.name][k]['value'] == json.value)
			{
				alert('');
				return;
			}
		}
	}
	else
	{
		specData[json.name]      = json;
		specValueData[json.name] = [];
	}
	specValueData[json.name].push({"image":json.image,"value":json.value});
	createProductList(specData,specValueData);
}

//IDajax
function selSpecVal(obj)
{
	var spec_id    = $(obj).val();
	var optionHtml = '<option value=""></option>';
	$.getJSON(creatUrl("block/spec_value_list"),{"id":spec_id},function(json)
	{
		if(json.value)
		{
			var valObj = $.parseJSON(json.value);
			for(var i in valObj)
			{
				json.value = i;
				json.image = valObj[i];
				optionHtml+= "<option value='"+JSON.stringify(json)+"'>"+i+"</option>";
			}
			$('#specValSel').html(optionHtml);
		}
	});
	$('#specValSel').html(optionHtml);
}

//
function descartes(list,specData)
{
	//parent;count
	var point  = {};

	var result = [];
	var pIndex = null;
	var tempCount = 0;
	var temp   = [];

	//
	for(var index in list)
	{
		if(typeof list[index] == 'object')
		{
			point[index] = {'parent':pIndex,'count':0}
			pIndex = index;
		}
	}

	//
	if(pIndex == null)
	{
		return list;
	}

	//
	while(true)
	{
		for(var index in list)
		{
			tempCount = point[index]['count'];
			var itemSpecData = list[index][tempCount];
			temp.push({"id":specData[index].id,"value":itemSpecData.value,"name":specData[index].name,"image":itemSpecData.image});
		}

		//
		result.push(temp);
		temp = [];

		//
		while(true)
		{
			if(point[index]['count']+1 >= list[index].length)
			{
				point[index]['count'] = 0;
				pIndex = point[index]['parent'];
				if(pIndex == null)
				{
					return result;
				}

				//parent
				index = pIndex;
			}
			else
			{
				point[index]['count']++;
				break;
			}
		}
	}
}

//
function getIsHereSpec()
{
	//
	var specValueData = {};
	var specData      = {};

	//
	if($('input:hidden[name^="_spec_array"]').length > 0)
	{
		$('input:hidden[name^="_spec_array"]').each(function()
		{
			var json = $.parseJSON(this.value);
			if(!specValueData[json.name])
			{
				specData[json.name]      = json;
				specValueData[json.name] = [];
			}

			//spec_array
			for(var i in specValueData[json.name])
			{
				for(var item in specValueData[json.name][i])
				{
					item = specValueData[json.name][i];
					if(item.value == json.value)
					{
						return;
					}
				}
			}
			specValueData[json.name].push({"image":json.image,"value":json.value});
		});
	}
	return {"specData":specData,"specValueData":specValueData};
}

/**
 * @brief 
 * @param object specData,{1:{id:1,value:"",name:"1"},2:{id:2,value:"",name:"2"}}
 * @param object specValueData ,{1:[{image:"1",value:"1"},{image:"2",value:"2"},{image:"3",value:"3"}],2:[{image:"5",value:"5"},{image:"6",value:"6"}]}
 */
function createProductList(specData,specValueData)
{
	//
	var specMaxData = descartes(specValueData,specData);

	//
	var productList = [];
	for(var i = 0;i < specMaxData.length;i++)
	{
		//
		var productJson = {};

		//JSON,tr
		var specContent = ['has(input[type="hidden"])'];
		for(var j in specMaxData[i])
		{
            specContent.push( 'has([value *= \'"value":"'+specMaxData[i][j]['value']+'","name":"'+specMaxData[i][j]['name']+'"\'])' );
		}

        //
		while(true)
		{
		    var inputDataJQ = $('#goodsBaseBody tr:'+specContent.join(":")).find('input[type="text"]');
    		if(inputDataJQ.length == 0 && specContent.length > 0)
    		{
    		    specContent.pop();
    		}
    		else
    		{
    		    break;
    		}
		}

		inputDataJQ.each(function(){
			productJson[this.name.replace(/^_(\w+)\[\d+\]/g,"$1")] = this.value;
		});

		var productItem = {};
		for(var index in productJson)
		{
			//
			if(index == 'goods_no')
			{
				//
				if(productJson[index] == '')
				{
					productJson[index] = defaultProductNo;
				}

				if(productJson[index].match(/(?:\-\d*)$/) == null)
				{
					//
					productItem['goods_no'] = productJson[index]+'-'+(i+1);
				}
				else
				{
					//
					productItem['goods_no'] = productJson[index].replace(/(?:\-\d*)$/,'-'+(i+1));
				}
			}
			else
			{
				productItem[index] = productJson[index];
			}
		}
		productItem['spec_array'] = specMaxData[i];
		productList.push(productItem);
	}

	//
	var goodsHeadHtml = template.render('goodsHeadTemplate',{'templateData':specData});
	$('#goodsBaseHead').html(goodsHeadHtml);

	//
	var goodsRowHtml = template.render('goodsRowTemplate',{'templateData':productList});
	$('#goodsBaseBody').html(goodsRowHtml);

	if($('#goodsBaseBody tr').length == 0)
	{
		initProductTable();
	}
}

/**
 * 
 */
function defaultImage(_self)
{
	$('#thumbnails img[name="picThumb"]').removeClass('current');
	$(_self).addClass('current');
}

//
function delSpec(specId)
{
	$('input:hidden[name^="_spec_array"]').each(function()
	{
		var json = $.parseJSON(this.value);
		if(json.id == specId)
		{
			$(this).remove();
		}
	});

	//
	var specIsHere = getIsHereSpec();
	createProductList(specIsHere.specData,specIsHere.specValueData);
}

//jquery
$('[name="_goodsFile"]').fileupload({
    dataType: 'json',
    done: function (e, data)
    {
    	if(data.result && data.result.flag == 1)
    	{
    	    //{'flag','img','list','show'}
    	    var picJson = data.result;
        	var picHtml = template.render('picTemplate',{'picRoot':picJson.img});
        	$('#thumbnails').append(picHtml);

        	//
        	if($('#thumbnails img[name="picThumb"][class="current"]').length == 0)
        	{
        		$('#thumbnails img[name="picThumb"]:first').addClass('current');
        	}
    	}
    	else
    	{
    		alert(data.result.error);
    		$('#uploadPercent').text(data.result.error);
    	}
    	$('[type="submit"]').attr('disabled',false);
    },
    progressall: function (e, data)
    {
        var progress = parseInt(data.loaded / data.total * 100);
        $('#uploadPercent').text(""+progress+"%");
    },
    start: function(e)
    {
        $('[type="submit"]').attr('disabled',true);
    }
});

/**
 * 
 * @param obj 
 */
function memberPrice(obj,seller_id)
{
	var sellerId  = seller_id ? seller_id : 0;
	var sellPrice = $(obj).siblings('input[name^="_sell_price"]')[0].value;
	if($.isNumeric(sellPrice) == false)
	{
		alert('');
		return;
	}

	var groupPriceValue = $(obj).siblings('input[name^="_groupPrice"]');

	//
	art.dialog.data('groupPrice',groupPriceValue.val());

	//
	var tempUrl = creatUrl("goods/member_price/sell_price/@sell_price@/seller_id/@seller_id@");
//	tempUrl = tempUrl.replace('@sell_price@',sellPrice).replace('@seller_id@',sellerId);
tempUrl = durl(tempUrl).replace('@sell_price@',sellPrice).replace('@seller_id@',sellerId);

	art.dialog.open(tempUrl,{
		id:'memberPriceWindow',
	    title: '',
	    ok:function(iframeWin, topWin)
	    {
	    	var formObject = iframeWin.document.forms['groupPriceForm'];
	    	var groupPriceObject = {};
	    	$(formObject).find('input[name^="groupPrice"]').each(function(){
	    		if(this.value != '')
	    		{
	    			//groupID
		    		var groupId = this.name.replace('groupPrice','');

		    		//json
		    		groupPriceObject[groupId] = this.value;
	    		}
	    	});

	    	//
    		var temp = [];
    		for(var gid in groupPriceObject)
    		{
    			temp.push('"'+gid+'":"'+groupPriceObject[gid]+'"');
    		}
    		groupPriceValue.val('{'+temp.join(',')+'}');
    		return true;
		}
	});
}

//
function speedSpec()
{
    var specItem = "<div class='form-group'><input type='text' class='form-control' placeholder='' name='speedSpecName' /><textarea class='form-control' placeholder='' rows='4' name='speedSpecValue' /></textarea></div>";
    var specHtml = "<form id='speedBox' style='width:350px;height:380px;overflow:auto'></form>";
    art.dialog(
    {
        "title":"",
        "init":function(){$('#speedBox').append(specItem);},
        "content":specHtml,
        "ok":function()
        {
            var specNameData  = {};
            var specValueData = {};
            $('[name="speedSpecValue"]').each(function(indexId)
            {
                var specArray = $(this).val();
                if(specArray)
                {
                    var specName = $('[name="speedSpecName"]:eq('+indexId+')').val();
                    specNameData[specName]  = {"id":String(Math.floor(Math.random()*(50000-5000+1)+5000)),"name":specName,"value":""};
                    specValueData[specName] = [];

                    var tmpData  = specArray.split("\n");
                    var testItems= [];//
                    for(var i in tmpData)
                    {
                        if(tmpData[i] && $.inArray(tmpData[i],testItems) == -1)
                        {
                            testItems.push(tmpData[i]);
                            specValueData[specName].push({"image":"","value":tmpData[i]});
                        }
                    }
                }
            });
            createProductList(specNameData,specValueData);

            //
            $('#synCheckBox').prop('checked',true);
            synData();
            return true;
        },
        "okVal":"",
        "button":[{"name":"","callback":function()
            {
                $('#speedBox').append(specItem);
                return false;
            }}
        ],
    });
}

//
function synData()
{
    var isOpen = $('#synCheckBox').prop('checked');
    var synName = ["_goods_no","_store_nums","_market_price","_sell_price","_cost_price","_weight"];
    if(isOpen == true)
    {
        for(var indexVal in synName)
        {
            $('input[name^="'+synName[indexVal]+'"]:eq(0)').on('keyup',function()
            {
                var nameVal = $(this).attr('name').replace("[0]","");
                $('input[name^="'+nameVal+'"]:gt(0)').val($(this).val());
            });
        }
    }
    else
    {
        for(var indexVal in synName)
        {
            $('input[name^="'+synName[indexVal]+'"]:eq(0)').off();
        }
    }
}