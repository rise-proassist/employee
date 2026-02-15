<?php
/* Smarty version 3.1.30, created on 2026-02-15 06:49:33
  from "/var/www/html/view/mypage/index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_69916c7ddfdf91_62515613',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9b5f2598c9f8fd0191acaa5751f1362ceeac22cf' => 
    array (
      0 => '/var/www/html/view/mypage/index.html',
      1 => 1771138007,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69916c7ddfdf91_62515613 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div>
	<?php $_smarty_tpl->_assignInScope('active_tab', (($tmp = @$_GET['tab'])===null||$tmp==='' ? 'qr' : $tmp));
?>

	
	<div class="mypage-tab-content <?php if ('qr' != $_smarty_tpl->tpl_vars['active_tab']->value) {?>is-hidden<?php }?>" id="qr-section">
	<div class="div_qr_code">
		<?php if (@constant('PARAM_CONST_USER_APPROVE_TYPE_PASSED') == $_smarty_tpl->tpl_vars['login_user']->value->approve_type) {?>
			<?php if (@constant('IS_DISPLAY_MEMBER_CODE_QR')) {?>
				<img src="<?php echo UtilCommon::get_base_url('api','generateQr');?>
" class="qr_code">
				<div><?php echo $_smarty_tpl->tpl_vars['login_user']->value->code;?>
</div>
			<?php }?>

			<?php if (@constant('IS_DISPLAY_MEMBER_CODE_BAR')) {?>
				<img src="<?php echo UtilCommon::get_base_url('api','generateBar');?>
" class="qr_code">
				<div><?php echo $_smarty_tpl->tpl_vars['login_user']->value->code;?>
</div>
			<?php }?>

		<?php } elseif (@constant('PARAM_CONST_USER_APPROVE_TYPE_WAIT') == $_smarty_tpl->tpl_vars['login_user']->value->approve_type) {?>
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<span class="notice_s">承認待ち</span>
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<span class="notice_s">Pending</span>
			<?php }?>
		<?php } else { ?>
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<span class="notice_s">承認見送り</span>
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				<span class="notice_s">Declined</span>
			<?php }?>
		<?php }?>
	</div>
	<div>
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		<ul class="menu">
			<li><p>作業の持ち物</p></li>
			<li><p>着替え（汗をかく場合があります。非常に寒くなる場合があります。）</p></li>
			<li><p>軽食・飲み物（すぐに食べられる軽食を持参してください。）</p></li>
			<li><p>携帯電話・スマートフォン（緊急時の連絡が取れるように持参してください。）</p></li>
			<li><p>免許書・資格証・外国人登録証（必ず持参してください。）</p></li>
			<li><p>作業参加のルール</p></li>
			<li><p>遅刻厳禁（バスが行ってしまいます。）</p></li>
			<li><p>発熱がある場合はお帰りいただきます。</p></li>
			<li><p>ルールや決まり事を遵守できない場合は退場となります。</p></li>
			<li><p>怠けている人には、リーダーの権限により退場していただきます。出入り禁止。</p></li>
			<li><p>県庁職員さんや従事者同士での暴言、暴力は即退場、出入り禁止になります。</p></li>
			<li><p>所定場所以外での喫煙は厳禁です。</p></li>
			<li><p>貴重品はSSや農場内へ持ち込まないでください。一切保証できません。</p></li>
			<li><p>駐車場内の事故や盗難におきましては一切責任を負えません。</p></li>
			<li><p>車両や機械の運転は安全を最も重視して行ってください。</p></li>
		</ul>
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		<ul class="menu">
			<li><p>What to bring to work</p></li>
			<li><p>Change of clothes (You may sweat. It may get very cold.)</p></li>
			<li><p>Snacks and drinks (Please bring snacks that you can eat quickly.)</p></li>
			<li><p>Cell phone/smartphone (Please be sure to bring it with you as a means of emergency contact.)</p></li>
			<li><p>Driver's license/certificate of qualification/alien registration card (Please be sure to bring it with you.)</p></li>
			<li><p>Rules for participating in work</p></li>
			<li><p>Being late is strictly prohibited or the bus will leave.</p></li>
			<li><p>If you have a fever, you will be asked to leave.</p></li>
			<li><p>If you do not follow the rules and regulations, you will be asked to leave.</p></li>
			<li><p>Anyone who is lazy will be asked to leave by the leader. No entry or exit.</p></li>
			<li><p>Abusive language or violence between prefectural government employees or other workers will result in immediate expulsion and a ban on entry.</p></li>
			<li><p>Smoking is strictly prohibited outside designated areas.</p></li>
			<li><p>Please do not bring valuables into the service station or farm. We cannot guarantee their safety.</p></li>
			<li><p>We cannot be held responsible for any accidents or thefts that occur in the parking lot.</p></li>
			<li><p>Please always prioritize safety when operating vehicles or machinery.</p></li>
		</ul>
	<?php }?>
	</div>
	</div>
	<div class="mypage-tab-content <?php if ('request-shift' != $_smarty_tpl->tpl_vars['active_tab']->value) {?>is-hidden<?php }?>" id="request-shift-section">
	<ul class="menu">
		<li>
			<a href="/mypage/requestShiftAddForm/">
				<p>
					<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						登録
					<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						Register
					<?php }?>
				</p>
			</a>
		</li>
		<li>
			<a href="/mypage/requestShiftList/">
				<p>
					<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						一覧
					<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						List
					<?php }?>
				</p>
			</a>
		</li>
	</ul>
	</div>
	<div class="mypage-tab-content <?php if ('settings' != $_smarty_tpl->tpl_vars['active_tab']->value) {?>is-hidden<?php }?>" id="setting-section">
	<ul class="menu">
		<li>
			<a href="/mypage/detail/">
				<p>
					<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						従事者情報詳細
					<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						Information details
					<?php }?>
				</p>
			</a>
		</li>
		<li>
			<a href="/mypage/uploadCertForm/">
				<p>
					<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						資格証アップロード
					<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						Upload certificate
					<?php }?>
				</p>
			</a>
		</li>
		<li>
			<a href="/mypage/editPasswordForm/">
				<p>
					<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						パスワードの変更
					<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						Change password
					<?php }?>
				</p>
			</a>
		</li>
		<li>
			<a href="/login/logout/">
				<p>
					<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						ログアウト
					<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						Logout
					<?php }?>
				</p>
			</a>
		</li>
		<li>
			<a href="/mypage/resignForm/">
				<p>
					<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						退会
					<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						Resign
					<?php }?>
				</p>
			</a>
		</li>
	</ul>
	</div>
	<?php echo '<script'; ?>
>
		document.addEventListener('DOMContentLoaded', function() {
			var settingSection = document.getElementById('setting-section');
			if (!settingSection || settingSection.classList.contains('is-hidden')) {
				return;
			}

			var links = settingSection.querySelectorAll('a[href]');
			for (var i = 0; i < links.length; i++) {
				links[i].addEventListener('click', function(event) {
					if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
						return;
					}

					event.preventDefault();
					var href = this.getAttribute('href');
					if (!href) {
						return;
					}

					settingSection.classList.add('is-push-transition');
					setTimeout(function() {
						window.location.href = href;
					}, 220);
				});
			}
		});
	<?php echo '</script'; ?>
>
	<div class="mypage-tab-content <?php if ('query-log' != $_smarty_tpl->tpl_vars['active_tab']->value) {?>is-hidden<?php }?>" id="query-log-section">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
		<?php echo '<script'; ?>
 src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js" defer><?php echo '</script'; ?>
>
		<?php echo '<script'; ?>
>
			document.addEventListener('DOMContentLoaded', function() {
				if (window.hljs) {
					window.hljs.highlightAll();
				}
			});
		<?php echo '</script'; ?>
>
		<div class="query-log-panel">
			<?php if ($_smarty_tpl->tpl_vars['query_logs']->value) {?>
				<pre class="query-log-textbox"><code class="language-sql"><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['query_logs']->value, 'query_log');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['query_log']->value) {
echo htmlspecialchars($_smarty_tpl->tpl_vars['query_log']->value, ENT_QUOTES, 'UTF-8', true);?>


<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>
</code></pre>
			<?php } else { ?>
				<div class="query-log-empty">
					<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						クエリログが見つかりません。
					<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
						No query logs found.
					<?php }?>
				</div>
			<?php }?>
		</div>
	</div>

</div><?php }
}
