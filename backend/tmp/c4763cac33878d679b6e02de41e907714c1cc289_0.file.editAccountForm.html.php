<?php
/* Smarty version 3.1.30, created on 2026-02-15 06:43:24
  from "/var/www/html/view/mypage/editAccountForm.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_69916b0cba5544_57282157',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c4763cac33878d679b6e02de41e907714c1cc289' => 
    array (
      0 => '/var/www/html/view/mypage/editAccountForm.html',
      1 => 1771083969,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69916b0cba5544_57282157 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div>
	
	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/mypage_detail_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	<div class="h_form">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			従事者情報変更
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			Change information
		<?php }?>
	</div>
	<br>
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			従事者情報を入力してください。
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			Please enter information.
		<?php }?>
	<br>
	<form method="post" action="/mypage/editAccountConfirm/">
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					氏名 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Name :
				<?php }?>
			</label>
			<div class="input_form">
				<?php if (isset($_POST['name'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_name', $_POST['name']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_name', $_smarty_tpl->tpl_vars['user']->value->name);
?>
				<?php }?>
				<input type="text" name="name" value="<?php echo $_smarty_tpl->tpl_vars['input_name']->value;?>
">
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['name'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['name'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					ふりがな :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Furigana :
				<?php }?>
			</label>
			<div class="input_form">
				<?php if (isset($_POST['name_kana'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_name_kana', $_POST['name_kana']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_name_kana', $_smarty_tpl->tpl_vars['user']->value->name_kana);
?>
				<?php }?>
				<input type="text" name="name_kana" value="<?php echo $_smarty_tpl->tpl_vars['input_name_kana']->value;?>
">
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['name_kana'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['name_kana'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					電話番号 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Telephone number :
				<?php }?>
			</label>
			<div class="input_form">
				<?php if (isset($_POST['tel'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_tel', $_POST['tel']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_tel', $_smarty_tpl->tpl_vars['user']->value->tel);
?>
				<?php }?>
				<input type="text" name="tel" value="<?php echo $_smarty_tpl->tpl_vars['input_tel']->value;?>
">
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['tel'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['tel'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					郵便番号 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Post code :
				<?php }?>
			</label>
			<div class="input_form">
				<?php if (isset($_POST['post_code'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_post_code', $_POST['post_code']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_post_code', $_smarty_tpl->tpl_vars['user']->value->post_code);
?>
				<?php }?>
				<input type="text" name="post_code" value="<?php echo $_smarty_tpl->tpl_vars['input_post_code']->value;?>
" placeholder="※000-0000の場合は、0000000">
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['post_code'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['post_code'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					住所 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Address :
				<?php }?>
			</label>
			<div class="input_form">
				<?php if (isset($_POST['address'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_address', $_POST['address']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_address', $_smarty_tpl->tpl_vars['user']->value->address);
?>
				<?php }?>
				<input type="text" name="address" value="<?php echo $_smarty_tpl->tpl_vars['input_address']->value;?>
">
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['address'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['address'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					性別 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Sex :
				<?php }?>
			</label>
			<div class="input_form">
				<?php if (isset($_POST['gender_type'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_gender_type', $_POST['gender_type']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_gender_type', $_smarty_tpl->tpl_vars['user']->value->gender_type);
?>
				<?php }?>
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['gender_types']->value, 'gender_type_name', false, 'gender_type');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['gender_type']->value => $_smarty_tpl->tpl_vars['gender_type_name']->value) {
?>
					<label><input type="radio" name="gender_type" value="<?php echo $_smarty_tpl->tpl_vars['gender_type']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['input_gender_type']->value == $_smarty_tpl->tpl_vars['gender_type']->value) {?>checked<?php }?>>&nbsp;<?php echo $_smarty_tpl->tpl_vars['gender_type_name']->value;?>
</label><br>
				<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['gender_id'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['gender_id'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					生年月日 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Birthday :
				<?php }?>
			</label>
			<div class="input_form">
				<?php if (isset($_POST['birth_day'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_birth_day', $_POST['birth_day']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_birth_day', $_smarty_tpl->tpl_vars['user']->value->birth_day);
?>
				<?php }?>
				<input type="date" name="birth_day" value="<?php echo $_smarty_tpl->tpl_vars['input_birth_day']->value;?>
">
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['birth_day'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['birth_day'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					国籍 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Nationality :
				<?php }?>
			</label>
			<div class="input_form">
				<?php $_smarty_tpl->_assignInScope('input_country_id', '');
?>
				<?php if (isset($_POST['country_id'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_country_id', $_POST['country_id']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_country_id', $_smarty_tpl->tpl_vars['user']->value->country_id);
?>
				<?php }?>
				<select name="country_id">
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['countries']->value, 'country');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['country']->value) {
?>
						<option value="<?php echo $_smarty_tpl->tpl_vars['country']->value->id;?>
" <?php if ($_smarty_tpl->tpl_vars['input_country_id']->value == $_smarty_tpl->tpl_vars['country']->value->id) {?>checked<?php }?>><?php echo $_smarty_tpl->tpl_vars['country']->value->name;?>
</option>
					<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				</select>
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['country_id'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['country_id'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					雇用形態 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Employment type :
				<?php }?>
			</label>
			<div class="input_form">
				<?php $_smarty_tpl->_assignInScope('input_cont_id', '');
?>
				<?php if (isset($_POST['employ_type'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_employ_type', $_POST['employ_type']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_employ_type', $_smarty_tpl->tpl_vars['user']->value->employ_type);
?>
				<?php }?>
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['employ_types']->value, 'employ_type_name', false, 'employ_type');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['employ_type']->value => $_smarty_tpl->tpl_vars['employ_type_name']->value) {
?>
					<label><input type="radio" name="employ_type" value="<?php echo $_smarty_tpl->tpl_vars['employ_type']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['input_employ_type']->value == $_smarty_tpl->tpl_vars['employ_type']->value) {?>checked<?php }?>>&nbsp;<?php echo $_smarty_tpl->tpl_vars['employ_type_name']->value;?>
</label><br>
				<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['employ_type'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['employ_type'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					所属会社 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Affiliated company :
				<?php }?>
			</label>
			<div class="input_form">
				<?php $_smarty_tpl->_assignInScope('input_company_id', '');
?>
				<?php if (isset($_POST['company_id'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_company_id', $_POST['company_id']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_company_id', $_smarty_tpl->tpl_vars['user']->value->company_id);
?>
				<?php }?>
				<select name="company_id">
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['companies']->value, 'company');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['company']->value) {
?>
						<option value="<?php echo $_smarty_tpl->tpl_vars['company']->value->id;?>
" <?php if ($_smarty_tpl->tpl_vars['input_company_id']->value == $_smarty_tpl->tpl_vars['company']->value->id) {?>checked<?php }?>><?php echo $_smarty_tpl->tpl_vars['company']->value->name;?>
</option>
					<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					<option value="0" <?php if ($_smarty_tpl->tpl_vars['input_company_id']->value == 0) {?>selected<?php }?> >その他</option>
				</select>
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['company_id'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['company_id'];?>
</div>
				<?php }?>
			</div>
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				※「その他」を選んだ場合のみ、ご入力ください。
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				※Please enter only if you select 「Other」.
			<?php }?>
			<div class="input_form">
				<?php if (isset($_POST['etc_company_name'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_etc_company_name', $_POST['etc_company_name']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_etc_company_name', $_smarty_tpl->tpl_vars['user']->value->etc_company_name);
?>
				<?php }?>
				<input type="text" name="etc_company_name" value="<?php echo $_smarty_tpl->tpl_vars['input_etc_company_name']->value;?>
">
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['etc_company_name'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['etc_company_name'];?>
</div>
				<?php }?>
			</div>
		</div>
		<div class="div_form">
			<label class="lb_form">
				<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					言語 :
				<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
					Language :
				<?php }?>
			</label>
			<div class="input_form">
				<?php $_smarty_tpl->_assignInScope('input_lang_type', '');
?>
				<?php if (isset($_POST['lang_type'])) {?>
					<?php $_smarty_tpl->_assignInScope('input_lang_type', $_POST['lang_type']);
?>
				<?php } else { ?>
					<?php $_smarty_tpl->_assignInScope('input_lang_type', $_smarty_tpl->tpl_vars['user']->value->lang_type);
?>
				<?php }?>
				<select name="lang_type">
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['lang_types']->value, 'lang_type_name', false, 'lang_type');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['lang_type']->value => $_smarty_tpl->tpl_vars['lang_type_name']->value) {
?>
						<option value="<?php echo $_smarty_tpl->tpl_vars['lang_type']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['input_lang_type']->value == $_smarty_tpl->tpl_vars['lang_type']->value) {?>selected<?php }?>><?php echo $_smarty_tpl->tpl_vars['lang_type_name']->value;?>
</option>
					<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				</select>
				<?php if (isset($_smarty_tpl->tpl_vars['error_messages']->value['lang_type'])) {?>
					<div class="alert"><?php echo $_smarty_tpl->tpl_vars['error_messages']->value['lang_type'];?>
</div>
				<?php }?>
			</div>
		</div>
		<br><br>
		<div class="div_input_button">
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<input type="submit" value="　確認画面へ　" class="input_button">
				<input type="button" value="従事者情報へ戻る" class="input_button" onclick="location.href='/mypage/detail/'">
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<input type="submit" value="　　　Confirm　　" class="input_button">
				<input type="button" value="Return to information" class="input_button" onclick="location.href='/mypage/detail/'">
			<?php }?>
		</div>
	</form>
</div><?php }
}
