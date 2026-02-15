<?php
/* Smarty version 3.1.30, created on 2026-02-15 01:41:46
  from "/var/www/html/view/top/index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_6991245aaa4d19_65505159',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '647fa083efa1b1d52bb54fd68ca14859b12770cf' => 
    array (
      0 => '/var/www/html/view/top/index.html',
      1 => 1771083970,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6991245aaa4d19_65505159 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div>
	本サービスをご利用いただき、ありがとうございます。<br>
	ご希望の操作を以下より選択してください。<br>
	<br>
	Thank you for using this service.<br>
	Please select your desired operation from the options below.<br>
	<br>
	<div class="div_input_button">
	<input type="button" value="　　ログイン / Log in 　　" class="input_button" onclick="location.href='/login/'">
	<input type="button" value="新規従事者の登録 / Rregistration" class="input_button" onclick="location.href='/account/langForm/'">
	</div>
</div><?php }
}
