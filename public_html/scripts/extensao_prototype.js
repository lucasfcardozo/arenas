Debug = {
	opened : false,
	pe     : null,
	
	open : function () {
		$('debug').setStyle({'height':'auto', 'width': (parseInt(document.viewport.getWidth()) - 5) + 'px'}).down().show().setStyle({'height': (parseInt(document.viewport.getHeight()) - 18) + 'px'}).next('.debug_box_3').hide();
		Debug.opened = true;
		
		try {
			Debug.pe.stop();
		} catch (e) {}
	},
	
	close : function () {
		$('debug').setStyle({'width': '50px', 'height':''}).down().hide().next('.debug_box_3').show();
		Debug.opened = false;
	},
	
	init : function () {
		$$('head')[0].insert(
			new Element('style', {type: 'text/css'}).update(
				'.debug_box {background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwOC8wNC8xMLtLDxEAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzVxteM2AAAADUlEQVQImWP4////GQAJyAPKSOz6nwAAAABJRU5ErkJggg==); z-index: 99999; margin:0; width:50px; height:50px; display:block; position:fixed; bottom:0; left:0; text-decoration:none; border: 2px solid #06C}' +
				'.debug_box * {color:#000; font-weight:normal; font-family:Verdana; font-size:11px; text-align:left; border:0; margin:0; padding:0}' + 
		
				'.debug_box_3       {cursor:pointer; font-weight: bold; color:#06C; text-align:center }' +
				'.debug_box_3.close {line-height:50px}' +
				'.debug_box_3.open  {background:url(data:image/gif;base64,R0lGODlhBQAbAMQAAP+mIf/aov/CZv/rzP+xO//PiP/15v/ku/+7Vf/89//Jd//TkP+qKv/hs//x3f+3TP/Mf//dqv/Fbv/u1f+0Q//47v/nxP++Xf/////Wmf+tMgAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAFABsAAAVFICaKSVlWKGqsq+O6UxwPNG3d96HrTd9HQGBgOMwYjYtkssBkQp5PhVQqqVYFWOxlu0V4vY9wmEImE85njVrNaLcBcHgIADs=); line-height:auto; height:auto}' +
				
				'.debug_box_2 .close {margin:3px}' +
				'.debug_box_2 .close a {padding:9px 2px 9px 11px; background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAK6wAACusBgosNWgAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNXG14zYAAAAVdEVYdENyZWF0aW9uIFRpbWUAMi8xNy8wOCCcqlgAAABcSURBVBiVhY/REYAwCENTr/tYJ8oomYWJdKP61R7gneQPeITQJA0AN/51QdKsJGkea8XMPja+t0GSYWBmILnr7h087KHgWCmA61yOEcCcKcPhmSzfa5JOAE8RcbyUIkZhBhiUxQAAAABJRU5ErkJggg==) no-repeat left center; color:#f00}' + 
				'.debug_box_2 .close a:hover {text-decoration:underline}' + 
				
				'.debug_box_2 .debug_info_area {overflow:auto}' +
				'.debug_box_2 .debug_info {overflow:auto}' +
				'.debug_box_2 .debug_info:hover {background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNXG14zYAAAAWdEVYdENyZWF0aW9uIFRpbWUAMDQvMjUvMTHJkGH2AAAADUlEQVQImWP4+/v2GQAJbAOgd8SdQAAAAABJRU5ErkJggg==)} ' + 
				
				'.debug_box_2 table {margin:5px 0; width:100%}' +
				'.debug_box_2 table th {font-weight: bold; color:#e8740d; font-size:14px; border:0} ' +
				'.debug_box_2 table td {padding:0 0 3px 3px; vertical-align:top}' +
				'.debug_box_2 a {color:#7250a2}' +
				
				'.debug_box_2 hr {margin: 15px 0; border:1px solid #d8dade; visibility:visible}' +
				
				'.debug_box_2 .ErrorTitle{background-color:#66C; color:#FFF; font-weight:bold; padding-left:10px}' +
				'.debug_box_2 .ErrorZebra{background:#efefef}' +
				'.debug_box_2 .ErrorLabel{font-weight:bold}'
			)
		);
		$('debug').down('.debug_box_3').observe('click', Debug.open);
		$('debug').down('.close').observe('click', Debug.close);
		
		if ($$('#debug div.debug_box_2 div.debug_info_area div.debug_info').size() > 1) {
			Debug.startPulsate();
		}
	},
	
	startPulsate : function () {
		Debug.pe = new PeriodicalExecuter(function () {
			Effect.Pulsate($('debug').down('.debug_box_3')); 
		}, 5);
	},
	
	printAjaxResults : function (titulo, txt, autoShow) {
		var w = (window.parent ? window.parent : window);
		var d = w.$$('.debug_box');
		if (d.size()) {
			with (d[0].down('.debug_box_2').down()) {
				insert({after: new Element('hr')});
				insert({after: txt});
			}
			if (!Debug.opened) {
				Debug.startPulsate();
			}
		}
	}
};

Ajax.Request.addMethods({
	respondToReadyState: function(readyState) {
		var state = Ajax.Request.Events[readyState], response = new Ajax.Response(this);
		if (state == 'Complete') {
		  try {
			this._complete = true;
			(this.options['on' + response.status]
			 || this.options['on' + (this.success() ? 'Success' : 'Failure')]
			 || Prototype.emptyFunction)(response, response.headerJSON);
		  } catch (e) {
			this.dispatchException(e);
		  }
	
		  var contentType = response.getHeader('Content-type');
		  if (this.options.evalJS == 'force'
			  || (this.options.evalJS && this.isSameOrigin() && contentType
			  && contentType.match(/^\s*(text|application)\/(x-)?(java|ecma)script(;.*)?\s*$/i)))
			this.evalResponse();
		}

		try {
		  (this.options['on' + state] || Prototype.emptyFunction)(response, response.headerJSON);
		  Ajax.Responders.dispatch('on' + state, this, response, response.headerJSON);
		} catch (e) {
		  this.dispatchException(e);
		}
		
		if (state == 'Complete') {
		  // avoid memory leak in MSIE: clean up
		  this.transport.onreadystatechange = Prototype.emptyFunction;
		}
		
		if (response.status == '500') {
			Debug.printAjaxResults('Error 500', response.responseText, Debug.AUTO_SHOW);
		} else if (state == 'Complete' && (response.headerJSON || response.responseText)) {
			if (response.responseJSON) {
				var json = response.responseJSON;
			} else if (response.responseText.isJSON()) {
				var json = response.responseText.evalJSON();
			} else {
				Debug.printAjaxResults('Result AJAX', response.responseText, false);
				return;
			}
			
			if (json.debug) {
				Debug.printAjaxResults('Result JSON', json.debug, Debug.AUTO_SHOW);
			}
		}
	}
});

document.observe('dom:loaded', Debug.init);