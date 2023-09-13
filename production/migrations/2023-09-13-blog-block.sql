# status = done
ALTER TABLE blogs
ADD COLUMN blocked_at TIMESTAMP NULL AFTER is_blocked;

UPDATE blogs
SET blocked_at = NOW()
WHERE is_blocked = 1;

CREATE TABLE blocked_users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    hyvor_user_id BIGINT UNIQUE
);