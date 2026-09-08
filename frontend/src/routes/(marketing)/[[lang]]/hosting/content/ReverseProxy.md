<script>
  import { Callout } from '@hyvor/design/components';
</script>

# Reverse Proxy

- [Things to know](#things-to-know)
- [Traefik](#traefik)
- [Caddy](#caddy)
- [NGINX](#nginx)

<h2 id="things-to-know">Things to know</h2>

<Callout type="info" title="Terminology">
  <strong>App Domain</strong> refers to the domain where your Hyvor Blogs instance is hosted (e.g. blogs.example.com). Configured via the <code>DOMAIN_APP</code> environment variable.

  <p>
  <strong>Custom Domain</strong> refers to any domain added to any blog within your Hyvor Blogs instance (e.g. personal-domain.com, blog.company.com, etc.). Each blog can have its own custom domain.
  </p>
</Callout>

<br />

Things to consider when running Hyvor Blogs behind a reverse proxy:

- For the **app domain**, you decide how TLS is handled.
  - see [TLS Mode](https://blogs.hyvor.localhost/hosting/deploy#tls)
  - use `TLS_MODE=external` if your reverse proxy terminates TLS. In this case, app domain will only listen on port `:80` and your reverse proxy will terminate TLS and forward requests to Hyvor Blogs' port 80 over HTTP.
  - use `TLS_MODE=auto` if you want to let Hyvor Blogs handle TLS. In this case, your reverse proxy should forward TCP traffic to the app domain on ports 80 and 443 (port 80 is required for the ACME HTTP challenge).
  - use `TLS_MODE=manual` with a custom certificate and key to handle TLS between your reverse proxy and the app domain.
- For **custom domains**, TLS is **always** handled by Hyvor Blogs.
  - forward port 80 TCP/HTTP traffic to Hyvor Blogs' port 80. This is used for the ACME HTTP challenge.
  - forward port 443 TCP traffic to Hyvor Blogs' port 443 without TLS termination. This is where the blog is actually served from.
- Make sure the IP address of the reverse proxy is included in the `TRUSTED_PROXIES` [env](/hosting/env) (by default, it includes all private IP ranges).

<h2 id="traefik">Traefik</h2>

<h3 id="traefik-tls-auto">TLS_MODE=auto</h3>

App domain and custom domains are handled the same way in this mode. Pass all TCP traffic on port 443 straight through to Hyvor Blogs, and forward port 80 for the ACME HTTP challenge:

```yaml
labels:
  # forward TCP on port 443 (any domain)
  - 'traefik.tcp.routers.hyvor-blogs.rule=HostSNI(`*`)'
  - 'traefik.tcp.routers.hyvor-blogs.entrypoints=https'
  - 'traefik.tcp.routers.hyvor-blogs.tls.passthrough=true'
  - 'traefik.tcp.routers.hyvor-blogs.service=hyvor-blogs'
  - 'traefik.tcp.services.hyvor-blogs.loadbalancer.server.port=443'

  # forward HTTP on port 80 (any domain)
  - 'traefik.http.routers.hyvor-blogs.rule=Host(`*`)'
  - 'traefik.http.routers.hyvor-blogs.entrypoints=http'
  - 'traefik.http.routers.hyvor-blogs.service=hyvor-blogs'
  - 'traefik.http.services.hyvor-blogs.loadbalancer.server.port=80'
```

<h3 id="traefik-tls-external">TLS_MODE=external</h3>

Traefik terminates TLS for the app domain itself, while custom domains still need raw TCP passthrough on port 443:

```yaml
labels:
  # app domain: terminate TLS at Traefik
  - 'traefik.http.routers.hyvor-blogs.rule=Host(`${DOMAIN_APP}`)'
  - 'traefik.http.routers.hyvor-blogs.entrypoints=https'
  - 'traefik.http.routers.hyvor-blogs.tls.certresolver=le'
  - 'traefik.http.routers.hyvor-blogs.service=hyvor-blogs'
  - 'traefik.http.services.hyvor-blogs.loadbalancer.server.port=80'

  ## IMPORTANT!
  ## This only works with `--certificatesresolvers.le.acme.tlschallenge=true` traefik setting
  ## httpchallenge will not work
  ## because it interferes with custom domain HTTP challenge since
  ## Traefik reserves /.well-known/acme-challenge for itself.

  # custom domains: pass TCP on port 443 straight through
  - 'traefik.tcp.routers.custom-domain-https.rule=HostSNI(`*`)'
  - 'traefik.tcp.routers.custom-domain-https.entrypoints=https'
  - 'traefik.tcp.routers.custom-domain-https.tls.passthrough=true'
  - 'traefik.tcp.routers.custom-domain-https.service=hyvor-blogs-custom-domain'
  - 'traefik.tcp.services.hyvor-blogs-custom-domain.loadbalancer.server.port=443'

  # forward HTTP on port 80 (any domain, incl. ACME HTTP challenge for custom domains)
  - 'traefik.http.routers.custom-domain-http.rule=Host(`*`)'
  - 'traefik.http.routers.custom-domain-http.entrypoints=http'
  - 'traefik.http.routers.custom-domain-http.service=hyvor-blogs-custom-domain'
  - 'traefik.http.services.hyvor-blogs-custom-domain.loadbalancer.server.port=80'
```

<h2 id="caddy">Caddy</h2>

Regardless of domain, port 80 is always plain HTTP, so a normal `reverse_proxy` works for it. Port 443 is the tricky part: custom domains (and the app domain, if `TLS_MODE=auto`) need to reach Hyvor Blogs' own TLS termination completely untouched, which Caddy cannot do out of the box. It requires the [`layer4`](https://github.com/mholt/caddy-l4) plugin, built into Caddy with [xcaddy](https://github.com/caddyserver/xcaddy):

```bash
xcaddy build --with github.com/mholt/caddy-l4
```

<h3 id="caddy-tls-auto">TLS_MODE=auto</h3>

App domain and custom domains are handled the same way in this mode. Pass all TCP traffic on port 443 straight through to Hyvor Blogs, and forward port 80 for the ACME HTTP challenge:

```js
{
	layer4 {
		:443 {
			route {
				proxy tcp/hyvor-blogs:443
			}
		}
	}
}

:80 {
	reverse_proxy hyvor-blogs:80
}
```

<h3 id="caddy-tls-external">TLS_MODE=external</h3>

Here Caddy terminates TLS for the app domain itself, while custom domains still need raw passthrough (their certificates are always issued by Hyvor Blogs). Since only one listener can bind `:443`, `layer4` matches on SNI first: the app domain is forwarded internally to a normal Caddy site bound to a loopback port for real TLS termination, everything else is passed through untouched:

```js
{
	layer4 {
		:443 {
			@app tls sni ${DOMAIN_APP}
			route @app {
				proxy tcp/127.0.0.1:8443
			}
			route {
				proxy tcp/hyvor-blogs:443
			}
		}
	}
}

:80 {
	reverse_proxy hyvor-blogs:80
}

{$DOMAIN_APP}:8443 {
	reverse_proxy hyvor-blogs:80 {
		header_up X-Forwarded-Proto https
	}
}
```

<h2 id="nginx">NGINX</h2>

NGINX needs its [`stream`](https://nginx.org/en/docs/stream/ngx_stream_core_module.html) module (built with `--with-stream` and `--with-stream_ssl_preread_module`, which the official `nginx` Docker image includes) to pass port 443 through without terminating TLS. Port 80 is plain HTTP and is reverse proxied normally in the regular `http` block.

<h3 id="nginx-tls-auto">TLS_MODE=auto</h3>

In this mode Hyvor Blogs terminates TLS for every domain, so `stream` just needs to preread the SNI and pass the connection straight through, without branching on which domain it is:

```js
stream {
    server {
        listen 443;
        proxy_pass hyvor-blogs:443;
        ssl_preread on;
    }
}

http {
    server {
        listen 80 default_server;
        server_name _;

        location / {
            proxy_pass http://hyvor-blogs:80;
            proxy_set_header Host $host;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
```

<h3 id="nginx-tls-external">TLS_MODE=external</h3>

Here NGINX terminates TLS for the app domain (replace `blogs.example.com` below with your `DOMAIN_APP`), while custom domains are still passed through as raw TCP, since their certificates are always issued by Hyvor Blogs:

```js
stream {
    map $ssl_preread_server_name $backend_443 {
        blogs.example.com   127.0.0.1:8443; # your app domain
        default             hyvor-blogs:443;
    }

    server {
        listen 443;
        proxy_pass $backend_443;
        ssl_preread on;
    }
}

http {
    server {
        listen 80 default_server;
        server_name _;

        location / {
            proxy_pass http://hyvor-blogs:80;
            proxy_set_header Host $host;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        }
    }

    server {
        listen 127.0.0.1:8443 ssl;
        server_name blogs.example.com;

        ssl_certificate     /etc/letsencrypt/live/blogs.example.com/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/blogs.example.com/privkey.pem;

        location / {
            proxy_pass http://hyvor-blogs:80;
            proxy_set_header Host $host;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
```
