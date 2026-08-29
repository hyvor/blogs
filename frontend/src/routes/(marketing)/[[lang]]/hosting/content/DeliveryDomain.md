<script>
    import { Callout } from '@hyvor/design/components';
</script>

# Delivery Domain

By default, the default hosting method for a blog is through the app domain subdirectory: `https://<app-domain>/blog/<subdomain>`

You can configure a **Delivery Domain** to serve blogs from subdomains of a given domain. For example, on our cloud, blogs are hosted at `*.hyvorblogs.io`.

## Why Delivery Domain?

For most cases, we recommend using our default method of hosting blogs through the app domain `/blog/*`. A Delivery Domain is recommended in a specific scenario: if your Hyvor Blogs instance hosts blogs of various users, whose content you want to keep isolated on separate subdomains to ensure proper content segregation and management (in simplier terms, you don't fully trust your users).

<Callout type="info">
    <strong>Delivery Domain</strong> is different feature from <a href="/docs/custom-domain">Custom Domains</a>. Delivery domain concerns about the default hosting method for a blog, while any blog may or may not set up their own custom domain.
</Callout>

## Configuring a Delivery Domain

Add `DELIVERY_URL` environment variable:

```yaml
DELIVERY_URL=https://delivery.domain
```

Then, configure a DNS record to point all subdomains of your delivery domain to your server:

```txt
A   *.delivery.domain   1.2.3.4
```

Finally, configure your server to terminate TLS for your delivery domain. It requires a wildcard certificate for `*.delivery.domain`, which may require a DNS-01 challenge for issuance.

Full architecture:

```
*.delivery.domain
    ↓
Reverse Proxy (HTTPS, Port 443)
    ↓
Hyvor Blogs Container (HTTP, Port 80)
```