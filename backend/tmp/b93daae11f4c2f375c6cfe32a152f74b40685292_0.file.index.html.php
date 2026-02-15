<?php
/* Smarty version 3.1.30, created on 2026-02-15 02:58:17
  from "/var/www/html/view/login/index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_69913649a6a754_63557730',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b93daae11f4c2f375c6cfe32a152f74b40685292' => 
    array (
      0 => '/var/www/html/view/login/index.html',
      1 => 1771124295,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69913649a6a754_63557730 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja" prefix="og: http://ogp.me/ns#">
	
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
<meta name="format-detection" content="telephone=no">
<meta name="robots" content="noindex">
<link rel="icon" href="../../image/rise.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" />
<?php echo '<script'; ?>
 src="https://code.jquery.com/jquery-3.2.1.min.js"><?php echo '</script'; ?>
>
<link rel="stylesheet" type="text/css" href="../../css/style.css" />
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
" class="site_name"><?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>Chiba Avian Flu<?php } else {
echo @constant('SITE_NAME');
}?></a></div>
	<?php if (isset($_smarty_tpl->tpl_vars['lang_types']->value)) {?>
		<details class="header-lang-toggle">
			<summary class="header-lang-button" aria-label="Language selector">
				<svg class="header-lang-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z" stroke="currentColor" stroke-width="1.8"/>
					<path d="M2.5 12H21.5" stroke="currentColor" stroke-width="1.8"/>
					<path d="M12 2C14.5 4.7 16 8.2 16 12C16 15.8 14.5 19.3 12 22" stroke="currentColor" stroke-width="1.8"/>
					<path d="M12 2C9.5 4.7 8 8.2 8 12C8 15.8 9.5 19.3 12 22" stroke="currentColor" stroke-width="1.8"/>
				</svg>
				<span class="header-lang-current">
					<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>EN<?php } else { ?>JP<?php }?>
				</span>
			</summary>
			<div class="header-lang-menu">
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['lang_types']->value, 'lang_name', false, 'lang_type');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['lang_type']->value => $_smarty_tpl->tpl_vars['lang_name']->value) {
?>
					<form method="post" action="/login/changeLang/" class="header-lang-item-form">
						<input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8', true);?>
">
						<input type="hidden" name="lang_type" value="<?php echo $_smarty_tpl->tpl_vars['lang_type']->value;?>
">
						<button type="submit" class="header-lang-item<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == $_smarty_tpl->tpl_vars['lang_type']->value) {?> is-active<?php }?>"><?php echo $_smarty_tpl->tpl_vars['lang_name']->value;?>
</button>
					</form>
				<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

			</div>
		</details>
	<?php }?>

</header>


<body class="login-page">

	
	<div class="watermark"></div>

	<div class="wrap">
		<?php $_smarty_tpl->_assignInScope('is_en', ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')));
?>

		
		<form method="post" id="login_exec" action="/login/exec/">
			<div class="div_form">
				<label class="lb_form"><?php if ($_smarty_tpl->tpl_vars['is_en']->value) {?>Email<?php } else { ?>メールアドレス<?php }?></label>
				<div class="input_form">
					<?php $_smarty_tpl->_assignInScope('input_email', '');
?>
					<?php if (isset($_POST['email'])) {?>
						<?php $_smarty_tpl->_assignInScope('input_email', $_POST['email']);
?>
					<?php }?>
					<input type="email" name="email" value="<?php echo $_smarty_tpl->tpl_vars['input_email']->value;?>
" placeholder="<?php if ($_smarty_tpl->tpl_vars['is_en']->value) {?>Email<?php } else { ?>メールアドレス<?php }?>">
				</div>
			</div>
			<div class="div_form">
				<label class="lb_form"><?php if ($_smarty_tpl->tpl_vars['is_en']->value) {?>Password<?php } else { ?>パスワード<?php }?></label>
				<div class="input_form">
					<?php $_smarty_tpl->_assignInScope('input_password', '');
?>
					<?php if (isset($_POST['password'])) {?>
						<?php $_smarty_tpl->_assignInScope('input_password', $_POST['password']);
?>
					<?php }?>
					<input type="password" name="password" value="<?php echo $_smarty_tpl->tpl_vars['input_password']->value;?>
" placeholder="<?php if ($_smarty_tpl->tpl_vars['is_en']->value) {?>Password<?php } else { ?>パスワード<?php }?>">
				</div>
			</div>
			<?php if (isset($_smarty_tpl->tpl_vars['error_message']->value)) {?>
				<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_message']->value;?>
</div>
			<?php }?>
			<br>
			<div class="div_input_button">
				<input type="hidden" name="csrf_token" value="<?php echo $_smarty_tpl->tpl_vars['csrf_token']->value;?>
">
				<input type="submit" value="<?php if ($_smarty_tpl->tpl_vars['is_en']->value) {?>Sign in<?php } else { ?>ログイン<?php }?>" class="input_button">
			</div>
		</form>
		<br>
		<div class="login-links">
			<?php if ($_smarty_tpl->tpl_vars['is_en']->value) {?>
				<a href="/account/langForm/">Sign up</a>　　
				<a href="/login/reissuePasswordForm/">Reset password</a>
			<?php } else { ?>
				<a href="/account/langForm/">新規登録</a>　　
				<a href="/login/reissuePasswordForm/">パスワードリセット</a>
			<?php }?>
		</div>

		<div class="login-note-area">
			<?php if ($_smarty_tpl->tpl_vars['is_en']->value) {?>
				This page is for avian flu control workers in Chiba prefecture. 
				You can register and check your qualifications and work schedule after signing up. 
			<?php } else { ?>
				<ul>
					<li>ここは、千葉県の鳥インフルエンザ殺処分作業の従事者サイトです。</li>
					<li>ユーザ登録すると、資格情報や作業希望日の登録／確認ができます。</li>
				</ul>
			<?php }?>
		</div>
	</div>

	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


</body>
</html><?php }
}
