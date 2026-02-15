<?php
/* Smarty version 3.1.30, created on 2026-02-15 03:44:55
  from "/var/www/html/view/mypage/resultList.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_699141379a54c0_35413697',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fe827f1f5b06e525ca30996d0766d48c5516046c' => 
    array (
      0 => '/var/www/html/view/mypage/resultList.html',
      1 => 1771127093,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699141379a54c0_35413697 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/var/www/html/lib/smarty/plugins/modifier.date_format.php';
?>
<div>
	
	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/mypage_result_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	<form method="post" action="<?php echo UtilCommon::get_base_url('mypage','forcastFinish');?>
">
		<div class="div_form">
			
			<?php if ($_smarty_tpl->tpl_vars['location_assign_users']->value) {?>
				<table class="input_list">
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['location_assign_users']->value, 'location_assign_user');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['location_assign_user']->value) {
?>
						<tr>
							<td class="left">
								<div class="h_form"><?php echo $_smarty_tpl->tpl_vars['location_assign_user']->value->location_name;?>
</div><br>
								<?php echo $_smarty_tpl->tpl_vars['location_assign_user']->value->location_address;?>
<br>
								<?php echo $_smarty_tpl->tpl_vars['location_assign_user']->value->location_shift_name;?>
<br>
								<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
									<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['location_assign_user']->value->shift_date_from,"%Y年%m月%d日 %-H:%M");?>
 - <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['location_assign_user']->value->shift_date_to,"%Y年%m月%d日 %-H:%M");?>

								<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
									<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['location_assign_user']->value->shift_date_from);?>
 <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['location_assign_user']->value->shift_date_from,"%-H:%M");?>
 - 
									<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['location_assign_user']->value->shift_date_to);?>
 <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['location_assign_user']->value->shift_date_to,"%-H:%M");?>

								<?php }?>
							</td>
						</tr>
					<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				</table>
			<?php } else { ?>
				確定シフトはございません。
			<?php }?>
		</div>

	</form>

</div><?php }
}
