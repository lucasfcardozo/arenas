<?php
class JSON {
	private $dados = array();
	private $headerStatus = 200;
	
	public function __construct() {
		Kernel::set_conf('sys_ajax', true);
		header('Content-type: application/json; charset=' . Kernel::get_conf('sys_charset'), true, $this->headerStatus);
	}
	
	public function add($dados) {
		$this->dados = array_merge($this->dados, $dados);
	}
	
	public function setHeaderStatus($status) {
		$this->headerStatus = $status;
		header('Content-type: application/json; charset=' . Kernel::get_conf('sys_charset'), true, $this->headerStatus);
	}
	
	public function printJ($andDie=true) {
		if (Kernel::get_conf('sys_debug')) {
			$this->dados['debug'] = Kernel::getDebug();
		}
		
		echo json_encode($this->dados);
		
		if ($andDie) {
			die;
		}
	}
	
	public function __toString() {
		$this->printJ();
	}
}
?>