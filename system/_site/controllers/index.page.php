<?php
class Index_Controller {
	public function __construct() {
		if (URI::_GET('search')) {
			return $this->search();
		}
		
		if (URI::_GET('cadastro')) {
			return $this->cadastro();
		}
		
		if (URI::_GET('ver')) {
			return $this->ver();
		}
		
		if (URI::_GET('editar')) {
			return $this->editar();
		}
		
		Template::start();
		Template::setCommon();
		
		Template::assign('urlSearchTimes', URI::build_url(array(), array('search' => '1')));
		Template::assign('urlCadastro', URI::build_url(array(), array('cadastro' => '1')));
		Template::assign('urlVerTime', URI::build_url(array(), array('ver' => '1')));
		
		$db = new DB;
		$db->execute('SELECT id, nome FROM classes ORDER BY nome');
		$classes = array();
		while ($res = $db->fetch_next()) {
			$classes[ $res['id'] ] = $res['nome'];
		}
		
		Template::assign('classes', $classes);
		unset($classes);
		
		$db->execute('SELECT nome, ganha, LOWER(nome) as time FROM times ORDER BY ganha DESC, 3 ASC');
		$times = array();
		while ($res = $db->fetch_next()) {
			$times[] = $res;
		}
		
		Template::assign('times', $times);
	}
	
	private function cadastro() {
		$json = new JSON;
		
		if (empty($_POST['itxNome']) || empty($_POST['selPlayer1']) || empty($_POST['selPlayer2'])) {
			$json->add(array('errors' => array('itxNome' => 'Preencha os campos.')));
		} else {
			$insert = new DBInsert('times');
			$insert->add(array(
				'nome' => $_POST['itxNome'],
				'ganha' => (isset($_POST['ichGanha']) ? 1 : 0)
			));
			
			$db = new DB;
			$db->execute($insert);
			
			$time = $db->get_inserted_id();
			
			
			$insert = new DBInsert('integrantes');
			$insert->add(array(
				'id_time' => $time,
				'id_classe' => $_POST['selPlayer1'],
				'nome' => $_POST['itxPlayer1'],
				'sucubus' => $_POST['itxSucubusPlayer1']
			));
			$db->execute($insert);
			
			$insert = new DBInsert('integrantes');
			$insert->add(array(
				'id_time' => $time,
				'id_classe' => $_POST['selPlayer2'],
				'nome' => $_POST['itxPlayer2'],
				'sucubus' => $_POST['itxSucubusPlayer2']
			));
			$db->execute($insert);
			
			$json->add(array('sucesso' => 'Cadastro realizado com sucesso'));
		}
		
		$json->printJ();
	}
	
	private function search() {
		$json = new JSON;
		
		$db = new DB;
		
		$_POST['text'] = strtolower($_POST['text']);
		
		$busca = array(
			$_POST['text'].'%',
			'%'.$_POST['text'],
			'%'.$_POST['text'].'%'
		);
		
		if (empty($_POST['tipo']) || $_POST['tipo'] == 'time') {
			$db->execute('SELECT id, nome FROM times WHERE (LOWER(nome) LIKE ?) OR (LOWER(nome) LIKE ?) OR (LOWER(nome) LIKE ?)', $busca);
		} else {
			$db->execute('
			SELECT 
				t.id,
				i.nome,
				t.nome AS time
			FROM integrantes i
				INNER JOIN times t ON ((LOWER(i.nome) LIKE ?) OR (LOWER(i.nome) LIKE ?) OR (LOWER(i.nome) LIKE ?)) AND t.id = i.id_time
			', $busca);
		}
		
		$retorno = array();
		
		while ($res = $db->fetch_next()) {
			$retorno[] = array(
				'id' => $res['id'],
				'text' => $res['nome'] . (isset($res['time']) ? ' - ' . $res['time'] : '')
			);
		}
		
		$json->add(array('dados' => $retorno));
		
		$json->printJ();
	}
	
	private function ver() {
		$json = new JSON;
		
		$db = new DB;
		$db->execute('SELECT nome, ganha FROM times WHERE id = ?', array($_POST['id']));
		$time = $db->fetch_next();
		
		Template::start('vertime');
		Template::assign('urlEditar', URI::build_url(array(), array('editar' => 1)));
		Template::assign('idTime', $_POST['id']);
		Template::assign('nome', $time['nome']);
		Template::assign('ganha', $time['ganha']);
		
		$db->execute('SELECT id, nome FROM classes ORDER BY nome');
		$classes = array();
		while ($res = $db->fetch_next()) {
			$classes[ $res['id'] ] = $res['nome'];
		}
		
		Template::assign('classes', $classes);
		
		$db->execute('
		SELECT
			i.id,
			i.nome,
			i.sucubus,
			i.healer,
			c.id AS idClasse
		FROM integrantes i
			INNER JOIN classes c ON id_time = ? AND c.id = i.id_classe
		', array($_POST['id']));
		
		
		$integrantes = array();
		
		while ($res = $db->fetch_next()) {
			$integrantes[$res['id']] = $res;
		}
		
		Template::assign('integrantes', $integrantes);
		
		$json->add(array('html' => Template::fetch()));
		Template::stop();
		
		$json->printJ();
	}
	
	private function editar() {
		$json = new JSON;
		
		$db = new DB;
		$db->execute('UPDATE times SET nome = ?, ganha = ? WHERE id = ?', array(
			$_POST['itxNome'],
			(isset($_POST['ichGanha']) ? 1 : 0),
			$_POST['ihdTime']
		));
		
		$db->execute('
		SELECT
			i.id,
			i.nome,
			i.sucubus,
			i.healer,
			c.id AS idClasse
		FROM integrantes i
			INNER JOIN classes c ON id_time = ? AND c.id = i.id_classe
		', array($_POST['ihdTime']));
		
		
		$integrantes = array();
		
		while ($res = $db->fetch_next()) {
			$integrantes[$res['id']] = $res;
		}
		
		foreach($integrantes as $id => $oldName) {
			if (!array_key_exists($id, $_POST['selPlayer'])) {
				$db->execute('DELETE FROM integrantes WHERE id = ?', array($id));
			} else {
				$db->execute('UPDATE integrantes SET id_classe = ?, nome = ?, sucubus = ? WHERE id = ?', array(
					trim($_POST['selPlayer'][$id]),
					trim($_POST['itxPlayer'][$id]),
					trim($_POST['itxSucubusPlayer'][$id]),
					$id
				));
			}
		}
		
		foreach($_POST['itxPlayerNovo'] as $id => $playerName) {
			if (empty($playerName)) {
				continue;
			}
			
			$db->execute('INSERT INTO integrantes (id_time, id_classe, nome, sucubus) VALUES (?, ?, ?, ?)', array(
				$_POST['ihdTime'],
				$_POST['selPlayerNovo'][$id],
				$playerName,
				$_POST['itxSucubusPlayerNovo'][$id]
			));
		}
		
		$json->add(array('sucesso' => 'Dados atualizados com sucesso.'));
		$json->printJ();
	}
}
?>