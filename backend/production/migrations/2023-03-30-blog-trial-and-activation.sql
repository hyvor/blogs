# status = pending

# add is_activated column
ALTER TABLE blogs ADD COLUMN is_activated BOOLEAN NOT NULL DEFAULT TRUE AFTER updated_at;

# change default value to false
ALTER TABLE blogs ALTER COLUMN is_activated SET DEFAULT FALSE;