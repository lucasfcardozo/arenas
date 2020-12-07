<?php
/**
 *	FVAL PHP Framework for Web Applications\n
 *	Copyright (c) 2007-2009 FVAL Consultoria e Informática Ltda.
 *
 *	\warning Este arquivo é parte integrante do framework e não pode ser omitido
 *
 *	\version 0.9.4
 *
 *	\brief Classe de tratamento de templates
 */

require_once 'Smarty' . DIRECTORY_SEPARATOR . 'Smarty.class.php';

// Define o subdiretório das subclasses do Smarty
//define('SMARTY_DIR', $GLOBALS['SYSTEM']['LIBRARY_PATH'] . DIRECTORY_SEPARATOR . 'Smarty' . DIRECTORY_SEPARATOR);

class Template extends Kernel {
	private static $started = false;
	private static $template_obj = NULL;
	private static $template_name = NULL;
	private static $smartyTplId = NULL;
	private static $template_name_sufix = '.tpl.html';
	
	private static $template_path;
	private static $template_config_path;
	private static $template_compiled_path;
	private static $template_cache_path;
	
	public static function setBasePath($base) {
		self::$template_path = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . 'templates';
		self::$template_config_path = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . 'templates_config';
		self::$template_compiled_path = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . 'templates_c';
		self::$template_cache_path = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . 'templates_cached';
	}

	/**
	 *	\brief Inicializa a classe de template
	 */
	public static function start($tpl=NULL) {
		if (self::$started) {
			return true;
		}
		
		// Verifica o sub-dir
		if (is_dir(self::$template_path . DIRECTORY_SEPARATOR . URI::current_page())) {
			$path = URI::current_page();
		} else {
			$path = 'default';
		}

		// Inicializa a classe de template
		if (self::$template_obj === NULL) {
			self::$template_obj = new Smarty();
		}

		//self::$template_obj->use_sub_dirs = true;
		self::$template_obj->cache_lifetime = 0;
		self::$template_obj->caching = false;
		self::$template_obj->force_compile = parent::get_conf('sys_development');
		
		// Limpa qualquer variável que por ventura exista no template
		self::$template_obj->clear_all_assign();

		// Ajusta os caminhos de template
		self::set_template_path( self::$template_path );
		
		if (self::$template_compiled_path) {
			self::set_template_compiled_path( self::$template_compiled_path );
		}
		
		if (self::$template_config_path) {
			self::set_config_dif( self::$template_config_path );
		}
		
		if ($tpl) {
			self::set_template($tpl);
		}

		self::$started = true;
		
		return true;
	}

	/**
	 *	\brief Finaliza a classe de template
	 */
	public static function stop() {
		if (!self::$started) return true;

		// Limpa qualquer variável que por ventura exista no template
		self::$template_obj->clear_all_assign();

		self::$template_name = NULL;
		self::$started = false;
		self::$template_obj = NULL;

		return true;
	}

	/**
	 *	\brief Verifica o template ideal de acordo com a página
	 */
	private static function _set_template_paths() {
		// Pega o caminha relativo da página atual
		$relative_path_page = URI::relative_path_page();

		// Se o nome do template não foi informado, define como sendo a página atual
		if (self::$template_name === NULL) {
			//self::$template_name = URI::current_page();
			self::$template_name = URI::relative_path_page();
		}

		// Monta o caminho do diretório do arquivo de template
		$path = self::$template_path . (empty($relative_path_page) ? '' : DIRECTORY_SEPARATOR) . $relative_path_page;

		// Verifica se existe o diretório e dentro dele um template com o nome da página e
		// havendo, usa como caminho relativo adicionao. Se não houver, limpa o caminho relativo.
		if (is_dir($path) && file_exists($path . DIRECTORY_SEPARATOR . self::$template_name . self::$template_name_sufix)) {
			$relative_path = (empty($relative_path_page) ? '' : DIRECTORY_SEPARATOR) . $relative_path_page;
		} else {
			$relative_path = '';
		}

		// Ajusta os caminhos de template
		self::set_template_path( self::$template_path . $relative_path );
		
		if (self::$template_compiled_path) {
			self::set_template_compiled_path( self::$template_compiled_path );
		}
		
		if (self::$template_config_path) {
			self::set_config_dif( self::$template_config_path );
		}

		// Se o arquivo de template não existir, exibe erro 404
		if (!self::$template_obj->template_exists(self::$template_name . self::$template_name_sufix)) {
			Errors::display_error(404, self::$template_name . self::$template_name_sufix);
		}

		return true;
	}

	/**
	 *	\brief Verifica se o template foi inicializado
	 */
	public static function is_started() {
		return self::$started;
	}
	
	public static function started() {
		return self::is_started();
	}

	/**
	 *	\brief Retorna o objeto da classe de template
	 */
	public static function get_template_obj() {
		return self::$template_obj;
	}

	/**
	 *	\brief Define o cacheamento dos templates
	 */
	public static function caching($bool) {
		self::$template_obj->cache_dir = self::$template_cache_path;
		self::$template_obj->caching = $bool;
	}
	
	public static function setCacheLifeTime($seconds) {
		self::$template_obj->cache_lifetime = $seconds;
	}

	/**
	 *	\brief Retorna a página montada
	 */
	public static function fetch() {
		if (!self::$started) return false;

		restore_error_handler();

		self::_set_template_paths();

		return self::$template_obj->fetch(self::$template_name . self::$template_name_sufix, self::$smartyTplId);
	}

	/**
	 *	\brief Faz a saída da página montada
	 */
	public static function display() {
		if (!self::$started) {
			return false;
		}
		
		self::assign('SYSTEM', array(
			'_debug' => (parent::get_conf('sys_debug') || Session::is_set('_developer'))
		));

		restore_error_handler();
		
		self::_set_template_paths();
		
		header('Content-type: text/html; charset=' . Kernel::get_conf('sys_charset'), true, 200);
		
		self::$template_obj->display(self::$template_name . self::$template_name_sufix, self::$smartyTplId);
	}

	/**
	 *	\brief Verifica se o template está cacheado
	 */
	public static function is_cached() {
		return (self::$template_obj->is_cached(self::$template_name . self::$template_name_sufix, self::$smartyTplId));
	}

	// seta as principais variaveis, usadas em TODOS os tpls
	public static function setCommon() {
		if (Kernel::get_conf('admin')) {
			
		} else if (parent::get_conf('common_urls')) {
			if (!parent::get_conf('register_method_set_common_urls')) {
				foreach(parent::get_conf('common_urls') as $var => $value) {
					if (isset($value[2])) {
						Template::assign($var, URI::build_url($value[0], $value[1], $value[2]));
					} else if (isset($value[1])) {
						Template::assign($var, URI::build_url($value[0], $value[1]));
					} else {
						Template::assign($var, URI::build_url($value[0]));
					}
				}
			} else if (Kernel::get_conf('register_method_set_common_urls')) {
				$toCall = Kernel::get_conf('register_method_set_common_urls');
				if ($toCall['static']) {
					if (!isset($toCall['method'])) {
						throw new Exception('You need to determine which method will be executed.', 500);
					}
					
					//$toCall['class']::$toCall['method'];
				} else {
					$obj = new $toCall['class'];
					if (isset($toCall['method']) && $toCall['method']) {
						$obj->$toCall['method'];
					}
				}
			}
		}
		
		self::assign('urlSite', URI::build_url(array()));
		
		self::assign('urlJS',   URI::build_url(array('scripts'), array(), isset($_SERVER['HTTPS'])));
		
		self::assign('urlCSS',  URI::build_url(array('css'), array(), isset($_SERVER['HTTPS'])));
		self::assign('urlIMG',  URI::build_url(array('images'), array(), isset($_SERVER['HTTPS'])));
		self::assign('urlSWF',  URI::build_url(array('swf'), array(), isset($_SERVER['HTTPS'])));
		
		self::assign('siteName', Kernel::get_conf('site_name'));
	}

	/**
	 *	\brief Define o local dos arquivos de template compilados
	 */
	public static function set_template_compiled_path($path) {
		if (self::$template_obj !== NULL) {
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			self::$template_obj->compile_dir = $path;
		}
	}

	/**
	 *	\brief Define o local dos arquivos de template compilados
	 */
	public static function set_config_dif($path) {
		if (self::$template_obj !== NULL) {
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			self::$template_obj->config_dir = $path;
		}
	}

	/**
	 *	\brief Define o arquivos de template
	 */
	public static function set_template($tpl) {
		self::$template_name = ((is_array($tpl)) ? join(DIRECTORY_SEPARATOR, $tpl) : $tpl);
	}

	// seta o ID do cache
	public static function smartySetTplId($id) {
		self::$smartyTplId = $id;
	}

	/**
	 *	\brief Define o local dos arquivos de template
	 */
	public static function set_template_path($path) {
		self::$template_obj->template_dir = $path;
	}

	/**
	 *	\brief Define uma variável do template
	 */
	public static function assign($var, $value) {
		self::$template_obj->assign($var, $value);
	}

	/**
	 *	\brief Limpa uma variável do template
	 */
	public static function clear_assign($var) {
		self::$template_obj->clear_assign($var);
	}
	
	public static function clear_caching() {
		self::$template_obj->clear_cache(self::$template_name . self::$template_name_sufix, self::$smartyTplId);
	}

	/**
	 *	\brief Verifica se um arquivo de template existe
	 */
	public static function template_exists($tpl) {
		return self::$template_obj->template_exists($tpl . self::$template_name_sufix);
	}
}
?>