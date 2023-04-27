# status = pending

# PRE
ALTER TABLE post_variants ADD COLUMN slug VARCHAR(255) NULL;
ALTER TABLE post_variants ADD UNIQUE post_variants_language_id_slug_unique (`language_id`, `slug`);

UPDATE post_variants SET slug = (SELECT slug FROM posts WHERE posts.id = post_variants.post_id);