<?php
class DB {
	private static $DB = array();
	private static $dbsInTransaction = array();
	
	private $SQLRes = array();

	private $LastQuery = '';

	private static $sqlNum = 0;
	
	private $dataConnet = false;
	private $database = false;
	
	//private $data;
	private $printSql = 1;
	
	/**
	 *	Conecta ao banco de dados
	 *
	 *	@param $database chave de configuração do banco de dados.
	 *		Default = 'default'
	 */
	public function __construct($database='default') {
		$this->database = $database;
		$this->dataConnet = $this->connect($this->database);
	}
	
	public static function connect($database) {
		// Verifica se a instância já está definida e conectada
		if (isset(self::$DB[$database])) {
			return self::$DB[$database];
		}
	
		// Lê as configurações de acesso ao banco de dados
		$dbs = Kernel::get_conf('db');
		$conf = $dbs[$database];
		
		$pdoConf = array();
		if ($conf['type'] == 'mysql') {
			$pdoConf[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES \'UTF8\'';
		}
		
		if ($conf['persistent']) {
			$pdoConf[ PDO::ATTR_PERSISTENT ] = true;
		}
		
		if ($conf['type'] == 'sqlite') {
			self::$DB[$database] = new PDO(
				'sqlite:' . $conf['file']
			);
		} else {
			self::$DB[$database] = new PDO(
				$conf['type'] . ':host=' . $conf['host'] . ';dbname=' . $conf['database'],
				$conf['user'],
				$conf['password'],
				$pdoConf
			);
		}
		unset($pdoConf);
		
		try {
			return self::$DB[$database];
		} catch (PDOException $e) {
			Errors::display_error(500, $e);
			//self::reportError('Can\'t connect to database server.', $e);
		}
	}

	/**
	 *	Fecha a conexão com o banco de dados
	 *
	 *	@param $this->database chave de configuração do banco de dados.
	 *		Default = 'default'
	 */
	public function disconnect() {
		if ($this->dataConnet && $this->dataConnet->IsConnected()) {
			/*
			 * a conexão fica no self, para nao criar uma nova a cada new DB
			 */
			self::$DB[$this->database]->Disconnect(); 
		}
		unset(self::$DB[$this->database]);
	}

	/*
		[pt-BR] Método de retorno de erros. Também envia e-mails com informações sobre o erro e grava-o em um arquivo de Log.
	*/
	private function reportError($msg, PDOException $exception=NULL) {
		// [pt-br] Lê as configurações de acesso ao banco de dados
		$conf = Kernel::get_conf('db_' . $this->database);

		if (isset($this->LastQuery)) {
			$sqlError = '<pre>' . htmlentities((is_object($this->LastQuery) ? $this->LastQuery->__toString() : $this->LastQuery)) . '</pre><br /> Parametros:<br />' . Kernel::print_rc($this->LastValues, true);
		} else {
			$sqlError = 'Still this connection was not executed some instruction SQL using.';
		}
		
		$errorInfo = ($this->SQLRes ? $this->SQLRes->errorInfo() : $this->dataConnet->errorInfo());
	
		$htmlError = '
			<table width="100%" border="0" cellspacing="2" cellpadding="2" class="ErrorTable">
			  <tr class="ErrorZebra">
				<td class="ErrorTitle" colspan="2">Description error</td>
			  </tr>
			  <tr>
				<td colspan="2" style="font-weight:bold"><span style="color:#FF0000">'.$msg . '</span> - ' . '(' . $errorInfo[1] . ') ' . $errorInfo[2] . ($exception ? '<br />' . $exception->getMessage() : '') . '</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">SQL:</label></td>
				<td>' . $sqlError . '</td>
			  </tr>
			  <tr>
				<td colspan="2" class="ErrorTitle">Debug</td>
			  </tr>
			  <tr class="ErrorZebra">
				<td valign="top"><label class="ErrorLabel">Tempo de execução da página até aqui:</label></td>
				<td>' . number_format(microtime(true) - $GLOBALS['FWGV_START_TIME'], 6) . ' segundos</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">Sistema:</label></td>
				<td>'.php_uname('n').'</td>
			  </tr>
			  <tr class="ErrorZebra">
				<td valign="top"><label class="ErrorLabel">Modo Seguro:</label></td>
				<td>'.(ini_get('safe_mode') ? 'Sim' : 'Não').'</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">Data:</label></td>
				<td>'.date('Y-m-d').'</td>
			  </tr>
			  <tr class="ErrorZebra">
				<td valign="top"><label class="ErrorLabel">Horario:</label></td>
				<td>'.date('G:i:s').'</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">Request:</label></td>
				<td>'.$_SERVER['REQUEST_URI'].'</td>
			  </tr>
			  <tr class="ErrorZebra">
				<td valign="top"><label class="ErrorLabel">Protocol:</label></td>
				<td>'.$_SERVER['SERVER_PROTOCOL'].'</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">URL:</label></td>
				<td>'.URI::get_uri_string().'</td>
			  </tr>
			  <tr class="ErrorZebra">
				<td valign="top"><label class="ErrorLabel">Debug:</label></td>
				<td><table width="100%"><tr><td>'.(Kernel::getDebug()).'</td></tr></table></td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">Info:</label></td>
				<td><table width="100%"><tr><td>'.Kernel::make_debug_backtrace().'</td></tr></table></td>
			  </tr>
			  <tr>
				<td class="ErrorTitle" colspan="2">IP</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">Referer:</label></td>
				<td>'.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '').'</td>
			  </tr>
			  <tr class="ErrorZebra">
				<td valign="top"><label class="ErrorLabel">IP:</label></td>
				<td>'.$_SERVER['REMOTE_ADDR'].'</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">Browser:</label></td>
				<td>'.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '').'</td>
			  </tr>
			  <tr>
				<td class="ErrorTitle" colspan="2">Dados do banco</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">Host:</label></td>
				<td>'.$conf['host'].'</td>
			  </tr>
			  <tr class="ErrorZebra">
				<td valign="top"><label class="ErrorLabel">User:</label></td>
				<td><span style="background:#efefef">'.$conf['user'].'</span></td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">Pass:</label></td>
				<td>'.$conf['password'].'</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">DB:</label></td>
				<td><span style="background:#efefef">'.$conf['database'].'</span></td>
			  </tr>
			  <tr>
				<td class="ErrorTitle" colspan="2">VARS</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">_POST</label></td>
				<td>'.kernel::print_rc($_POST, true).'</td>
			  </tr>
			  <tr>
				<td valign="top"><label class="ErrorLabel">_COOKIE</label></td>
				<td style="padding:3px 2px">'.kernel::print_rc($_COOKIE, true).'</td>
			  </tr>
			</table>
		';
		unset($sqlError);

		// [pt-BR] Caso o sistema esteja em produção ($printSql == 1), mas não há um desenvolvedor vendo a página, manda e-mail.
		if (!Kernel::get_conf('sys_development') && Kernel::get_conf('sys_mail_error') && Kernel::get_conf('sys_from_site') && Kernel::get_conf('sys_log_mysql_file') && file_exists(Kernel::get_conf('sys_log_mysql_file'))) {
			if (!Kernel::get_conf('sys_log_mysql_file') || !strpos(file_get_contents(Kernel::get_conf('sys_log_mysql_file')), mysql_error() )) {
				$email = new Mail;
				$email->to(Kernel::get_conf('mail_errors_go_to'), Kernel::get_conf('sys_site_name'));
				$email->from(Kernel::get_conf('sys_mail_error'), Kernel::get_conf('sys_site_name'));
				$email->subject(Kernel::get_conf('sys_site_name') . ' - Query error.');
				$email->body($htmlError,'');
				$email->set_email_header('Reply-To', Kernel::get_conf('sys_mail_error'), Kernel::get_conf('sys_site_name'));
				$email->send();
				unset($email);
			}
		}

		if ($this->printSql == 2 || Kernel::get_conf('sys_development')) {
			/*
				[pt-BR] Caso o sistema esteja em desenvolvimento OU algum desenvolvedor esteja vendo o sistema, imprime o erro no browser.
			*/
			Errors::printHTML(500, $htmlError);
			die;
		}
	}
	
	public static function debug($debug) {
		Kernel::set_conf('sys_sql_debug', $debug);
		Kernel::debug('DEBUG DE BANCO DE DADOS: ' . ($debug ? 'LIGADO' : 'DESLIGADO'));
	}
	
	// como as transações podem utilzar varias classes e métodos, a transação será "estatica"
	public static function transactionBegin($database='default') {
		self::connect($database)->beginTransaction();
		self::$dbsInTransaction[$database] = true;
	}
	
	public static function transactionRollBack($database='default') {
		self::connect($database)->rollBack();
		unset(self::$dbsInTransaction[$database]);
	}
	
	public static function transactionCommit($database='default') {
		self::connect($database)->commit();
		unset(self::$dbsInTransaction[$database]);
	}
	
	// em caso de erro no php, executa um all roll back
	public static function transactionAllRollBack() {
		Kernel::debug('rollback start');
		foreach (self::$dbsInTransaction as $database => $v) {
			Kernel::debug('rollback ' . $database);
			self::connect($database)->rollBack();
		}
		Kernel::debug('rollback done!');
	}
	
	/**
	 *	Executa uma consulta no banco de dados
	 *
	 *	@param[in] $sql Comando SQL a ser executado
	 */
	public function execute($sql, $where_v=array()) {
		self::$sqlNum++;
		
		$this->LastQuery = $sql;
		
		if (($sql instanceof DBSelect) || ($sql instanceof DBInsert) || ($sql instanceof DBUpdate) || ($sql instanceof DBDelete)) {
			$this->LastValues = $sql->getAllValues();
		} else {
			$this->LastValues = $where_v;
			$where_v = array();
		}
		
		$sql = NULL;
		
		if (($this->SQLRes = $this->dataConnet->prepare($this->LastQuery)) === false) {
			$this->reportError('Can\'t prepare query.');
		}
		
		if (count($this->LastValues)) {
			$numeric = 0;
			
			foreach($this->LastValues as $key => $where) {
				switch(gettype($where)) {
					case 'boolean' :
						$param = PDO::PARAM_BOOL;
					break;
					case 'integer' :
						$param = PDO::PARAM_INT;
					break;
					default :
						$param = PDO::PARAM_STR;
					break;
				}
				
				if (is_numeric($key)) {
					$this->SQLRes->bindValue(++$numeric, $where, $param);
				} else {
					$this->SQLRes->bindParam($key, $where, $param);
				}
			}
			unset($key, $where, $param, $numeric);
		}
		
		if ($this->SQLRes->execute() === false) {
			$this->reportError('Can\'t execute query.');
		}
		
		if (Kernel::get_conf('sys_sql_debug')) {
			Kernel::debug('<pre>' . $this->LastQuery . '</pre><br />Valores: ' . Kernel::print_rc($this->LastValues, true) . '<br />Affected Rows: ' . $this->affected_rows(), 'SQL #'  . self::$sqlNum, false);
		}
		
		return true;
	}

	/**
	 *	Retorna o último comando executado
	 */
	public function last_query() {
		return $this->LastQuery;
	}
	
	public function getDatabase() {
		switch ($this->dataConnet->databaseType) {
			case 'postgres' :
			case 'postgres7' :
			case 'postgres8' :
			case 'postgres64' :
				return 'postgres';
			case 'mssql' :
			case 'mssqlnative' :
			case 'mssqlpo':
			case 'mssql_n':
				return 'mysql';
			break;
		}
		return false;
	}

	/**
	 *	Retorna o valor do campo autoincremento do último INSERT
	 */
	public function get_inserted_id($indice='') {
		return $this->dataConnet->lastInsertId( ((!$indice && $this->LastQuery instanceof DBInsert) ? $this->LastQuery->getTable() . '_id_seq' : $indice) );
	}

	/**
	 *  Retorna o número de linhas afetadas no último comando
	 */
	public function affected_rows() {
		return $this->num_rows();
	}

	/**
	 *	Retorna o número de resultados de um SELECT
	 */
	public function num_rows() {
		return $this->SQLRes->rowCount();
	}

	/**
	 *	Retorna o resultado de um SELECT
	 */
	public function get_all($resultType=PDO::FETCH_ASSOC) {
		if ($this->SQLRes) {
			return $this->SQLRes->fetchAll($resultType);
		}
		
		return false;
	}

	/**
	 *	Retorna o próximo resultado de um SELECT
	 */
	public function fetch_next($resultType=PDO::FETCH_ASSOC) {
		if ($this->SQLRes) {
			return $this->SQLRes->fetch($resultType);
		}
		
		return false;
	}

	/**
	 *	Retorna o valor de uma coluna do último registro pego por fetch_next
	 */
	public function get_column($var) {
		if ($this->SQLRes && is_numeric($var)) {
			return $this->dataConnet->fetchColumn($var);
		}
		
		$this->reportError($var . ' is not defined in select (remember, it\'s a case sensitive) or $data is empty.');
		
		return false;
	}
	
	public static function gravaData($dataHora, $exibeHora=false) {
		if (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', $dataHora, $res)) {
			$data = array($res[1], $res[2], $res[3]);
		} else {
			return '';
		}
		
		if ($exibeHora) {
			if (preg_match('/([0-9]{2})\:([0-9]{2})\:([0-9]{2})$/', $dataHora, $res)) {
				$hora = array($res[1], $res[2], $res[3]);
			} else {
				return '';
			}
		}
		
		$data = $data[2] . '-' . $data[1] . '-' . $data[0];
		unset($res);
		
		if (!$exibeHora) {
			return $data;
		}
		
		return $data . ' ' . $hora[0] . ':' . $hora[1] . ':' . $hora[2];
    }
	
	public static function leData($dataHora, $exibeHora=false, $segundo=false) {
		if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})/', $dataHora, $res)) {
			$data = array($res[1], $res[2], $res[3]);
		} else {
			return '';
		}
		
		if ($exibeHora) {
			if (preg_match('/([0-9]{2})\:([0-9]{2})\:([0-9]{2})/', $dataHora, $res)) {
				$hora = array($res[1], $res[2], $res[3]);
			} else {
				return '';
			}
		}
		
		$data = $data[2] . '/' . $data[1] . '/' . $data[0];
		unset($res);
		
		if (!$exibeHora) {
			return $data;
		}
		
		return $data . ' ' . $hora[0] . ':' . $hora[1] . ($segundo ? ':' . $hora[2] : '');
    }
	
	/**
	 * O formato da data deve ser Y-m-d H:i:s
	 */
	public static function dateToTime($dateTime) {
		if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})/', $dateTime, $res)) {
			$data = array(
				$res[1],
				$res[2],
				$res[3],
			);
		} else if (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', $dateTime, $res)) {
			$data = array(
				$res[3],
				$res[2],
				$res[1],
			);
		}
		unset($res);
		
		preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $dateTime, $res);
		if (isset($res[1])) {
			$hora = array(
				$res[1],
				$res[2],
				$res[3],
			);
		} else {
			$hora = array(
				0,
				0,
				0,
			);
		}
		unset($res);
		return mktime($hora[0], $hora[1], $hora[2], $data[1], $data[2], $data[0]);
    }

	public static function dateToStr($dataTimeStamp) {
		$dateTime = DB::dateToTime($dataTimeStamp);
		$mes = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
		return date('d', $dateTime) . ' de ' . $mes[date('m', $dateTime)];
    }
}
?>