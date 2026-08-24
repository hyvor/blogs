<script lang="ts">
   import { Table, TableRow } from '@hyvor/design/components';
</script>

# Hosting

[Hyvor Blogs](https://blogs.hyvor.com) is a fast and simple blogging platform that can be self-hosted on your own servers. This page will introduce you to the self-hosting process. To get started right away, see the [Deploy](/hosting/deploy) page.

## Self-hosting is first-class

Hyvor Blogs is designed to be self-hosted by developers and organizations.

- **Minimal dependencies**: only Docker, PostgreSQL, and an OIDC provider.
- **Open-source**: AGPLv3 codebase available on [Github](https://github.com/hyvor/blogs).
- **Multi-tenant**: Run multiple blogs on a single instance.
- **Single sign-on**: OIDC-based authentication by default

## Self-hosting vs. Cloud

<Table columns="1fr 1fr" style="bordered">
   <TableRow head>
      <div>Self-hosting</div>
      <div>Cloud</div>
   </TableRow>
   <TableRow>
      <div>Your own servers. Data never leaves your infrastructure</div>
      <div>Hosted on HYVOR's servers</div>
   </TableRow>
   <TableRow>
      <div>Requires Docker, PostgreSQL, and an OIDC provider</div>
      <div>No setup, start blogging immediately</div>
   </TableRow>
   <TableRow>
      <div>You manage updates and maintenance</div>
      <div>Automatic updates managed by HYVOR</div>
   </TableRow>
   <TableRow>
      <div>Pay for your own infrastructure costs</div>
      <div>Subscription-based pricing</div>
   </TableRow>
   <TableRow>
      <div>
         Community support (paid support plans available)
      </div>
      <div>Dedicated support from HYVOR</div>
   </TableRow>
</Table>

## Hyvor Blogs vs other blogging platforms

A few comparisons with other blogging platforms:

- Hyvor Blogs focuses entirely on blogging and is lighter than a general-purpose CMS like **WordPress**, **Drupal**, or **Joomla**. Hyvor Blogs is not extensible with plugins, but bundles the tools you need for a blog out of the box
  - [Hyvor Blogs vs WordPress](https://hyvor.com/compare/blogs/wordpress)
- Hyvor Blogs is comparable to **Ghost**. Hyvor Blogs does not come with in-built membership and newsletter features, but allows you to integrate with third-party services and focuses on building statically served blogs that are fast and SEO-friendly. Hyvor Blogs provides better team collaboration, approval workflows, multi-language support, etc.
  - [Hyvor Blogs vs Ghost](https://hyvor.com/compare/blogs/ghost)
- Hyvor Blogs gives you more control over your data and infrastructure than **Medium** or **Substack**, which are closed platforms. With Hyvor Blogs, you own your data and can host it on your own servers. Hyvor Blogs maybe more suitable for a primary blog, while Medium and Substack can be used as secondary platforms to reach a wider audience.
  - [Hyvor Blogs vs Medium](https://hyvor.com/compare/blogs/medium)
  - [Hyvor Blogs vs Substack](https://hyvor.com/compare/blogs/substack)
- Hyvor Blogs is not a text-based blog engine like **Hugo** or **Jekyll**. It focuses on rich-editor based content writing with a simple and intuitive interface.
- Hyvor Blogs provides headless CMS features (e.g. Data API), but has a narrow focus on blogging than **Strapi**, **Payload CMS**, and other general-purpose headless CMS platforms.

## License & Pricing

We offer three licensing options for self-hosting Hyvor Blogs:

- **Open-Source**:
  - Free, AGPLv3 license
  - Community support
  - Basic features, including rich editor, media, tags, authors, and more.
  - Team collaboration
  - Multi-language support
  - Custom themes support
  - In-built SEO features
  - Custom domain and TLS support
  - Console API, Data API, Delivery API, and Webhooks
  - AI agent and translations
  - Broken link detection
- **Enterprise Unicorn** (soon):
  - €5/user/month (billed annually), minimum 10 users
  - Everything in Open-Source, plus:
  - Email support from HYVOR
  - Audit logs
  - Custom roles and permissions
  - Custom approval workflows
- **Enterprise Apex** (soon):
  - Contact us for pricing
  - Everything in Enterprise Unicorn, plus:
  - Priority support with SLA
  - Invoicing

## Support

- [Github Repository](https://github.com/hyvor/blogs) for issues, feature requests
- [Community Support](https://hyvor.community)

See the [Deploy](/hosting/deploy) page for step-by-step instructions.
