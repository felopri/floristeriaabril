window.addEvent('load', function() {
	var base = document.id('gkWrap1');
	var main = document.id('gkDropMain');
	var sub = document.id('gkDropSub');
	
	if(main) {
		var submenus = base.getElements('#gkDropSub > ul');
		var currentsub = null;
		
		submenus.each(function(el, i) {
			if(el.hasClass('active')) currentsub = submenus[i];
		});
		
		main.getElements('li').each(function(el, i) {
			el.addEvent('mouseenter', function() {
				if(currentsub) {
					currentsub.setStyle('left', '-999em'); 
				}
				
				currentsub = submenus[i];
				currentsub.setStyle('left', 'auto');
			});
		});
	}
});  