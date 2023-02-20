window.gourl=function (url){
window.exitconfirm=false;
	location.href = url
}

//
function removeCart(goods_id,type)
{
	var goods_id = parseInt(goods_id);
	$.getJSON(creatUrl("simple/removeCart"),{goods_id:goods_id,type:type},function(content){
		if(content.isError == false)
		{
			$('[name="mycart_count"]').html(content.data['count']);
			$('[name="mycart_sum"]').html(content.data['sum']);
		}
		else
		{
			alert(content.message);
		}
	});
}

//
function favorite_add_ajax(goods_id,obj)
{
	$.getJSON(creatUrl("simple/favorite_add"),{"goods_id":goods_id,"random":Math.random()},function(content){
		tips(content.message);
	});
}

//
function showCart()
{
	$.getJSON(creatUrl("simple/showCart"),{sign:Math.random()},function(content)
	{
		var cartTemplate = template.render('cartTemplete',{'goodsData':content.data,'goodsCount':content.count,'goodsSum':content.sum});
		$('#div_mycart').html(cartTemplate);
		$('#div_mycart').show();
	});
}


//dom
jQuery(function()
{
	//
	if($('[name="mycart_count"]').length > 0)
	{
		$.getJSON(creatUrl("simple/showCart"),{sign:Math.random()},function(content)
		{
			$('[name="mycart_count"]').html(content.count);
		});

		//div
		var mycartLateCall = new lateCall(200,function(){showCart();});
		$('[name="mycart"]').hover(
			function(){
				mycartLateCall.start();
			},
			function(){
				mycartLateCall.stop();
				$('#div_mycart').hide('slow');
			}
		);
	}
});

//[ajax]
function joinCart_ajax(id,type)
{
	$.getJSON(creatUrl("simple/joinCart"),{"goods_id":id,"type":type,"random":Math.random()},function(content){
		if(content.isError == false)
		{
			var count = parseInt($('[name="mycart_count"]').html()) + 1;
			$('[name="mycart_count"]').html(count);
			tips(content.message);
		}
		else
		{
			alert(content.message);
		}
	});
}

//
function joinCart_list(id)
{
	$.getJSON(creatUrl("/simple/getProducts"),{"id":id},function(content)
	{
		if(!content || content.length == 0)
		{
			joinCart_ajax(id,'goods');
		}
		else
		{
			artDialog.open(creatUrl("/block/products_list/goods_id/"+id+"/type/radio"),{
				id:'selectProduct',
				title:'',
				okVal:'',
				ok:function(iframeWin, topWin)
				{
					var productObj = $(iframeWin.document).find('input[name="id[]"]:checked');

					//
					joinCart_ajax(productObj.val(),'product');
					return true;
				}
			})
		}
	});
}