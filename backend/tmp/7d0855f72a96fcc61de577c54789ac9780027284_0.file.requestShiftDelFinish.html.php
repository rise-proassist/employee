<?php
/* Smarty version 3.1.30, created on 2026-02-15 05:10:07
  from "/var/www/html/view/mypage/requestShiftDelFinish.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_6991552f8fde49_51781483',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7d0855f72a96fcc61de577c54789ac9780027284' => 
    array (
      0 => '/var/www/html/view/mypage/requestShiftDelFinish.html',
      1 => 1771083970,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6991552f8fde49_51781483 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div>
	
	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/mypage_forcast_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	<div class="h_form">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			希望シフト削除
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			Delete desired shift
		<?php }?>
	</div>
	<br>
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		希望シフトを削除しました。
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		The desired shift has been deleted.
	<?php }?>
	<br>
	<br>
	<br>
	<div class="div_input_button">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			<input type="button" value="希望シフト一覧へ戻る" class="input_button" onclick="location.href='/mypage/requestShiftList/'">
			<input type="button" value="従事者情報へ戻る" class="input_button" onclick="location.href='/mypage/'">
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			<input type="button" value="Return to desired shift list" class="input_button" onclick="location.href='/mypage/requestShiftList/'">
			<input type="button" value="Return to information" class="input_button" onclick="location.href='/mypage/'">
		<?php }?>
	</div>

</div><?php }
}
