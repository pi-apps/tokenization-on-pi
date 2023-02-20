$(function(){
	//  
	var pageInfo = $("#pageInfo"),
		pageInfoTitle = pageInfo.data('title'),
		pageInfoGoback = pageInfo.data('goback');
	if (pageInfoTitle) {
		$("#pageName").html(pageInfoTitle);
	};
	var gobackBtn = $("#goBack");
	gobackBtn.on('click',function() {
		if (pageInfoGoback) {
			gourl(pageInfoGoback);
		} else{
			window.history.back();
		};
	});
})
// 
window.gourl=function (url){
//alert(gourl+" "+url);
window.exitconfirm=false;
	window.location.href = url;
}
// url
function getUrlParam(name){
	var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
	var r = window.location.search.substr(1).match(reg);
	if (r!=null) return unescape(r[2]); return null;
}


// handle android / browser back
window.exitconfirm=true;
window.addEventListener("beforeunload", function (e) {
    if(window.exitconfirm){
//alert('pre_flight cookie');
	window.preflight_cookie();
    }
    else window.exitconfirm=true;
});


