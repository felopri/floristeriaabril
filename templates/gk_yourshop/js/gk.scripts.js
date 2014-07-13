window.addEvent('domready', function(){
	// smooth anchor scrolling
	new SmoothScroll(); 
	// style area
	if(document.id('gkStyleArea')){
		$$('#gkStyleArea a').each(function(element,index){
			element.addEvent('click',function(e){
	            e.stop();
				changeStyle(index+1);
			});
		});
	}
	// cart checker
	if($('gkCartBtn')) {
		var gkvalue = $$('#gkCart .total_products');
		if(gkvalue[0]) {
			var numb = gkvalue.get('text').toString().match(/\d{1,}/g);
			document.id('gkItems').getElement('strong').innerHTML = '';
			document.id('gkItems').getElement('strong').innerHTML = (numb != null) ? numb[0] : 0;
		} else {
			document.id('gkItems').getElement('strong').innerHTML = '';
			document.id('gkItems').getElement('strong').innerHTML = '0';
		}
		
		(function() {
			var gkvalue = $$('#gkCart .total_products');
			if(gkvalue[0]) {
				var numb = gkvalue.get('text').toString().match(/\d{1,}/g);
				document.id('gkItems').getElement('strong').innerHTML = '';
				document.id('gkItems').getElement('strong').innerHTML = (numb != null) ? numb[0] : 0;
			}
		}).periodical(4000);
	}
	// font-size switcher
	if(document.id('gkTools') && document.id('gkComponentWrap')) {
		var current_fs = 100;
		var content_fx = new Fx.Tween(document.id('gkComponentWrap'), { property: 'font-size', unit: '%', duration: 200 }).set(100);
		document.id('gkToolsInc').addEvent('click', function(e){ 
			e.stop(); 
			if(current_fs < 150) { 
				content_fx.start(current_fs + 10); 
				current_fs += 10; 
			} 
		});
		document.id('gkToolsReset').addEvent('click', function(e){ 
			e.stop(); 
			content_fx.start(100); 
			current_fs = 100; 
		});
		document.id('gkToolsDec').addEvent('click', function(e){ 
			e.stop(); 
			if(current_fs > 70) { 
				content_fx.start(current_fs - 10); 
				current_fs -= 10; 
			} 
		});
	}
	// popup cart
	var cart_over = false;
    var cart_opened = false;
	if(document.id('gkItems')){
		var cart_fx = new Fx.Tween(document.id('gkCart'), {property: 'top', duration:350}).set(-600);
		
		document.id('gkItems').addEvent('click', function() {
            cart_fx.start((cart_opened) ? -600 : 38);
            cart_opened = !cart_opened;
        });
		
		document.id('gkCart').addEvent('mouseenter',function(){cart_over = true;});
		document.id('gkCart').addEvent('mouseleave',function(){cart_over = false;});
		
		$(document.body).addEvent('click', function(){
            if(cart_opened && !cart_over) cart_fx.start(-600);
        });
	}
	if(document.id('gkProductTabs')) {
		
		document.id('gkComponent').addEvent('click', function(e){
		   
		   var evt = new Event(e).target;
		   
		     if((window.ie && evt.nodeName == 'SPAN') || (!window.ie && evt.get('tag') == 'span')) {
                if($(evt).getParent().getParent().getProperty('id') == 'gkProductTabs') {
                    $$('.gkProductTab').addClass('gkUnvisible');
                    $$('#gkProductTabs li').setProperty('class', '');
                    var num = 0;
                    $$('#gkProductTabs li').each(function(el, i){
                        if(el == evt.getParent()){ num = i; }
                    });
                    $$('.gkProductTab')[num].removeClass('gkUnvisible');
		            $$('#gkProductTabs li')[num].setProperty('class', 'gkProductTabActive');
                }
            } else if((window.ie && evt.nodeName == 'LI') || (!window.ie && evt.get('tag') == 'li')) {
                if($(evt).getParent().getProperty('id') == 'gkProductTabs') {
                    $$('.gkProductTab').addClass('gkUnvisible');
                    $$('#gkProductTabs li').setProperty('class', '');
                    var num = 0;
                    $$('#gkProductTabs li').each(function(el, i){
                        if(el == evt.getParent()){ num = i; }
                    });
                    $$('.gkProductTab')[num].removeClass('gkUnvisible');
		            $$('#gkProductTabs li')[num].setProperty('class', 'gkProductTabActive');
                }
            }
		});
	}
	// login popup
	if(document.id('gkButtonLogin') && document.id('gkLoginPopup')) {
        var popup_enabled = false;
        var popup_over = false;
        var overlay_opacity = 0.75;
        var overlay_fx = new Fx.Morph(document.id('gkLoginPopupOverlay'), {
            duration: 450,
            transition: Fx.Transitions.Expo.easeInOut
        });
        overlay_fx.set({ 'opacity': 0 });
        var popup_fx = new Fx.Morph(document.id('gkLoginPopup'), {
            duration: 450,
            transition: Fx.Transitions.Expo.easeInOut
        });
        document.id('gkLoginPopupOverlay').addEvent('click', function() {
            (function() {
                if(!popup_over) {
                    popup_enabled = false;
                    popup_fx.start({ 'top': -1000, 'opacity': [1, 0] });
                    overlay_fx.set({ 'height' : window.getSize().y });
                    overlay_fx.start({ 'opacity' : 0 });
                }
            }).delay(100);
        });
        document.id('gkLoginPopup').addEvent('click', function() {
            popup_over = true;
        });
        document.id('gkButtonLogin').addEvent('click', function(e) {
			e.stop();
			document.id('gkLoginPopup').setStyle( 'left', (window.getSize().x - 400) / 2 );
            if(popup_enabled) {
                popup_fx.start({ 'top': -1000, 'opacity': [1,0] });
                overlay_fx.set({ 'height' : window.getSize().y });
                overlay_fx.start({ 'opacity' : [overlay_opacity, 0] });
			} else {
                popup_fx.start({ 'top': 260, 'opacity': [0, 1] });
                overlay_fx.set({ 'height' : window.getSize().y });
                overlay_fx.start({ 'opacity' : [0, overlay_opacity] });
			}
			popup_enabled = !popup_enabled;
		});
		document.id('gkLoginPopup').addEvents({
            "mouseenter" : function() { popup_over = true; },
            "mouseleave" : function() { popup_over = false; }
		});
	}
});
// function to set cookie
function setCookie(c_name, value, expire) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expire);
	document.cookie=c_name+ "=" +escape(value) + ((expire==null) ? "" : ";expires=" + exdate.toUTCString());
}
// Function to change styles
function changeStyle(style){
	var file1 = $GK_TMPL_URL+'/css/style'+style+'.css';
	var file2 = $GK_TMPL_URL+'/css/typography.style'+style+'.css';
	new Asset.css(file1);
	new Asset.css(file2);
	Cookie.write('gk2_style',style, { duration:365, path: '/' });
}