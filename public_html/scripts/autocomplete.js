AutoComplete = Class.create({
	iptText : '',
	iptName : '',
	urlSearch : '',
	objItemId : '',
	selectedClassName  : 'selecionado',
	pe : null,
	filtro : null,
	pe_inicia_get_list : null,
	multiple : null,
	
	// ----
	
	itens : [],
	indice : 0,
	objAjax : null,
	
	initialize : function (obj) {
		this.iptName = obj.name;
		this.objItemId = 'suggest_' + obj.name;
		this.iptText = new Element('input', {type: 'text', id: obj.name, name: (!obj.multiple && obj.value ? obj.name + '['+obj.value+']' : '')}).addClassName('toAutoComplete');
		
		if (obj.text) {
			this.iptText.value = obj.text;
			this.iptText.setStyle({backgroundColor: '#88CB7E'});
		}
		
		if (obj.style) {
			this.iptText.setStyle(obj.style);
		}
		
		this.listResult = new Element('div').setStyle({position: 'absolute'}).addClassName('listResultAutoComplete').hide();
		this.imgLoading = new Element('span').addClassName('loadingAutoComplete').hide();
		
		if (obj.textoDefault) {
			this.textoDefault = obj.textoDefault;
		} else {
			this.textoDefault = 'Inicie sua pesquiza aqui';
		}
		
		if (obj.filtro) {
			this.filtro = obj.filtro;
		}
		
		if (obj.clear) {
			$(obj.to).update('');
		}
		
		if (obj.multiple) {
			this.multiple = obj.multiple;
		}
		
		if (obj.afterAddNew) {
			this.afterAddNew = obj.afterAddNew.bind(this);
		}
		
		/*this.iptHidden = new Element('input', {type: 'hidden', name: obj.name, value: obj.value});*/
		
		this.urlSearch = obj.urlSearch;
		
		var conteudo = new Element('div').setStyle({float: 'left'});
		var div = new Element('div').setStyle({'verticalAlign': 'top'}).insert(this.iptText).insert(this.imgLoading);
		conteudo.insert(div);
		conteudo.insert(this.listResult);
		
		if (obj.to) {
			obj.to.insert(conteudo);
		}
		
		this.listResult.setStyle({width: this.iptText.getWidth() + 'px'});
		
		this.hide();
		
		this.iptText.observe('keyup', this.keyboardEvent.bind(this));
		this.iptText.observe('blur', this.blur.bind(this));
		this.iptText.observe('focus', this.focus.bind(this));
	},
	
	keyboardEvent : function (e) {
		if (e) {
			tecla = e.keyCode; //FF
		} else {
			tecla = event.keyCode; // IE
		}
		
		// qualquer tecla q prescionar, faz com q não inicie o request
		if (this.pe_inicia_get_list != null)  {
			this.pe_inicia_get_list.stop();
		}
		
		// se já tiver um request, aborta.
		this.abort();
		
		// Verifica se é seta para cima ou para baixo
		
		if ((tecla == 38) || (tecla == 40)) {
			this.selecListItem();
		} else if (tecla == 13) { // enter
			this.enter();
		} else {
			this.iptText.setStyle({backgroundColor: '#fff'});
			this.pe_inicia_get_list = new PeriodicalExecuter(function (pe) {
				pe.stop();
				this.pe_inicia_get_list = null;
				this.getList();
			}.bind(this), .5);
		}
	},
	
	selecListItem : function(e) {
		// Verifica se há algum item na lista de modelos
		if (this.itens.length) {
			this.listResult.show();
			
			// Define o movimento
			var movimento = 0;
			if (tecla == 38) {
				movimento = -1;
			} else if (tecla == 40) {
				movimento = 1;
			}

			// Se houve movimento v�lido
			if (movimento != 0) {
				var i = this.indice;
				if (i != 'primeiro') {
					this.removeOldSelected();
					// Define o novo �dice
					i = i + movimento;
					if (i < 0) {
						i = 0;
					} else if (i >= this.itens.length) {
						i = this.itens.length - 1;
					}
				} else {
					i = 0;
				}
				
				this.setNewSelected(i);
			}
		}
	},
	
	setNewSelected : function (i) {
		this.indice = i;
		// Define a classe do novo item selecionado
		$(this.objItemId + this.indice).addClassName( this.selectedClassName );
		
		if (!this.multiple) {
			this.iptText.name = this.iptName + '[' + this.itens[ this.indice ].id + ']';
		}
		
		this.iptText.value = this.itens[ this.indice ].text;
		this.iptText.setStyle({backgroundColor: '#88CB7E'});
	},
	
	removeOldSelected : function () {
		// Limpa a classe do item atual
		o = $(this.objItemId + this.indice);
		if (!o) {
			return;
		}
		$(this.objItemId + this.indice).removeClassName( this.selectedClassName );
	},
	
	aHover : function (event) {
		this.removeOldSelected();
		this.setNewSelected( parseInt(Event.findElement(event, 'a').id.replace(this.objItemId, '')) );
	},
	
	aClick : function (event) {
		this.indice = parseInt(Event.findElement(event, 'a').id.replace(this.objItemId, ''));
		this.addNew();
	},
	
	checkExists : function (id) {
		if (isNaN(id)) {
			id = id.replace(/[^0-9]+/gi, '');
		}
		
		li = $('li' + id);
		if (li) {
			return li;
		}
		
		return -1;
	},
	
	addNew : function () {
		if (!this.itens.length) {
			this.listResult.hide();
			return;
		}
		
		//colab = this.itens[ this.indice ];
		
		try {
			if (this.indice && this.checkExists(this.indice) != -1) {
				return;
			}
		} catch(e) { return; }
		
		if (this.multiple) {
			var tr = new Element('tr');
			
			var td = new Element('td', {id: 'td' + this.itens[this.indice].id}).update(this.itens[ this.indice ].text);
			td.insert(new Element('input', {'type': 'hidden', name: this.iptName + '[' + this.itens[this.indice].id + ']', id: this.iptName + '[' + this.itens[this.indice].id + ']', value: this.itens[ this.indice ].text}));
			
			tr.insert(td);
			
			var td = new Element('td', {align: 'right'});
			var ex = new Element('input', {'type': 'button', value: 'excluir'});
			ex.observe('click', this.excluir.bind(this));
			td.insert(ex);
			
			tr.insert(td);
			
			this.multiple.insert(tr);
			
			this.iptText.focus();
			this.iptText.value = '';
		} else {
			this.iptText.name = this.iptName + '[' + this.itens[this.indice].id + ']';
			this.iptText.value = this.itens[ this.indice ].text;
		}
		
		this.afterAddNew(this.itens[this.indice]);
		
		this.listResult.hide();
	},
	
	afterAddNew : function () {},
	
	excluir : function (event) {
		Event.element(event).up('tr').remove();
	},
	
	abort : function () {
		this.imgLoading.hide();
		if (this.objAjax != null) {
			this.objAjax.abort();
		}
	},
	
	getList : function () {
		// Se tem algo no campo, faz a busca
		if (this.iptText.value.strip().length >= 2) {
			this.abort();
			
			this.imgLoading.show();
			
			var param = {'text': this.iptText.value};
			if (this.filtro) {
				for (var k in this.filtro) {
					param[k] = this.filtro[k];
				}
			}
			
			this.objAjax = new Ajax.Request(this.urlSearch, {
				method: 'post',
				parameters: param,
				onSuccess: this.reciveNewList.bind(this)
			});
		} else {
			// Esconde o popup com a lista
			//this.listResult.hide();
			
			// Limpa a lista de itens
			this.itens = new Array();
			this.listResult.hide();
			this.indice = 'primeiro';
		}
	},
	
	lower_and_remove_accented_chars : function (txt) {
		txt = txt.toLowerCase()
		txt = txt.replace(/[áàâãåäªÁÀÂÄÃª]/, 'a');
		txt = txt.replace(/[éèêëÉÈÊË]/, 'e');
		txt = txt.replace(/[íìîïÍÌÎÏ]/, 'i');
		txt = txt.replace(/[óòôõöºÓÒÔÕÖº]/, 'o');
		txt = txt.replace(/[úùûüÚÙÛÜµ]/, 'u');
		txt = txt.replace(/[ñÑ]/, 'n');
		txt = txt.replace(/[çÇ]/, 'c');
		txt = txt.replace(/[ÿ¥]/, 'y');
		txt = txt.replace(/[¹]/, '1');
		txt = txt.replace(/[²]/, '2');
		txt = txt.replace(/[³]/, '3');
		txt = txt.replace(/[Ææ]/, 'ae');
		txt = txt.replace(/[Øø]/, '0');
		return txt.replace(/[†°¢£§•¶ß®©™´¨≠±≤≥∂∑∏π∫Ω]/, '');
	},
	
	reciveNewList : function (response) {
		if (response.responseJSON.dados) {
			var lista = '';
			this.listResult.update('');
			// Varre a lista, montando o conteúdo do popup e a lista
			this.itens = new Array();
			
			this.listResult.hide();
			
			response.responseJSON.dados.each(function (item, index) {
				var nome_original = item.text;
				var nome = this.lower_and_remove_accented_chars(item.text);
				
				var reg = new RegExp('(' + this.lower_and_remove_accented_chars(this.iptText.value) + ')', 'gi');
				regResult = reg.exec(nome);
				
				try {
					nome = nome.replace(reg, '<strong>' + regResult[0] + '</strong>');
				} catch (e) {}
				
				var pInicial = nome.indexOf('<strong>');
				var pFinal   = nome.indexOf('</strong>') - 8;
				nome = nome_original.substring(0, pInicial) + '<strong>' + nome_original.substring(pInicial, pFinal) + '</strong>' + nome_original.substr(pFinal);
				
				a = new Element('a', {href: 'javascript:;', id: this.objItemId + this.itens.length}).update(nome);
				a.observe('mousemove', this.aHover.bind(this));
				a.observe('click', this.aClick.bind(this));
				
				this.listResult.insert(a);
				this.itens.push(item);
			}, this);
			
			this.imgLoading.hide();
			
			// Se há uma lista de modelos
			if (this.itens.length) {
				this.indice = 'primeiro';
				this.listResult.show();
			}
		}
	},
	
	hide : function () {
		this.reset();
		
		this.listResult.hide();
		if (this.pe != null) {
			this.pe.stop();
			this.pe = null;
		}
	},
	
	enter : function () {
		this.hide();
		if (this.iptText.value != this.textoDefault) {
			try {
				this.indice = $(this.objItemId + this.indice).id.replace(this.objItemId, '');
				this.addNew();
			} catch (e) {}
		}
	},
	
	reset : function (empty) {
		if (empty) {
			this.iptText.value = '';
		}
		
		if (this.iptText.value.strip() == '') {
			this.iptText.value = this.textoDefault;
			this.iptText.name = this.iptName;
			this.iptText.setStyle({fontStyle: 'italic', color: '#666', backgroundColor: '#fff'});
		}
	},
	
	focus : function () {
		if (this.iptText.value == this.textoDefault) {
			this.iptText.value = '';
			this.iptText.setStyle({fontStyle: 'normal', color: '#000'});
		}
	},
	
	blur : function () {
		this.pe = new PeriodicalExecuter(this.hide.bind(this), .1);
	}
});