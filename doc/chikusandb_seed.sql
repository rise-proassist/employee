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

COMMIT;
