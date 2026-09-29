<script>
  import { Callout } from '@hyvor/design/components';
</script>

# Proxy inverse

- [Choses à savoir](#things-to-know)
- [Traefik](#traefik)
- [Caddy](#caddy)
- [NGINX](#nginx)

<h2 id="things-to-know">Choses à savoir</h2>

<Callout type="info" title="Terminologie">
  <strong>Domaine de l'application</strong> désigne le domaine où votre instance Hyvor Blogs est hébergée (par ex. blogs.example.com). Configuré via la variable d'environnement <code>DOMAIN_APP</code>.

  <p>
  <strong>Domaine personnalisé</strong> désigne tout domaine ajouté à un blog quelconque au sein de votre instance Hyvor Blogs (par ex. personal-domain.com, blog.company.com, etc.). Chaque blog peut avoir son propre domaine personnalisé.
  </p>
</Callout>

<br />

Points à prendre en compte lors de l'exécution de Hyvor Blogs derrière un proxy inverse :

- Pour le **domaine de l'application**, c'est vous qui décidez comment le TLS est géré.
  - voir [Mode TLS](https://blogs.hyvor.localhost/hosting/deploy#tls)
  - utilisez `TLS_MODE=external` si votre proxy inverse termine le TLS. Dans ce cas, le domaine de l'application n'écoutera que sur le port `:80` et votre proxy inverse terminera le TLS et transmettra les requêtes au port 80 de Hyvor Blogs en HTTP.
  - utilisez `TLS_MODE=auto` si vous souhaitez laisser Hyvor Blogs gérer le TLS. Dans ce cas, votre proxy inverse doit transmettre le trafic TCP au domaine de l'application sur les ports 80 et 443 (le port 80 est requis pour le challenge HTTP ACME).
  - utilisez `TLS_MODE=manual` avec un certificat et une clé personnalisés pour gérer le TLS entre votre proxy inverse et le domaine de l'application.
- Pour les **domaines personnalisés**, le TLS est **toujours** géré par Hyvor Blogs.
  - transmettez le trafic TCP/HTTP du port 80 vers le port 80 de Hyvor Blogs. Ceci est utilisé pour le challenge HTTP ACME.
  - transmettez le trafic TCP du port 443 vers le port 443 de Hyvor Blogs sans terminaison TLS. C'est de là que le blog est réellement servi.
- Assurez-vous que l'adresse IP du proxy inverse est incluse dans la variable [env](/hosting/env) `TRUSTED_PROXIES` (par défaut, elle inclut toutes les plages d'IP privées).

<h2 id="traefik">Traefik</h2>

<h3 id="traefik-tls-auto">TLS_MODE=auto</h3>

Le domaine de l'application et les domaines personnalisés sont traités de la même manière dans ce mode. Transmettez tout le trafic TCP sur le port 443 directement à Hyvor Blogs, et transmettez le port 80 pour le challenge HTTP ACME :

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

Traefik termine le TLS pour le domaine de l'application lui-même, tandis que les domaines personnalisés ont toujours besoin d'un passthrough TCP brut sur le port 443 :

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

Quel que soit le domaine, le port 80 est toujours du HTTP simple, donc un `reverse_proxy` classique fonctionne pour lui. Le port 443 est la partie délicate : les domaines personnalisés (et le domaine de l'application, si `TLS_MODE=auto`) doivent atteindre la propre terminaison TLS de Hyvor Blogs de manière totalement intacte, ce que Caddy ne peut pas faire de base. Cela nécessite le plugin [`layer4`](https://github.com/mholt/caddy-l4), intégré à Caddy avec [xcaddy](https://github.com/caddyserver/xcaddy) :

```bash
xcaddy build --with github.com/mholt/caddy-l4
```

<h3 id="caddy-tls-auto">TLS_MODE=auto</h3>

Le domaine de l'application et les domaines personnalisés sont traités de la même manière dans ce mode. Transmettez tout le trafic TCP sur le port 443 directement à Hyvor Blogs, et transmettez le port 80 pour le challenge HTTP ACME :

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

Ici, Caddy termine le TLS pour le domaine de l'application lui-même, tandis que les domaines personnalisés ont toujours besoin d'un passthrough brut (leurs certificats sont toujours émis par Hyvor Blogs). Comme un seul écouteur peut se lier au `:443`, `layer4` fait d'abord correspondre le SNI : le domaine de l'application est transmis en interne à un site Caddy normal lié à un port de loopback pour une véritable terminaison TLS, tout le reste est transmis sans modification :

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

NGINX a besoin de son module [`stream`](https://nginx.org/en/docs/stream/ngx_stream_core_module.html) (compilé avec `--with-stream` et `--with-stream_ssl_preread_module`, que l'image Docker officielle `nginx` inclut) pour transmettre le port 443 sans terminer le TLS. Le port 80 est du HTTP simple et est reverse-proxifié normalement dans le bloc `http` habituel.

<h3 id="nginx-tls-auto">TLS_MODE=auto</h3>

Dans ce mode, Hyvor Blogs termine le TLS pour chaque domaine, donc `stream` a juste besoin de prélire le SNI et de transmettre la connexion directement, sans distinction selon le domaine :

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

Ici, NGINX termine le TLS pour le domaine de l'application (remplacez `blogs.example.com` ci-dessous par votre `DOMAIN_APP`), tandis que les domaines personnalisés sont toujours transmis en TCP brut, puisque leurs certificats sont toujours émis par Hyvor Blogs :

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
