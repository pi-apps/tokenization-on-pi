window.loadding = function(message){var message = message ? message : 'Please wait...';_loadWin = layer.open({"type": 2,"content": message,"shadeClose": false});}
window.unloadding = function(){layer.close(_loadWin);}
window.tips = function(mess){layer.open({"content":mess,"time":2.5,"skin":"msg"});}
window.alert = function(mess){layer.open({"content":mess,"btn":"OK"});}
window.confirm = function(mess,bnYes,bnNo)
{
	layer.open({
		"content":mess,
		"btn":["OK","Cancel"],
		"yes":function(){typeof bnYes == "function" ? bnYes() : bnYes && (bnYes.indexOf('/') == 0 || bnYes.indexOf('http') == 0) ? window.location.href=bnYes : eval(bnYes);},
		"no":function(){typeof bnNo == "function" ? bnNo() : bnNo && (bnNo.indexOf('/') == 0 || bnNo.indexOf('http') == 0) ? window.location.href=bnNo : eval(bnNo);},
	});
}