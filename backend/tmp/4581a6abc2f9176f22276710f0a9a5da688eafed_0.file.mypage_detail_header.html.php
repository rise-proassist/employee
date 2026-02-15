<?php
/* Smarty version 3.1.30, created on 2026-02-15 03:14:07
  from "/var/www/html/view/common/mypage_detail_header.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_699139ff946047_83053288',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4581a6abc2f9176f22276710f0a9a5da688eafed' => 
    array (
      0 => '/var/www/html/view/common/mypage_detail_header.html',
      1 => 1771083969,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699139ff946047_83053288 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="page_titile">
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		従事者情報詳細
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		Employee information details
	<?php }?>
</div>
<div class="header_detail">
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		登録中の従事者情報をご確認いただけます。<br>
		また、内容の変更もこちらから行なっていただけます。
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		You can check the employee information you are currently registering. <br>
		You can also make changes to the information here. 
	<?php }?>
	<br>
</div>
<hr><?php }
}
