CREATE TABLE `post_variant_histories` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `post_variant_id` bigint NOT NULL,
    `content` mediumtext NOT NULL,
    PRIMARY KEY (`id`)
);