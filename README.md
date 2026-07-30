# Hyvor Blogs

[Hyvor Blogs](https://blogs.hyvor.com) is a self-hosted, open-source blogging platform for
anyone who wants to easily create and manage blogs. It is built to be fast and simple, while
also being flexible enough to support complex publishing workflows.

<p align="center">
  <a href="https://blogs.hyvor.com">
    <img src="https://hyvor.com/api/public/logo/blogs.png" alt="Hyvor Blogs Logo" width="130"/>
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
- **Multi-blog**: Manage multiple blogs with a single installation.
- **Rich Editor**: Block-based editor with images, videos, code blocks, and more, with Markdown support.
- **Themes**: Fully customizable themes with template language, CSS, and JavaScript.
- **Custom Domains**: Serve each blog on its own custom domain.
- **Internationalization**: Multi-language support from ground up, no plugins required.
- **Users & Roles**: Manage authors and editors per blog with role-based access control.
- **SEO**: Built-in sitemaps, Open Graph, and canonical URL support.
- **API**: Console API for automations, Data API for headless usage, and Delivery API for sub-directory hosting.
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
