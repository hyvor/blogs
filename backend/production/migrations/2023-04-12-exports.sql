CREATE TABLE `exports` (
   `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
   `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   `blog_id` bigint NOT NULL,
   `format` enum('hyvor_blogs','wordpress') NOT NULL DEFAULT 'hyvor_blogs',
   `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
   `url` varchar(255) DEFAULT NULL,
   `error` varchar(255) DEFAULT NULL,
   PRIMARY KEY (`id`)
)