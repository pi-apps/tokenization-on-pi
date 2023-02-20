/**
 * @brief areaSelectchild
 * 
 * <select name="province" child="city,area" areaSelect></select>
 * <select name="city" child="area"></select>
 * <select name="area"></select>
 */
function areaSelect(topName)
{
	var _this    = this;
	this.topName = topName;
	this.child   = [];

	/**
	 * js
	 * @param name
	 * @param parent_id
	 * @param select_id
	 */
	this.createAreaSelect = function(name,parent_id,select_id)
	{
		parent_id = parent_id ? parent_id : 0;

		//artTemplate {name:,area_id:ID,data:}
		areaTemplate = '<option value=""></option>'+'<%for(var index in data){%>'+'<%var item = data[index]%>'+'<option value="<%=item.area_id%>" <%if(item.area_id == select_id){%>selected="selected"<%}%>><%=item.area_name%></option>'+'<%}%>';

		//
		$.getJSON(creatUrl("block/area_child"),{"aid":parent_id,"random":Math.random()},function(json)
		{
			$('select[name="'+name+'"]').html( template.compile(areaTemplate)({"select_id":select_id,"data":json}) );
		});
	}

	/**
	 * @brief (SELECT)onchange
	 * @param object _self 
	 */
	this.areaChangeCallback = function(_self)
	{
		var parent_id = $(_self).val();
		var childName = $(_self).attr('child');

		if(!childName)
		{
			return;
		}

		//
		var childArray = childName.split(',');
		for(var index in childArray)
		{
			$('select[name="'+childArray[index]+'"]').html('<option value=""></option>');
		}

		//js
		if(parent_id)
		{
			_this.createAreaSelect(childArray[0],parent_id);
		}
	}

	/**
	 * @brief init
	 * @param object initData 
	 */
	this.init = function(initData)
	{
		$(function()
		{
			var lastParent = "";
			if(initData)
			{
				for(var c in _this.child)
				{
					for(var i in initData)
					{
						//
						if(_this.child[c] == i && initData[i])
						{
							_this.createAreaSelect(i,lastParent,initData[i]);
							lastParent = initData[i];
						}
					}
				}
			}

			if(!initData || !lastParent)
			{
				_this.createAreaSelect(_this.child[0]);
			}
		})
	}

	//
	$(function()
	{
		var topObj = $("select[name='"+topName+"']");
		if(topObj.length > 0)
		{
			//
			var childString = topObj.attr('child');
			var childArray  = childString ? childString.split(",") : [];
			childArray.unshift(topObj.attr('name'));

			_this.child = childArray;
			if(childArray.length > 0)
			{
				for(var i in childArray)
				{
					$('select[name="'+childArray[i]+'"]').on("change",function(){_this.areaChangeCallback(this);});
				}
			}
		}
	})
}