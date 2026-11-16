<script>
    import { Callout } from '@hyvor/design/components';
</script>

# Media Domain

Media (images, audio, and other files uploaded to a blog) is served independently of where the blog is hosted. By default, media URLs look like this:

```txt
https://<app-domain>/media/<blog-id>/<file-name>
```

Since media URLs do not depend on the blog's hosting, they keep working when you change a blog's hosting (subdomain, custom domain, or self-hosted), and in headless setups that only use the APIs.

You can configure a **Media Domain** to serve media from a dedicated domain instead:

```txt
https://<media-domain>/<blog-id>/<file-name>
```

## Why use a media domain?

The default is fine for most cases. A media domain is useful if you want to:

- put media behind a CDN without routing the app domain through it
- keep user-uploaded content on a separate domain from the console (similar to the [Delivery Domain](/hosting/delivery-domain))

## Configuring a Media Domain

Add the `MEDIA_DOMAIN` environment variable:

```yaml
MEDIA_DOMAIN=media.yourcompany.com
```

Then, point the domain to your server:

```txt
A   media.yourcompany.com   1.2.3.4
```

TLS for the media domain is handled the same way as the app domain (see [TLS Mode](/hosting/deploy#tls)):

- `TLS_MODE=auto`: a certificate is obtained automatically. Ports 80 and 443 must be reachable for the media domain.
- `TLS_MODE=manual`: the mounted certificate must also be valid for the media domain.
- `TLS_MODE=external`: your reverse proxy must terminate TLS for the media domain and forward it to port 80, like the app domain (see [Reverse Proxy](/hosting/reverse-proxy)).

Once the media domain is configured, new media URLs use it, and existing `https://<app-domain>/media/...` URLs redirect to it.

<Callout type="warning">
    If you use a CDN in front of the media domain, make sure it forwards the original <code>Host</code> header (the media domain) to Hyvor Blogs.
</Callout>
