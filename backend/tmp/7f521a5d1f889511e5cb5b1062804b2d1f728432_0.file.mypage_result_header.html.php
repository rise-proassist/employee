<?php
/* Smarty version 3.1.30, created on 2026-02-15 03:38:58
  from "/var/www/html/view/common/mypage_result_header.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_69913fd291d6e3_75774220',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7f521a5d1f889511e5cb5b1062804b2d1f728432' => 
    array (
      0 => '/var/www/html/view/common/mypage_result_header.html',
      1 => 1771083969,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69913fd291d6e3_75774220 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="page_titile">
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		確定シフト一覧
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		Confirmed shift
	<?php }?>
</div>

<div class="header_detail">
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		ここでは、作業予定が確定しているシフトをご確認いただけます。<br>
		（※直近の作業予定<?php echo $_smarty_tpl->tpl_vars['result_list_limit']->value;?>
件が表示されます。）
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		Here you can see the shifts that are scheduled for work.<br>
		（※The next <?php echo @constant('RESULT_LIST_LIMIT');?>
 scheduled tasks are displayed.）	
	<?php }?>
</div>

<hr><?php }
}
