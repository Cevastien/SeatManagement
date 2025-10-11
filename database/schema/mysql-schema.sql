/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_consents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` enum('accepted','declined') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consented_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `terms_consents_session_id_action_index` (`session_id`,`action`),
  KEY `terms_consents_consented_at_index` (`consented_at`),
  KEY `terms_consents_session_id_index` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `event_type` enum('registered','called','seated','completed','cancelled','no_show','hold','priority_applied') COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_time` timestamp NOT NULL,
  `staff_id` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `queue_events_customer_id_event_time_index` (`customer_id`,`event_time`),
  KEY `queue_events_event_type_event_time_index` (`event_type`,`event_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_events_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_events_archive` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `event_type` enum('registered','called','seated','completed','cancelled','no_show','hold','priority_applied') COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_time` timestamp NOT NULL,
  `staff_id` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `queue_events_archive_customer_id_event_time_index` (`customer_id`,`event_time`),
  KEY `queue_events_archive_event_type_event_time_index` (`event_type`,`event_time`),
  KEY `queue_events_archive_archived_at_index` (`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `total_customers` int NOT NULL DEFAULT '0',
  `avg_wait_time` int NOT NULL DEFAULT '0',
  `table_utilization` decimal(5,2) NOT NULL DEFAULT '0.00',
  `peak_hours` json DEFAULT NULL,
  `revenue_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `analytics_data_date_unique` (`date`),
  KEY `analytics_data_date_index` (`date`),
  KEY `analytics_data_date_total_customers_index` (`date`,`total_customers`),
  KEY `analytics_data_avg_wait_time_index` (`avg_wait_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_analytics_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_analytics_archive` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `metric_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metric_data` json NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `analytics_data_archive_metric_name_date_index` (`metric_name`,`date`),
  KEY `analytics_data_archive_archived_at_index` (`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `id_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `id_verifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `verification_method` enum('qr_code','barcode','ocr','manual','unified_scanner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `id_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_result` enum('verified','not_verified','partial','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'failed',
  `confidence_score` decimal(5,4) DEFAULT NULL,
  `field_confidence` json DEFAULT NULL,
  `raw_extracted_data` text COLLATE utf8mb4_unicode_ci,
  `processing_details` json DEFAULT NULL,
  `verification_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `processing_time_ms` int DEFAULT NULL,
  `attempt_number` int NOT NULL DEFAULT '1',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `error_details` json DEFAULT NULL,
  `is_manual_override` tinyint(1) NOT NULL DEFAULT '0',
  `requires_review` tinyint(1) NOT NULL DEFAULT '0',
  `review_notes` text COLLATE utf8mb4_unicode_ci,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customer_created` (`customer_id`,`created_at`),
  KEY `idx_method_result` (`verification_method`,`verification_result`),
  KEY `idx_result_created` (`verification_result`,`created_at`),
  KEY `idx_idtype_result` (`id_type`,`verification_result`),
  KEY `idx_confidence_created` (`confidence_score`,`created_at`),
  KEY `idx_customer_result_created` (`customer_id`,`verification_result`,`created_at`),
  KEY `idx_method_result_created` (`verification_method`,`verification_result`,`created_at`),
  CONSTRAINT `id_verifications_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `queue_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `priority_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `priority_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue_id` bigint unsigned NOT NULL,
  `review_type` enum('senior','pwd','pregnant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `verification_time` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priority_reviews_queue_id_unique` (`queue_id`),
  KEY `priority_reviews_queue_id_index` (`queue_id`),
  KEY `priority_reviews_status_index` (`status`),
  KEY `priority_reviews_review_type_status_index` (`review_type`,`status`),
  KEY `priority_reviews_verified_by_index` (`verified_by`),
  CONSTRAINT `priority_reviews_queue_id_foreign` FOREIGN KEY (`queue_id`) REFERENCES `queue_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `priority_reviews_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `staff_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `priority_verification_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `priority_verification_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority_type` enum('senior','pwd','pregnant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `pin` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_completed_at` timestamp NULL DEFAULT NULL,
  `pin_issued_at` timestamp NULL DEFAULT NULL,
  `timeout_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `timeout_notified` tinyint(1) NOT NULL DEFAULT '0',
  `rejected_at` timestamp NULL DEFAULT NULL,
  `verified_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejected_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priority_verifications_pin_unique` (`pin`),
  KEY `priority_verifications_status_index` (`status`),
  KEY `priority_verifications_requested_at_index` (`requested_at`),
  KEY `priority_verifications_customer_name_status_index` (`customer_name`,`status`),
  KEY `priority_verifications_status_requested_at_index` (`status`,`requested_at`),
  KEY `priority_verifications_timeout_at_index` (`timeout_at`),
  KEY `priority_verifications_verification_completed_at_index` (`verification_completed_at`),
  KEY `priority_verifications_pin_issued_at_index` (`pin_issued_at`),
  KEY `priority_verifications_expires_at_index` (`expires_at`),
  KEY `priority_verifications_customer_id_index` (`customer_id`),
  CONSTRAINT `priority_verifications_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `queue_customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `priority_verification_requests_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `priority_verification_requests_archive` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `timeout_at` timestamp NULL DEFAULT NULL,
  `timeout_notified` tinyint(1) NOT NULL DEFAULT '0',
  `rejected_at` timestamp NULL DEFAULT NULL,
  `verified_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejected_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejection_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `priority_verifications_archive_status_index` (`status`),
  KEY `priority_verifications_archive_priority_type_index` (`priority_type`),
  KEY `priority_verifications_archive_archived_at_index` (`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queue_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `party_size` int NOT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queue_number` int NOT NULL,
  `assigned_table_id` bigint unsigned DEFAULT NULL,
  `table_assigned_at` timestamp NULL DEFAULT NULL,
  `priority_type` enum('normal','senior','pwd','pregnant','group') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `is_group` tinyint(1) NOT NULL DEFAULT '0',
  `has_priority_member` tinyint(1) NOT NULL DEFAULT '0',
  `priority_applied_at` timestamp NULL DEFAULT NULL,
  `id_verification_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_verified_at` timestamp NULL DEFAULT NULL,
  `id_verification_data` text COLLATE utf8mb4_unicode_ci,
  `status` enum('waiting','called','seated','completed','cancelled','no_show') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `estimated_wait_minutes` int NOT NULL DEFAULT '0',
  `registered_at` timestamp NOT NULL,
  `registration_confirmed_at` timestamp NULL DEFAULT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `seated_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `table_id` int DEFAULT NULL,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_status_priority_type_index` (`status`),
  KEY `customers_queue_number_registered_at_index` (`queue_number`,`registered_at`),
  KEY `customers_registration_confirmed_at_index` (`registration_confirmed_at`),
  KEY `customers_id_verified_at_index` (`id_verified_at`),
  KEY `customers_priority_applied_at_index` (`priority_applied_at`),
  KEY `customers_last_updated_at_index` (`last_updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queue_customers_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_customers_archive` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `party_size` int NOT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queue_number` int NOT NULL,
  `assigned_table_id` bigint unsigned DEFAULT NULL,
  `table_assigned_at` timestamp NULL DEFAULT NULL,
  `priority_type` enum('normal','senior','pwd','pregnant','group') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `is_group` tinyint(1) NOT NULL DEFAULT '0',
  `has_priority_member` tinyint(1) NOT NULL DEFAULT '0',
  `id_verification_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_verification_data` text COLLATE utf8mb4_unicode_ci,
  `status` enum('waiting','called','seated','completed','cancelled','no_show') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `estimated_wait_minutes` int NOT NULL DEFAULT '0',
  `registered_at` timestamp NOT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `seated_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `table_id` int DEFAULT NULL,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customers_archive_status_priority_type_index` (`status`,`priority_type`),
  KEY `customers_archive_queue_number_registered_at_index` (`queue_number`,`registered_at`),
  KEY `customers_archive_archived_at_index` (`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queue_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `table_id` bigint unsigned DEFAULT NULL,
  `queue_number` int NOT NULL,
  `party_size` int NOT NULL,
  `priority_type` enum('normal','senior','pwd','pregnant','group') COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority_verified` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('waiting','called','seated','completed','cancelled','no_show') COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_time` timestamp NOT NULL,
  `estimated_wait` int DEFAULT NULL,
  `people_ahead` int DEFAULT NULL,
  `seated_time` timestamp NULL DEFAULT NULL,
  `completed_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `queue_entries_table_id_foreign` (`table_id`),
  KEY `queue_entries_customer_id_index` (`customer_id`),
  KEY `queue_entries_status_index` (`status`),
  KEY `queue_entries_queue_number_index` (`queue_number`),
  KEY `queue_entries_status_priority_type_index` (`status`,`priority_type`),
  KEY `queue_entries_registration_time_status_index` (`registration_time`,`status`),
  CONSTRAINT `queue_entries_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `queue_customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `queue_entries_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `restaurant_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restaurant_tables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tables_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_key_index` (`key`),
  KEY `settings_category_index` (`category`),
  KEY `settings_is_public_index` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff_login_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_login_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint unsigned NOT NULL,
  `login_time` timestamp NOT NULL,
  `logout_time` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_sessions_staff_id_index` (`staff_id`),
  KEY `staff_sessions_is_active_index` (`is_active`),
  KEY `staff_sessions_staff_id_is_active_index` (`staff_id`,`is_active`),
  KEY `staff_sessions_login_time_logout_time_index` (`login_time`,`logout_time`),
  CONSTRAINT `staff_sessions_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff_login_sessions_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_login_sessions_archive` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint unsigned NOT NULL,
  `login_time` timestamp NOT NULL,
  `logout_time` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_sessions_archive_staff_id_index` (`staff_id`),
  KEY `staff_sessions_archive_status_index` (`status`),
  KEY `staff_sessions_archive_archived_at_index` (`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','host','server','manager') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_users_email_unique` (`email`),
  KEY `staff_users_email_index` (`email`),
  KEY `staff_users_role_is_active_index` (`role`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint unsigned DEFAULT NULL,
  `action_type` enum('login','logout','queue_update','table_assign','verification','system') COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_staff_id_index` (`staff_id`),
  KEY `activity_logs_timestamp_index` (`timestamp`),
  KEY `activity_logs_action_type_timestamp_index` (`action_type`,`timestamp`),
  KEY `activity_logs_staff_id_timestamp_index` (`staff_id`,`timestamp`),
  CONSTRAINT `activity_logs_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_activity_logs_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_activity_logs_archive` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint unsigned NOT NULL,
  `data` json DEFAULT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `activity_logs_archive_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  KEY `activity_logs_archive_action_index` (`action`),
  KEY `activity_logs_archive_archived_at_index` (`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `table_reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `table_reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `table_id` bigint unsigned NOT NULL,
  `queue_id` bigint unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_assignments_table_id_index` (`table_id`),
  KEY `table_assignments_queue_id_index` (`queue_id`),
  KEY `table_assignments_assigned_at_status_index` (`assigned_at`,`status`),
  CONSTRAINT `table_assignments_queue_id_foreign` FOREIGN KEY (`queue_id`) REFERENCES `queue_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `table_assignments_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `table_reservations_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `table_reservations_archive` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `table_id` bigint unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `table_assignments_archive_customer_id_index` (`customer_id`),
  KEY `table_assignments_archive_table_id_index` (`table_id`),
  KEY `table_assignments_archive_status_index` (`status`),
  KEY `table_assignments_archive_archived_at_index` (`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_09_27_150828_create_customers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_09_27_150831_create_queue_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_09_27_150832_create_analytics_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_09_28_064122_create_id_verifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_10_02_012816_update_customers_table_for_priority_types',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_10_02_024300_create_table_turnover_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_10_02_024316_create_queue_wait_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_10_02_024706_create_priority_type_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_10_02_024726_create_priority_verification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_10_02_024746_create_queue_counter_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_10_02_024758_create_terms_acceptance_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_10_02_024813_create_staff_action_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_10_02_024839_create_queue_views',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_10_02_030440_create_sessions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_10_03_154956_drop_unnecessary_tables_to_follow_erd',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_10_03_155558_create_staff_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_10_03_155603_create_priority_type_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_10_03_155610_create_tables_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_10_04_080954_create_priority_verifications_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_10_04_092423_create_priority_verifications_table_new',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_10_04_095330_fix_priority_verifications_table_structure',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_10_04_101654_update_priority_verifications_for_qr_system',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_10_04_102709_fix_priority_verifications_for_pin_system',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_10_04_143231_create_terms_consents_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_10_04_143446_add_timeout_to_priority_verifications_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_10_04_151614_create_settings_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_10_06_231222_create_archive_tables',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_10_06_235009_drop_outdated_tables',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_10_06_063130_create_queue_entries_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_10_06_063148_create_staff_users_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_10_06_063202_create_table_assignments_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_10_06_063218_create_priority_reviews_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_10_06_063233_create_staff_sessions_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_10_06_063248_create_activity_logs_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_10_06_063304_create_analytics_data_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_10_07_235112_add_id_number_to_priority_verifications_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_10_08_110041_add_verification_tracking_timestamps_to_customers_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_10_08_110052_add_completion_tracking_to_priority_verifications_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_10_08_110425_rename_tables_for_clarity',25);
