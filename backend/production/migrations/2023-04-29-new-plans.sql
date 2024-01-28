UPDATE subscriptions SET plan = 'A';

ALTER TABLE subscriptions MODIFY COLUMN plan ENUM('A', 'starter', 'growth', 'premium', 'team', 'business', 'enterprise');

UPDATE subscriptions SET plan = 'growth';

ALTER TABLE subscriptions MODIFY COLUMN plan ENUM('starter', 'growth', 'premium', 'team', 'business', 'enterprise');

# RUN command to upgrade all old blogs

ALTER TABLE blogs DROP COLUMN is_activated;