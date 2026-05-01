# Deploy

This page covers how to deploy Hyvor Blogs on a single server using Docker Compose. This is the
recommended way to self-host Hyvor Blogs and is suitable for most use cases.

## Prerequisites

**Server**: A Linux server with at least 1 GB RAM and 1 vCPU. 2 GB RAM and 2 vCPUs are recommended.

**OS**: Ubuntu 24.04 LTS or any modern Linux distribution.

**Docker**: Install the latest version following the
[official guide](https://docs.docker.com/engine/install/).

**OIDC Provider**: Hyvor Blogs uses OpenID Connect for authentication. Create an application in your
OIDC provider (Google, GitHub, Keycloak, Authentik, etc.) and obtain:

- Issuer URL
- Client ID
- Client Secret

Allow the following redirect URLs in your OIDC provider:

- **Callback URL**: `https://<DOMAIN_APP>/api/oidc/callback`
- **Logout URL**: `https://<DOMAIN_APP>`

**DNS**: Point the following domains to your server's IP address:

- `DOMAIN_APP` — e.g. `blogs.example.com`
- `DELIVERY_URL` and its wildcard — e.g. `*.blogs.example.com` (for subdomain blogs)

**Reverse Proxy**: Use Caddy or nginx to terminate TLS and proxy traffic to port 80.
An example Caddyfile:

```
blogs.example.com, *.blogs.example.com {
    reverse_proxy localhost:80
}
```

**Storage**: An S3-compatible storage bucket (Cloudflare R2, DigitalOcean Spaces, MinIO, etc.) for
media uploads.

## Install

Download the latest release tarball from the
[releases page](https://github.com/hyvor/blogs/releases):

```bash
curl -L https://github.com/hyvor/blogs/releases/latest/download/deploy.tar.gz | tar -xz
cd deploy
```

This gives you two files: `compose.yaml` and `.env`.

## Configure

Edit the `.env` file and fill in the required values:

```bash
# Required
APP_SECRET=           # Run: openssl rand -base64 32
POSTGRES_PASSWORD=    # A strong password for the database
DOMAIN_APP=           # e.g. blogs.example.com
DELIVERY_URL=         # e.g. https://blogs.example.com

# OIDC (on-prem authentication)
OIDC_ISSUER_URL=      # e.g. https://accounts.google.com
OIDC_CLIENT_ID=
OIDC_CLIENT_SECRET=

# S3-compatible storage
FILESYSTEM_DRIVER=s3
S3_ACCESS_KEY_ID=
S3_SECRET_ACCESS_KEY=
S3_ENDPOINT=          # e.g. https://s3.amazonaws.com
S3_BUCKET=
S3_USE_PATH_STYLE_ENDPOINT=false
```

The `DATABASE_URL` is pre-configured to connect to the Postgres service defined in `compose.yaml`
using `POSTGRES_PASSWORD`, so you do not need to change it.

## Start

```bash
docker compose up -d
```

Hyvor Blogs will start and run database migrations automatically on the first launch.

To check logs:

```bash
docker compose logs -f
```

## DNS Routing

The container listens on **port 80** and routes requests based on the incoming domain:

| Domain | Served content |
|---|---|
| `DOMAIN_APP` | Console, API, and marketing pages |
| Subdomain of `DELIVERY_URL` | Blog at that subdomain (e.g. `myblog.blogs.example.com`) |
| Any other domain | Custom domain blog |

## Upgrading

To upgrade to the latest version, pull the new image and restart the container:

```bash
docker compose pull
docker compose up -d
```

Migrations are applied automatically on startup.
