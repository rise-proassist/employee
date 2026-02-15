<?php
/* Smarty version 3.1.30, created on 2026-02-15 06:01:40
  from "/var/www/html/view/mypage/requestShiftList.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_6991614478ce79_58819104',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b3d31cc2a9d3a8f226bae6817daebd31e3b00c4c' => 
    array (
      0 => '/var/www/html/view/mypage/requestShiftList.html',
      1 => 1771135297,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6991614478ce79_58819104 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div>
	<?php echo '<script'; ?>
 src="https://unpkg.com/sweetalert/dist/sweetalert.min.js" defer><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 type="text/javascript">
	$(function() {
		var isDragging = false;
		var dragStartDate = null;
		var $popupOverlay = $('.shift-popup-overlay');
		var $startSlider = $('#shift-start-hour');
		var $endSlider = $('#shift-end-hour');
		var $timeRange = $('.shift-popup-time-range');
		var $startTimeLabel = $('.shift-popup-handle-label.is-start');
		var $endTimeLabel = $('.shift-popup-handle-label.is-end');
		var $targetDateList = $('.shift-popup-target-date-list');
		var $overwriteWarning = $('.shift-popup-overwrite-warning');
		var $popupOkButton = $('.shift-popup-ok');

		var collectRegisteredRanges = function(dateKey) {
			var ranges = [];
			var $targetCell = $('.calendar-day-cell[data-date="' + dateKey + '"]').first();

			$targetCell.find('.calendar-shift-item').each(function() {
				var startHour = parseInt($(this).attr('data-start-hour'), 10);
				var endHour = parseInt($(this).attr('data-end-hour'), 10);

				if (!isNaN(startHour) && !isNaN(endHour) && endHour >= startHour) {
					ranges.push({
						start: startHour,
						end: endHour
					});
				}
			});

			return ranges;
		};

		var hasRequestedShift = function(dateKey) {
			var $targetCell = $('.calendar-day-cell[data-date="' + dateKey + '"]').first();
			return $targetCell.find('.calendar-shift-item.is-request').length > 0;
		};

		var updateShiftPopupPreview = function() {
			var startHour = parseInt($startSlider.val(), 10);
			var endHour = parseInt($endSlider.val(), 10);

			if (startHour > endHour) {
				startHour = endHour;
				$startSlider.val(startHour);
			}

			var startLabel = ('0' + startHour).slice(-2);
			var endLabel = ('0' + endHour).slice(-2);
			$startTimeLabel.text(startLabel + ':00');
			$endTimeLabel.text(endLabel + ':00');

			var leftPercent = (startHour / 24) * 100;
			var widthPercent = ((endHour - startHour) / 24) * 100;
			$timeRange.css({
				left: leftPercent + '%',
				width: (widthPercent > 0 ? widthPercent : 1.5) + '%'
			});

			$('.shift-popup-target-date-range').css({
				left: leftPercent + '%',
				width: (widthPercent > 0 ? widthPercent : 1.5) + '%'
			});

			$startTimeLabel.css('left', leftPercent + '%');
			$endTimeLabel.css('left', (endHour / 24) * 100 + '%');
		};

		var openShiftPopup = function(dateKey) {
			var $selectedCells = $('.calendar-day-cell.is-selected');
			var targetDates = [];

			if ($selectedCells.length > 1 && $selectedCells.filter('[data-date="' + dateKey + '"]').length) {
				$selectedCells.each(function() {
					targetDates.push($(this).data('date'));
				});
			} else {
				targetDates.push(dateKey);
			}

			targetDates.sort();

			$targetDateList.empty();
			$.each(targetDates, function(_, targetDate) {
				var formattedDate = String(targetDate).replace(/-/g, '/');
				var $targetDateItem = $('<div class="shift-popup-target-date-item">');
				var $targetDateBar = $('<div class="shift-popup-target-date-bar"><div class="shift-popup-target-date-range"></div></div>');

				$.each(collectRegisteredRanges(targetDate), function(_, range) {
					var leftPercent = (range.start / 24) * 100;
					var widthPercent = ((range.end - range.start) / 24) * 100;
					$targetDateBar.append(
						$('<div class="shift-popup-target-date-existing-range">').css({
							left: leftPercent + '%',
							width: (widthPercent > 0 ? widthPercent : 1.5) + '%'
						})
					);
				});

				$targetDateItem.append(
					$('<div class="shift-popup-target-date-text">').text(formattedDate)
				);
				$targetDateItem.append(
					$targetDateBar
				);
				$targetDateList.append(
					$targetDateItem
				);
			});

			var shouldShowWarning = false;
			$.each(targetDates, function(_, targetDate) {
				if (hasRequestedShift(targetDate)) {
					shouldShowWarning = true;
					return false;
				}
			});
			$overwriteWarning.toggle(shouldShowWarning);

			$popupOverlay
				.attr('data-date', dateKey)
				.attr('data-dates', targetDates.join(','))
				.addClass('is-open');
			$popupOkButton.prop('disabled', false);
			$startSlider.val(9);
			$endSlider.val(17);
			updateShiftPopupPreview();
		};

		var closeShiftPopup = function() {
			$popupOverlay.removeClass('is-open');
		};

		var removeShiftItemFromView = function($button) {
			var $shiftItem = $button.closest('.calendar-shift-item');
			var $shiftList = $shiftItem.closest('.calendar-shift-list');
			$shiftItem.remove();

			if ($shiftList.find('.calendar-shift-item').length === 0) {
				$shiftList.remove();
			}
		};

		var deleteShiftButton = function($button) {
			var deleteUrl = $button.data('deleteUrl');
			if (!deleteUrl || $button.prop('disabled')) {
				return $.Deferred().resolve().promise();
			}

			$button.prop('disabled', true);

			return $.ajax({
				url: deleteUrl,
				type: 'GET'
			}).done(function() {
				removeShiftItemFromView($button);
			}).fail(function() {
				$button.prop('disabled', false);
			});
		};

		var getPopupTargetDates = function() {
			var dates = String($popupOverlay.attr('data-dates') || '').split(',');
			return $.grep(dates, function(dateKey) {
				return dateKey;
			});
		};

		var registerShiftForDate = function(dateKey) {
			var startHour = parseInt($startSlider.val(), 10);
			var endHour = parseInt($endSlider.val(), 10);

			return $.ajax({
				url: '/mypage/requestShiftAddFinish/',
				type: 'POST',
				dataType: 'html',
				data: {
					shift_date_from: dateKey,
					shift_time_from: startHour + ':00',
					shift_date_to: dateKey,
					shift_time_to: endHour + ':00'
				}
			});
		};

		var extractRegisterErrorMessage = function(responseText) {
			var $response = $('<div>').html(responseText || '');
			var $alert = $response.find('.alert').first();

			if ($alert.length) {
				return $.trim($alert.text());
			}

			return '';
		};

		var isRegisterSuccessResponse = function(responseText) {
			if (!responseText) {
				return false;
			}

			return responseText.indexOf('希望シフトを登録しました') !== -1 ||
				responseText.indexOf('Your desired shift has been registered') !== -1;
		};

		var registerShiftsSequentially = function(targetDates) {
			var deferred = $.Deferred();
			var index = 0;

			var next = function() {
				if (index >= targetDates.length) {
					deferred.resolve();
					return;
				}

				registerShiftForDate(targetDates[index])
					.done(function(responseText) {
						if (!isRegisterSuccessResponse(responseText)) {
							deferred.reject(extractRegisterErrorMessage(responseText));
							return;
						}

						index += 1;
						next();
					})
					.fail(function() {
						deferred.reject('');
					});
			};

			next();
			return deferred.promise();
		};

		var setCellSelected = function($cell, selected) {
			$cell.toggleClass('is-selected', selected);
		};

		var applyDateRangeSelection = function($table, startDate, endDate, selected) {
			var from = startDate <= endDate ? startDate : endDate;
			var to = startDate <= endDate ? endDate : startDate;

			$table.find('.calendar-day-cell').each(function() {
				var cellDate = $(this).data('date');
				if (cellDate >= from && cellDate <= to) {
					setCellSelected($(this), selected);
				}
			});
		};

		$('.request-shift-calendar').on('mousedown', '.calendar-day-cell', function(e) {
			if ($(e.target).closest('.icon-delete-button, .calendar-add-button').length) {
				return;
			}

			e.preventDefault();
			isDragging = true;
			dragStartDate = $(this).data('date');
			$('.calendar-day-cell.is-selected').removeClass('is-selected');
			setCellSelected($(this), true);
		});

		$('.request-shift-calendar').on('mouseenter', '.calendar-day-cell', function() {
			if (!isDragging) {
				return;
			}

			$('.calendar-day-cell.is-selected').removeClass('is-selected');

			applyDateRangeSelection(
				$(this).closest('.request-shift-calendar'),
				dragStartDate,
				$(this).data('date'),
				true
			);
		});

		$(document).on('mouseup', function() {
			isDragging = false;
			dragStartDate = null;
		});

		$('.request-shift-calendar').on('keydown', '.calendar-day-cell', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				$('.calendar-day-cell.is-selected').removeClass('is-selected');
				$(this).addClass('is-selected');
			}
		});

		$('.request-shift-calendar').on('click', '.calendar-add-button', function(e) {
			e.preventDefault();
			e.stopPropagation();
			openShiftPopup($(this).closest('.calendar-day-cell').data('date'));
		});

		$('.request-shift-calendar').on('click', '.icon-delete-button', function(e) {
			e.preventDefault();
			e.stopPropagation();

			deleteShiftButton($(this)).fail(function() {
				alert('削除に失敗しました。再度お試しください。');
			});
		});

		$(document).on('keydown', function(e) {
			if ((e.key !== 'Delete' && e.keyCode !== 46) || $popupOverlay.hasClass('is-open')) {
				return;
			}

			var $selectedRequestButtons = $('.calendar-day-cell.is-selected .calendar-shift-item.is-request .icon-delete-button');
			if ($selectedRequestButtons.length === 0) {
				return;
			}

			e.preventDefault();

			swal({
				title: '<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Delete desired shift?<?php } else { ?>希望シフトを削除しますか？<?php }?>',
				text: '<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Selected desired shifts will be deleted.<?php } else { ?>選択中の日付にある希望シフトを削除します。<?php }?>',
				icon: 'warning',
				buttons: ['<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Cancel<?php } else { ?>キャンセル<?php }?>', '<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Delete<?php } else { ?>削除<?php }?>'],
				dangerMode: true
			}).then(function(willDelete) {
				if (!willDelete) {
					return;
				}

				var requests = [];
				$selectedRequestButtons.each(function() {
					requests.push(deleteShiftButton($(this)));
				});

				$.when.apply($, requests).fail(function() {
					alert('<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Failed to delete desired shift.<?php } else { ?>希望シフトの削除に失敗しました。<?php }?>');
				});
			});
		});

		$startSlider.on('input change', function() {
			if (parseInt($startSlider.val(), 10) > parseInt($endSlider.val(), 10)) {
				$startSlider.val($endSlider.val());
			}
			updateShiftPopupPreview();
		});

		$endSlider.on('input change', function() {
			if (parseInt($endSlider.val(), 10) < parseInt($startSlider.val(), 10)) {
				$endSlider.val($startSlider.val());
			}
			updateShiftPopupPreview();
		});

		$('.shift-popup-cancel').on('click', function() {
			closeShiftPopup();
		});

		$popupOkButton.on('click', function() {
			if ($popupOkButton.prop('disabled')) {
				return;
			}

			var targetDates = getPopupTargetDates();
			if (!targetDates.length) {
				closeShiftPopup();
				return;
			}

			$popupOkButton.prop('disabled', true);

			registerShiftsSequentially(targetDates)
				.done(function() {
					window.location.reload();
				})
				.fail(function(errorMessage) {
					$popupOkButton.prop('disabled', false);
					if (errorMessage) {
						alert(errorMessage);
						return;
					}
					alert('登録に失敗しました。再度お試しください。');
				});
		});

		$popupOverlay.on('click', function(e) {
			if ($(e.target).is('.shift-popup-overlay')) {
				closeShiftPopup();
			}
		});
	});
	<?php echo '</script'; ?>
>
	
	
	<?php $_smarty_tpl->_subTemplateRender(((string)@constant('VIEW_DIR'))."/common/mypage_forcast_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


	<div class="h_form">
		<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			希望シフト一覧
		<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
			Desired shift list desired shift
		<?php }?>
	</div>
	<br>
	<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		現在希望しているシフト日時の一覧です。<br>
		希望シフトを削除する場合は、該当日時の「削除」ボタンを押下してください。<br>
		<span class="caution">※既にアサインが確定している日時を削除しても、アサインはキャンセルされないのでご注意ください。</span>
	<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
		This is a list of currently desired shift dates and times. <br>
		To delete a desired shift, click the "Delete" button for the desired date and time. <br>
		<span class="caution">※Please note that deleting a date and time for which an assignment has already been confirmed will not cancel the assignment.</span>
	<?php }?>
	<br>
	<div class="request-shift-list-actions">
		<a href="/mypage/requestShiftAddForm/" class="request-shift-add-link" aria-label="<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Add desired shift<?php } else { ?>希望シフトを追加<?php }?>">
		</a>
	</div>
	<br>
	<div class="div_form">
		<?php if ($_smarty_tpl->tpl_vars['request_shift_calendar_months']->value) {?>
			<div class="request-shift-calendar-list">
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['request_shift_calendar_months']->value, 'calendar_month');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['calendar_month']->value) {
?>
					<div class="request-shift-calendar-block">
						<div class="request-shift-calendar-title"><?php echo $_smarty_tpl->tpl_vars['calendar_month']->value['month_label'];?>
</div>
						<table class="request-shift-calendar" aria-label="<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Desired shift calendar<?php } else { ?>希望シフトカレンダー<?php }?>">
							<thead>
								<tr>
									<th class="is-sunday"><?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Sun<?php } else { ?>日<?php }?></th>
									<th><?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Mon<?php } else { ?>月<?php }?></th>
									<th><?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Tue<?php } else { ?>火<?php }?></th>
									<th><?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Wed<?php } else { ?>水<?php }?></th>
									<th><?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Thu<?php } else { ?>木<?php }?></th>
									<th><?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Fri<?php } else { ?>金<?php }?></th>
									<th class="is-saturday"><?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Sat<?php } else { ?>土<?php }?></th>
								</tr>
							</thead>
							<tbody>
								<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['calendar_month']->value['weeks'], 'calendar_week');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['calendar_week']->value) {
?>
									<tr><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['calendar_week']->value, 'calendar_day');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['calendar_day']->value) {
?><td class="calendar-day-cell <?php if (!$_smarty_tpl->tpl_vars['calendar_day']->value['in_month']) {?>is-other-month<?php }?> <?php if ($_smarty_tpl->tpl_vars['calendar_day']->value['is_sunday']) {?>is-sunday<?php }?> <?php if ($_smarty_tpl->tpl_vars['calendar_day']->value['is_saturday']) {?>is-saturday<?php }?>" tabindex="0" data-date="<?php echo $_smarty_tpl->tpl_vars['calendar_day']->value['date_key'];?>
"><button type="button" class="calendar-add-button" aria-label="<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Add shift<?php } else { ?>シフト追加<?php }?>"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.8"/></svg></button><div class="calendar-day-number"><?php echo $_smarty_tpl->tpl_vars['calendar_day']->value['day'];?>
</div><?php if ($_smarty_tpl->tpl_vars['calendar_day']->value['shifts']) {?><div class="calendar-shift-list"><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['calendar_day']->value['shifts'], 'calendar_shift');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['calendar_shift']->value) {
?><div class="calendar-shift-item <?php if ('confirmed' == $_smarty_tpl->tpl_vars['calendar_shift']->value['type']) {?>is-confirmed<?php } else { ?>is-request<?php }?>" data-start-hour="<?php echo $_smarty_tpl->tpl_vars['calendar_shift']->value['start_hour'];?>
" data-end-hour="<?php echo $_smarty_tpl->tpl_vars['calendar_shift']->value['end_hour'];?>
"><span class="calendar-shift-type-icon" aria-hidden="true"><?php if ('confirmed' == $_smarty_tpl->tpl_vars['calendar_shift']->value['type']) {?><svg viewBox="0 0 24 24" fill="none"><path d="M4 12L9 17L20 6" stroke="currentColor" stroke-width="1.8"/></svg><?php } else { ?><svg viewBox="0 0 24 24" fill="none"><path d="M12 21C12 21 18 14.5 18 10A6 6 0 1 0 6 10C6 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2" stroke="currentColor" stroke-width="1.8"/></svg><?php }?></span><span class="calendar-shift-label"><?php echo $_smarty_tpl->tpl_vars['calendar_shift']->value['label'];?>
</span><?php if ('request' == $_smarty_tpl->tpl_vars['calendar_shift']->value['type']) {?><button type="button" class="icon-delete-button" data-delete-url="/mypage/requestShiftDelFinish/?id=<?php echo $_smarty_tpl->tpl_vars['calendar_shift']->value['id'];?>
" aria-label="<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Delete<?php } else { ?>削除<?php }?>"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7H20" stroke="currentColor" stroke-width="1.8"/><path d="M9 7V5C9 4.45 9.45 4 10 4H14C14.55 4 15 4.45 15 5V7" stroke="currentColor" stroke-width="1.8"/><path d="M7 7L8 20H16L17 7" stroke="currentColor" stroke-width="1.8"/><path d="M10 11V17M14 11V17" stroke="currentColor" stroke-width="1.8"/></svg></button><?php }?></div><?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>
</div>
												<?php }?>
											</td>
										<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

									</tr>
									
								<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

							</tbody>
						</table>
					</div>
				<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

			</div>
		<?php } else { ?>
			<?php if (@constant('PARAM_CONST_LANG_TYPE_JP') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				希望シフトの登録がありません。
			<?php } elseif (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>
				There is no desired shift registered.
			<?php }?>
		<?php }?>
	</div>

	<div class="shift-popup-overlay" aria-hidden="true">
		<div class="shift-popup-window" role="dialog" aria-modal="true">
			<div class="shift-popup-time-block">
				<div class="shift-popup-section-title">Time.</div>
				<div class="shift-popup-slider-block">
					<div class="shift-popup-dual-slider">
						<div class="shift-popup-slider-track"></div>
						<div class="shift-popup-time-range"></div>
						<input id="shift-start-hour" class="shift-popup-slider is-start" type="range" min="0" max="24" step="1" value="9" aria-label="開始時刻">
						<input id="shift-end-hour" class="shift-popup-slider is-end" type="range" min="0" max="24" step="1" value="17" aria-label="終了時刻">
						<div class="shift-popup-handle-label is-start">09:00</div>
						<div class="shift-popup-handle-label is-end">17:00</div>
					</div>
				</div>
			</div>
			<div class="shift-popup-target-date-block">
				<div class="shift-popup-section-title">Date</div>
				<div class="shift-popup-target-date-list"></div>
			</div>
			<div class="shift-popup-overwrite-warning" style="display:none;">
				<span class="shift-popup-overwrite-warning-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none">
						<path d="M12 3L22 21H2L12 3Z" stroke="currentColor" stroke-width="1.8"/>
						<path d="M12 9V14" stroke="currentColor" stroke-width="1.8"/>
						<circle cx="12" cy="17" r="1" fill="currentColor"/>
					</svg>
				</span>
				<?php if (@constant('PARAM_CONST_LANG_TYPE_EN') == $_smarty_tpl->tpl_vars['login_user']->value->lang_type) {?>Desired shift will be overwritten.<?php } else { ?>希望シフトは上書きされます。<?php }?>
			</div>
			<div class="shift-popup-buttons">
				<button type="button" class="shift-popup-ok">OK</button>
				<button type="button" class="shift-popup-cancel">キャンセル</button>
			</div>
		</div>
	</div>

</div><?php }
}
