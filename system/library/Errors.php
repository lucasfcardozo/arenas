<?php
class Errors extends Kernel {
	public static function ajax($errorType, $msg='') {
		self::error_handler(E_USER_ERROR, $msg, '', '', '', $errorType);
	}

	/**
	 *	\brief Encerra o processamento e dá saída na página de erro HTML
	 */
	public static function display_error($errorType, $msg='') {
		self::error_handler(E_USER_ERROR, $msg, '', '', '', $errorType);
	}

	public static function error_handler($errno, $errstr, $errfile, $errline, $localErro, $errorType=500, $printHTML=true) {
		DB::transactionAllRollBack();
		
		switch ($errno) {
			case E_ERROR:
				$printError = 'Error';
			break;
			case E_WARNING:
				$printError = 'Warning';
			break;
			case E_PARSE:
				$printError = 'Parse Error';
			break;
			case E_NOTICE:
				$printError = 'Notice';
			break;
			case E_CORE_ERROR:
				$printError = 'Core Error';
			break;
			case E_CORE_WARNING:
				$printError = 'Core Warning';
			break;
			case E_COMPILE_ERROR:
				$printError = 'Compile Error';
			break;
			case E_COMPILE_WARNING:
				$printError = 'Compile Warning';
			break;
			case E_USER_ERROR:
				$printError = 'User Error';
			break;
			case E_USER_WARNING:
				$printError = 'User Warning';
			break;
			case E_USER_NOTICE:
				$printError = 'User Notice';
			break;
			case E_STRICT:
				$printError = 'Fatal Error';
			break;
			case E_RECOVERABLE_ERROR:
			default:
				return false;
			break;
		}
		
		$msg = '
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px">
			  <tr>
				<td colspan="2" style="background-color:#66C; color:#FFF; font-weight:bold; padding-left:10px; padding:3px 2px">Description error</td>
			  </tr>
			  <tr>
				<td colspan="2" style="padding:3px 2px"><span style="color:#FF0000">'.$printError.'</span>'.($errstr ? ': <em>'.$errstr.'</em>':'').($errfile ?' in <strong>'.$errfile.'</strong> on line <strong>'.$errline.'</strong>' : '').'</td>
			  </tr>
			  <tr>
				<td colspan="2" class="ErrorTitle" style="background-color:#66C; color:#FFF; font-weight:bold; padding-left:10px; padding:3px 2px">Debug</td>
			  </tr>
			  <tr style="background:#efefef">
				<td style="padding:3px 2px"><label style="font-weight:bold">Tempo de execução da página até aqui:</label></td>
				<td style="padding:3px 2px">' . number_format(microtime(true) - $GLOBALS['FWGV_START_TIME'], 6) . ' segundos</td>
			  </tr>
			  <tr>
				<td style="padding:3px 2px"><label style="font-weight:bold">Sistema:</label></td>
				<td style="padding:3px 2px">'.php_uname('n').'</td>
			  </tr>
			  <tr style="background:#efefef">
				<td style="padding:3px 2px"><label style="font-weight:bold">Modo Seguro:</label></td>
				<td style="padding:3px 2px">'.(ini_get('safe_mode') ? 'Sim' : 'Não').'</td>
			  </tr>
			  <tr>
				<td style="padding:3px 2px"><label style="font-weight:bold">Data:</label></td>
				<td style="padding:3px 2px">'.date('Y-m-d').'</td>
			  </tr>
			  <tr style="background:#efefef">
				<td style="padding:3px 2px"><label style="font-weight:bold">Horario:</label></td>
				<td style="padding:3px 2px">'.date('G:i:s').'</td>
			  </tr>
			  <tr>
				<td style="padding:3px 2px"><label style="font-weight:bold">Request:</label></td>
				<td style="padding:3px 2px">'.$_SERVER['REQUEST_URI'].'</td>
			  </tr>
			  <tr style="background:#efefef">
				<td style="padding:3px 2px"><label style="font-weight:bold">Protocol:</label></td>
				<td style="padding:3px 2px">'.$_SERVER['SERVER_PROTOCOL'].'</td>
			  </tr>
			  <tr>
				<td style="padding:3px 2px"><label style="font-weight:bold">URL:</label></td>
				<td style="padding:3px 2px">'.URI::get_uri_string().'</td>
			  </tr>
			  <tr style="background:#efefef">
				<td valign="top" style="padding:3px 2px"><label style="font-weight:bold">Debug:</label></td>
				<td style="padding:3px 2px"><table width="100%"><tr><td style="font-family:Arial, Helvetica, sans-serif; font-size:12px; padding:3px 2px">'.parent::getDebug().'</td></tr></table></td>
			  </tr>
			  <tr>
				<td valign="top" style="padding:3px 2px"><label style="font-weight:bold">Info:</label></td>
				<td style="padding:3px 2px"><table width="100%"><tr><td style="padding:3px 2px">'.parent::make_debug_backtrace($errno).'</td></tr></table></td>
			  </tr>
			  <tr>
				<td colspan="2" style="background-color:#66C; color:#FFF; font-weight:bold; padding-left:10px; padding:3px 2px">IP</td>
			  </tr>
			  <tr>
				<td style="padding:3px 2px"><label style="font-weight:bold">Referer:</label></td>
				<td style="padding:3px 2px">'.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '').'</td>
			  </tr>
			  <tr style="background:#efefef">
				<td style="padding:3px 2px"><label style="font-weight:bold">IP:</label></td>
				<td style="padding:3px 2px">'.$_SERVER['REMOTE_ADDR'].'</td>
			  </tr>
			  <tr style="background:#efefef">
				<td style="padding:3px 2px"><label style="font-weight:bold">Reverso:</label></td>
				<td style="padding:3px 2px">'.gethostbyaddr($_SERVER['REMOTE_ADDR']).'</td>
			  </tr>
			  <tr>
				<td style="padding:3px 2px"><label style="font-weight:bold">Browser:</label></td>
				<td style="padding:3px 2px">'.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '').'</td>
			  </tr>
			  <tr>
				<td colspan="2" style="background-color:#66C; color:#FFF; font-weight:bold; padding-left:10px; padding:3px 2px">VARS</td>
			  </tr>
			  <tr>
				<td valign="top" style="padding:3px 2px"><label style="font-weight:bold">_POST</label></td>
				<td style="padding:3px 2px">'.kernel::print_rc($_POST, true).'</td>
			  </tr>
			  <tr>
				<td valign="top" style="padding:3px 2px"><label style="font-weight:bold">_COOKIE</label></td>
				<td style="padding:3px 2px">'.kernel::print_rc($_COOKIE, true).'</td>
			  </tr>
			  <tr>
				<td valign="top" style="padding:3px 2px"><label style="font-weight:bold">_SESSION</label></td>
				<td style="padding:3px 2px">'.kernel::print_rc(Session::get_all(), true).'</td>
			  </tr>
			</table>
		';
		
		if ($printHTML) {
			self::printHTML($errorType, $msg);
		}
		
		self::sendReport($errorType, $msg);
		
		die;
	}
	
	public static function sendReport($errorType, $msg) {
		if (!in_array($errorType, array(404, 503)) && parent::get_conf('mail_errors_go_to') && !Kernel::get_conf('sys_development')) {
			$msg = preg_replace('/\<a href="javascript\:\;" onclick="var obj=\$\((.*?)\)\.toggle\(\)" style="color:#06c; margin:3px 0"\>ver argumentos passados a função\<\/a\>/', '<span style="font-weight:bold; color:#06c; margin:3px 0">Argumentos da Função:</span>', $msg);
			$msg = preg_replace('/ style="display:none"/', '', $msg);
			
			$email = new Mail;
			$email->to(parent::get_conf('mail_errors_go_to'));
			$email->from(Kernel::get_conf('mail_from'));
			$email->subject('Erro em - ' . Kernel::get_conf('site_name'));
			$email->body($msg);
			$email->send();
			unset($email);
		}
	}
	
	public static function printHTML($errorType, $msg) {
		if (!parent::get_conf('sys_ajax') || !in_array('Content-type: application/json', headers_list())) {
			if (ob_get_level() > 0) {
				ob_clean();
			}
			
			Template::setBasePath('_global');
			Template::start();
			
			header('Content-type: text/html; charset=UTF-8', true, $errorType);
			
			Template::assign('errorDebug', (parent::get_conf('sys_development') ? $msg : ''));
			Template::setCommon();
			Template::set_template('_error' . $errorType);
			Template::display();
		} else {
			header('Content-type: application/json; charset=utf-8', true, $errorType);
			if (is_array($msg)) {
				echo json_encode($msg);
			} else if ($msg != '') {
				echo $msg;
			}
		}
	}
}
?>