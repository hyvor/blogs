<script>
    import { Callout } from '@hyvor/design/components';
</script>

# Delivery Domain

By default, a blog is hosted through the app domain subdirectory: `https://<app-domain>/blog/<subdomain>`

You can configure a **Delivery Domain** to serve blogs from subdomains of a given domain instead. For example, on our cloud, blogs are hosted at `*.hyvorblogs.io`.

## Why Delivery Domain?

For most cases, we recommend using the default method of hosting blogs through the app domain `/blog/*`. A Delivery Domain is recommended in one specific scenario: your Hyvor Blogs instance hosts blogs for various users, and you want to keep their content isolated on separate subdomains to ensure proper content segregation.

<Callout type="info">
    <strong>Delivery Domain</strong> is a different feature from <a href="/docs/custom-domain">Custom Domains</a>. Delivery Domain concerns the default hosting method for a blog, while any blog may or may not set up its own custom domain.
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

Finally, configure your server to terminate TLS for your delivery domain. This requires a wildcard certificate for `*.delivery.domain`, which may require a DNS-01 challenge for issuance.

Full architecture:

```
*.delivery.domain
    ↓
Reverse Proxy (HTTPS, Port 443)
    ↓
Hyvor Blogs Container (HTTP, Port 80)
```
