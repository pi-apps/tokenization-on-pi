function Form(form)
{
	/**
	 * @brief 
	 * @param obj Object key:name; value:
	 */
	this.init = function(obj)
	{
		for(var item in obj)
		{
			this.setValue(item,obj[item]);
		}
	}

	/**
	 * @brief 
	 * @return Object key:name; value:
	 */
	this.getItems = function()
	{
		var obj = new Object();
		var elements = (form == undefined) ? document.forms[0].elements : document.forms[form].elements;
		var len = elements.length;
		for(var i=0;i<len;i++)
		{
			if(obj[elements[i].name] == undefined) obj[elements[i].name]=this.getValue(elements[i].name);
		}
		return obj;
	}

	/**
	 * @brief 
	 * @return String 
	 */
	this.formatRequest = function()
	{
		var elements = this.getItems();
		var tem='';
		for(i in elements)
		{
			if(i!='')tem+='&'+i+'='+elements[i];
		}
		return tem.substr(1);
	}

	/**
	 * @brief 
	 * @param String name name
	 * @param String value value
	 * @return String
	 */
	this.setValue=function(name,value)
	{
        var e = (form == undefined) ? document.forms[0].elements[name] : document.forms[form].elements[name];
		if(e == undefined)
		{
			if(name.indexOf("[") == -1)
			{
				this.setValue(name+"[]",value);
			}
			return;
		}

		switch(e.type)
		{
			case 'date':
			case 'datetime':
			case 'email':
			case 'number':
			case 'tel':
			case 'url':
			case 'text':
			case 'hidden':
			case 'textarea':
			{
				if(value)
				{
					value = String(value).replace(/&gt;/g,">").replace(/&amp;/g,"&").replace(/&lt;/g,"<");
				}
				e.value=value;
				break;
			}
			case 'radio':
			case 'checkbox':
			{
				var len = e.length;
				if(len > 1)
				{
					var _value = (';'+value+';');
					for(var i = 0; i < len; i++)
					{
						if(e[i]!=undefined)
						{
							if(value == e[i].value || _value.indexOf(';'+e[i].value+';') != -1)
							{
								e[i].checked = true;
							}
							else
							{
								e[i].checked = false;
							}
						}
					}
				}
				else
				{
					if(e.value == value)
					{
						e.checked = true;
					}
					else
					{
						e.checked = false;
					}
				}
				break;
			}
			case 'select-one': this.setSelected(e,value);break;
			case 'select-multiple':
			{
				var len=e.length;
				if (len>0)
				{
					var _value = (';'+value+';');
					for(var j=0;j<len;j++)
					{
						if(e[j]!=undefined)
						{
							if(value==e[j].value || _value.indexOf(";"+e[j].value+";")!=-1 || value.indexOf(";"+e[j].innerHTML+";")!=-1){e[j].selected=true;}
						}
					}
				}
				break;
			}
			default:
			{
				var len=e.length;
				if (len>0)
				{
					value = String(value).replace(",",";");
					var _value = (';'+value+';');

					for(var j=0;j<len;j++)
					{
						if(e[j]!=undefined)
						{
							if(value==e[j].value || _value.indexOf(";"+e[j].value+";")!=-1)
							{
								e[j].checked=true;
							}
						}
					}
				}
				break;
			}
		}
	}

	/**
	 * @brief 
	 * @param String name name
	 * @return String 
	 */
	this.getValue = function(name)
	{
        var e = (form == undefined) ? document.forms[0].elements[name] : document.forms[form].elements[name];
		if(e == undefined)
		{
			if(name.indexOf("[") == -1)
			{
				return this.getValue(name+"[]");
			}
			return null;
		}

		switch(e.type)
		{
			case 'date':
			case 'datetime':
			case 'email':
			case 'number':
			case 'tel':
			case 'url':
			case 'text':
			case 'hidden':
			case 'textarea':
			{
				return e.value;
				break;
			}
			case 'radio':
			case 'checkbox':
			{
				if(e.checked)
				{
					return e.value;
				}
				break;
			}
			case undefined:
			{
				var len=e.length;
				var tmp = '';
				if (len>0)
				{
					for(var j=0;j<len;j++)
					{
						if(e[j]!=undefined)
						{
							if(e[j].checked)
							{
								if(e[j].value!='') tmp += e[j].value+';';
								else tmp += e[j].innerText+';';
							}
						}
					}
				}
				if(tmp.length>0) tmp = tmp.substring(0,(tmp.length-1));
				if(tmp!='')return tmp;
				else return null;
				break;
			}
			case 'select-one': return e.value;break;
			case 'select-multiple':
			{
				var len=e.length;
				if (len>0)
				{
					var tmp = '';
					for(var j=0;j<len;j++)
					{

						if(e[j]!=undefined)
						{
							if(e[j].checked)
							{
								if(e[j].value!='') tem += e[j].value+';';
								else tem += e[j].innerText+';';
							}
						}
					}
				}
				if(tmp.length>0) tmp = tmp.substring(0,(tmp.length-1));
				if(tmp!='')return tmp;
				else return null;
				break;
			}
		}
	}

	/**
	 * @brief select
	 */
	this.setSelected = function(obj,value)
	{
		objSelect=obj;
	    for(var i=0;i<objSelect.options.length;i++)
	    {
	        if(objSelect.options[i].value == value || objSelect.options[i].text == value)
	        {
	            objSelect.options[i].selected = true;
	            break;
	        }
	     }
	}
}
