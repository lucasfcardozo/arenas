/*
alertWindow = new ModalAlert();
alertWindow.show('titulo', 'mensagem', ['cancel', 'ok']);
*/

/** 
 *
 *  sobrescreve a funÃ§Ã£o do prototype.
 *  foi adicionado mais 1 parametro q define em qual frame serÃ¡ buscado o elemento
 *
 *
$ = function (element, wnd) {
  if (Object.isArray(element)) {
	for (var i = 0, elements = [], length = element.length; i < length; i++)
	  elements.push($(element[i]), wnd);
	return elements;
  }
  
  if (Object.isString(element)) {
	if (Object.isUndefined(wnd)) {
		wnd = window;
	}
	element = wnd.document.getElementById(element);
  }

  return Element.extend(element);
};*/

ModalAlert = Class.create({
	button : [],
	showingBts : [],
	frame : null,
	
	initialize : function (addIn) {
		this.button = [];
		this.addButton({'typeFunction': 'ok', 'type': 'button', 'value': 'ok', 'action': this.hide});
		this.addButton({'typeFunction': 'cancel', 'type': 'button', 'value': 'cancel', 'action': this.hide});
		
		if ((addIn && $(addIn) && $(addIn).down('#mdlAlt')) || $('mdlAlt')) {
			return;
		}
		
		if (!$('BkgModal')) {
			$(document.body).insert(new Element('div', {id: 'BkgModal'}).setOpacity(.6).hide())/*.setStyle({height: '100%'})*/;
		}
		
		var alt = new Element('div', {id: 'mdlAlt'});
		
		wnd = new Element('div', {id: 'mdlAltWnd'});
		wnd.appendChild(new Element('h3', {id: 'mdlAltTitle'}).update('.:: Alerta ::.'));
		wnd.appendChild(new Element('p', {id: 'mdlAltMesnagem'}));
		wnd.appendChild(new Element('div', {id: 'mdlAltBts'}));
		
		alt.appendChild(wnd);
		
		if (addIn) {
			$(addIn).insert(alt);
		} else {
			$(document.body).insert(alt);
		}
	},
	
	setActionBtOk : function (f) {
		$('mdlAltBtOk').observe('click', f);
	},
	
	setActionBtCancel : function (f) {
		$('mdlAltBtCancel').observe('click', f);
	},
	
	addButton : function (dados) {
		switch (dados.type) {
			case 'buttonimg' :
				var ipt = new Element('input', {type: 'image'});
			break;
			case 'button' :
				var ipt = new Element('button', {type: 'button'}).update(dados.value);
			break;
			case 'img' :
				var ipt = new Element('a', {href: 'javascript:;'});
				ipt.appendChild(new Element('img', {src: dados.src, border: 0}));
			break;
			case 'link' :
				var ipt = new Element('a', {href: 'javascript:;'}).update(dados.value);
			break;
		}
		
		this.button[ dados.typeFunction ] = {
			input : ipt,
			id : 'mdlAltBt' + dados.typeFunction,
			action : dados.action
		};
	},
	
	show : function (titulo, mensagem, btsToShow, focosIn) {
		$('mdlAltTitle').update(titulo);
		$('mdlAltMesnagem').update(mensagem.replace(/\n/g, '<br />'));
		$('mdlAltBts').update('');
		$('BkgModal').show();
		$('mdlAlt').show();
		
		this.showingBts.clear();
		
		if (btsToShow) {
			btsToShow.each(function (bt) {
				try {
					this.button[ bt ].input.observe('click', this.button[ bt ].action.bind(this));
					
					$('mdlAltBts').appendChild(this.button[ bt ].input);
					
					if (focosIn == bt) {
						this.button[ bt ].input.focus();
					}
					
					this.showingBts.push( bt );
				} catch (e) {alert(e)}
			}.bind(this));
		}
		
		var wd = $('mdlAltWnd');
		wd.setStyle({marginTop: -(wd.getHeight() / 2) + 'px'});
	},
	
	hide : function () {
		for (types in this.showingBts) {
			t = this.showingBts[ types ];
			try {
				$(this.button[ t ].id).stopObserving('click', this.button[ t ].action);
			} catch(e) {}
		}
		$('BkgModal').hide();
		$('mdlAlt').hide();
	}
});
