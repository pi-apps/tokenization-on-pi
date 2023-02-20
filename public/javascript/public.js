//
function changeCaptcha()
{
	$('#captchaImg').prop('src',creatUrl("site/getCaptcha/random/"+Math.random()));
}

//web
function webroot(path)
{
	if(!path || typeof(path) != 'string')
	{
		return;
	}
	return path.indexOf('http') == 0 ? path : _webRoot+path;
}

/**
 * @brief 
 * @param object {"type":radio,none,checkbox,"callback":,"submit":,"seller_id":ID,"is_products":0,1,"mode":simple,normal}
 */
function searchGoods(config)
{
	var data         = config.data        ? config.data        : "";
	var mode         = config.mode        ? config.mode        : "simple";
	var is_products  = config.is_products ? config.is_products : 0;
	var listType     = config.type        ? config.type        : 'radio';
	var seller_id    = config.seller_id   ? config.seller_id   : 0;
	var conditionUrl = creatUrl('/goods/search/type/'+listType+'/seller_id/'+seller_id+'/is_products/'+is_products+'/mode/'+mode+'/'+data);

	var step = 0;
	var artConfig =
	{
		"id":"searchGoods",
		"title":"",
		"okVal":"",
		"button":
		[{
			"name":"",
			"callback":function(iframeWin,topWin)
			{
				if(step > 0)
				{
					iframeWin.window.history.go(-1);
					this.size(1,1);
					step--;
				}
				return false;
			}
		}],
		"ok":function(iframeWin,topWin)
		{
			//
			if(config.submit)
			{
				config.submit(iframeWin);
				return;
			}

			if(step == 0)
			{
				iframeWin.document.forms[0].submit();
				step++;
				return false;
			}
			else if(step == 1)
			{
				var goodsList = $(iframeWin.document).find('input[name="id[]"]:checked');

				//
				if(goodsList.length == 0)
				{
					alert('');
					return false;
				}
				//
				config.callback(goodsList);
				return true;
			}
		}
	};

	//
	if(config.submit)
	{
		artConfig.button = null;
	}

	art.dialog.open(conditionUrl,artConfig);
}

//
function selectAll(nameVal)
{
	if($("input[type='checkbox'][name^='"+nameVal+"']:not(:checked)").length > 0)
	{
		$("input[type='checkbox'][name^='"+nameVal+"']").prop('checked',true);
	}
	else
	{
		$("input[type='checkbox'][name^='"+nameVal+"']").prop('checked',false);
	}
}
/**
 * @brief 
 * @param string nameVal name
 * @param string sort    :checkbox,radio,text,textarea,select
 * @return array
 */
function getArray(nameVal,sort)
{
	//ajaxjson
	var jsonData = new Array;

	switch(sort)
	{
		case "checkbox":
		$('input[type="checkbox"][name="'+nameVal+'"]:checked').each(
			function(i)
			{
				jsonData[i] = $(this).val();
			}
		);
		break;
	}
	return jsonData;
}
window.loadding = function(message){var message = message ? message : '...';art.dialog({"id":"loadding","lock":true,"fixed":true,"drag":false}).content(message);}
window.unloadding = function(){art.dialog({"id":"loadding"}).close();}
window.tips = function(mess){art.dialog.tips(mess);}
window.alert = function(mess){art.dialog.alert(String(mess));}
window.confirm = function(mess,bnYes,bnNo)
{
	art.dialog.confirm(
		String(mess),
//		function(){typeof bnYes == "function" ? bnYes() : bnYes && (bnYes.indexOf('/') == 0 || bnYes.indexOf('http') == 0) ? window.location.href=bnYes : eval(bnYes);},
//		function(){typeof bnNo == "function" ? bnNo() : bnNo && (bnNo.indexOf('/') == 0 || bnNo.indexOf('http') == 0) ? window.location.href=bnNo : eval(bnNo);}
function(){typeof bnYes == "function" ? bnYes() : bnYes && (bnYes.indexOf('/') == 0 || bnYes.indexOf('http') == 0) ? gourl(bnYes) : eval(bnYes);},
function(){typeof bnNo == "function" ? bnNo() : bnNo && (bnNo.indexOf('/') == 0 || bnNo.indexOf('http') == 0) ? gourl(bnNo) : eval(bnNo);}
	);
}
/**
 * @brief 
 * @param object conf
	   msg :;
	   form:;
	   link:;
 */
function delModel(conf)
{
	var ok = null;            //
	var msg= '';//

	if(conf)
	{
		if(conf.form)
		{
			var ok = 'formSubmit("'+conf.form+'")';
			if(conf.link)
			{
				var ok = 'formSubmit("'+conf.form+'","'+conf.link+'")';
			}
		}
		else if(conf.link)
		{
//			var ok = 'window.location.href="'+conf.link+'"';
var ok = 'gourl("'+conf.link+'")';
		}

		if(conf.msg)
		{
			var msg = conf.msg;
		}

		if(conf.name && checkboxCheck(conf.name,"") == false)
		{
			return '';
		}
	}
	if(ok==null && document.forms.length >= 1)
		var ok = 'document.forms[0].submit();';

	if(ok!=null)
	{
		window.confirm(msg,ok);
	}
	else
	{
		alert('');
	}
}

//name
function formSubmit(formName,url)
{
	if(url)
	{
		$('form[name="'+formName+'"]').attr('action',url);
	}
	$('form[name="'+formName+'"]').submit();
}

//checkboxnamecheckbox
function checkboxCheck(boxName,errMsg)
{
	if($('input[name="'+boxName+'"]:checked').length < 1)
	{
		alert(errMsg);
		return false;
	}
	return true;
}

//
var countdown=function()
{
	var _self=this;
	this.handle={};
	this.parent={'second':'minute','minute':'hour','hour':""};
	this.add=function(id)
	{
		_self.handle.id=setInterval(function(){_self.work(id,'second');},1000);
	};
	this.work=function(id,type)
	{
		if(type=="")
		{
			return false;
		}

		var e = document.getElementById("cd_"+type+"_"+id);
		var value=parseInt(e.innerHTML);
		if( value == 0 && _self.work( id,_self.parent[type] )==false )
		{
			clearInterval(_self.handle.id);
			return false;
		}
		else
		{
			e.innerHTML = (value==0?59:(value-1));
			return true;
		}
	};
};

/**/
function event_link(url)
{
//	window.location.href = url;
gourl(url);
}

//
function lateCall(t,func)
{
	var _self = this;
	this.handle = null;
	this.func = func;
	this.t=t;

	this.execute = function()
	{
		_self.func();
		_self.stop();
	}

	this.stop=function()
	{
		clearInterval(_self.handle);
	}

	this.start=function()
	{
		_self.handle = setInterval(_self.execute,_self.t);
	}
}

//1
function numsReduce(id)
{
	let value = parseInt($("#"+id).val());
	value--;
	if(value <= 0)
	{
		value = 1;
	}
	$("#"+id).val(value);
}

//1
function numsAdd(id)
{
	let max   = parseInt($("#"+id).attr('max'));
	let value = parseInt($("#"+id).val());
	value++;
	if(value >= max)
	{
		value = max;
	}
	$("#"+id).val(value);
}

//
function numUpdate(id)
{
	let value = parseInt($("#"+id).val());
	let max = parseInt($("#"+id).attr('max'));
	if(value <= 1)
	{
		value = 1;
	}
	else if(value >= max)
	{
		value = max;
	}
	$("#"+id).val(value);
}

/**
 * @brief 
 * @param object {"submit":,"seller_id":ID}
 */
function searchOrder(config)
{
	var data         = config.data        ? config.data        : "";
	var seller_id    = config.seller_id   ? config.seller_id   : 0;
	var conditionUrl = creatUrl('/order/search/seller_id/'+seller_id+'/'+data);

	var artConfig =
	{
		"id":"searchOrder",
		"title":"",
		"okVal":"",
		"ok":function(iframeWin,topWin)
		{
			//
			if(config.submit)
			{
				config.submit(iframeWin);
				return;
			}
		}
	};
	art.dialog.open(conditionUrl,artConfig);
}

