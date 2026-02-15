<?php
/* Smarty version 3.1.30, created on 2026-02-15 05:14:12
  from "/var/www/html/view/error/index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_69915624259580_40620123',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8687fa102b9147eb5991e0884a47c91aaa4791ea' => 
    array (
      0 => '/var/www/html/view/error/index.html',
      1 => 1771083969,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69915624259580_40620123 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div>
	<div class="page_titile">エラー</div>
	<hr>
	<?php if (isset($_smarty_tpl->tpl_vars['error_message']->value)) {?>

		<br /><?php echo $_smarty_tpl->tpl_vars['error_message']->value;?>
 
		
	<?php } else { ?>

		リクエストされたページは存在しません。

	<?php }?>

</div><?php }
}
