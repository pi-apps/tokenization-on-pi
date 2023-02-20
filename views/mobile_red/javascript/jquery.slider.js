(function($){
	$.fn.MobileSlider = function(settings){
		var defaultSettings = {
			width: 720, //
			height: 720, //
			during: 5000, //
			speed:30, //
			ftype:1
		}
		settings = $.extend(true, {}, defaultSettings, settings);
		return this.each(function(){
			var _this = $(this), s = settings;
			var startX = 0, startY = 0; //
			var temPos; //
			var iCurr = 0; //
			var timer = null; //
			var oMover = $("ul", _this); //
			var oLi = $("li", oMover); //
			var num = oLi.length; //
			var oPosition = {}; //
			var moveWidth = s.width; //
			//
			_this.width(s.width).height(s.height).css({
				position: 'relative',
				overflow: 'hidden',
				margin:'0 auto',
				display:'block'
			}); //
			oMover.css({
				position: 'absolute',
				left: 0
			});
			oLi.css({
				float: 'left',
				display: 'inline'
			});
			$("img", oLi).attr('width','100%');

			//
			_this.append('<div class="focus"><div></div></div>');
			var oFocusContainer = $(".focus");
			for (var i = 0; i < num; i++) {
				$("div", oFocusContainer).append("<span></span>");
			}
			var oFocus = $("span", oFocusContainer);
			oFocusContainer.css({
				minHeight: $(this).find('span').height() * 2,
				position: 'absolute',
				bottom: 0,
			})

			oFocus.first().addClass("current");

			// 
			setSize();
			//
			$(window).bind('resize load', function(){
				setSize();
			});
			// 
			function setSize(){
				if (isMobile()) {
					mobileSettings();
					bindTochuEvent();
				}
				oLi.width(_this.width()).height(_this.height());//
				oMover.width(num * oLi.width());
				oFocusContainer.width(_this.width()).css({
					zIndex: 1
				});//
				_this.fadeIn(300);
			}
			//BANNER
			autoMove();
			//PC
			if (!isMobile()) {
				oFocus.hover(function(){
					iCurr = $(this).index() - 1;
					stopMove();
					doMove();
				}, function(){
					autoMove();
				})
			}
			//
			function autoMove(){
				timer = setInterval(doMove, s.during);
			}
			//
			function stopMove(){
				clearInterval(timer);
			}
			//
			function doMove(){
				iCurr = iCurr >= num - 1 ? 0 : iCurr + 1;
				doAnimate(-moveWidth * iCurr);
				oFocus.eq(iCurr).addClass("current").siblings().removeClass("current");
			}
			//
			function bindTochuEvent(){
				oMover.get(0).addEventListener('touchstart', touchStartFunc, false);
				oMover.get(0).addEventListener('touchmove', touchMoveFunc, false);
				oMover.get(0).addEventListener('touchend', touchEndFunc, false);
			}
			//
			function touchPos(e){
				var touches = e.changedTouches, l = touches.length, touch, tagX, tagY;
				for (var i = 0; i < l; i++) {
					touch = touches[i];
					tagX = touch.clientX;
					tagY = touch.clientY;
				}
				oPosition.x = tagX;
				oPosition.y = tagY;
				return oPosition;
			}
			//
			function touchStartFunc(e){
				clearInterval(timer);
				touchPos(e);
				startX = oPosition.x;
				startY = oPosition.y;
				temPos = oMover.position().left;
			}
			//
			function touchMoveFunc(e){
				touchPos(e);
				var moveX = oPosition.x - startX;
				var moveY = oPosition.y - startY;
				if (Math.abs(moveY) < Math.abs(moveX)) {
					e.preventDefault();
					oMover.css({
						left: temPos + moveX
					});
				}
			}
			//
			function touchEndFunc(e){
				touchPos(e);
				var moveX = oPosition.x - startX;
				var moveY = oPosition.y - startY;
				if (Math.abs(moveY) < Math.abs(moveX)) {
					if (moveX > 0) {
						iCurr--;
						if (iCurr >= 0) {
							var moveX = iCurr * moveWidth;
							doAnimate(-moveX, autoMove);
						}
						else {
							doAnimate(0, autoMove);
							iCurr = 0;
						}
					}
					else {
						iCurr++;
						if (iCurr < num && iCurr >= 0) {
							var moveX = iCurr * moveWidth;
							doAnimate(-moveX, autoMove);
						}
						else {
							iCurr = num - 1;
							doAnimate(-(num - 1) * moveWidth, autoMove);
						}
					}
					oFocus.eq(iCurr).addClass("current").siblings().removeClass("current");
				}
			}
			//
			function mobileSettings(){
				var _winW = $(window).width();
				if (s.ftype==1) {
					objW = _winW;
				} else{
					objW = _winW - 32;
				};
				objW = objW > 720 ? 720 : objW;
				moveWidth = objW;
				var iScale = objW / s.width;
				_this.height(s.height * iScale).width(objW);
				oMover.css({
					left: -iCurr * moveWidth
				});
			}
			//
			function doAnimate(iTarget, fn){
				oMover.stop().animate({
					left: iTarget
				}, _this.speed , function(){
					if (fn)
						fn();
				});
			}
			//
			function isMobile(){
				if (
					navigator.userAgent.match(/Android/i)
					|| navigator.userAgent.indexOf('iPhone') != -1
					|| navigator.userAgent.indexOf('iPod') != -1
					|| navigator.userAgent.indexOf('iPad') != -1
					|| navigator.userAgent.indexOf('Mobile') != -1
					) {
					return true;
				}
				else {
					return false;
				}
			}
		});
	}
})(jQuery);
