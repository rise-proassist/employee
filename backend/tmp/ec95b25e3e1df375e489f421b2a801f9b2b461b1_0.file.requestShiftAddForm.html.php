<?php
/* Smarty version 3.1.30, created on 2026-02-15 03:27:55
  from "/var/www/html/view/mypage/requestShiftAddForm.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_69913d3b8d2091_54632852',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ec95b25e3e1df375e489f421b2a801f9b2b461b1' => 
    array (
      0 => '/var/www/html/view/mypage/requestShiftAddForm.html',
      1 => 1771083970,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69913d3b8d2091_54632852 (Smarty_Internal_Template $_smarty_tpl) {
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
		作業が可能な希望日時を入力してください。
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		Please enter the desired date and time that you are available to work.
	<?php }?>
	<br>
	<span class="caution">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			※希望シフトが定員に達した場合は参加できなくなります。より多い選択肢を入力してください。
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			※If your desired shift is full, you will not be able to participate. Please enter more options.
		<?php }?>
	</span>
	<br>
	<form method="post" action="/mypage/requestShiftAddFinish/">
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					希望日時（開始）
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Desired date and time (start)
				<?php }?>
			</label>
			<div class="input_form">
				<?php $_smarty_tpl->_assignInScope('input_shift_date_from', '');
?>
				<?php if (isset($_POST['shift_date_from'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_shift_date_from', $_POST['shift_date_from']);
?>
				<?php }?>
				<input type="date" name="shift_date_from" value="<?php echo $_smarty_tpl->tpl_vars['input_shift_date_from']->value;?>
" style="width: 200px;">
					<select name="shift_time_from" style="width: 150px;">
						<?php
$__section_from_hour_0_saved = isset($_smarty_tpl->tpl_vars['__smarty_section_from_hour']) ? $_smarty_tpl->tpl_vars['__smarty_section_from_hour'] : false;
$_smarty_tpl->tpl_vars['__smarty_section_from_hour'] = new Smarty_Variable(array());
if (true) {
for ($__section_from_hour_0_iteration = 1, $_smarty_tpl->tpl_vars['__smarty_section_from_hour']->value['index'] = 0; $__section_from_hour_0_iteration <= 24; $__section_from_hour_0_iteration++, $_smarty_tpl->tpl_vars['__smarty_section_from_hour']->value['index']++){
?>
							<option value="<?php echo (isset($_smarty_tpl->tpl_vars['__smarty_section_from_hour']->value['index']) ? $_smarty_tpl->tpl_vars['__smarty_section_from_hour']->value['index'] : null);?>
:00" checked=""><?php echo (isset($_smarty_tpl->tpl_vars['__smarty_section_from_hour']->value['index']) ? $_smarty_tpl->tpl_vars['__smarty_section_from_hour']->value['index'] : null);?>
:00</option>
						<?php
}
}
if ($__section_from_hour_0_saved) {
$_smarty_tpl->tpl_vars['__smarty_section_from_hour'] = $__section_from_hour_0_saved;
}
?>
					</select>
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['shift_date_from'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['shift_date_from'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					希望日時（終了）
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Desired date and time (end)
				<?php }?>
			</label>
			<div class="input_form">
				<?php $_smarty_tpl->_assignInScope('input_shift_date_to', '');
?>
				<?php if (isset($_POST['shift_date_to'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_shift_date_to', $_POST['shift_date_to']);
?>
				<?php }?>
				<input type="date" name="shift_date_to" value="<?php echo $_smarty_tpl->tpl_vars['input_shift_date_to']->value;?>
" style="width: 200px;">
					<select name="shift_time_to" style="width: 150px;">
						<?php
$__section_to_hour_1_saved = isset($_smarty_tpl->tpl_vars['__smarty_section_to_hour']) ? $_smarty_tpl->tpl_vars['__smarty_section_to_hour'] : false;
$_smarty_tpl->tpl_vars['__smarty_section_to_hour'] = new Smarty_Variable(array());
if (true) {
for ($__section_to_hour_1_iteration = 1, $_smarty_tpl->tpl_vars['__smarty_section_to_hour']->value['index'] = 0; $__section_to_hour_1_iteration <= 24; $__section_to_hour_1_iteration++, $_smarty_tpl->tpl_vars['__smarty_section_to_hour']->value['index']++){
?>
							<option value="<?php echo (isset($_smarty_tpl->tpl_vars['__smarty_section_to_hour']->value['index']) ? $_smarty_tpl->tpl_vars['__smarty_section_to_hour']->value['index'] : null);?>
:00" checked=""><?php echo (isset($_smarty_tpl->tpl_vars['__smarty_section_to_hour']->value['index']) ? $_smarty_tpl->tpl_vars['__smarty_section_to_hour']->value['index'] : null);?>
:00</option>
						<?php
}
}
if ($__section_to_hour_1_saved) {
$_smarty_tpl->tpl_vars['__smarty_section_to_hour'] = $__section_to_hour_1_saved;
}
?>
					</select>
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['shift_date_to'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['shift_date_to'];?>
</div>
				<?php }?>
			</div>
		</div>
		<br>
		<div class="div_input_button">
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<input type="submit" value="　登 録 す る 　" class="input_button">
				<input type="button" value="マイページへ戻る" class="input_button" onclick="location.href='/mypage/'">
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<input type="submit" value="　　　Register　　" class="input_button">
				<input type="button" value="Return to Mypage" class="input_button" onclick="location.href='/mypage/'">
			<?php }?>
		</div>

	</form>

</div><?php }
}
