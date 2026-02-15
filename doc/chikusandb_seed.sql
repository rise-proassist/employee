START TRANSACTION;

SET NAMES utf8mb4;

INSERT INTO `country` (`id`, `name`, `create_date`, `update_date`)
VALUES
  (1, '日本', NOW(), NOW()),
  (2, 'Vietnam', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `update_date` = NOW();

INSERT INTO `company` (`id`, `name`, `create_date`, `update_date`)
VALUES
  (1, 'テスト会社', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `update_date` = NOW();

INSERT INTO `cert` (`id`, `name`, `short_name`, `allowance`, `create_date`, `update_date`)
VALUES
  (1, '普通自動車免許', 'CAR1', 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `short_name` = VALUES(`short_name`),
  `allowance` = VALUES(`allowance`),
  `update_date` = NOW();

INSERT INTO `withhold_tax` (`id`, `calc_type`, `amount_range_from`, `amount_range_to`, `amount_ratio`, `ratio_base_amount`, `ratio_base_tax_amount`, `tax_amount`, `create_date`, `update_date`)
VALUES
  (1, 1, 0, 99999999, NULL, NULL, NULL, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `calc_type` = VALUES(`calc_type`),
  `amount_range_from` = VALUES(`amount_range_from`),
  `amount_range_to` = VALUES(`amount_range_to`),
  `tax_amount` = VALUES(`tax_amount`),
  `update_date` = NOW();

INSERT INTO `admin_user` (`id`, `name`, `email`, `password`, `auth_level`, `create_date`, `update_date`)
VALUES
  (1, '管理者テスト', 'admin@example.com', '$2y$10$MLJSmHYdOf6iBc8kCKNqQOMHpzGP01csoZpqjvex6bBjhpJ1IVxNK', 2, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `email` = VALUES(`email`),
  `password` = VALUES(`password`),
  `auth_level` = VALUES(`auth_level`),
  `update_date` = NOW();

INSERT INTO `user` (`id`, `code`, `status_type`, `approve_type`, `name`, `name_kana`, `tel`, `email`, `password`, `post_code`, `address`, `gender_type`, `birth_day`, `country_id`, `employ_type`, `company_id`, `etc_company_name`, `lang_type`, `last_login_date`, `note`, `create_date`, `update_date`)
VALUES
  (1, 'U000001', 1, 2, 'テストユーザ', 'てすとゆーざ', '09012345678', 'user@example.com', '$2y$10$E9dycipcp0NZSWL3.wGqTuhHpEofQ4L3Dzk8soOrX677G3tLgWvkS', '1000001', '東京都千代田区', 1, '1990-01-01', 1, 1, 1, NULL, 1, NULL, 'seed data', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `code` = VALUES(`code`),
  `status_type` = VALUES(`status_type`),
  `approve_type` = VALUES(`approve_type`),
  `name` = VALUES(`name`),
  `email` = VALUES(`email`),
  `password` = VALUES(`password`),
  `country_id` = VALUES(`country_id`),
  `employ_type` = VALUES(`employ_type`),
  `company_id` = VALUES(`company_id`),
  `lang_type` = VALUES(`lang_type`),
  `update_date` = NOW();

INSERT INTO `user_request_shift` (`id`, `user_id`, `shift_date_from`, `shift_date_to`, `create_date`, `update_date`)
VALUES
  (90001, 1, DATE(NOW()) + INTERVAL 9 HOUR, DATE(NOW()) + INTERVAL 12 HOUR, NOW(), NOW()),
  (90002, 1, DATE_ADD(DATE(NOW()), INTERVAL 30 DAY) + INTERVAL 13 HOUR, DATE_ADD(DATE(NOW()), INTERVAL 30 DAY) + INTERVAL 17 HOUR, NOW(), NOW()),
  (90003, 1, DATE_ADD(DATE(NOW()), INTERVAL 60 DAY) + INTERVAL 8 HOUR, DATE_ADD(DATE(NOW()), INTERVAL 60 DAY) + INTERVAL 11 HOUR, NOW(), NOW()),
  (90004, 1, DATE_ADD(DATE(NOW()), INTERVAL 89 DAY) + INTERVAL 9 HOUR, DATE_ADD(DATE(NOW()), INTERVAL 89 DAY) + INTERVAL 12 HOUR, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `user_id` = VALUES(`user_id`),
  `shift_date_from` = VALUES(`shift_date_from`),
  `shift_date_to` = VALUES(`shift_date_to`),
  `update_date` = NOW();

INSERT INTO `location` (`id`, `name`, `location_date`, `address`, `detail`, `is_assigned`, `is_closed`, `create_date`, `update_date`)
VALUES
  (91001, 'テスト農場A', DATE(NOW()), '千葉県千葉市中央区 1-1-1', 'seed confirmed shift location', 1, 0, NOW(), NOW()),
  (91002, 'テスト農場B', DATE_ADD(DATE(NOW()), INTERVAL 88 DAY), '千葉県千葉市中央区 2-2-2', 'seed confirmed shift location', 1, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `location_date` = VALUES(`location_date`),
  `address` = VALUES(`address`),
  `detail` = VALUES(`detail`),
  `is_assigned` = VALUES(`is_assigned`),
  `is_closed` = VALUES(`is_closed`),
  `update_date` = NOW();

INSERT INTO `location_shift` (`id`, `location_id`, `name`, `shift_date_from`, `shift_date_to`, `request_num`, `note`, `create_date`, `update_date`)
VALUES
  (91001, 91001, '午前シフト', DATE(NOW()) + INTERVAL 9 HOUR, DATE(NOW()) + INTERVAL 12 HOUR, 5, 'seed confirmed shift', NOW(), NOW()),
  (91002, 91002, '午後シフト', DATE_ADD(DATE(NOW()), INTERVAL 88 DAY) + INTERVAL 13 HOUR, DATE_ADD(DATE(NOW()), INTERVAL 88 DAY) + INTERVAL 17 HOUR, 5, 'seed confirmed shift', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `location_id` = VALUES(`location_id`),
  `name` = VALUES(`name`),
  `shift_date_from` = VALUES(`shift_date_from`),
  `shift_date_to` = VALUES(`shift_date_to`),
  `request_num` = VALUES(`request_num`),
  `note` = VALUES(`note`),
  `update_date` = NOW();

INSERT INTO `location_assign_user` (`id`, `location_shift_id`, `location_shift_row`, `user_id`, `allocate_cert_ids`, `work_date_from`, `is_modify_from`, `work_date_to`, `is_modify_to`, `comment`, `receipt_user_name`, `daily_wage`, `withhold_tax`, `total_wage`, `is_confirmed`, `is_paid`, `paid_date`, `create_date`, `update_date`)
VALUES
  (91001, 91001, 1, 1, NULL, NULL, NULL, NULL, NULL, 'seed confirmed shift', NULL, NULL, NULL, NULL, 1, 0, NULL, NOW(), NOW()),
  (91002, 91002, 1, 1, NULL, NULL, NULL, NULL, NULL, 'seed confirmed shift', NULL, NULL, NULL, NULL, 1, 0, NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `location_shift_id` = VALUES(`location_shift_id`),
  `location_shift_row` = VALUES(`location_shift_row`),
  `user_id` = VALUES(`user_id`),
  `comment` = VALUES(`comment`),
  `is_confirmed` = VALUES(`is_confirmed`),
  `is_paid` = VALUES(`is_paid`),
  `update_date` = NOW();

COMMIT;
