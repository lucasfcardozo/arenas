placeholder = function (obj) {
	if (Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf('MSIE')+5)) == 7) {
		$(obj).previous('span').observe('click', function (e) {
			Event.element(e).hide().next('input').focus();
		});
	}
	
	$(obj).observe('focus', function (e) {
		Event.findElement(e, 'input').up('label').down('span').hide();
	});
	
	$(obj).observe('blur', function (e) {
		var ipt = Event.findElement(e, 'input');
		if (!ipt.value.strip()) {
			ipt.up('label').down('span').show();
		}
	});
	
	if ($(obj).value.strip()) {
		$(obj).up('label').down('span').hide();
	}
};

reSetAutoBusca = function (tipoBusca) {
	new AutoComplete({
		to : $('tdAutoNomeProduto'),
		style : {width: '220px'},
		filtro : {tipo: tipoBusca},
		name : 'ihdNomeTime',
		urlSearch : URL_SEARCH_TIMES,
		textoDefault : 'Digite o nome do time a ser procurado.',
		clear : true,
		afterAddNew : function (p) {
			this.reset(true);
			
			new Ajax.Request(URL_VER_TIME, {
				method: 'post',
				parameters: {id: p.id},
				onSuccess: function (r) {
					$('showTime').update(r.responseJSON.html);
					
					$$('a.remover').invoke('observe', 'click', function (e) {Event.element(e).up('tr').remove()});
					
					new SendForm('fEdit', $('fEdit').down('button.submit'));
					$('fEdit').observe('evt:onPostSuccess', function () {
						$('showTime').update('');
					});
					
					$('fEdit').down('button').observe('click', function (e) {
						var bt = Event.element(e);
						var tr = new Element('tr').update($('tpl').innerHTML);
						tr.down('a.remover').observe('click', function (e) {Event.element(e).up('tr').remove()});
						bt.up('tr').insert({before: tr});
					});
				}
			})
		}
	});
};

document.observe('dom:loaded', function () {
	$$('.placeholder input').each(placeholder);
	
	$(document.body).insert(new Element('div', {id: 'BkgModal'}).setOpacity(.6).hide().setStyle({
		position: 'fixed', top: '0px', left: '0px', zIndex: 99, width: '100%', minHeight: '100%',
		height: 'auto', backgroundColor: '#000'
	}));
	
	$$('input[name="irdTipoBusca"]').invoke('observe', 'click', function (e) {
		reSetAutoBusca(Event.element(e).value);
	});
	
	new SendForm('fCad', $('fCad').down('button'));
	$('fCad').observe('evt:onPostSuccess', function () {
		$('fCad').reset();
		$('itxNome').focus();
		$('showTime').update('');
	});
	
	reSetAutoBusca('');
});