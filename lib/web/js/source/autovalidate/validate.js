(function autoValidate()
{
	//
	addEvent(window,'load',init);

	//
	function addEvent(obj, type, fn)
	{
        if($)
        {
            $(window).on('autoValidate',function()
            {
                $(window).off();
                init();
            });
        }

		if (obj.attachEvent)
		{
			obj['e'+type+fn] = fn;
			obj[type+fn] = function(){obj['e'+type+fn]( window.event );}
			obj.attachEvent('on'+type, obj[type+fn]);
		}
		else
			obj.addEventListener(type, fn, false);
	}

	//
	function FireEvent(elem, eventName)
	{
		if (document.all)
		{
			elem.fireEvent(eventName);
		}
		else
		{
			 var evt = document.createEvent('HTMLEvents');
			 evt.initEvent('blur',true,true);
			 elem.dispatchEvent(evt);
		}
	}

	//
	function removeEvent(obj, type, fn)
	{
		if (obj.detachEvent)
		{
			obj.detachEvent('on'+type, obj[type+fn]);
			obj[type+fn] = null;
		}
		else
		{
			obj.removeEventListener(type, fn, false);
		}
	}

	//
    function init()
    {
    	var checkType = 'text,password,select-one,textarea,file';
        for(var i = 0; i < document.forms.length; i++)
        {
            var f = document.forms[i];
            var needsValidation = false;
            for(j = 0; j < f.elements.length; j++)
            {
                var e = f.elements[j];

                //
                if(checkType.indexOf(e.type) === -1)
                {
                	continue;
                }

				//pattern
                if(e.getAttribute("pattern"))
                {
					addEvent(e,'blur',validateOnChange);
                    needsValidation = true;
                }
            }

            if(needsValidation)
            {
				//,js
				if(f.onsubmit)
				{
					f.laterSubmit = f.onsubmit;
				}

				f.onsubmit = validateOnSubmit;
				f.setAttribute('novalidate','true');
            }

else {
  if(f.onsubmit) f.laterSubmit = f.onsubmit;
  f.onsubmit = __Submit;
}

        }

// Create event listener for all link clicks
document.querySelectorAll('a').forEach(link => {
  link.addEventListener('click', (e) => {
//alert(link.href);
	window.exitconfirm=false;
  });
});

    }

    //
    function validateOnChange()
    {
    	var value     = this.value;
        var textfield = this;
        var pattern   = textfield.getAttribute("pattern");
		var empty     = textfield.getAttribute("empty");

		//
		textfield.className = textfield.className.replace("invalid-text","").replace("valid-text","");

		switch(pattern)
		{
			case 'required': pattern = /\S+/i;break;
			case 'email': pattern = /^\w+([-+.]\w+)*@\w+([-.]\w+)+$/i;break;
			case 'qq':  pattern = /^[1-9][0-9]{4,}$/i;break;
			case 'id': pattern = /^\d{15}(\d{2}[0-9x])?$/i;break;
			case 'ip': pattern = /^[a-z\d:\.]{3,}$/i;break;
			case 'zip': pattern = /^\d{6}$/i;break;
			case 'mobi': pattern = /^1[3|4|5|6|7|8|9][0-9]\d{8}$/;break;
			case 'phone': pattern = /^((\d{3,4})|\d{3,4}-)?\d{3,8}(-\d+)*$/i;break;
			case 'url': pattern = /^[a-zA-z]+:\/\/(\w+(-\w+)*)(\.(\w+(-\w+)*))+(\/?\S*)?$/i;break;
			case 'date': pattern = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/i;break;
			case 'datetime': pattern = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) (?:(?:[0-1][0-9])|(?:2[0-3])):(?:[0-5][0-9]):(?:[0-5][0-9])$/i;break;
			case 'int':	pattern = /^-?\d+$/i;break;
			case 'float': pattern = /^-?\d+\.?\d*$/i;break;
			case 'percent': pattern = /^[1-9][0-9]*$/;break;
		}

		//
		if(empty != null && !value)
		{
			return;
		}

		//
        if(!value || value && value.search(pattern) === -1)
        {
        	textfield.className += " invalid-text";
        }
        //
        else
        {
        	textfield.className +=" valid-text";

			//
			if(this.type == 'password')
	        {
	        	var bind = textfield.getAttribute("bind");
		        var bind_flag = true;
		        var bind_arr = document.getElementsByName(bind);
		        var bind_arr_len = bind_arr.length;
		        for(var i=0; i<bind_arr_len; i++)
			    {
			    	if(bind_arr[i].name == bind && bind_arr[i].value != textfield.value && bind_arr[i].value != '')
			    	{
			    		bind_flag = false;
			    	}
			    }

			    //
			    if(bind_flag == false)
			    {
			    	textfield.className+= " invalid-text";
			    }
			    else
			    {
			    	//
			    	for(var i=0; i<bind_arr_len; i++)
				    {
				    	if(bind_arr[i].name == bind && bind_arr[i].value == textfield.value && bind_arr[i].value != '')
				    	{
			    			bind_arr[i].className = bind_arr[i].className.replace("invalid-text","").replace("valid-text","");
			    			bind_arr[i].className+= " valid-text";
				    	}
				    }
			    	textfield.className += " valid-text";
			    }
			}
        }
    }

    __isSubmit = false;

function __Submit(that){	//
	that = that || this;
        if(__isSubmit == false)
        {
		//onsubmit
		if(that.laterSubmit && that.laterSubmit != that.onsubmit)
		{
			if(that.laterSubmit()){
			  __isSubmit = true;
			  setTimeout(function(){__isSubmit = false;},4000);
window.exitconfirm=false;
			  return true;
			}
			else return false;
		}

        	__isSubmit = true;
        	setTimeout(function(){__isSubmit = false;},4000);
window.exitconfirm=false;
        	return true;
        }
        return false;
}

    //
    function validateOnSubmit()
    {
    	var checkType = 'text,password,select-one,textarea,file';
        var invalid   = false;
        for(var i = 0; i < this.elements.length; i++)
        {
            var e = this.elements[i];

			//
            if(checkType.indexOf(e.type) !== -1 && e.getAttribute("pattern") && e.style.display!='none' && e.offsetWidth > 0)
            {
            	//
				addEvent(e,'blur',validateOnChange);

				//
				if(e.className.indexOf(" invalid-text")!==-1)
				{
					invalid = true;
					if(e.offsetHeight > 0 || e.client > 0)
					{
						e.focus();
					}
					break;
				}
				else
				{
					FireEvent(e,'onblur');
					if (e.className.indexOf(" invalid-text")!==-1)
					{
						invalid = true;
						if(e.offsetHeight > 0 || e.client > 0)
						{
							e.focus();
						}
						break;
					}
				}
            }
        }
        var callback = this.getAttribute('callback');
        var result = true;
        if(callback !=null) {result = eval(callback);}
        result = !(result==undefined?true:result);
        if (invalid || result)
        {
            return false;
        }

return __Submit(this);

    }
})();
