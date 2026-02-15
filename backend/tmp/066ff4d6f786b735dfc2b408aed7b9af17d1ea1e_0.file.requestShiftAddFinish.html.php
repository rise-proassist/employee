<?php
/* Smarty version 3.1.30, created on 2026-02-15 05:13:10
  from "/var/www/html/view/mypage/requestShiftAddFinish.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_699155e6590fb1_93934762',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '066ff4d6f786b735dfc2b408aed7b9af17d1ea1e' => 
    array (
      0 => '/var/www/html/view/mypage/requestShiftAddFinish.html',
      1 => 1771083970,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699155e6590fb1_93934762 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div>
	
	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/mypage_forcast_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	<div class="h_form">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			希望シフト登録
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			Register desired shift
		<?php }?>
	</div>
	<br>
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		希望シフトを登録しました。
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		Your desired shift has been registered.
	<?php }?>
	<br>
	<br>
	<br>
	<div class="div_input_button">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			<input type="button" value="続けて希望シフト登録する" class="input_button" onclick="location.href='/mypage/requestShiftAddForm/'">
			<input type="button" value="従事者情報へ戻る" class="input_button" onclick="location.href='/mypage/'">
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			<input type="button" value="Continue to register" class="input_button" onclick="location.href='/mypage/requestShiftAddForm/'">
			<input type="button" value="Return to information" class="input_button" onclick="location.href='/mypage/'">
		<?php }?>
	</div>

</div><?php }
}
