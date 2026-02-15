-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: mysql322.phy.lolipop.lan
-- 生成日時: 2026 年 2 月 15 日 10:48
-- サーバのバージョン： 8.0.35
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `LA05383289-chikusandb`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `admin_user`
--

CREATE TABLE `admin_user` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `name` varchar(64) NOT NULL COMMENT '名前',
  `email` varchar(128) NOT NULL COMMENT 'メールアドレス',
  `password` varchar(60) NOT NULL COMMENT 'パスワード',
  `auth_level` tinyint UNSIGNED NOT NULL COMMENT '権限レベル',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='管理者ユーザテーブル';

-- --------------------------------------------------------

--
-- テーブルの構造 `cert`
--

CREATE TABLE `cert` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `short_name` varchar(4) COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称（略称）',
  `allowance` int UNSIGNED NOT NULL COMMENT '手当',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='資格証明書マスタ';

-- --------------------------------------------------------

--
-- テーブルの構造 `company`
--

CREATE TABLE `company` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL COMMENT '会社名',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='所属会社マスタ';

-- --------------------------------------------------------

--
-- テーブルの構造 `country`
--

CREATE TABLE `country` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL COMMENT '国籍名',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='国籍マスタ';

-- --------------------------------------------------------

--
-- テーブルの構造 `location`
--

CREATE TABLE `location` (
  `id` bigint NOT NULL COMMENT '通番',
  `name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `location_date` date NOT NULL COMMENT '現場日付',
  `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '住所（集合場所）',
  `detail` text COLLATE utf8mb4_general_ci COMMENT '詳細',
  `is_assigned` tinyint UNSIGNED NOT NULL COMMENT 'アサイン済みフラグ',
  `is_closed` tinyint UNSIGNED NOT NULL COMMENT '締め処理済みフラグ',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='現場';

-- --------------------------------------------------------

--
-- テーブルの構造 `location_assign_user`
--

CREATE TABLE `location_assign_user` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `location_shift_id` bigint UNSIGNED NOT NULL COMMENT '現場シフトID',
  `location_shift_row` smallint UNSIGNED NOT NULL COMMENT '現場シフト枠番号',
  `user_id` bigint UNSIGNED NOT NULL COMMENT '従事者ID',
  `allocate_cert_ids` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '担当資格ID',
  `work_date_from` datetime DEFAULT NULL COMMENT '業務日時（開始）',
  `is_modify_from` tinyint UNSIGNED DEFAULT NULL COMMENT '編集フラグ（開始）',
  `work_date_to` datetime DEFAULT NULL COMMENT '業務日時（終了）',
  `is_modify_to` tinyint UNSIGNED DEFAULT NULL COMMENT '編集フラグ（終了）',
  `comment` text COLLATE utf8mb4_general_ci COMMENT 'コメント',
  `receipt_user_name` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '領収従事者名',
  `receipt_user_post_code` varchar(7) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '領収従事者郵便番号',
  `receipt_user_address` text COLLATE utf8mb4_general_ci COMMENT '領収従事者住所',
  `receipt_user_tel` varchar(11) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '領収従事者電話番号',
  `receipt_user_birth_day` date DEFAULT NULL COMMENT '領収従事者生年月日',
  `daily_wage` decimal(10,0) DEFAULT NULL COMMENT '日当額',
  `withhold_tax` decimal(10,0) DEFAULT NULL COMMENT '源泉徴収税額',
  `total_wage` decimal(10,0) DEFAULT NULL COMMENT '合計支給額',
  `is_confirmed` tinyint UNSIGNED DEFAULT NULL COMMENT '確定済フラグ',
  `is_paid` tinyint UNSIGNED DEFAULT NULL COMMENT '支払済フラグ',
  `paid_date` datetime DEFAULT NULL COMMENT '支払い日時',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='現場アサイン従事者';

-- --------------------------------------------------------

--
-- テーブルの構造 `location_assign_user_notified`
--

CREATE TABLE `location_assign_user_notified` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `location_id` bigint UNSIGNED NOT NULL COMMENT '現場ID',
  `user_id` bigint UNSIGNED NOT NULL COMMENT '従事者ID',
  `is_notified` tinyint UNSIGNED NOT NULL COMMENT '通知済みフラグ',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='現場アサイン従事者通知済み';

-- --------------------------------------------------------

--
-- テーブルの構造 `location_shift`
--

CREATE TABLE `location_shift` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `location_id` bigint UNSIGNED NOT NULL COMMENT '現場ID',
  `name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `shift_date_from` datetime NOT NULL COMMENT 'シフト日時（開始）',
  `shift_date_to` datetime NOT NULL COMMENT 'シフト日時（終了）',
  `request_num` smallint UNSIGNED NOT NULL COMMENT '要請数',
  `note` text COLLATE utf8mb4_general_ci COMMENT '備考',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='現場シフト';

-- --------------------------------------------------------

--
-- テーブルの構造 `user`
--

CREATE TABLE `user` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'コード',
  `status_type` tinyint UNSIGNED NOT NULL COMMENT 'ステータス種別',
  `approve_type` tinyint UNSIGNED NOT NULL COMMENT '承認種別',
  `name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL COMMENT '氏名',
  `name_kana` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '氏名（ふりがな）',
  `tel` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '電話番号',
  `email` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'メールアドレス',
  `password` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'パスワード',
  `post_code` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '郵便番号',
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '住所',
  `gender_type` tinyint UNSIGNED NOT NULL COMMENT '性別種別',
  `birth_day` date DEFAULT NULL COMMENT '生年月日',
  `country_id` bigint UNSIGNED NOT NULL COMMENT '国籍ID',
  `employ_type` tinyint UNSIGNED NOT NULL COMMENT '雇用形態種別',
  `company_id` bigint UNSIGNED NOT NULL COMMENT '所属会社ID',
  `etc_company_name` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'その他会社名',
  `lang_type` tinyint UNSIGNED NOT NULL COMMENT '言語種別',
  `last_login_date` datetime DEFAULT NULL COMMENT '最終ログイン日時',
  `note` text COLLATE utf8mb4_general_ci COMMENT '備考',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ユーザ';

-- --------------------------------------------------------

--
-- テーブルの構造 `user_cert`
--

CREATE TABLE `user_cert` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `user_id` bigint UNSIGNED NOT NULL COMMENT '従事者ID',
  `cert_id` bigint UNSIGNED NOT NULL COMMENT '資格証明書ID',
  `cert_type` tinyint UNSIGNED NOT NULL COMMENT '資格証明書種別',
  `file_name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ファイル名',
  `is_approved` tinyint UNSIGNED NOT NULL COMMENT '承認フラグ',
  `comment` text COLLATE utf8mb4_general_ci COMMENT 'コメント',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='従事者資格証明書';

-- --------------------------------------------------------

--
-- テーブルの構造 `user_reissue_password`
--

CREATE TABLE `user_reissue_password` (
  `id` bigint NOT NULL COMMENT '通番',
  `user_id` bigint NOT NULL COMMENT 'ユーザID',
  `access_code` varchar(64) NOT NULL COMMENT 'アクセスコード',
  `expire_date` datetime NOT NULL COMMENT '有効期限日',
  `is_processed` tinyint UNSIGNED NOT NULL COMMENT '処理済みフラグ',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='ユーザパスワード再設定';

-- --------------------------------------------------------

--
-- テーブルの構造 `user_request_shift`
--

CREATE TABLE `user_request_shift` (
  `id` bigint NOT NULL COMMENT '通番',
  `user_id` bigint NOT NULL COMMENT '従事者ID',
  `shift_date_from` datetime NOT NULL COMMENT 'シフト希望日時（開始）',
  `shift_date_to` datetime NOT NULL COMMENT 'シフト希望日時（終了）',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='従事者希望シフト';

-- --------------------------------------------------------

--
-- テーブルの構造 `withhold_tax`
--

CREATE TABLE `withhold_tax` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `calc_type` tinyint UNSIGNED NOT NULL COMMENT '算出種別',
  `amount_range_from` decimal(10,0) UNSIGNED NOT NULL COMMENT '給与金額範囲（開始）',
  `amount_range_to` decimal(10,0) UNSIGNED NOT NULL COMMENT '給与金額範囲（終了）',
  `amount_ratio` float UNSIGNED DEFAULT NULL COMMENT '給与金額加算割合',
  `ratio_base_amount` decimal(10,0) UNSIGNED DEFAULT NULL COMMENT '割合加算基準金額',
  `ratio_base_tax_amount` decimal(10,0) UNSIGNED DEFAULT NULL COMMENT '割合加算税金額',
  `tax_amount` int UNSIGNED DEFAULT NULL COMMENT '税金額',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='源泉徴収金額マスタ';

-- --------------------------------------------------------

--
-- テーブルの構造 `_location_assign_user_result`
--

CREATE TABLE `_location_assign_user_result` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `location_assign_user_id` bigint UNSIGNED NOT NULL COMMENT '現場アサイン従事者ID',
  `receipt_user_name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL COMMENT '領収従事者名',
  `daily_wage` decimal(10,0) DEFAULT NULL COMMENT '日当額',
  `work_time` time NOT NULL COMMENT '稼働時間',
  `withhold_tax` decimal(10,0) DEFAULT NULL COMMENT '源泉徴収税額',
  `total_wage` decimal(10,0) DEFAULT NULL COMMENT '合計支給額',
  `is_confirmed` tinyint(1) NOT NULL COMMENT '確定済フラグ',
  `is_paid` tinyint(1) NOT NULL COMMENT '支払済フラグ',
  `paid_date` datetime DEFAULT NULL COMMENT '支払い日時',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='現場アサイン従事者実績';

-- --------------------------------------------------------

--
-- テーブルの構造 `_user_request_shift`
--

CREATE TABLE `_user_request_shift` (
  `id` bigint UNSIGNED NOT NULL COMMENT '通番',
  `user_id` bigint UNSIGNED NOT NULL COMMENT '従事者ID',
  `shift_date` date NOT NULL COMMENT 'シフト希望日付',
  `type` tinyint UNSIGNED NOT NULL COMMENT 'シフト種別',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='従事者希望シフト';

-- --------------------------------------------------------

--
-- テーブルの構造 `_user_work_record`
--

CREATE TABLE `_user_work_record` (
  `id` bigint NOT NULL COMMENT '通番',
  `user_id` bigint NOT NULL COMMENT '従事者ID',
  `location_shift_id` bigint NOT NULL COMMENT '現場シフトID',
  `work_date_from` datetime NOT NULL COMMENT '業務日時（開始）',
  `is_modify_from` tinyint(1) NOT NULL COMMENT '編集フラグ（開始）',
  `work_date_to` datetime DEFAULT NULL COMMENT '業務日時（終了）',
  `is_modify_to` tinyint(1) NOT NULL COMMENT '編集フラグ（終了）',
  `comment` text COLLATE utf8mb4_general_ci COMMENT 'コメント',
  `create_date` datetime DEFAULT NULL COMMENT '作成日',
  `update_date` datetime DEFAULT NULL COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='従事者業務記録（打刻）';

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `cert`
--
ALTER TABLE `cert`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `location_assign_user`
--
ALTER TABLE `location_assign_user`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `location_assign_user_notified`
--
ALTER TABLE `location_assign_user_notified`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `location_shift`
--
ALTER TABLE `location_shift`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `user_cert`
--
ALTER TABLE `user_cert`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `user_reissue_password`
--
ALTER TABLE `user_reissue_password`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `user_request_shift`
--
ALTER TABLE `user_request_shift`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `withhold_tax`
--
ALTER TABLE `withhold_tax`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `_location_assign_user_result`
--
ALTER TABLE `_location_assign_user_result`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `_user_request_shift`
--
ALTER TABLE `_user_request_shift`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `_user_work_record`
--
ALTER TABLE `_user_work_record`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=3;

--
-- テーブルの AUTO_INCREMENT `cert`
--
ALTER TABLE `cert`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=16;

--
-- テーブルの AUTO_INCREMENT `company`
--
ALTER TABLE `company`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=8;

--
-- テーブルの AUTO_INCREMENT `country`
--
ALTER TABLE `country`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=8;

--
-- テーブルの AUTO_INCREMENT `location`
--
ALTER TABLE `location`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=21;

--
-- テーブルの AUTO_INCREMENT `location_assign_user`
--
ALTER TABLE `location_assign_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=134;

--
-- テーブルの AUTO_INCREMENT `location_assign_user_notified`
--
ALTER TABLE `location_assign_user_notified`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=32;

--
-- テーブルの AUTO_INCREMENT `location_shift`
--
ALTER TABLE `location_shift`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=48;

--
-- テーブルの AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=22;

--
-- テーブルの AUTO_INCREMENT `user_cert`
--
ALTER TABLE `user_cert`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=56;

--
-- テーブルの AUTO_INCREMENT `user_reissue_password`
--
ALTER TABLE `user_reissue_password`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=5;

--
-- テーブルの AUTO_INCREMENT `user_request_shift`
--
ALTER TABLE `user_request_shift`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=55;

--
-- テーブルの AUTO_INCREMENT `withhold_tax`
--
ALTER TABLE `withhold_tax`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=228;

--
-- テーブルの AUTO_INCREMENT `_location_assign_user_result`
--
ALTER TABLE `_location_assign_user_result`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=26;

--
-- テーブルの AUTO_INCREMENT `_user_request_shift`
--
ALTER TABLE `_user_request_shift`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=186;

--
-- テーブルの AUTO_INCREMENT `_user_work_record`
--
ALTER TABLE `_user_work_record`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT COMMENT '通番', AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
