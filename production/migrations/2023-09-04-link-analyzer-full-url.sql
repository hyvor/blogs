# status = pending
# before
ALTER TABLE link_analyzer_links
    ADD COLUMN full_url VARCHAR(255) NULL AFTER url;

UPDATE link_analyzer_links SET full_url = url WHERE full_url IS NULL;

# after deployment
ALTER TABLE link_analyzer_links
    MODIFY COLUMN full_url VARCHAR(255) NOT NULL;