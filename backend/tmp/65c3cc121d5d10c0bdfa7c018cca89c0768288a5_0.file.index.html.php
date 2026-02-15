<?php
/* Smarty version 3.1.30, created on 2026-02-15 01:41:46
  from "/var/www/html/view/index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_6991245a976907_85313099',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '65c3cc121d5d10c0bdfa7c018cca89c0768288a5' => 
    array (
      0 => '/var/www/html/view/index.html',
      1 => 1771083969,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6991245a976907_85313099 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja" prefix="og: http://ogp.me/ns#">
	
	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	<body>

		
		<div class="watermark"></div>

		<div class="wrap">

			
			<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/".((string)$_smarty_tpl->tpl_vars['controller']->value)."/".((string)$_smarty_tpl->tpl_vars['action']->value).".html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


		</div>

		
		<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	</body>

</html><?php }
}
