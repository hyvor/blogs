# Reverse Proxy

- [Things to know](#things-to-know)
- [Traefik](#traefik)

<h2 id="things-to-know">Things to know</h2>

Things to consider when running Hyvor Blogs behind a reverse proxy:

- For the **app domain**, you decide how TLS is handled.
  - see [TLS Mode](https://blogs.hyvor.localhost/hosting/deploy#tls)
  - use `TLS_MODE=external` if your reverse proxy terminates TLS. In this case, app domain will only listen on port `:80` and your reverse proxy will terminate TLS and forward requests to Hyvor Blogs' port 80 over HTTP.
  - use `TLS_MODE=auto` if you want to let Hyvor Blogs handle TLS. In this case, your reverse proxy should forward TCP traffic to the app domain on ports 80 and 443 (port 80 is required for the ACME HTTP challenge).
  - use `TLS_MODE=manual` with a custom certificate and key to handle TLS between your reverse proxy and the app domain.
- For **custom domains**, TLS is **always** handled by Hyvor Blogs.
  - forward port 80 TCP/HTTP traffic to Hyvor Blogs' port 80. This is used for the ACME HTTP challenge.
  - forward port 443 TCP traffic to Hyvor Blogs' port 443 without TLS termination. This is where the blog is actually served from.

<h2 id="traefik">Traefik</h2>

<h3 id="traefik-app-domain">App Domain</h3>

To let Hyvor Blogs handle TLS (`TLS_MODE=auto`):

```yaml
labels:
  - 'traefik.tcp.routers.hyvor-blogs.rule=HostSNI(`${APP_DOMAIN}`)'
  - 'traefik.tcp.routers.hyvor-blogs.entrypoints=websecure'
  - 'traefik.tcp.routers.hyvor-blogs.tls.passthrough=true'
  - 'traefik.tcp.routers.hyvor-blogs.service=hyvor-blogs'
  - 'traefik.tcp.services.hyvor-blogs.loadbalancer.server.port=443'
```

To terminate TLS at the reverse proxy (`TLS_MODE=external`):

```yaml
labels:
  - 'traefik.http.routers.hyvor-blogs.rule=Host(`${APP_DOMAIN}`)'
  - 'traefik.http.routers.hyvor-blogs.entrypoints=https'
  - 'traefik.http.routers.hyvor-blogs.tls.certresolver=le'

## IMPORTANT!
## This only works with `--certificatesresolvers.le.acme.tlschallenge=true` traefik setting
## httpchallenge will not work
## because it interferes with custom domain HTTP challenge since
## Traefik reserves /.well-known/acme-challenge for itself.
```

<h3 id="traefik-custom-domains">Custom Domains</h3>

Finally, to handle custom domains:

```yaml
labels:
  # forward TCP on port 443
  - 'traefik.tcp.routers.custom-domain-https.rule=HostSNI(`*`)'
  - 'traefik.tcp.routers.custom-domain-https.entrypoints=websecure'
  - 'traefik.tcp.routers.custom-domain-https.tls.passthrough=true'
  - 'traefik.tcp.routers.custom-domain-https.service=hyvor-blogs-custom-domain'
  - 'traefik.tcp.services.hyvor-blogs-custom-domain.loadbalancer.server.port=443'

  # forward HTTP on port 80
  - 'traefik.http.routers.custom-domain-http.rule=Host(`*`)'
  - 'traefik.http.routers.custom-domain-http.entrypoints=web'
  - 'traefik.http.routers.custom-domain-http.service=hyvor-blogs-custom-domain'
  - 'traefik.http.services.hyvor-blogs-custom-domain.loadbalancer.server.port=80'
```
