var objReg = new RegExp("1.2");
var mtMatch = objReg.exec(MooTools.version);
var BTLAdmin = {
	init: function() {
		BTLAdmin.clean();
		BTLAdmin.setSlider();
	},
	clean: function(){
		var empties = $$('table.paramlist tr');
		if(mtMatch == null)
		{				
			empties.each(function(empty) {
				var children = empty.getChildren();
				if(empty.className.indexOf('btl-about') == -1)
				{
					if (children.length < 2) empty.remove();
					else if(!children[1].innerHTML.length) empty.remove();
				}
			});				
		}
		else
		{
			empties.each(function(empty) {
				var children = empty.getChildren();
				if(empty.className.indexOf('btl-about') == -1)
				{
					if (children.length < 2) empty.destroy();
					else if(!children[1].innerHTML.length) empty.destroy();
				}
			});
		}
	},
	setSlider:function(){
		var status = {'true': 'open', 'false': 'close'};
		var settings = {selected: null};
		var HashCookie = new Hash.Cookie('HashCookieIndex', {duration: 24*60*60});
		if(HashCookie.get('selected') != null){
			var strCookieParse = HashCookie.get('selected').substr(0, HashCookie.get('selected').length - 1);	
		}else{
			HashCookie.extend(settings);
		}			
		var collapsibles = new Array();
		var headings = $$('#content-pane div h3');
		var index = 0;
		$$('.btl-panel').each(function(item, i){	
			var collapsible = new Fx.Slide( item.getElement('.btl-pane-slider'), { 
				duration: 300,
				transition: Fx.Transitions.linear,
				resetHeight: true
			});
			collapsibles[i] = collapsible;			
			item.getElement('.title').addEvent( 'click', function(){
				collapsible.toggle();
				if (collapsible.open==true) collapsible.wrapper.setStyle('height', '');
				return false;
			});			
			
			if(HashCookie.get('selected') != null && HashCookie.get('selected') !=',,'){
				if(strCookieParse.indexOf(i) == -1){
					collapsible.hide();
				}else{
					collapsible.show();
					if(mtMatch == null)
					{
						//var h3 = $E('h3', item);
						var h3 = item.getElement('h3', item);
					}
					else
					{	
						var h3 = item.getElement('h3', item);
					}
					h3.className = "title btl-pane-toggler-down";				
				}				
			}else{
				collapsible.hide();
			}
			collapsible.addEvent('onStart', function() {
				if(mtMatch == null)
				{
					//var h3 = $E('h3', item);
					var h3 = item.getElement('h3', item);
				}
				else
				{	
					var h3 = item.getElement('h3', item);
				}
				if(h3){
					if(h3.className == "title btl-pane-toggler"){
						h3.className = "title btl-pane-toggler-down";
					}else{						
						h3.className = "title btl-pane-toggler";
					}
				}		
			});			
			collapsible.addEvent('onComplete', function() {
				var strCookie = HashCookie.get('selected');
				if(strCookie != null){
					if(strCookie.indexOf(i) == -1){
						strCookie += i+",";
						settings['selected'] = strCookie;
						HashCookie.extend(settings);
						
					}else{	
						str = strCookie.replace(i+",", ",");	
						settings['selected'] = str;
						HashCookie.extend(settings);								
					}
				}else{
					settings['selected'] = i+",";
					HashCookie.extend(settings);	
				}	
			});			
			// set auto height on page load
			if (collapsible.open==true) collapsible.wrapper.setStyle('height', 'auto');
		});
		
		$('collapse-all').addEvent('click', function(){
			$$('.btl-panel').each(function(item, i){	
				collapsibles[i].hide();
				item.getElement( '.title' ).className = "title btl-pane-toggler";
			});
			settings['selected'] = null;
			HashCookie.extend(settings);
			return false;				
		});
		$('expand-all').addEvent('click', function(){
			var strCookie = null;
			$$( '.btl-panel' ).each(function(item, i){
				strCookie += i+",";
				collapsibles[i].show();
				item.getElement( '.title' ).className = "title btl-pane-toggler-down";
				settings['selected'] = strCookie;
				if (collapsibles[i].open==true) collapsibles[i].wrapper.setStyle('height', '');
			});
			HashCookie.extend(settings);	
			return false;			
		});
	}
};
window.addEvent('domready', BTLAdmin.init);