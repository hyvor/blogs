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

Add the `MEDIA_URL` environment variable (scheme and host only, no path):

```yaml
MEDIA_URL=https://media.yourcompany.com
```

Then, point the domain to your server:

```txt
A   media.yourcompany.com   1.2.3.4
```

TLS depends on the scheme of `MEDIA_URL` (independent of `TLS_MODE`):

- `https://`: Hyvor Blogs obtains and renews a certificate for the media domain automatically. Ports 80 and 443 must be reachable for the media domain (forward them as TCP, like custom domains, if you use a [reverse proxy](/hosting/reverse-proxy)).
- `http://`: the media domain is served on port 80 without TLS. Use this if a reverse proxy or a CDN in front terminates TLS. Note that media URLs are generated with `http://`, so only use it if visitors can reach media over plain HTTP, or if the URL is only used internally.

Once the media domain is configured, new media URLs use it, and existing `https://<app-domain>/media/...` URLs redirect to it.

<Callout type="warning">
    If you use a CDN in front of the media domain, make sure it forwards the original <code>Host</code> header (the media domain) to Hyvor Blogs.
</Callout>
