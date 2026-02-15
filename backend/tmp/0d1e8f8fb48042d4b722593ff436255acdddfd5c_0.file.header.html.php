<?php
/* Smarty version 3.1.30, created on 2026-02-15 06:49:33
  from "/var/www/html/view/common/header.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_69916c7dccc829_83410420',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0d1e8f8fb48042d4b722593ff436255acdddfd5c' => 
    array (
      0 => '/var/www/html/view/common/header.html',
      1 => 1771138164,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69916c7dccc829_83410420 (Smarty_Internal_Template $_smarty_tpl) {
?>
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
	<?php $_smarty_tpl->_assignInScope('header_active_tab', (($tmp = @$_GET['tab'])===null||$tmp==='' ? 'qr' : $tmp));
?>
	<?php $_smarty_tpl->_assignInScope('header_top_title', '');
?>
	<?php if (isset($_smarty_tpl->tpl_vars['controller']->value) && 'mypage' == $_smarty_tpl->tpl_vars['controller']->value) {?>
		<?php if ('rules' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
			<?php $_smarty_tpl->_assignInScope('header_top_title', '作業ルール');
?>
		<?php } elseif ('request-shift' == $_smarty_tpl->tpl_vars['header_active_tab']->value || 'requestShiftList' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftAddForm' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftAddFinish' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftDelFinish' == $_smarty_tpl->tpl_vars['action']->value) {?>
			<?php $_smarty_tpl->_assignInScope('header_top_title', '希望シフト');
?>
		<?php } elseif ('result-shift' == $_smarty_tpl->tpl_vars['header_active_tab']->value || 'resultList' == $_smarty_tpl->tpl_vars['action']->value) {?>
			<?php $_smarty_tpl->_assignInScope('header_top_title', '確定シフト');
?>
		<?php } elseif ('query-log' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
			<?php $_smarty_tpl->_assignInScope('header_top_title', 'クエリログ');
?>
		<?php } elseif ('settings' == $_smarty_tpl->tpl_vars['header_active_tab']->value || 'detail' == $_smarty_tpl->tpl_vars['action']->value || 'uploadCertForm' == $_smarty_tpl->tpl_vars['action']->value || 'uploadCertConfirm' == $_smarty_tpl->tpl_vars['action']->value || 'uploadCertFinish' == $_smarty_tpl->tpl_vars['action']->value || 'editPasswordForm' == $_smarty_tpl->tpl_vars['action']->value || 'editPasswordConfirm' == $_smarty_tpl->tpl_vars['action']->value || 'editPasswordFinish' == $_smarty_tpl->tpl_vars['action']->value || 'resignForm' == $_smarty_tpl->tpl_vars['action']->value || 'resignFinish' == $_smarty_tpl->tpl_vars['action']->value) {?>
			<?php $_smarty_tpl->_assignInScope('header_top_title', 'アカウント設定');
?>
		<?php } else { ?>
			<?php $_smarty_tpl->_assignInScope('header_top_title', ((string)$_smarty_tpl->tpl_vars['login_user']->value->name)." (".((string)$_smarty_tpl->tpl_vars['login_user']->value->code).")");
?>
		<?php }?>
	<?php }?>

	<div class="header-main-row">
		<div class="site_name"><a href="<?php echo @constant('WEB_SRC');?>
" class="site_name"><?php if (isset($_smarty_tpl->tpl_vars['controller']->value) && 'mypage' == $_smarty_tpl->tpl_vars['controller']->value) {
echo $_smarty_tpl->tpl_vars['header_top_title']->value;
} elseif ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>Chiba Avian Flu<?php } else {
echo @constant('SITE_NAME');
}?></a></div>
		<div class="header-main-tools">
			<?php if (isset($_smarty_tpl->tpl_vars['controller']->value) && 'mypage' == $_smarty_tpl->tpl_vars['controller']->value && ('request-shift' == $_smarty_tpl->tpl_vars['header_active_tab']->value || 'requestShiftList' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftAddForm' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftAddFinish' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftDelFinish' == $_smarty_tpl->tpl_vars['action']->value)) {?>
				<div class="header-shift-legend">
					<span class="legend-chip legend-request">希望シフト</span>
					<span class="legend-chip legend-confirmed">確定シフト</span>
				</div>
			<?php }?>
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
		</div>
	</div>
	<div class="header-sub-row">
		<?php if (isset($_smarty_tpl->tpl_vars['controller']->value) && 'mypage' == $_smarty_tpl->tpl_vars['controller']->value) {?>
			<nav class="header-breadcrumb" aria-label="Breadcrumb">
				<?php if ('requestShiftAddForm' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftAddFinish' == $_smarty_tpl->tpl_vars['action']->value) {?>
					<a class="header-breadcrumb-item" href="/mypage/?tab=qr">
						<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>My Page<?php } else { ?>マイページ<?php }?>
					</a>
					<a class="header-breadcrumb-item" href="/mypage/requestShiftList/">
						<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>Desired Shift<?php } else { ?>希望シフト<?php }?>
					</a>
					<a class="header-breadcrumb-item" href="/mypage/requestShiftList/">
						<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>List<?php } else { ?>一覧<?php }?>
					</a>
					<span class="header-breadcrumb-item is-current" aria-current="page">
						<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>Register<?php } else { ?>登録<?php }?>
					</span>
				<?php } elseif ('requestShiftList' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftDelFinish' == $_smarty_tpl->tpl_vars['action']->value || 'resultList' == $_smarty_tpl->tpl_vars['action']->value) {?>
					<a class="header-breadcrumb-item" href="/mypage/?tab=qr">
						<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>My Page<?php } else { ?>マイページ<?php }?>
					</a>
					<?php if ('requestShiftList' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftDelFinish' == $_smarty_tpl->tpl_vars['action']->value) {?>
						<a class="header-breadcrumb-item" href="/mypage/?tab=request-shift">
							<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>Desired Shift<?php } else { ?>希望シフト<?php }?>
						</a>
					<?php } else { ?>
						<a class="header-breadcrumb-item" href="/mypage/?tab=result-shift">
							<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>Confirmed Shift<?php } else { ?>確定シフト<?php }?>
						</a>
					<?php }?>
					<span class="header-breadcrumb-item is-current" aria-current="page">
						<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>
							<?php if ('requestShiftList' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftDelFinish' == $_smarty_tpl->tpl_vars['action']->value) {?>
								List
							<?php } else { ?>
								Confirmed shift list page
							<?php }?>
						<?php } else { ?>
							<?php if ('requestShiftList' == $_smarty_tpl->tpl_vars['action']->value || 'requestShiftDelFinish' == $_smarty_tpl->tpl_vars['action']->value) {?>
								一覧
							<?php } else { ?>
								確定シフト一覧ページ
							<?php }?>
						<?php }?>
					</span>
				<?php } else { ?>
					<a class="header-breadcrumb-item" href="/mypage/?tab=qr">
						<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>My Page<?php } else { ?>マイページ<?php }?>
					</a>
					<span class="header-breadcrumb-item is-current" aria-current="page">
						<?php if ($_smarty_tpl->tpl_vars['selected_lang_type']->value == @constant('PARAM_CONST_LANG_TYPE_EN')) {?>
							<?php if ('rules' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								Work Rules
							<?php } elseif ('request-shift' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								Desired Shift
							<?php } elseif ('result-shift' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								Confirmed Shift
							<?php } elseif ('settings' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								Account Settings
							<?php } elseif ('query-log' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								Query Log
							<?php } else { ?>
								Punch
							<?php }?>
						<?php } else { ?>
							<?php if ('rules' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								作業ルール
							<?php } elseif ('request-shift' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								希望シフト
							<?php } elseif ('result-shift' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								確定シフト
							<?php } elseif ('settings' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								アカウント設定
							<?php } elseif ('query-log' == $_smarty_tpl->tpl_vars['header_active_tab']->value) {?>
								クエリログ
							<?php } else { ?>
								打刻
							<?php }?>
						<?php }?>
					</span>
				<?php }?>
			</nav>
		<?php }?>
	</div>

</header>
<?php }
}
