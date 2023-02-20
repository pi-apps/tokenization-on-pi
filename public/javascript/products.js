/**/
__GOODSID = "";//ID

/**
 * @brief 
 * @param int goods_id ID
 * @param int user_id ID
 * @param string promo 
 * @param int active_id ID
 * @param string type 
 */
function productClass(goods_id,user_id,promo,active_id,type)
{
	_self         = this;
	this.goods_id = goods_id; //ID
	__GOODSID     = goods_id; //ID
	this.user_id  = user_id;  //ID
	this.type     = type;

	this.province_id;         //ID
	this.province_name;       //

	this.promo    = promo;    //
	this.active_id= active_id;//ID

	/**
	 * 
	 * @page 
	 */
	this.comment_ajax = function(page)
	{
		comment_ajax(page);
	}

	/**
	 * 
	 * @page 
	 */
	this.history_ajax = function(page)
	{
		history_ajax(page);
	}

	/**
	 * 
	 * @page 
	 */
	this.refer_ajax = function(page)
	{
		refer_ajax(page);
	}

	/**
	 * 
	 * @page 
	 */
	this.discuss_ajax = function(page)
	{
		discuss_ajax(page);
	}

	/**
	 * @brief 
	 * @param int provinceId ID
	 * @param string provinceName 
	 */
	this.delivery = function(provinceId,provinceName)
	{
		$('[name="localArea"]').text(provinceName);

		var buyNums   = $('#buyNums').val();
		var productId = $('#product_id').val();
		var goodsId   = _self.goods_id;

		//
		var deliveryTemplate = '<%if(if_delivery == 0){%><%=name%><b style="color:#fe6c00"><%=price%></b><%=description%>&nbsp;&nbsp;';
			deliveryTemplate+= '<%}else{%>';
			deliveryTemplate+= '<%=name%><b style="color:red"><%=reason%></b>&nbsp;&nbsp;<%}%>';

		//id,
		$.getJSON(creatUrl("block/order_delivery"),{'province':provinceId,'goodsId':goodsId,'productId':productId,'num':buyNums,'random':Math.random},function(json)
		{
			//
			$('#deliveInfo').empty();

			for(var item in json)
			{
				var deliveRowHtml = template.compile(deliveryTemplate)(json[item]);
				$('#deliveInfo').append(deliveRowHtml);
			}
		});

		//
		_self.province_id   = provinceId;
		_self.province_name = provinceName;
	}

	/**
	 * @brief 
	 */
	this.initLocalArea = function()
	{
		//IP
		$.getJSON(creatUrl('/block/iplookupAjax'),function(json){
			var ipAddress = json['province'];

			$.getJSON(creatUrl("block/searchProvince"),{'province':ipAddress,'random':Math.random},function(json)
			{
				if(json.flag == 'success')
				{
					//
					_self.delivery(json.area_id,ipAddress);
				}
			});
		});

		//
		$('[name="areaSelectButton"]').bind("click",function(){
			var provinceId   = $(this).attr('value');
			var provinceName = $(this).text();
			_self.delivery(provinceId,provinceName);
		});
	}

	//
	this.sendDiscuss = function()
	{
		var userId = _self.user_id;
		if(userId)
		{
			$('#discussTable').show('normal');
			$('#discussContent').focus();
		}
		else
		{
			alert('!');
		}
	}

	//
	this.sendDiscussData = function()
	{
		var content = $('#discussContent').val();
		var captcha = $('[name="captcha"]').val();

		if($.trim(content)=='')
		{
			alert('!');
			$('#discussContent').focus();
			return false;
		}
		if($.trim(captcha)=='')
		{
			alert('!');
			$('[name="captcha"]').focus();
			return false;
		}

		$.getJSON(creatUrl("site/discussUpdate"),{'content':content,'captcha':captcha,'random':Math.random,'id':_self.goods_id},function(json)
		{
			if(json.isError == false)
			{
				var discussHtml = template.render('discussRowTemplate',json);
				$('#discussBox').prepend(discussHtml);

				//
				$('#discussContent').val('');
				$('[name="captcha"]').val('');
				$('#discussTable').hide('normal');
				changeCaptcha();
			}
			else
			{
				alert(json.message);
			}
		});
	}

	//
	this.checkSpecSelected = function()
	{
		if(_self.specCount === $('[specId].current').length)
		{
			return true;
		}
		return false;
	}

	//
	this.initSpec = function()
	{
		//specId
		_self.specCount = 0;
		var tmpSpecId   = "";
		$('[specId]').each(function()
		{
			if(tmpSpecId != $(this).attr('specId'))
			{
				_self.specCount++;
				tmpSpecId = $(this).attr('specId');
			}
		});

		//
		$('[specId]').bind('click',function()
		{
			//
			$("[specId='"+$(this).attr('specId')+"']").removeClass('current');
			$(this).addClass('current');

			//
			if(_self.checkSpecSelected() == true)
			{
				//
				var specJSON = [];
				$('[specId].current').each(function()
				{
					var specData = $(this).data('specData');
					if(!specData)
					{
						alert("");
						return;
					}
					specData = typeof(specData) == 'string' ? JSON.parse(specData) : specData;

					specJSON.push({
						"id":specData.id,
						"value":specData.value,
						"name":specData.name,
						"image":specData.image,
					});
				});

				//
				$.post(creatUrl("site/getProduct"),{"goods_id":_self.goods_id,"specJSON":specJSON,"random":Math.random},function(json){
					if(json.flag == 'success')
					{
						//
						$('#data_goodsNo').text(json.data.products_no);
						$('#data_storeNums').text(json.data.store_nums);$('#data_storeNums').trigger('change');
						$('#data_groupPrice').text(json.data.group_price);
						$('#data_sellPrice').text(json.data.sell_price);
						$('#data_marketPrice').text(json.data.market_price);
						$('#data_weight').text(json.data.weight);
						$('#product_id').val(json.data.id);

window.day_nums=json.data.day_nums;
$('#buyNowText').html(window.day_nums==0?"":"");

						//
						_self.delivery(_self.province_id,_self.province_name);
					}
					else
					{
						alert(json.message);
					}
				},'json');
			}
		});
	}

	//
	this.checkBuyNums = function()
	{
		var minNums   = parseInt($('#buyNums').attr('minNums'));
		    minNums   = minNums ? minNums : 1;
		var maxNums   = parseInt($('#buyNums').attr('maxNums'));
			maxNums   = maxNums ? maxNums : parseInt($.trim($('#data_storeNums').text()));

		var buyNums   = parseInt($.trim($('#buyNums').val()));

		//0
		if(isNaN(buyNums) || buyNums < minNums)
		{
			$('#buyNums').val(minNums);
			alert(""+minNums);
		}

		//
		if(buyNums > maxNums)
		{
			$('#buyNums').val(maxNums);
			alert(""+maxNums);
		}
	}

	/**
	 * 
	 * @param code 
	 */
	this.modified = function(code)
	{
		var buyNums = parseInt($.trim($('#buyNums').val()));
		switch(code)
		{
			case 1:
			{
				buyNums++;
			}
			break;

			case -1:
			{
				buyNums--;
			}
			break;
		}
		$('#buyNums').val(buyNums);
		$('#buyNums').trigger('change');
	}

	//
	this.joinCart = function()
	{
		if(_self.checkSpecSelected() == false)
		{
			tips('');
			return;
		}

		var buyNums   = parseInt($.trim($('#buyNums').val()));
		var price     = parseFloat($.trim($('#real_price').text()));
		var productId = $('#product_id').val();
		var type      = productId ? 'product' : 'goods';
		var goods_id  = (type == 'product') ? productId : _self.goods_id;

		$.getJSON(creatUrl("simple/joinCart"),{"goods_id":goods_id,"type":type,"goods_num":buyNums,"random":Math.random},function(content)
		{
			if(content.isError == false)
			{
				//
				$.getJSON(creatUrl("simple/showCart"),{"random":Math.random},function(json)
				{
					$('[name="mycart_count"]').text(json.count);
					$('[name="mycart_sum"]').text(json.sum);

					tips(""+json.count+""+json.sum.toFixed(7));
				});
			}
			else
			{
				alert(content.message);
			}
		});
	}

	//
	this.buyNow = function()
	{
		//
		if(_self.checkSpecSelected() == false)
		{
			tips('');
			return;
		}

if(window.day_nums==0) return;

		//
		var buyNums = parseInt($.trim($('#buyNums').val()));
		var id      = _self.goods_id;
		var type    = 'goods';

		if($('#product_id').val())
		{
			id   = $('#product_id').val();
			type = 'product';
		}

		//
		var url = "/simple/cart2/id/"+id+"/num/"+buyNums+"/type/"+type;

		//
//		window.location.href = creatUrl(url);
window.localStorage.href = url;
gourl(eurl(creatUrl(url), true));

	}

	//
	!(function()
	{
		//IP
		_self.initLocalArea();

		//
		_self.initSpec();

		//ID
		$("<input type='hidden' id='product_id' alt='ID' value='' />").appendTo("body");

		//
		$('[thumbimg]').bind('click',function()
		{
			$('#picShow').prop('src',$(this).attr('thumbimg'));
			$('#picShow').attr('rel',$(this).attr('sourceimg'));
			$(this).addClass('current');
		});

		//
		$('[name="discussButton"]').bind("click",function(){_self.sendDiscuss();});
		$('[name="sendDiscussButton"]').bind("click",function(){_self.sendDiscussData();});

		//
		$('#buyAddButton').bind("click",function(){_self.modified(1);});
		$('#buyReduceButton').bind("click",function(){_self.modified(-1);});
		$('#buyNums').bind("change",function()
		{
			//
			_self.checkBuyNums();

			//
			_self.delivery(_self.province_id,_self.province_name);
		});

		//
		$('#buyNowButton').bind('click',function(){_self.buyNow();});

		//
		$('#joinCarButton').bind('click',function(){_self.joinCart();});

		//,
		$('#data_storeNums').bind('change',function()
		{
			var storeNum = parseInt($(this).text());
			if(storeNum <= 0)
			{
				alert("");

				//
				$('#buyNowButton,#joinCarButton').prop('disabled',true);
				$('#buyNowButton,#joinCarButton').addClass('disabled');
			}
			else
			{
				//
				$('#buyNowButton,#joinCarButton').prop('disabled',false);
				$('#buyNowButton,#joinCarButton').removeClass('disabled');
			}
		});

		//
		if((_self.promo && _self.active_id) || _self.type != 'default')
		{
			$('#joinCarButton').hide();
		}
	}())
}

/**
 * 
 * @page 
 */
comment_ajax = function(page)
{
	if(!page && $.trim($('#commentBox').text()))
	{
		return;
	}

	page = page ? page : 1;
	$.getJSON(creatUrl("site/comment_ajax/page/"+page+"/goods_id/"+__GOODSID),function(json)
	{
		//
		$('#commentBox').empty();

		for(var item in json.data)
		{
			var commentHtml = template.render('commentRowTemplate',json.data[item]);
			$('#commentBox').append(commentHtml);
		}

		if(json.data.length == 0)
		{
		    $('#commentBox').html('<div style="text-align: center;line-height: 120px;color: #999;"></div>');
		}
		$('#commentBox').append(json.pageHtml);
	});
}

/**
 * 
 * @page 
 */
history_ajax = function(page)
{
	if(!page && $.trim($('#historyBox').text()))
	{
		return;
	}
	page = page ? page : 1;
	$.getJSON(creatUrl("site/history_ajax/page/"+page+"/goods_id/"+__GOODSID),function(json)
	{
		//
		$('#historyBox').empty();
		$('#historyBox').parent().parent().find('.pagination').remove();

		for(var item in json.data)
		{
			var historyHtml = template.render('historyRowTemplate',json.data[item]);
			$('#historyBox').append(historyHtml);
		}
		$('#historyBox').parent().after(json.pageHtml);
	});
}

/**
 * 
 * @page 
 */
refer_ajax = function(page)
{
	if(!page && $.trim($('#referBox').text()))
	{
		return;
	}
	page = page ? page : 1;
	$.getJSON(creatUrl("site/refer_ajax/page/"+page+"/goods_id/"+__GOODSID),function(json)
	{
		//
		$('#referBox').empty();

		for(var item in json.data)
		{
			var commentHtml = template.render('referRowTemplate',json.data[item]);
			$('#referBox').append(commentHtml);
		}

		if(json.data.length == 0)
		{
		    $('#referBox').html('<div style="text-align: center;line-height: 120px;color: #999;"></div>');
		}
		$('#referBox').append(json.pageHtml);
	});
}

/**
 * 
 * @page 
 */
discuss_ajax = function(page)
{
	if(!page && $.trim($('#discussBox').text()))
	{
		return;
	}
	page = page ? page : 1;
	$.getJSON(creatUrl("site/discuss_ajax/page/"+page+"/goods_id/"+__GOODSID),function(json)
	{
		//
		$('#discussBox').empty();
		$('#discussBox').parent().parent().find('.pagination').remove();

		for(var item in json.data)
		{
			var historyHtml = template.render('discussRowTemplate',json.data[item]);
			$('#discussBox').append(historyHtml);
		}
		$('#discussBox').parent().after(json.pageHtml);
	});
}

//
dialogIndex = '';
function showTicket()
{
	var templateHtml = template.render("ticketTemplate");
	dialogIndex = layer.open({
		type:'1',
		style: 'background-color:#fff;width:100%;bottom:0px;padding:0;',
		content:templateHtml,
		skin:'footer',
		title:''
	})
}

function getTicket(id)
{
	$.getJSON(creatUrl("ucenter/trade_ticket"),{"ticket_id":id,"isAjax":1},function(json)
	{
		if(json && json['status'] == 'success')
		{
			tips('');
			layer.close(dialogIndex);
		}
		else
		{
			alert(json['msg']);
		}
	});
}