<script lang="ts">
   import { Table, TableRow } from '@hyvor/design/components';
</script>

<h1>Deploy</h1>

<p>Let's deploy Hyvor Blogs on your server using Docker Compose. You can easily adapt this guide to other deployment methods such as Kubernetes.</p>

<h2 id="pre-req">Prerequisites</h2>

<p><strong>Server</strong>: A Linux server with at least 2 GB RAM and 2 vCPUs. For production use, we recommend at least 4 GB RAM. OS can be any modern Linux distribution (Ubuntu, Debian, CentOS, etc.).</p>

<p><strong>Docker</strong>: Install the latest version following the
<a href="https://docs.docker.com/engine/install/">official guide</a>.</p>

<p><strong>OpenID Connect (OIDC) Provider</strong>: Hyvor Blogs relies on OIDC for authentication. Create an application in your OIDC provider and obtain the issuer URL, client ID, and client secret. Then, allow the following URLs:</p>

<ul>
    <li><strong>Callback URL</strong>: <code>https://&lt;your-app-domain&gt;/api/oidc/callback</code></li>
    <li><strong>Logout URL</strong>: <code>https://&lt;your-app-domain&gt;</code></li>
</ul>

<p><strong>Domain</strong>: At least one domain name.</p>

<h2 id="dns">DNS Routing</h2>

<p>Before deploying, configure your DNS records to point to your server. You need to set up two domains:</p>

<Table columns="1fr 2fr 150px" style="bordered">
   <TableRow head>
      <div>Domain</div>
      <div>Usage</div>
      <div>Example</div>
   </TableRow>
   <TableRow>
      <div>App domain</div>
      <div>Console, Sudo, and API access</div>
      <div><code>blogs.example.com</code></div>
   </TableRow>
   <TableRow>
      <div>Delivery domain</div>
      <div>Subdomain hosting for blogs. Use a wildcard DNS record.</div>
      <div><code>*.blogs.example.com</code></div>
   </TableRow>
</Table>

<p>
    You can also use two different domains if you prefer. For example, our cloud uses <code>blogs.hyvor.com</code> as the app domain and <code>*.hyvorblogs.io</code> as the delivery domain.
</p>

<h2 id="install">Install</h2>

<p>Download the latest release tarball from the
<a href="https://github.com/hyvor/blogs/releases">releases page</a>:</p>

<pre><code>curl -L https://github.com/hyvor/blogs/releases/latest/download/deploy.tar.gz | tar -xz
cd deploy</code></pre>

<p>This gives you two files: <code>compose.yaml</code> and <code>.env</code>.</p>

<h2>Configure</h2>

<p>Edit the <code>.env</code> file and fill in the required values:</p>

<pre><code># Required
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
S3_USE_PATH_STYLE_ENDPOINT=false</code></pre>

<p>The <code>DATABASE_URL</code> is pre-configured to connect to the Postgres service defined in <code>compose.yaml</code>
using <code>POSTGRES_PASSWORD</code>, so you do not need to change it.</p>

<h2>Start</h2>

<pre><code>docker compose up -d</code></pre>

<p>Hyvor Blogs will start and run database migrations automatically on the first launch.</p>

<p>To check logs:</p>

<pre><code>docker compose logs -f</code></pre>

<h2>Upgrading</h2>

<p>To upgrade to the latest version, pull the new image and restart the container:</p>

<pre><code>docker compose pull
docker compose up -d</code></pre>

<p>Migrations are applied automatically on startup.</p>
