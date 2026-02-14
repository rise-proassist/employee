<?php

/**
 * メッセージ定義
 *
 * @author kanemiya
 */

/*
 * メール関連
 */
define('NOTICE_EMAIL', 'no_reply@gtuned.net');
define('ADMIN_EMAIL', 'k.kanemiya@initiaworks.com');

define('MAIL_SUBJECT_PRE_REGIST_FINISH', '【' . SITE_NAME . '】仮会員登録が完了しました。');
define('MAIL_SUBJECT_REGIST_FINISH', '【' . SITE_NAME . '】会員登録が完了しました。');
define('MAIL_SUBJECT_REGIST_FINISH_EN', '【' . SITE_NAME . '】Your membership registration has been completed.');
define('MAIL_SUBJECT_RESIGN_FINISH', '【' . SITE_NAME . '】退会のお手続きが完了しました。');
define('MAIL_SUBJECT_RESIGN_FINISH_EN', '【' . SITE_NAME . '】The cancellation procedure has been completed.');
define('MAIL_SUBJECT_REISSUE_PASSWORD', '【' . SITE_NAME . '】パスワード再発行手続きのお知らせ / Password reissue procedure information');
define('MAIL_SUBJECT_MYPAGE_UPD_EMAIL_FINISH', '【' . SITE_NAME . '】メールアドレスの変更確認のお願い');
define('MAIL_SUBJECT_MYPAGE_UPD_PASSWORD_FINISH', '【' . SITE_NAME . '】パスワードの変更が完了しました。');
define('MAIL_SUBJECT_INQUIRY_FINISH', '【' . SITE_NAME . '】お問い合わせを承りました。');
define('MAIL_SUBJECT_INQUIRY_FINISH_ADMIN', '【' . SITE_NAME . '】お問い合わせがあります。');
define('MAIL_SUBJECT_APPROVE_PASSED', '【' . SITE_NAME . '】従業者登録の承認のお知らせ');
define('MAIL_SUBJECT_APPROVE_REJECTED', '【' . SITE_NAME . '】従業者登録の見送りのお知らせ');
define('MAIL_SUBJECT_ASSIGN_NOTICE', '【' . SITE_NAME . '】アサイン確定のお知らせ');

/*
 * 通常メッセージ
 */
define('MSG_RECORD_SUCCESS', '記録しました。');

/*
 * エラーメッセージ定義
 */
define('ERR_MSG_RETRY', 'システムエラーが発生しました。お手数ですが、最初からやり直してください。');
define('ERR_MSG_INPUT', '%sに誤りがあります。');
define('ERR_MSG_NO_MODIFY', '%sが変更されていません。');
define('ERR_MSG_LOGIN', 'メールアドレスまたは、パスワードに誤りがあります。<br>The email address or password is incorrect.');
define('ERR_MSG_LOGIN_RESTRICT', 'このアカウントは現在ログインが制限されています。<br />お手数ですが、お問い合わせ窓口にて、ご連絡くださいますようお願い申し上げます。');
define('ERR_MSG_EMPTY', '%sが未入力です。');
define('ERR_MSG_NO_EMTRY', '%sが登録されていません。');
define('ERR_MSG_RQUIRED', '%sは必須です。');
define('ERR_MSG_CONSENT', '%sに同意してください。');
define('ERR_MSG_NUM', '%sに半角数字以外を入力はできません。');
define('ERR_MSG_ALNUM', '%sに半角英数字以外を入力はできません。');
define('ERR_MSG_ALNUM_SGN', '%sに半角英数字記号以外を入力はできません。');
define('ERR_MSG_LENTGTH', '%sは%s文字を超えて入力することはできません。');
define('ERR_MSG_LENTGTH_RANGE', '%sは%s文字以上、%s以下で入力してください。');
define('ERR_MSG_HIRAGANA', '%sにひらがな以外を入力するはできません。');
define('ERR_MSG_DUPLICATE', '%sまたはその一部が重複しています。');
define('ERR_MSG_USED_YET', 'その%sは既に使用されています。');
define('ERR_MSG_NOT_MODIFI', '現在設定されている%sは設定できません。');
define('ERR_MSG_NOT_FOUND', '%sが見つかりませんでした。');
define('ERR_MSG_READ_FAILED', '%sの読み込みに失敗しました。');
define('ERR_MSG_INVALID', 'その%sは無効です。');
define('ERR_MSG_EXPIRE_DATE', '%sの有効期限が切れています。');
define('ERR_MSG_PARAM', 'パラメータにエラーがあります。');
define('ERR_MSG_NO_SAME', '%sと%sが一致していません。');
define('ERR_MSG_UNDER', '%sは%sを超える値を入力してください。');
define('ERR_MSG_OVER_PRICE', '%sは¥%dを超えて入力することはできません。');
define('ERR_MSG_FAILED', '%sに失敗しました。時間をおいて、再度お試しください。');
define('ERR_MSG_DATE_REVERSE', '%sに%s以降の日付が入力されています。');
define('ERR_MSG_RESERVED', 'その日時は、既に予約されています。');
define('ERR_MSG_PROCESSED', 'その%sは、既に%sされています。');
define('ERR_MSG_BAD_REQUEST', '不正なリクエストです。');
define('ERR_MSG_BAD_OPERATION', '不正な操作です。');
define('ERR_MSG_EXPIRARE_DATE', '%sの有効期限が切れています。');
define('ERR_MSG_DEADLINE', '%sは締め切りました。');
define('ERR_MSG_EVALUATED', 'この%sに対する評価は、既に評価済みです。');
define('ERR_MSG_OVER_UPLOAD', '%sは%dを超えてアップロードすることはできません。');
define('ERR_MSG_RESIGNED', 'この会員は、既に退会された会員です。');
define('ERR_MSG_AGE_VALID', '%s歳未満の方はご利用いただけません。');
define('ERR_MSG_EITHER', '%sと%sのどちらか一方に入力する必要があります。');
define('ERR_MSG_NG_WORD', '%sに、禁止文字が含まれています。');
define('ERR_MSG_CERT_NOW', '現在審査中となりますので、しばらくお待ち下さい。');
define('ERR_MSG_CERTED', '既に承認済みとなっておりますので、申請はできません。');
define('ERR_MSG_NOT_CONFIRM_ASSIGIN', 'この%sはアサインが確定していません。');
define('ERR_MSG_NOT_ASSIGIN', 'この%sは、このシフトにアサインされていません。');
define('ERR_MSG_NOT_CONFIRM_WAGE', 'この%sの日当が確定していません。');
define('ERR_MSG_RECORDED_YET', 'このシフトの%sは既に打刻済みです。');
define('ERR_MSG_CLOSED', 'この%sは、この締め処理済みです。');
define('ERR_MSG_UPLOAD_FILE_FAILED', 'アップロードできない画像が含まれています（error : %d）');
define('ERR_MSG_CSV_IMPORT_EMPTY', '[%s]行目の[%s]が、未入力です。登録をスキップしました。');
define('ERR_MSG_CSV_IMPORT_LENTGTH', '[%s]行目の[%s]は、%s文字を超えて入力することはできません。登録をスキップしました。');
define('ERR_MSG_CSV_IMPORT_INPUT_PARAM', '[%s]行目の[%s]のデータに誤りがあります。処理をスキップしました。');
define('ERR_MSG_CSV_IMPORT_DUPLICATE_ENTRY', '[%s]行目の[%s]は、すでに登録済みです。処理をスキップしました。');
define('ERR_MSG_CSV_IMPORT_INSERT_FAILD', '[%s]行目のデータ登録に失敗しました。処理をスキップしました。');
define('ERR_MSG_CSV_IMPORT_UPDATE_FAILD', '[%s]行目のデータ更新に失敗しました。処理をスキップしました。');
define('ERR_MSG_VALIDATE_ERROR', 'validate error.'); // ログ用
define('ERR_MSG_DB_ERROR', 'データベース処理にエラーが発生しました。');

define('ERR_MSG_EMPTY_EN', '%s not entered.');
define('ERR_MSG_NOT_FOUND_EN', '%s not found.');
define('ERR_MSG_INPUT_EN', 'There is an error in the %s.');
define('ERR_MSG_LENTGTH_EN', '%s cannot be more than %d characters.');
define('ERR_MSG_NUM_EN', '%s cannot contain anything other than half-width numbers.');
define('ERR_MSG_USED_YET_EN', 'That %s is already in use.');
define('ERR_MSG_NO_SAME_EN', '%s and %s do not match.');
define('ERR_MSG_DUPLICATE_EN', '%s or parts of it are duplicated.');
define('ERR_MSG_HIRAGANA_EN', '%s cannot be entered in any characters other than hiragana.');
define('ERR_MSG_INVALID_EN', 'That %s is invalid.');
define('ERR_MSG_CONSENT_EN', 'Please accept the %s.');
define('ERR_MSG_BAD_OPERATION_EN', 'Invalid operation.');
define('ERR_MSG_EXPIRARE_DATE_EN', '%s has expired.');
define('ERR_MSG_RQUIRED_EN', '%s is required.');
