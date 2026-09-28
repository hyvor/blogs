<script>
   import { Table, TableRow, Callout } from '@hyvor/design/components';
</script>

# Deploy

Let's deploy Hyvor Blogs on your server using Docker Compose. You can easily adapt this guide to other deployment methods such as Kubernetes.

<h2 id="pre-req">Prerequisites</h2>

**Server**: A Linux server with at least 1 GB RAM and 1 vCPUs.

**Docker**: Install Docker following the [official guide](https://docs.docker.com/engine/install/).

**OpenID Connect (OIDC) Provider**: Hyvor Blogs relies on OIDC for authentication. Create an application in your OIDC provider and obtain the issuer URL, client ID, and client secret. Then, allow the following URLs:

- **Redirect URL**: `https://<your-app-domain>/api/oidc/callback`
- **Logout URL**: `https://<your-app-domain>`

**Domain**: Domain name for your Hyvor Blogs instance. This is called the "App Domain".

<Callout type="info" title="App Domain">
   Hyvor Blogs is designed to handle multiple domains in a single installation (custom domains for individual blogs). <strong>App Domain</strong> is where the main API and Console are hosted. All other domains are considered custom domains. 
</Callout>

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

See [Reverse Proxy](/hosting/reverse-proxy) if you are running Hyvor Blogs behind a reverse proxy.

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

- `APP_SECRET`: A strong random string. You can generate one using the following command:
  ```bash
  openssl rand -base64 32
  ```
- `POSTGRES_PASSWORD`: Use a strong, URL-safe password for the Postgres database. You can generate one using the following command:
  ```bash
  openssl rand -base64 32 | tr '+/' '-_' | tr -d '='
  ```
- `DOMAIN_APP`: The main domain where your Hyvor Blogs instance is hosted (e.g., blogs.example.com). This is where you access the Console, Sudo, and APIs.
- `OIDC_ISSUER_URL`, `OIDC_CLIENT_ID`, `OIDC_CLIENT_SECRET`: Set these variables based on your OIDC provider configuration.

See [Environment Variables](/hosting/env) for all available environment variables.

<h3 id="tls">TLS Mode</h3>

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

If you are running Hyvor Blogs behind a reverse proxy, see [Reverse Proxy](/hosting/reverse-proxy).

## Start

```bash
docker compose up -d
```

Hyvor Blogs will start and run database migrations automatically on the first launch.

To check logs:

```bash
docker compose logs -f blogs
```

To verify your config:

```bash
docker compose exec blogs bin/console app:verify
```

## Upgrading

To upgrade to the latest version, replace the image version in `compose.yaml`:

```yaml
services:
  blogs:
    image: hyvor/blogs:<version>
```

Then, run:

```bash
docker compose up -d
```

Migrations will be applied automatically on startup.
