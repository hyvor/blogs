# status = pending
ALTER TABLE post_variants
    ADD COLUMN seo_primary_keyword VARCHAR(255) NULL AFTER words,
    ADD COLUMN seo_secondary_keywords JSON NULL AFTER seo_primary_keyword,
    ADD COLUMN link_analysis JSON NULL AFTER seo_secondary_keywords;

CREATE TABLE link_analyzer_links (
     id INT AUTO_INCREMENT PRIMARY KEY,
     created_at TIMESTAMP,
     last_checked_at TIMESTAMP,
     blog_id INT,
     post_variant_id INT,
     url VARCHAR(255),
     status_code SMALLINT,
     ignore BOOLEAN DEFAULT FALSE,
     UNIQUE (post_variant_id, url),
     INDEX (url),
     INDEX (post_variant_id, last_checked_at)
);

CREATE TABLE link_analyzer_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    blog_id BIGINT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    error VARCHAR(255) NULL,
    posts_count INT DEFAULT 0,
    post_variants_count INT DEFAULT 0,
    pages_count INT DEFAULT 0,
    page_variants_count INT DEFAULT 0,
    links_total_count INT DEFAULT 0,
    links_ok_count INT DEFAULT 0,
    links_broken_count INT DEFAULT 0,
    links_redirect_count INT DEFAULT 0,
    links_ignored_count INT DEFAULT 0
);

CREATE TABLE gpt_prompts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    blog_id INT,
    post_id INT NULL,
    prompt VARCHAR(1000),
    gpt_response TEXT NULL,
    model_name VARCHAR(255) NULL,
    tokens_prompt INT NULL,
    tokens_response INT NULL,
    tokens_total INT NULL,
    INDEX (post_id),
    INDEX (blog_id)
);
