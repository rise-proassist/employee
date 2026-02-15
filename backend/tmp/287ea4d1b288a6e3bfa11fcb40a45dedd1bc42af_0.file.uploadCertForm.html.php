<?php
/* Smarty version 3.1.30, created on 2026-02-15 06:43:40
  from "/var/www/html/view/mypage/uploadCertForm.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_69916b1cdaadb9_53468749',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '287ea4d1b288a6e3bfa11fcb40a45dedd1bc42af' => 
    array (
      0 => '/var/www/html/view/mypage/uploadCertForm.html',
      1 => 1771083970,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69916b1cdaadb9_53468749 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div>
	
	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/mypage_detail_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	<div class="h_form">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			資格証アップロード
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			Upload certificate
		<?php }?>
	</div>
	<br>
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		資格証をアップロードしてください。<br>
		なお、既にご提出いただいている資格証は、アップロードできません。
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		Please upload your qualifications.<br>
		Please note that qualifications that have already been submitted cannot be uploaded.
	<?php }?>
	<br>
	<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['file'])) {?>
		<br>
		<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['file'];?>
</div>
	<?php }?>
	<form method="post" action="/mypage/uploadCertConfirm/" enctype="multipart/form-data">

		<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['certs']->value, 'cert');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['cert']->value) {
?>
			<div class="div_form">
				<label class="lb_form"><?php echo $_smarty_tpl->tpl_vars['cert']->value->name;?>
</label>
				<div class="input_form">
					<?php if (!$_smarty_tpl->tpl_vars['cert']->value->is_uploaded) {?>
						<ul>
							<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
								<li>表 : <input type="file" name="file_<?php echo $_smarty_tpl->tpl_vars['cert']->value->id;?>
_1" accept="image/*" values="" multiple></li>
								<li>裏 : <input type="file" name="file_<?php echo $_smarty_tpl->tpl_vars['cert']->value->id;?>
_2" accept="image/*" multiple></li>
							<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
								<li>Front : <input type="file" name="file_<?php echo $_smarty_tpl->tpl_vars['cert']->value->id;?>
_1" accept="image/*" values="" multiple></li>
								<li>Back : <input type="file" name="file_<?php echo $_smarty_tpl->tpl_vars['cert']->value->id;?>
_2" accept="image/*" multiple></li>
							<?php }?>
						</ul>
					<?php } else { ?>
						<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
							提出済み
						<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
							Submitted
						<?php }?>
					<?php }?>
				</div>
			</div>
		<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

		<br>
		<div class="div_input_button">
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<input type="submit" value="　確認画面へ 　" class="input_button">
				<input type="button" value="マイページへ戻る" class="input_button" onclick="location.href='/mypage/'">
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<input type="submit" value="　　Confirm　　" class="input_button">
				<input type="button" value="Return to Mypage" class="input_button" onclick="location.href='/mypage/'">
			<?php }?>
		</div>

	</form>

</div><?php }
}
