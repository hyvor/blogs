# Hyvor Blogs

[Hyvor Blogs](https://blogs.hyvor.com) is a self-hosted, open-source blogging platform designed to make creating and managing a blog simple. It’s built for speed and simplicity, while remaining flexible enough to support everything from straightforward personal blogs to more complex publishing workflows.

<p align="center">
  <a href="https://blogs.hyvor.com">
    <img src="https://hyvor.com/api/public/logo/blogs.png" alt="Hyvor Blogs Logo" width="110"/>
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
- **OIDC SSO**: Use any OIDC provider for authentication (Authentik, Keycloak, etc.)
- **Rich Editor**: Block-based editor with images, videos, code blocks, and more, with Markdown support.
- **Themes**: Fully customizable themes with template language, CSS, and JavaScript.
- **Custom Domains**: Serve each blog on its own custom domain.
- **Internationalization**: Multi-language support from ground up, no plugins required.
- **Users & Roles**: Manage authors and editors per blog with role-based access control.
- **SEO**: Built-in sitemaps, Open Graph, and canonical URL support.
- **RSS**: Automatically generated RSS (Atom) feeds for each blog.
- **API**: Console API for automations, Data API for headless usage, and Delivery API for sub-directory hosting.
- **Webhooks**: HTTP callbacks for post, tag, and member events.
- **AI Agent**: AI agent that reviews posts and suggests improvements.
- **Real-Time Collaboration**: Multiple authors editing together with a suggestion mode for proposed changes.
- **Import & Export**: Import from WordPress or a sitemap, and export your content anytime.
- **Redirects & Routes**: Manage redirects and customize URL routes.

## Screenshots

Console:

![Hyvor Blogs Console](/meta/assets/screenshot-console.png)

Rich-text Editor:

![Hyvor Blogs Editor](/meta/assets/screenshot-editor.png)

AI Agent:

![Hyvor Blogs AI Agent](/meta/assets/screenshot-agent.png)

## Architecture

- **PHP + Symfony** for the API backend.
- **SvelteKit** with [Hyvor Design System](https://github.com/hyvor/design) for the frontend.
- **PostgreSQL** as the database and queue backend.

## Self-Hosting

See the [self-hosting documentation](https://blogs.hyvor.com/hosting) for instructions on how to
deploy Hyvor Blogs using Docker Compose.

## Development

See [DEV.md](DEV.md) for instructions on setting up a local development environment.

## Community

- [Github](https://github.com/hyvor/blogs)
- [HYVOR Community](https://hyvor.community)
- [Discord](https://discord.com/invite/2WRJxQB)

## License

Hyvor Blogs is licensed under the [GNU Affero General Public License v3.0](LICENSE). We also offer [enterprise licenses](https://hyvor.com/enterprise) for organizations that require a commercial license or do not wish to comply with the AGPLv3 terms. See [Self-Hosting License FAQ](https://hyvor.com/docs/hosting-license) for more information.

![HYVOR Banner](https://raw.githubusercontent.com/hyvor/relay/refs/heads/main/meta/assets/hyvor-banner.svg)

Copyright © HYVOR. HYVOR name and logo are trademarks of HYVOR, SARL.
