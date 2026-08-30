<script>
   import { Table, TableRow } from '@hyvor/design/components';
</script>

# Deploy

Let's deploy Hyvor Blogs on your server using Docker Compose. You can easily adapt this guide to other deployment methods such as Kubernetes.

<h2 id="pre-req">Prerequisites</h2>

**Server**: A Linux server with at least 1 GB RAM and 1 vCPUs.

**Docker**: Install Docker following the [official guide](https://docs.docker.com/engine/install/).

**OpenID Connect (OIDC) Provider**: Hyvor Blogs relies on OIDC for authentication. Create an application in your OIDC provider and obtain the issuer URL, client ID, and client secret. Then, allow the following URLs:

- **Callback URL**: `https://<your-app-domain>/api/oidc/callback`
- **Logout URL**: `https://<your-app-domain>`

**Domain**: Domain name for your Hyvor Blogs instance. This is called the "App Domain".

<h2 id="dns">
   DNS Routing
</h2>

Point your app domain to your server's IP address.

<Table columns="1fr 2fr 150px" style="bordered">
   <TableRow head>
      <div>Type</div>
      <div>Host</div>
      <div>Value</div>
   </TableRow>
   <TableRow>
      <div>A</div>
      <div>blogs.example.com</div>
      <div>123.123.123.123</div>
   </TableRow>
</Table>

<h2 id="install">Install</h2>

Download the latest release tarball from the [releases page](https://github.com/hyvor/blogs/releases):

```bash
curl -L https://github.com/hyvor/blogs/releases/latest/download/deploy.tar.gz | tar -xz
cd deploy
```

This gives you two files:

```
deploy/
   compose.yaml
   .env
```

## Configure

Edit the `.env` file and fill in the required values:

```yaml
# Required
APP_SECRET=           # Run: openssl rand -base64 32
POSTGRES_PASSWORD=    # A strong password for the database
DOMAIN_APP=           # e.g. blogs.example.com
DELIVERY_URL=         # e.g. https://blogs.example.com
MERCURE_JWT_SECRET=   # Run: openssl rand -base64 32

# OIDC (on-prem authentication)
OIDC_ISSUER_URL=      # e.g. https://accounts.google.com
OIDC_CLIENT_ID=
OIDC_CLIENT_SECRET=

# S3-compatible storage
S3_ACCESS_KEY_ID=
S3_SECRET_ACCESS_KEY=
S3_ENDPOINT=          # e.g. https://s3.amazonaws.com
S3_BUCKET=
S3_USE_PATH_STYLE_ENDPOINT=false
```

The `DATABASE_URL` is pre-configured to connect to the Postgres service defined in `compose.yaml` using `POSTGRES_PASSWORD`, so you do not need to change it.

<h3 id="tls">TLS Configuration</h3>

`TLS_MODE` controls how HTTPS is handled for the app domain (`DOMAIN_APP`). It does not affect custom domains attached to individual blogs, which always get TLS certificates automatically. Set it to one of the following:

<Table columns="120px 1fr" style="bordered">
   <TableRow head>
      <div>Mode</div>
      <div>Behavior</div>
   </TableRow>
   <TableRow>
      <div><code>auto</code></div>
      <div>
         Default. Caddy automatically obtains and renews a certificate from Let's Encrypt. Requires
         <code>DOMAIN_APP</code> to be publicly resolvable, with ports 80 and 443 reachable from the internet.
      </div>
   </TableRow>
   <TableRow>
      <div><code>external</code></div>
      <div>
         Use this if you run a reverse proxy (Nginx, Traefik, a load balancer, etc.) in front of Hyvor
         Blogs that terminates TLS. The container is reached over HTTP only; only port 80 needs to be
         published. The container does not redirect HTTP to HTTPS itself in this mode &mdash; handle
         that in your reverse proxy if needed. Make sure your proxy forwards the
         <code>X-Forwarded-Proto: https</code> and <code>X-Forwarded-For</code> headers, and that its
         IP is included in <code>TRUSTED_PROXIES</code>.
      </div>
   </TableRow>
   <TableRow>
      <div><code>manual</code></div>
      <div>
         Provide your own certificate and key by mounting them into the container at
         <code>/certs/cert.pem</code> and <code>/certs/key.pem</code>.
      </div>
   </TableRow>
   <TableRow>
      <div><code>disabled</code></div>
      <div>
         TLS is fully disabled and no HTTPS redirect happens. All links are generated as
         <code>http://</code>. Only use this on a trusted internal network.
      </div>
   </TableRow>
</Table>

## Start

```bash
docker compose up -d
```

Hyvor Blogs will start and run database migrations automatically on the first launch.

To check logs:

```bash
docker compose logs -f
```

## Upgrading

To upgrade to the latest version, pull the new image and restart the container:

```bash
docker compose pull
docker compose up -d
```

Migrations are applied automatically on startup.
