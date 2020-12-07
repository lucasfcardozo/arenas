<?php
$FWGV_START_TIME = microtime(true); // Memoriza a hora do início do processamento

class Kernel {
	/// Array interno com dados de configuração
	private static $confs = array();
	/// Array com informações de debug
	private static $debug = array();
	/// Determina se o usuário está usando dispositivo móvel
	private static $mobile = NULL;
	/// Determina o tipo de dispositivo móvel
	private static $mobile_device = NULL;

	/**
	 *	\brief Põe uma informação na janela de debug
	 */
	public static function debug($txt, $name='', $highlight=true, $revert=true) {
		$id      = 'debug_' . str_replace('.', '', current(explode(' ', microtime())));
		
		$size = memory_get_usage(true);
		$unit = array('b', 'KB', 'MB', 'GB', 'TB', 'PB');
		$memoria = round($size / pow(1024, ($i = floor(log($size,1024)))), 2) . ' ' . $unit[$i];
		unset($unit, $size);
		
		$debug = '
		<div class="debug_info">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
			  <thead>
				<th colspan="2" align="left">' . ($name ? $name . ' - ' : '') . 'Memória Alocada até aqui: ' . $memoria . '</th>
			  </thead>
			  <tr>
				<td width="50%" valign="top"> ' . ($highlight ? self::print_rc($txt) : $txt) . '</td>
				<td width="50%" valign="top">
					<a href="javascript:;" onclick="var obj=$(\'' . $id . '\').toggle()">Debug BackTrace</a>
					<div id="' . $id . '" style="display:none">' . self::make_debug_backtrace() . '</div></td>
			  </tr>
			</table>
		</div>
		';
		
		if ($revert) {
			array_unshift(self::$debug, $debug);
		} else {
			self::$debug[] = $debug;
		}
	}

	/**
	 *	\brief Imprime o bloco de debug
	 */
	public static function debug_print() {
		if (self::get_conf('sys_debug') == true && !self::get_conf('sys_ajax')) {
			$size = memory_get_peak_usage(true);
			$unit = array('b', 'KB', 'MB', 'GB', 'TB', 'PB');
			$memoria = round($size / pow(1024, ($i = floor(log($size,1024)))), 2) . ' ' . $unit[$i];
			unset($unit, $size);
			
			self::debug('Tempo de execução de página: ' . number_format(microtime(true) - $GLOBALS['FWGV_START_TIME'], 6) . ' segundos' . "\n" . 'Pico de memória: ' . $memoria, '', true, false);
			unset($memoria);
			
			if (!Template::is_started()) {
				Template::start();
				Template::set_template(array('_blank'));
				Template::setCommon();
				Template::display();
			}
			
			$conteudo = ob_get_contents();
			ob_clean();
			
			echo preg_replace('/<body(.*?)>/', '
				<body\\1>
				<div class="debug_box" id="debug">
					 <div class="debug_box_2" style="display:none">
						<div class="close">
							<a href="javascript:;">Fechar</a>
						</div>
						<div class="debug_info_area">
						' .
						self::getDebug() . '
						</div>
					 </div>
					 <div class="debug_box_3 close">DEBUG</div>
				</div>
			', $conteudo);
		}
	}
	
	public static function getDebug() {
		return implode('<hr />', self::$debug);
	}
	
	public static function print_rc($par, $return=false) {
		if (is_object($par)) {
			if (method_exists($par, '__toString')) {
				return str_replace('&lt;?php', '', str_replace('?&gt;', '', highlight_string('<?php ' . var_export($par->__toString(), true) . ' ?>', true ) ));
			} else {
				return '<pre>' . print_r($par, true) . '</pre>';
			}
		} else {
			return str_replace('&lt;?php', '', str_replace('?&gt;', '', 
				highlight_string('<?php ' . print_r($par, true) . ' ?>', true )
			));
		}
	}
	
	public static function make_debug_backtrace($errno='') {
		$debug = debug_backtrace();
		array_shift($debug);
		
		$aDados = array();
		
		foreach($debug as $value) {
			if (empty($value['line'])) {
				continue;
			}
			
			$linhas = explode('<br />', str_replace('<br /></span>', '</span><br />', highlight_string( file_get_contents($value['file']), true)));
			$aDados[] = array(
				'arquivo' => $value['file'],
				'linha' => $value['line'],
				'args' => isset($value['args']) ? $value['args'] : 'Sem argumentos passados',
				'conteudo_linha' => trim( preg_replace('/^(&nbsp;)+/', '', $linhas[ $value['line'] - 1 ] ))
			);
		}
		
		$tr = 0;
		$saida = '    <ul style="font-family:Arial, Helvetica, sans-serif; font-size:12px">';
		$i  = 0;
		$li = 0;
		
		foreach($aDados as $key => $backtrace) {
			if ($backtrace['linha'] > 0) {
				
				$backtrace['conteudo_linha'] = preg_replace('/^<\/span>/', '', trim($backtrace['conteudo_linha']));
				if (!preg_match('/<\/span>$/', $backtrace['conteudo_linha'])) {
					$backtrace['conteudo_linha'] .= '</span>';
				}
				
				$linha  = sprintf('[%05d]', $backtrace['linha']);
				$saida .= '      <li style="margin-bottom: 5px; '.($li +1 < count($aDados) ? 'border-bottom:1px dotted #000; padding-bottom:5px' : '').'">'
					   .  '        <span style="' . ($i == 1 ? ' color:#F00; ' : '') . '"><b>' . $linha . '</b>&nbsp;<b>' . $backtrace['arquivo'] . '</b></span><br />'
					   .  '        ' . $backtrace['conteudo_linha'];
				
				if (count($backtrace['args'])) {
					$id     = 'args_' . str_replace('.', '', current(explode(' ', microtime())));
					$saida .= '        <br />' . "\n"
						   .  '        <a href="javascript:;" onclick="var obj=$(\'' . $id . '\').toggle()" style="color:#06c; margin:3px 0">ver argumentos passados a função</a>'
						   .  '        ' . (is_array($backtrace['args']) ? '<div id="'.$id.'" style="display:none">' . self::print_rc($backtrace['args'], true) . '</div>' : $backtrace['args']);
				}
				
				$saida .= '      </li>';
				
				$li++;
			}
			
			$tr++;
			
		}
		
		return $saida . '</ul>';
	}

	/**
	 *	\brief Pega o conteúdo de um registro de configuração
	 *
	 *	@param[in] $local nome do arquivo de configuração
	 *	@param[in] $var registro desejado
	 *	\return se o registro existir, retorna seu valor, caso contrário retorna NULL
	 */
	public static function get_conf($var) {
		$value = (isset(self::$confs[$var]) ? self::$confs[$var] : NULL);
		if (is_string($value)) {
			preg_match_all('/#(.*?)#/', $value, $res);
			if (isset($res[1])) {
				foreach ($res[1] as $conf) {
					$value = str_replace('#' . $conf .'#', self::get_conf($conf), $value);
				}
				self::$confs[$var] = $value;
			}
		}
		return $value;
	}
	
	public static function set_conf($var, $value) {
		self::$confs[$var] = $value;	
	}

	/**
	 *	Carrega um arquivo de configuração
	 *
	 *	@param[in] $local nome do arquivo de configuração
	 *	return true se tiver carregado o arquivo de configuração. Caso contrário, retorna false
	 */
	public static function load_conf($key) {
		global $conf;
		
		if ($key == '_default_' && !array_key_exists($key, $conf)) {
			return;
		}
		
		if (!array_key_exists($key, $conf)) {
			throw new Exception('To load configuration "'.$key.'" you must define it in "sysconf.php"', 500);
		}
		
		self::$confs = array_merge(self::$confs, $conf[$key]);
	}

	/**
	 *	\brief Verifica se o usuário está usando um browser de dispositivo móvel
	 */
	private static function mobile_device_detect() {
		// Define que não é um dispositivo móvel até que seja provado o contrário
		self::$mobile = false;
		// Define que não é um dispositivo móvel até que seja provado o contrário
		self::$mobile_device = NULL;
		// Pega o valor do USER AGENT
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		// Pega o conteúdo de HTTP_ACCEPT
		$accept = $_SERVER['HTTP_ACCEPT'];
		switch (true) {
			// iPhone ou iPod?
			case (eregi('ipod',$user_agent)||eregi('iphone',$user_agent));
				self::$mobile = true;
				self::$mobile_device = 'Apple';
				break;
			// Android?
			case (eregi('android',$user_agent));
				self::$mobile = true;
				self::$mobile_device = 'Google';
				break;
			// Opera Mini?
			case (eregi('opera mini',$user_agent));
				self::$mobile = true;
				self::$mobile_device = 'Opera';
				break;
			// Blackberry?
			case (eregi('blackberry',$user_agent));
				self::$mobile = true;
				self::$mobile_device = 'Blackberry';
				break;
			// Palm?
			case (preg_match('/(palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent));
				self::$mobile = true;
				self::$mobile_device = 'Palm';
				break;
			// Windows Mobile?
			case (preg_match('/(windows ce; ppc;|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent));
				self::$mobile = true;
				self::$mobile_device = 'Windows';
				break;
			// Outros dispositivos móveis conhecidos?
			case (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|pda|psp|treo)/i',$user_agent));
				self::$mobile = true;
				self::$mobile_device = 'Other';
				break;
			// Dispositivo com suporte a text/vnd.wap.wml ou application/vnd.wap.xhtml+xml
			case ((strpos($accept,'text/vnd.wap.wml')>0)||(strpos($accept,'application/vnd.wap.xhtml+xml')>0));
				self::$mobile = true;
				self::$mobile_device = 'WAP';
				break;
			// Dispositivo usa cabeçalho HTTP_X_WAP_PROFILE ou HTTP_PROFILE
			case (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE']));
				self::$mobile = true;
				self::$mobile_device = 'WAP';
				break;
			// Verifica numa lista de outros agentes
			case (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','comp'=>'comp','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','tosh'=>'tosh','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-',)));
				self::$mobile = true;
				self::$mobile_device = 'WAP';
				break;
		}

		// tell adaptation services (transcoders and proxies) to not alter the content based on user agent as it's already being managed by this script
		header('Cache-Control: no-transform'); // http://mobiforge.com/developing/story/setting-http-headers-advise-transcoding-proxies
		header('Vary: User-Agent, Accept'); // http://mobiforge.com/developing/story/setting-http-headers-advise-transcoding-proxies

		return self::$mobile;
	}

	/**
	 *	\brief Informa se o usuário está usando um dispositivo móvel
	 */
	public static function get_mobile_device() {
		if (self::$mobile === NULL) {
			self::mobile_device_detect();
		}
		return (self::$mobile) ? (self::$mobile_device) : (self::$mobile);
	}

}

require 'sysconf.php';
Kernel::load_conf('_default_');
Kernel::load_conf(str_replace('www.', '', $_SERVER['HTTP_HOST']));
unset($conf);

date_default_timezone_set(Kernel::get_conf('sys_timezone'));


/*  ------------------------------------------------------------------------------------ --- -- -
	[pt-br] Função de carga automática de classes
	------------------------------------------------------------------------------------ --- -- - */
function __autoload($classe) {
	$file = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . $classe . '.php';
	
	if (file_exists($file)) {
		require_once $file;
		return;
	}
	
	if (preg_match('/dao$/i', $classe)) {
		$file = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . 'user_classes' . DIRECTORY_SEPARATOR . preg_replace('/Dao$/', '.dao', $classe) . '.php';
	} else {
		$file = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . 'user_classes' . DIRECTORY_SEPARATOR . $classe . '.class.php';
	}
	
	if (file_exists($file)) {
		require_once $file;
		return;
	}
	
	if (defined('DOMPDF_INC_DIR')) {
		$file = DOMPDF_INC_DIR . '/' . mb_strtolower($classe) . '.cls.php';
		if (file_exists($file)) {
			require_once $file;
		}
	}
}

/*  ------------------------------------------------------------------------------------ --- -- -
	[pt-br] Função de tratamento de erros para impedir que a classe de erros seja carregada
	desnecessariamente
	------------------------------------------------------------------------------------ --- -- - */

set_error_handler(array('Errors', 'error_handler'));

/*  ------------------------------------------------------------------------------------ --- -- -
	[pt-br] Início do script
	------------------------------------------------------------------------------------ --- -- - */


// [pt-br] Resolve a URI e monta as variáveis internas
URI::parse_uri();

if (URI::get_segment(0, false) == 'captcha') {
	Captcha::show();
}

// [pt-br] Envia o charset
header('Content-Type: text/html; charset=' . Kernel::get_conf('sys_charset'), true);

//ini_set('zlib.output_compression', 'on');
ini_set('mbstring.internal_encoding', Kernel::get_conf('sys_charset'));
ini_set('default_charset', Kernel::get_conf('sys_charset'));

if (Kernel::get_conf('developer_user') && Kernel::get_conf('developer_pass')) {
	if (URI::_GET( Kernel::get_conf('developer_user') ) == Kernel::get_conf('developer_pass')) {
		Session::set('_developer', true);
	} else if (URI::_GET( Kernel::get_conf('developer_user') ) == 'off') {
		Session::unregister('_developer');
	}
}

if (Session::is_set('_developer')) {
	Kernel::set_conf('sys_maintenance', false);
	Kernel::set_conf('sys_development', true);
	Kernel::set_conf('sys_debug', true);
}

// apenas se o debug estiver ligado, verifica se o DBA (modo de exibição de SQLs) está ligado
if (Kernel::get_conf('sys_debug')) {
	if (URI::_GET( Kernel::get_conf('dba_user') ) == Kernel::get_conf('developer_pass')) {
		Session::set('_dba', true);
	} else if (URI::_GET( Kernel::get_conf('dba_user') ) == 'off') {
		Session::unregister('_dba');
	}
	
	if (Session::is_set('_dba')) {
		Kernel::set_conf('sys_sql_debug', true);
	}
}

if (Kernel::get_conf('sys_development')) {
	ini_set('display_errors', 1);
} else {
	ini_set('display_errors', 0);
}

// [pt-br] Verifica se o sistema está em manutenção
if (Kernel::get_conf('sys_maintenance')) {
	Errors::display_error(503, 'The system is under maintenance');
}

if (Kernel::get_conf('admin_url') && URI::get_segment(0, false) == Kernel::get_conf('admin_url')) {
	if (Kernel::get_conf('admin_https')) {
		if (Kernel::get_conf('admin_https_exception')) {
			kernel::debug(preg_replace('/^'.Kernel::get_conf('admin_url').'[\/]?/', '', URI::relative_path_page()));
			if (!in_array(preg_replace('/^'.Kernel::get_conf('admin_url').'[\/]?/', '', URI::relative_path_page()), Kernel::get_conf('admin_https_exception'))) {
				URI::redirect2Https();
			}
		} else {
			URI::redirect2Https();
		}
	}
	
	if (URI::get_segment(1, false) == 'logout') {
		Administrator::logout();
	} else if (!Session::is_set('_admin_user')) {
		if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || !Session::is_set('login'))	{
			Session::set('login', true);
			Administrator::requireAuthenticate();
		} else if (!Administrator::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
			Session::unregister('login');
			header('Location: ' . Administrator::build());
			die;
		}
	}
	
	Uri::deleteSegment(0);
	$path = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . '_administrator';
	Template::setBasePath('_administrator');
	
	if (!URI::get_segment(0, false)) {
		URI::add_segment('index');
	}
	
	Kernel::set_conf('admin', true);
} else {
	$path = Kernel::get_conf('sys_path') . DIRECTORY_SEPARATOR . '_site';
	Template::setBasePath('_site');
}

$path .= DIRECTORY_SEPARATOR . 'controllers';

// [pt-br] Verifica se a controller _global existe
if (file_exists($path . DIRECTORY_SEPARATOR . '_global.php')) {
	require $path . DIRECTORY_SEPARATOR . '_global.php';
	
	$pageClassName = 'Global_Controller';
	if (class_exists($pageClassName)) {
		new $pageClassName;
	}
	unset($pageClassName);
}
unset($pathGlobal);

$segment = 0;
while (URI::get_segment($segment, false)) {
	$path .= DIRECTORY_SEPARATOR . str_replace('-', '', URI::get_segment($segment, false));
	$file = $path . '.page.php';
	
	if (file_exists($file)) {
		$controller = $file;
		URI::set_current_page($segment);
		break;
	} else if (is_dir($path) && (!URI::get_segment($segment + 1, false))) {
		$file = $path . DIRECTORY_SEPARATOR . 'index.page.php';
		if (file_exists($file)) {
			$controller = $file;
			URI::add_segment('index');
			URI::set_current_page($segment + 1);
			break;
		}
	} else if (is_dir($path)) {
		$segment++;
	} else {
		break;
	}
}
unset($file, $path, $segment);

// [pt-br] Se foi definido uma Controller, carega
if (isset($controller)) {
	ob_start();
	
	// [pt-br] Carrega a controller
	require_once($controller);

	// [pt-br] Inicializa a controller
	$ControllerClassName = str_replace('-', '', URI::current_page()) . '_Controller';
	
	if (class_exists($ControllerClassName)) {
		new $ControllerClassName;
	} else {
		Errors::display_error(404, 'No ' . $ControllerClassName . ' on ' . $controller);
	}
	unset($controller);
} else {
	Errors::display_error(404, 'Page not Found. No controller.');
}


// [pt-br] se o template estiver carregado, imprime
if (Template::is_started()) {
	Template::display();
}

// [pt-br] se tiver algum debug, utiliza-o
Kernel::debug_print();

ob_end_flush();
?>