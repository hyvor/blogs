# Hyvor Blogs

[Hyvor Blogs](https://blogs.hyvor.com) is a self-hosted, open-source blogging platform for
developers and teams. It is designed to be simple to self-host, easy to manage, and powerful enough
for multi-blog, multi-author publishing workflows.

<p align="center">
  <a href="https://blogs.hyvor.com">
    <img src="https://hyvor.com/img/logo.png" alt="Hyvor Blogs Logo" width="130"/>
  </a>
</p>

<p align="center">
  <a href="https://blogs.hyvor.com">
    Open-Source Blogging Platform
  </a>
    <span> | </span>
    <a href="https://blogs.hyvor.com/hosting">
    Self-Hosting Docs
  </a>
    <span> | </span>
    <a href="https://blogs.hyvor.com/docs">
    Product Docs
  </a>
</p>

## Features

- **Self-Hosted**: Docker Compose-based deployment.
- **Multi-blog**: Manage multiple blogs from a single console.
- **Rich Editor**: Block-based editor with images, videos, code blocks, and more.
- **Themes**: Fully customizable themes with template language, CSS, and JavaScript.
- **Custom Domains**: Serve each blog on its own custom domain.
- **Internationalization**: Publish content in multiple languages with translation support.
- **Users & Roles**: Manage authors and editors per blog with role-based access control.
- **SEO**: Built-in sitemaps, Open Graph, and canonical URL support.
- **API**: Console API and Delivery API for headless usage.
- **Webhooks**: HTTP callbacks for post, tag, and member events.
- **OpenID Connect**: Use any OIDC provider for authentication (Google, GitHub, Keycloak, etc.).

## Screenshots

<!-- Screenshots coming soon -->

## Architecture

- **PHP + Symfony** for the API backend.
- **SvelteKit** for the frontend.
- **PostgreSQL** as the primary database.
- **S3-compatible storage** for media uploads.

## Self-Hosting

See the [self-hosting documentation](https://blogs.hyvor.com/hosting) for instructions on how to
deploy Hyvor Blogs using Docker Compose.

## Development

See [DEV.md](DEV.md) for instructions on setting up a local development environment.

## License

Hyvor Blogs is licensed under the [GNU Affero General Public License v3.0](LICENSE).
