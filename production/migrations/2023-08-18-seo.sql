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
     url VARCHAR(255),
     status_code SMALLINT,
     ignore BOOLEAN DEFAULT FALSE,
     UNIQUE KEY unique_blog_url (blog_id, url),
     INDEX index_blog_checked (blog_id, last_checked_at)
);
