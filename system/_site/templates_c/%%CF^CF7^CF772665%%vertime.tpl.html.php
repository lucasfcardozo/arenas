<?php /* Smarty version 2.6.25, created on 2011-05-13 13:35:36
         compiled from vertime.tpl.html */ ?>
<form action="<?php echo $this->_tpl_vars['urlEditar']; ?>
" method="post" style="width:500px" id="fEdit">
	<input type="hidden" name="ihdTime" value="<?php echo $this->_tpl_vars['idTime']; ?>
" />
    <table width="500" border="0" cellspacing="2" cellpadding="2">
      <tr>
        <td colspan="2" class="title">Time <?php echo $this->_tpl_vars['nome']; ?>
</td>
      </tr>
      <?php $_from = $this->_tpl_vars['integrantes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['integrante']):
?>
      <tr>
        <td>Integrante:</td>
        <td>
           <input name="itxPlayer[<?php echo $this->_tpl_vars['id']; ?>
]" type="text" value="<?php echo $this->_tpl_vars['integrante']['nome']; ?>
" />
           <select name="selPlayer[<?php echo $this->_tpl_vars['id']; ?>
]">
             <option value="">Selecione</option>
             <?php $_from = $this->_tpl_vars['classes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idClasse'] => $this->_tpl_vars['classe']):
?>
             <option value="<?php echo $this->_tpl_vars['idClasse']; ?>
"<?php if ($this->_tpl_vars['idClasse'] == $this->_tpl_vars['integrante']['idClasse']): ?> selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['classe']; ?>
</option>
             <?php endforeach; endif; unset($_from); ?>
           </select>
           <input name="itxSucubusPlayer[<?php echo $this->_tpl_vars['id']; ?>
]" type="text" size="2" maxlength="1" value="<?php echo $this->_tpl_vars['integrante']['sucubus']; ?>
" /> Sucubus points
        </td>
      </tr>
      <?php endforeach; endif; unset($_from); ?>
      <tr id="tpl" style="display:none">
        <td>Integrante:</td>
        <td>
           <input name="itxPlayerNovo[]" type="text" />
           <select name="selPlayerNovo[]">
             <option value="">Selecione</option>
             <?php $_from = $this->_tpl_vars['classes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idClasse'] => $this->_tpl_vars['classe']):
?>
             <option value="<?php echo $this->_tpl_vars['idClasse']; ?>
"><?php echo $this->_tpl_vars['classe']; ?>
</option>
             <?php endforeach; endif; unset($_from); ?>
           </select>
           <input name="itxSucubusPlayerNovo[]" type="text" size="2" maxlength="1" value="0" /> Sucubus points
        </td>
      </tr>
      <tr>
        <td align="left" colspan="2"><button type="button" style="margin:0">Add novo integrante</button></td>
      </tr>
      <tr>
        <td align="left">Ganha:</td>
        <td align="left"><input type="checkbox" name="ichGanha"<?php if ($this->_tpl_vars['ganha']): ?> checked="checked"<?php endif; ?> />
          sim</td>
      </tr>
      <tr>
        <td colspan="2" align="right">
           <button class="submit" type="button" style="margin:0">Salvar</button>
        </td>
      </tr>
    </table>
</form>