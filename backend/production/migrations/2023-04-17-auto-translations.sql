CREATE TABLE auto_translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    blog_id BIGINT UNSIGNED NOT NULL,
    source_lang VARCHAR(10) NOT NULL,
    target_lang VARCHAR(10) NOT NULL,
    chars INT NOT NULL,
    INDEX auto_translations_blog_id_index (blog_id),
    INDEX auto_translations_blog_id_created_at_index (blog_id, created_at)
);