ALTER TABLE blogs
ADD COLUMN integration ENUM('shopify') NULL AFTER billing_type;

UPDATE blogs SET blogs.integration = 'shopify' WHERE billing_type = 'shopify';