# status = pending
ALTER TABLE blogs
ADD COLUMN blocked_at TIMESTAMP NULL AFTER is_blocked;

UPDATE blogs
SET blocked_at = NOW()
WHERE is_blocked = 1;