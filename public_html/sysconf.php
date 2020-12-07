<?php
$conf = array(
	/**
	 *  _default_, seta vars para todos os dominios. Esta "key" não é obrigatória.
	 *  Ex: array('class' => 'Urls', 'method' => 'setCommon', 'static' => true) // executa: Urls::setCommon();
	 *      array('class' => 'Urls', 'method' => 'setCommon', 'static' => false) // executa: (new Urls())->setCommon();
	 *      array('class' => 'Urls', 'static' => false) // executa: new Urls();
	 */
	'_default_' => array(
		/**
		 * Para usar uma classe para fazer algum tipo de tratamento nas urls, expecifique conforme o exemplo: 
		 * 'register_method_set_common_urls' => array('Urls', 'setCommon'),
		 */
		'common_urls' => array(
		),
		'sys_timezone' => 'America/Sao_Paulo',
		'sys_charset' => 'UTF-8',
		
		/**
		 * informa qual parametro deverá ser passado para ligar o modo debug em servidores q não são de desenvolvimento
		 * deveserá ser usado da seguinte forma:
		 * www.meusite.com.br/?{$developer_user}={$developer_pass}
		 *
		 * para desligar o debug use:
		 * www.meusite.com.br/?{$developer_user}=off
		 */
		'developer_user' => 'developer',
		'developer_pass' => 'EenS-[WR',
		
		/**
		 * Habilita o debug de SQLs exibindo TODOS os SQLs executados na página.
		 * Para ligar este modo, primeiro deve-se habilitar o modo desenvolvedor usando o #developer_user#
		 * ex.: www.meusite.com.br/?{$developer_user}={$developer_pass}&{$dba_user}
		 *
		 * para desligar:
		 * www.meusite.com.br/?{$dba_user}=off
		 */
		'dba_user' => 'dba', 
	),
	'localhost' => array(
		'site_name' => 'Arena Project',
		
		'root_path' => realpath(dirname(__FILE__)),
		
		'sys_maintenance' => false,
		'sys_development' => true,
		'sys_debug' => true,
		'sys_rewrite_url' => true,
		'sys_path' => '#root_path#' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'system',
		'sys_dominio' => 'localhost',
		'sys_uri' => '/arena',
		
		'db' => array(
			'default' => array(
				'type' 			=> 'sqlite',// [mysql, pgsql, sqlite]
				'file' 			=> 'D:' . DIRECTORY_SEPARATOR . 'Desenvolvimento' . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'arena' . DIRECTORY_SEPARATOR . 'banco.db', // somente sqlite
				'charset'       => 'utf8',
				'persistent'    => false
			)
		),
		
		'dynamic_image_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'images',
		
		'mail_method' => 'default',
		'mail_host' => '',
		'mail_port' => '25',
		'mail_ssl' => '0',
		'mail_starttls' => '0',
		'mail_direct_delivery' => '0',
		'mail_exclude_address' => '',
		'mail_user' => '',
		'mail_pass' => '',
		
		'mail_contato' => '',
		'mail_from' => '',
		
		'mail_realm' => '',
		'mail_workstation' => '',
		'mail_auth_host' => '',
		'mail_debug' => 0,
		'mail_html_debug' => 0,
		'mail_errors_go_to' => true,
	)
);
$conf['artelumos.com.br'] = array_merge($conf['localhost'], array(
	'sys_dominio' => 'www.artelumos.com.br',
	'sys_path' => '#root_path#' . DIRECTORY_SEPARATOR . 'system',
	'sys_debug' => false,
	'db' => array(
		'default' => array(
			'type' 			=> 'sqlite',// [mysql, pgsql, sqlite]
			'file' 			=> '/home/artelu/public_html/arena/banco/banco.db', // somente sqlite
			'charset'       => 'utf8',
			'persistent'    => false
		)
	),
	'mail_errors_go_to' => 'lucas.cardozo@live.com',
));
?>