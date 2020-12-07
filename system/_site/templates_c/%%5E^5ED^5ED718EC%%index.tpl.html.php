<?php /* Smarty version 2.6.25, created on 2011-05-13 15:03:59
         compiled from index.tpl.html */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Documento sem título</title>
	<link type="text/css" rel="stylesheet" href="<?php echo $this->_tpl_vars['urlCSS']; ?>
/style.css"  />
    <script type="text/javascript" src="<?php echo $this->_tpl_vars['urlJS']; ?>
/prototype.js"></script>
    <?php if ($this->_tpl_vars['SYSTEM']['_debug']): ?>
	<script type="text/javascript" src="<?php echo $this->_tpl_vars['urlJS']; ?>
/extensao_prototype.js"></script>
    <script type="text/javascript">
		AUTO_SHOW_DEBUG = false;
	</script>
    <?php endif; ?>
	<script type="text/javascript">
		URL_SEARCH_TIMES = '<?php echo $this->_tpl_vars['urlSearchTimes']; ?>
';
		URL_VER_TIME     = '<?php echo $this->_tpl_vars['urlVerTime']; ?>
';
		URL_IMG = '<?php echo $this->_tpl_vars['urlIMG']; ?>
';
	</script>
    <script type="text/javascript" src="<?php echo $this->_tpl_vars['urlJS']; ?>
/scriptaculous.js?load=effects,autocomplete,mAlert,send_form,functions"></script>
</head>
<body>
<table width="550" border="0" cellspacing="2" cellpadding="2" align="center">
  <tr>
    <td valign="top"><form id="fCad" action="<?php echo $this->_tpl_vars['urlCadastro']; ?>
" method="post" style="width:500px">
      <table width="100%" border="0" cellspacing="2" cellpadding="2">
        <tr>
          <td colspan="2" class="title">Novo Time</td>
          </tr>
        <tr>
          <td width="23%">Nome:</td>
          <td width="77%"><input name="itxNome" type="text" id="itxNome" size="45" /></td>
          </tr>
        <tr>
          <td>Integrante 1:</td>
          <td><input name="itxPlayer1" type="text" id="itxPlayer1" />
            <select name="selPlayer1" id="selPlayer1">
              <option value="">Selecione</option>
              <?php $_from = $this->_tpl_vars['classes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['classe']):
?>
              <option value="<?php echo $this->_tpl_vars['id']; ?>
"><?php echo $this->_tpl_vars['classe']; ?>
</option>
              <?php endforeach; endif; unset($_from); ?>
              </select>
            <input name="itxSucubusPlayer1" type="text" id="itxSucubusPlayer1" value="0" size="2" maxlength="1" />
            Sucubus points </td>
          </tr>
        <tr>
          <td>Integrante 2:</td>
          <td><input name="itxPlayer2" type="text" id="itxPlayer2" />
            <select name="selPlayer2" id="selPlayer2">
              <option value="">Selecione</option>
              <?php $_from = $this->_tpl_vars['classes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['classe']):
?>
              <option value="<?php echo $this->_tpl_vars['id']; ?>
"><?php echo $this->_tpl_vars['classe']; ?>
</option>
	          <?php endforeach; endif; unset($_from); ?>
              </select>
            <input name="itxSucubusPlayer2" type="text" id="itxSucubusPlayer2" value="0" size="2" maxlength="1" />
            Sucubus points </td>
          </tr>
        <tr>
          <td align="left">Ganha:</td>
          <td align="left"><input type="checkbox" name="ichGanha" id="ichGanha" />
            sim</td>
          </tr>
        <tr>
          <td colspan="2" align="right"><button type="button" style="margin:0">Salvar</button></td>
          </tr>
        </table>
    </form></td>
  </tr>
  <tr>
    <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td colspan="2">&nbsp;</td>
        </tr>
      <tr>
        <td width="92">Busca de time: </td>
        <td width="338" id="tdAutoNomeProduto">&nbsp;</td>
        </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
        </tr>
      <tr>
        <td colspan="2" id="showTime">&nbsp;</td>
        </tr>
    </table>    </td>
  </tr>
</table>
</body>
</html>