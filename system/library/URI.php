<?php
class URI extends Kernel {
	/// String da URI
	private static $uri_string = '';
	/// Array dos segmentos da URI
	private static $segments = array();
	/// Array da relação dos parâmetros recebidos por GET
	private static $get_params = array();
	/// Índice do segmento que determina a página atual
	private static $segment_page = 0;
	
	/**
	 *	\brief Lê a URLs (em modo re-write) e transforma em variáveis $_GET
	 *
	 *	\note Este método não retorna valor
	 */
	public static function parse_uri() {
		// $_GET['_rw_'] é definida no .htaccess e contém a url em modo ReWrite
		$UriString = (!empty($_GET['_rw_']) ? $_GET['_rw_'] : '');
		unset($_GET['_rw_']);

		// [pt-br] Processa a URI
		$Segments = array();
		
		//foreach(explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $UriString)) as $val) {
		preg_match('/^([A-Za-z0-9-\/]+)*[&]?/', $UriString, $UriString);
		
		if (isset($UriString[1])) {
			$UriString = $UriString[1];
			
			foreach(explode('/', $UriString) as $val) {
				$val = trim($val);
	
				if ($val != '') {
					self::$segments[] = $val;
				}
			}
		}
		
		if (empty(self::$segments)) {
			self::$segments[] = 'index';
		}

		// [pt-br] Guarda os parâmetros passados por GET na URL
		foreach ($_GET as $key => $value) {
			self::$get_params[ $key ] = $value;
			unset($_GET[ $key ]);
		}
	}

	/**
	 *	\brief Retorna a página atual
	 *
	 *	\return O segmento que representa a página atual
	 */
	public static function current_page() {
		return self::get_segment(self::$segment_page, false);
	}

	/**
	 *	\brief Retorna o caminho relativo da página atual
	 *
	 *	\return Uma string contendo o caminho relativo à página atual
	 */
	public static function relative_path_page() {
		/*$path = '';
		for ($i = 0; $i < self::$segment_page; $i++) {
			$path .= (empty($path) ? '' : DIRECTORY_SEPARATOR) . self::get_segment($i, false);
		}*/
		return implode('/', self::$segments);
	}

	/**
	 *	\brief Define o segmento relativo à página atual
	 *
	 *	@param[in] $segment_num número relativo ao segmento da URI
	 *	\return \c trus se definiu o segmento relativo à página atual e \c false em caso contrário
	 */
	public static function set_current_page($segment_num) {
		if (self::get_segment($segment_num, false)) {
			self::$segment_page = $segment_num;
			return true;
		}

		return false;
	}

	/**
	 *	\brief Retorna o segmento da URI selecionado
	 *
	 *	@param[in] $segment_num O número do segmento desejado
	 *	@param[in] $relative_to_page Flag (true/false) que determina se o segmento desejado é 
	 *		relativo ao segmento que determina a página atual. Default = true
	 *	\return o valor do segmento ou \c false caso o segmento não exista
	 */
	public static function get_segment($segment_num, $relative_to_page=true) {
		if ($relative_to_page) {
			$segment_num += (1 + self::$segment_page);
		}
		if (array_key_exists($segment_num, self::$segments)) {
			return self::$segments[ $segment_num ];
		}
		return false;
	}

	/**
	 *	\brief Adiciona um novo segmento de URI
	 *
	 *	@param[in] $segment String contendo o valor do segmento
	 *	\return \c true se tiver sucesso e \c false em caso contrário
	 */
	public static function add_segment($segment) {
		if (trim($segment) != '') {
			self::$segments[] = $segment;
			return true;
		}
		return false;
	}
	
	public static function deleteSegment($segment) {
		unset(self::$segments[ $segment ]);
		$new = array();
		foreach(self::$segments as $value) {
			$new[] = $value;
		}
		self::$segments = $new;
		return true;
	}
	
	/**
	 *	\brief Retorna o valor de um parâmetro GET
	 *
	 *	@param[i] $var String contendo o nome da variável desesada
	 *	\return O valor da variável, caso exista, ou \c false caso a variável não exista
	 */
	public static function _GET($var) {
		if (array_key_exists($var, self::$get_params)) {
			return self::$get_params[ $var ];
		}
		return false;
	}

	public static function get_params() {
		return self::$get_params;
	}

	/**
	 *	\brief Retorna a URI atual
	 *
	 *	\return A string da URI
	 */
	public static function get_uri_string() {
		$uri = self::relative_path_page();
		if (count(self::$get_params)) {
			$uri .= '?';
			foreach (self::$get_params as $key => $value) {
				$uri .= $key . '=' . $value . '&';
			}
			$uri = substr($uri, 0, strlen($uri)-1);
		}
		return  $uri;
	}
	
	/**
	 *	\brief Define o valor de um parâmetro
	 *
	 *	@param[in] $var String contendo o nome da variável a ser definida
	 *	@param[in] $value O valor da variável
	 */
	public static function set_param( $var, $value ) {
		self::$get_params[ $var ] = $value;
	}

	/**
	 *	\brief Monta uma URL
	 *
	 *	@param[in] $segments Array contendo os segmentos da URI
	 *	@param[in] $query Array contendo as variáveis a serem passadas via na URL GET
	 *	@param[in] $https Boolean informando se a urls a ser montada é um HTTPS ou não
	 *	@param[in] $subdom String utilizado em wildcard
	 *	@param[in] $forceRewrite flag (true/false) que determina se o formato SEF deve ser forçado
	 *	\return Uma \c string contendo a URL
	 */
	public static function build_url($segments=array(), $query=array(), $https=false, $subdom=NULL, $forceRewrite=false) {
		$url = str_replace('//', '/', parent::get_conf('sys_uri') . '/');

		// Se rewrite de URL está desligado e não está sendo forçado, acrescenta ? à URL
		if (parent::get_conf('sys_rewrite_url') === false && $forceRewrite === false) {
			$url .= '?';
		}

		// Monta a URI
		$uri = '';
		for ($i=0; $i < count($segments); $i++) {
			if ($segments[ $i ] != 'index') {
				$uri .= (empty($uri) ? '' : '/') . /*self::slug_generator(*/$segments[ $i ]/*)*/;
			}
		}
		
		$url .= $uri;

		/*if (parent::get_conf('sys_ext_file_url')) {
			$url .= parent::get_conf('sys_ext_file_url');
		}*/

		// Monta os parâmetros a serem passados por GET
		$param = '';
		foreach ($query as $var => $value) {
			$param .= (empty($param) ? '?' : '&') . $var . '=' . $value;
		}
		
		return 'http' . ($https ? 's' : '') . '://' . parent::get_conf('sys_dominio') . $url . $param;
	}

	/**
	 *	\brief Manda o header de redirecionamento para uma URL
	 *
	 *	Este método envia o cabeçalho (header) de redirecionamento para o usuário e termina a
	 *	execução do sistema.
	 *
	 *	@param[in] $url A URL para qual o usuário deve ser redirecionado
	 *	@param[in] $header Um inteiro com o código de redirecionamento
	 *		(302 = permanente, 301 = temporário, etc.).\n
	 *		Se omitido usa 302 por padrão.
	 */
	public static function redirect($url, $header=302) {
		header('Location: ' . $url, $header);
		exit;
	}

	/**
	 *	\brief Gera o slug de um string
	 *
	 *	@param[in] $txt String a ser convertida em slug
	 *	@paran[in] $space String que será usada para substituir os espaços em $txt.
	 *		Se for omitido utiliza '-' como padrão.
	 *	\return Uma string com o slug
	 */
	public static function slug_generator($txt, $space='-') {
		$txt = mb_strtolower(trim($txt));

		if (mb_check_encoding($txt, 'UTF-8')) {
			$txt = Strings_UTF8::remove_accented_chars($txt);
		} else {
			$txt = Strings_ANSI::remove_accented_chars($txt);
		}

		$txt = mb_ereg_replace('[  ]+', ' ', $txt);
		$txt = preg_replace('/[__]+/', '_', $txt);
		$txt = mb_ereg_replace('[ ]+', $space, $txt);
		$txt = preg_replace('/[^a-z0-9_\-]/', '', $txt);
		return preg_replace('/[-]+/', '-', $txt);
	}
	
	public static function redirect2Https() {
		if (!isset($_SERVER['HTTPS'])) {
			self::redirect( self::build_url(self::$segments, self::$get_params, true) );
		}
	}
	
	public static function redirect2Http() {
		if (isset($_SERVER['HTTPS'])) {
			self::redirect( self::build_url(self::$segments, self::$get_params) );
		}
	}
}
?>