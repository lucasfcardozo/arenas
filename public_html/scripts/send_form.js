SendForm = Class.create({ 
	form  : null,
	buto  : null,
	elem  : null,
	aw    : null,
	res   : null,
	
	initialize: function(f, b) {
		this.form = $(f);
		
		this.buto  = null;
		this.elem  = null;
		this.aw    = null;
		this.res   = null;
		
		if (b) {
			this.buto = $(b);
			this.buto.observe('click', this.send.bind(this));
		}
		this.form.el = this;
	},
	
	trataErrors : function (json) {
		var mensagem = '';
		for (iptId in json.errors) {
			$$(
				'#' + this.form.id + ' input[name="' + iptId + '"]',
				'#' + this.form.id + ' select[name="' + iptId + '"]',
				'#' + this.form.id + ' textarea[name="' + iptId + '"]',
				'#' + this.form.id + ' #' + iptId
			).each(function (s) {
				s = s.up('label');
				if (s && !s.hasClassName('error')) {
					s.addClassName('error')
				}
			});
			
			if (json.errors[iptId] !== true) {
				mensagem = (mensagem ? mensagem + '\n' : '') + json.errors[iptId];
			}
		}
		
		return mensagem;
	},
	
	showErrors : function (json) {
		var mensagem = this.trataErrors(json);
		
		var aw = new ModalAlert();
		
		if (json.redirect) {
			aw.addButton({'typeFunction': 'ok', 'type': 'button', 'value': 'ok', 'action': function () {
				document.location = json.redirect;
			}.bind(json)});
		} else {
			aw.addButton({'typeFunction': 'ok', 'type': 'button', 'value': 'ok', 'action': function () {
				this.form.enable();
				this.aw.hide();
				this.form.fire('evt:onPostError');
			}.bind({form: this.form, 'aw': aw})});
		}
		
		aw.show('Erro', (mensagem ? mensagem : 'Oops. Você não preencheu o formulário corretamente.\nVerifique os campos destacados.'), ['ok'], 'ok');
	},
	
	showSucesso : function (json) {
		if (Object.isString(json.sucesso)) {
			var aw = new ModalAlert();
			aw.addButton({'typeFunction': 'ok', 'type': 'button', 'value': 'ok', 'action': function () {
				this.form.enable();
				this.aw.hide();
				this.form.fire('evt:onPostSuccess');
				if (this.redir) {
					if ($('updating')) {
						$('updating').show();
					}
					
					document.location = this.redir;
				}
			}.bind({form: this.form, 'aw': aw, redir: (json.redirect ? json.redirect : false)})});
			aw.show('Sucesso', json.sucesso, ['ok'], 'ok');
		} else if (json.redirect) {
			document.location = json.redirect;
		} else {
			this.form.fire('evt:onPostSuccess');
			this.form.enable();
		}
	},
	
	showLoading : function () {
		if (this.buto) {
			if (!this.buto.next('span.loading')) {
				this.buto.insert({after: new Element('span').addClassName('loading')})
			} else {
				this.buto.next('span.loading').show();
			}
			
			this.buto.hide();
		}
	},
	
	hideLoading : function () {
		if (this.buto) {
			this.buto.show();
			this.buto.next('span.loading').hide();
		}
	},
	
	send : function () {
		this.showLoading();
		
		this.form.request({
			onFailure : function () {
				this.form.enable();
				
				this.hideLoading();
				
				var aw = new ModalAlert();
				aw.addButton({'typeFunction': 'ok', 'type': 'button', 'value': 'ok', 'action': function () {
					aw.hide();
					formu.focusFirstElement();
				}.bind({'aw': aw, 'formu': this.form})});
				
				aw.show('Erro', 'Houve um erro no servidor.', ['ok'], 'ok');
				
			}.bind(this),
			
			on403: function (r) {
				var alertWindow = new ModalAlert();
				alertWindow.show('Atenção', (r.responseJSON.mensagem ? r.responseJSON.mensagem : 'Para executar esta operação, você precisa estar logado.'), ['ok']);
				$('formComentario').enable();
			},
			
			onComplete: function(r) { 
				this.hideLoading();
				
				this.res = r;
				
				/*
					primeiro varre todos os marcados com erro, e os remove
				*/
				$$('#' + this.form.id + ' label.error').invoke('removeClassName', 'error');
				
				var mensagem = '';
				var title = '';
				
				var json = r.responseJSON;
				if (json.errors) {
					this.showErrors(json);
				} else {
					this.showSucesso(json);
				}
			}.bind(this)
		});
		
		this.form.disable();
	}
});

sendFlyForm = Class.create({
	form : null,
	buto : null,
	elem : null,
	aw   : null,
	sf   : null,
	
	initialize: function(f, b) {
		this.elem = $(f);
		this.form = this.elem.down('form');
		this.buto = $(b);
		if (!this.form.id) {
			this.form.id = 'formFly' + (new Date().valueOf());
		}
	},
	
	send : function () {
		this.sf = new SendForm(this.form, this.buto);
		this.sf.showMessage = this.showMessage.bind(this);
		this.form.observe('evt:onPostSuccess', this.afterComplete.bind(this));
		//this.sf.afterComplete = ;
		this.sf.send();
		return;
	},
	
	showMessage : function (title, mensagem, json) {
		if (mensagem) {
			this.elem.hide();
			
			var aw = new ModalAlert();
			if (json.redirect) {
				aw.addButton({'typeFunction': 'ok', 'type': 'button', 'value': 'ok', 'action': function () {
					document.location = json.redirect;
				}.bind({elem: this.elem, 'json': json})});
			}
			
			aw.show(title, mensagem, ['ok'], 'ok');
		}
	},
	
	afterComplete : function (sucesso) {
		this.form.enable();
		if (sucesso) {
			this.elem.hide();
		}
	}
});