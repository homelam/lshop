/*
 Navicat Premium Data Transfer

 Source Server         : mysqls
 Source Server Type    : MySQL
 Source Server Version : 50722
 Source Host           : 192.168.1.65
 Source Database       : lshop

 Target Server Type    : MySQL
 Target Server Version : 50722
 File Encoding         : utf-8

 Date: 07/16/2018 15:41:14 PM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `admin_menu`
-- ----------------------------
DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE `admin_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
--  Records of `admin_menu`
-- ----------------------------
BEGIN;
INSERT INTO `admin_menu` VALUES ('1', '0', '1', '首页', 'fa-bar-chart', '/', null, '2018-07-10 07:25:15'), ('2', '0', '6', '系统管理', 'fa-tasks', null, null, '2018-07-13 09:45:43'), ('3', '2', '7', '管理员', 'fa-users', 'auth/users', null, '2018-07-13 09:45:43'), ('4', '2', '8', '角色管理', 'fa-user', 'auth/roles', null, '2018-07-13 09:45:43'), ('5', '2', '9', '权限管理', 'fa-ban', 'auth/permissions', null, '2018-07-13 09:45:43'), ('6', '2', '10', '菜单管理', 'fa-bars', 'auth/menu', null, '2018-07-13 09:45:43'), ('7', '2', '11', '操作日志', 'fa-history', 'auth/logs', null, '2018-07-13 09:45:43'), ('8', '0', '2', '会员管理', 'fa-users', '/users', '2018-07-10 07:35:33', '2018-07-10 07:35:54'), ('9', '0', '3', '商品管理', 'fa-cubes', '/products', '2018-07-10 10:14:52', '2018-07-10 10:15:10'), ('10', '0', '4', '订单管理', 'fa-list', '/orders', '2018-07-12 11:14:47', '2018-07-12 11:14:57'), ('11', '0', '5', '优惠券管理', 'fa-align-center', '/coupon_codes', '2018-07-13 09:45:33', '2018-07-13 09:45:59');
COMMIT;

-- ----------------------------
--  Table structure for `admin_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `admin_permissions`;
CREATE TABLE `admin_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `http_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_path` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
--  Records of `admin_permissions`
-- ----------------------------
BEGIN;
INSERT INTO `admin_permissions` VALUES ('1', 'All permission', '*', '', '*', null, null), ('2', 'Dashboard', 'dashboard', 'GET', '/', null, null), ('3', 'Login', 'auth.login', '', '/auth/login\r\n/auth/logout', null, null), ('4', 'User setting', 'auth.setting', 'GET,PUT', '/auth/setting', null, null), ('5', 'Auth management', 'auth.management', '', '/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs', null, null), ('6', '会员管理', 'users', '', '/users*', '2018-07-10 07:40:32', '2018-07-10 07:41:20'), ('7', '商品管理', 'products', '', '/products*', '2018-07-16 06:26:22', '2018-07-16 06:26:22'), ('8', '订单管理', 'orders', '', '/orders*', '2018-07-16 06:27:03', '2018-07-16 06:27:03'), ('9', '优惠券管理', 'coupon_codes', '', '/coupon_codes*', '2018-07-16 06:27:41', '2018-07-16 06:27:41');
COMMIT;

-- ----------------------------
--  Table structure for `admin_role_menu`
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_menu`;
CREATE TABLE `admin_role_menu` (
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_menu_role_id_menu_id_index` (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
--  Records of `admin_role_menu`
-- ----------------------------
BEGIN;
INSERT INTO `admin_role_menu` VALUES ('1', '2', null, null);
COMMIT;

-- ----------------------------
--  Table structure for `admin_role_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_permissions`;
CREATE TABLE `admin_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_permissions_role_id_permission_id_index` (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
--  Records of `admin_role_permissions`
-- ----------------------------
BEGIN;
INSERT INTO `admin_role_permissions` VALUES ('1', '1', null, null), ('2', '2', null, null), ('2', '3', null, null), ('2', '4', null, null), ('2', '6', null, null), ('2', '7', null, null), ('2', '8', null, null), ('2', '9', null, null);
COMMIT;

-- ----------------------------
--  Table structure for `admin_role_users`
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_users`;
CREATE TABLE `admin_role_users` (
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_users_role_id_user_id_index` (`role_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
--  Records of `admin_role_users`
-- ----------------------------
BEGIN;
INSERT INTO `admin_role_users` VALUES ('1', '1', null, null), ('2', '2', null, null);
COMMIT;

-- ----------------------------
--  Table structure for `admin_roles`
-- ----------------------------
DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
--  Records of `admin_roles`
-- ----------------------------
BEGIN;
INSERT INTO `admin_roles` VALUES ('1', 'Administrator', 'administrator', '2018-07-10 07:03:53', '2018-07-10 07:03:53'), ('2', '运营', 'operator', '2018-07-10 07:42:37', '2018-07-10 07:42:37');
COMMIT;

-- ----------------------------
--  Table structure for `admin_user_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `admin_user_permissions`;
CREATE TABLE `admin_user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_user_permissions_user_id_permission_id_index` (`user_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
--  Table structure for `admin_users`
-- ----------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
--  Records of `admin_users`
-- ----------------------------
BEGIN;
INSERT INTO `admin_users` VALUES ('1', 'admin', '$2y$10$6xeDUUbpewyUuv3p7zzqFex/fPwXVk9GTmeDftPzp8.trQGtk0N1G', 'Administrator', null, 'p60VFB8Ku3CK5tdfF1GH9koJ1Ntlqt7BUuBZKf6gPyJFBAhS9S8pHgsH5MD3', '2018-07-10 07:03:53', '2018-07-10 07:03:53'), ('2', 'operator', '$2y$10$ZUoREACxV4il.ydGFNI34ehnq83RYEAKQfmWXf5nqeD8/gQqp7x66', '运营', null, '8QCmP2uCIkqoIGgN1FNQ4VQyKcUY6g2iltF00vrhbn1Cfe15ZuujiLsRrHKz', '2018-07-10 07:46:12', '2018-07-16 06:29:01');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
