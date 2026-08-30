# Environment Variables

These are the environment variables you can use to customize Hyvor Blogs:

```yaml
# Environment: prod, dev, or test
# you probably want to use prod for a deployment
APP_ENV=prod

# The secret key (32 bytes) used to encrypt sensitive data.
# Generate one using `openssl rand -base64 32`
APP_SECRET=

# The PostgreSQL database URL.
# Use the format: "postgresql://user:pass@host:5432/database_name?serverVersion=16&charset=utf8"
DATABASE_URL=

# OpenID Connect (OIDC) configuration
# Create an application in your OIDC provider and set these values
# Callback URL: https://<DOMAIN_APP>/api/oidc/callback
# Logout URL: https://<DOMAIN_APP>
OIDC_ISSUER_URL=
OIDC_CLIENT_ID=
OIDC_CLIENT_SECRET=

# App Domain
# Where Hyvor Blogs is running (console, sudo, API)
# Example: blogs.yourcompany.com
DOMAIN_APP=

# Delivery domain / URL (optional)
# If not set, blogs will be delivered at https://<DOMAIN_APP>/blog/{subdomain}.
# If set, a subdomain of the delivery domain will be used for hosting the blogs
# If the delivery URL is https://blogs.yourcompany.com, blogs will be hosted at https://<blog-subdomain>.blogs.yourcompany.com
# TLS termination for *.deliverydomain must be handled by a reverse proxy
# see https://blogs.hyvor.com/hosting/delivery-domain
DELIVERY_URL=

# Filesystem for media storage
# one of: file, s3
FILESYSTEM=file

# S3 Configuration if FILESYSTEM=s3
S3_ENDPOINT=
S3_ACCESS_KEY_ID=
S3_SECRET_ACCESS_KEY=
S3_BUCKET=
S3_REGION=
S3_USE_PATH_STYLE_ENDPOINT=

# SMTP configuration for sending emails (link analysis notifications, etc.)
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=

# TLS_MODE controls how HTTPS is handled for DOMAIN_APP.
# One of: auto, external, manual, or disabled
# See https://blogs.hyvor.com/hosting/deploy#tls
TLS_MODE=

# Trusted proxy IP addresses or CIDR ranges.
# This is used to determine the real client IP address and HTTPS status.
# By default, all private IP ranges are trusted.
TRUSTED_PROXIES=

# Logging level
# One of: debug, info (default), notice, warning, error, critical, alert, emergency
LOG_LEVEL=

# Whether to run migrations automatically on startup
# Set this to false if you want to run migrations manually (e.g. in a CI pipeline) instead of on every startup
# default: true
RUN_MIGRATIONS_ON_STARTUP=

# Mercure Hub configuration
# Used for real-time communication (e.g. collaborative editing)
# if MERCURE_INTERNAL is true (default: true), the built-in Mercure hub will be used.
# and, you can ignore other Mercure-related settings.
# set MERCURE_INTERNAL=false to use an external Mercure hub.
MERCURE_INTERNAL=
MERCURE_JWT_SECRET=           # Run: openssl rand -base64 32
MERCURE_URL=                  # where symfony calls to publish updates (private URL)
MERCURE_PUBLIC_URL=           # where JS clients connect to

# Integrations
# ===================

# Unsplash for image search
UNSPLASH_ACCESS_KEY=
UNSPLASH_SECRET_KEY=

# AI platform keys for AI agent and auto-translation features
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
MISTRAL_API_KEY=

# Sentry
# Used for error tracking
SENTRY_DSN=
```
