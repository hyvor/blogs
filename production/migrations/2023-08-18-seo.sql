# status = pending
ALTER TABLE post_variants
    ADD COLUMN seo_primary_keyword VARCHAR(255) NULL AFTER words,
    ADD COLUMN seo_secondary_keywords JSON NULL AFTER seo_primary_keyword;