<?php
class Session extends Kernel {
 
	/**
	 *	Classe estática não pode ser inicializada
	 */
    private function __construct() {}
	
	public static function getId() {
		return session_id();
	}
	
	public static function setId($id) {
		if (!preg_match('/[a-zA-Z0-9]{26}/', $id)) {
			return;
		}
		session_id($id);
	}

	/**
	 *	Informa se a variável de sessão está definida
	 */
	public static function is_set($var) {
		return isset($_SESSION[$var]);
	}

	/**
	 *	Coloca um valor em variável de sessão
	 */
	public static function set($var, $value) {
		$_SESSION[$var] = $value;
	}

	/**
	 *	Pega o valor de uma variável de sessão
	 */
	public static function get($var) {
		if (isset($_SESSION[$var])) {
			return $_SESSION[$var];
		}

		return NULL;
	}

	/**
	 *	Coloca todos os dados armazenados na sessão na variável fornecida
	 *
	 *	return Boolean (retorna true se tiver sucesso ou false se não houver sessão)
	 */
	public static function get_all() {
		return $_SESSION;
	}

	/**
	 *	Remove uma variável de sessão
	 */
	public static function unregister($var) {
		unset($_SESSION[$var]);
	}
}

if (!empty($_REQUEST['PHPSESSID'])) {
	Session::setId($_REQUEST['PHPSESSID']);
}
//session_set_cookie_params(0, '/', Kernel::get_conf('sys_dominio'), true, false);
session_start();
?>