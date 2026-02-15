<?php
/* Smarty version 3.1.30, created on 2026-02-15 01:41:46
  from "/var/www/html/view/common/header.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_6991245aa86ac5_03177986',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0d1e8f8fb48042d4b722593ff436255acdddfd5c' => 
    array (
      0 => '/var/www/html/view/common/header.html',
      1 => 1771083969,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6991245aa86ac5_03177986 (Smarty_Internal_Template $_smarty_tpl) {
?>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
<meta name="format-detection" content="telephone=no">
<meta name="robots" content="noindex">
<link rel="icon" href="../../image/rise.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100&display=swap" />
<?php echo '<script'; ?>
 src="https://code.jquery.com/jquery-3.2.1.min.js"><?php echo '</script'; ?>
>
<link rel="stylesheet" type="text/css" href="/../../css/style.css" />
<link rel="stylesheet" type="text/css" media="screen,print" href="../../css/style_watermark.css" />

<title>
	<?php if (isset($_smarty_tpl->tpl_vars['site_title']->value)) {?>
		<?php echo $_smarty_tpl->tpl_vars['site_title']->value;?>

	<?php } else { ?>
		<?php echo @constant('SITE_NAME');?>

	<?php }?>
</title>

<header>

	<div class="site_name"><a href="<?php echo @constant('WEB_SRC');?>
" class="site_name"><?php echo @constant('SITE_NAME');?>
</a></div>

</header>
<?php }
}
