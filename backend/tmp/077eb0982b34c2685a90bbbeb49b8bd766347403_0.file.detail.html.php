<?php
/* Smarty version 3.1.30, created on 2026-02-15 03:14:07
  from "/var/www/html/view/mypage/detail.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_699139ff900328_56166424',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '077eb0982b34c2685a90bbbeb49b8bd766347403' => 
    array (
      0 => '/var/www/html/view/mypage/detail.html',
      1 => 1771083969,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699139ff900328_56166424 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/var/www/html/lib/smarty/plugins/modifier.date_format.php';
?>
<div>
	
	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/mypage_detail_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	<div class="h_form">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			従事者メールアドレス
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			Employee email address
		<?php }?>
	</div>
	<div class="div_form">
		<label class="lb_form">
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				メールアドレス :
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				email address :
			<?php }?>	
		</label>
		<div class="input_form"><?php echo $_smarty_tpl->tpl_vars['user']->value->email;?>
</div>
	</div>
	<div style="width: 100%; text-align: right;">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			<b>▶︎&nbsp;メールアドレスの変更は<a href="/mypage/editEmailForm/">こちら</a></b>
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			<b>▶︎&nbsp;Click <a href="/mypage/editEmailForm/">here</a> to your email address</b>
		<?php }?>	
	</div>
	<hr>
	<div class="h_form">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			従事者情報詳細
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			Employee information details
		<?php }?>
	</div>
	<div class="div_form">
		<label class="lb_form">
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				氏名 :
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				Name :
			<?php }?>
		</label>
		<div class="input_form"><?php echo $_smarty_tpl->tpl_vars['user']->value->name;?>
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
		<div class="input_form"><?php echo $_smarty_tpl->tpl_vars['user']->value->name_kana;?>
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
		<div class="input_form"><?php echo $_smarty_tpl->tpl_vars['user']->value->tel;?>
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
		<div class="input_form"><?php echo $_smarty_tpl->tpl_vars['user']->value->gender_type_name;?>
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
		<div class="input_form">〒<?php echo $_smarty_tpl->tpl_vars['user']->value->post_code;?>
<br><?php echo $_smarty_tpl->tpl_vars['user']->value->address;?>
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
		<div class="input_form"><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['user']->value->birth_day,'%Y年%m月%d日');?>
 生（<?php echo $_smarty_tpl->tpl_vars['user']->value->age;?>
 歳）</div>
	</div>
	<div class="div_form">
		<label class="lb_form">
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				国籍 :
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				Nationality :
			<?php }?>
		</label>
		<div class="input_form"><?php echo $_smarty_tpl->tpl_vars['user']->value->country_name;?>
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
		<div class="input_form"><?php echo $_smarty_tpl->tpl_vars['user']->value->employ_type_name;?>
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
			<?php if ($_smarty_tpl->tpl_vars['user']->value->etc_company_name) {?>
				<?php echo $_smarty_tpl->tpl_vars['user']->value->etc_company_name;?>

			<?php } else { ?>
				<?php echo $_smarty_tpl->tpl_vars['user']->value->company_name;?>

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
		<div class="input_form"><?php echo $_smarty_tpl->tpl_vars['user']->value->lang_type_name;?>
</div>
	</div>
	<div style="width: 100%; text-align: right;">
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<b>▶︎&nbsp;従事者情報の変更は<a href="/mypage/editAccountForm/">こちら</a></b>
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<b>▶︎&nbsp;Click <a href="/mypage/editAccountForm/">here</a> to change employee information</b>
			<?php }?>
	</div>
	<br><br>
	<div class="div_input_button">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			<input type="button" value="マイページへ戻る" class="input_button" onclick="location.href='/mypage'">
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			<input type="button" value="Return to Mypage" class="input_button" onclick="location.href='/mypage'">
		<?php }?>
	</div>
</div><?php }
}
