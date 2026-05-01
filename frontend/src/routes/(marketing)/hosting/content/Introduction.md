# Self-Hosting Hyvor Blogs

[Hyvor Blogs](https://blogs.hyvor.com) is a fully-featured, open-source blogging platform you can
self-host on your own infrastructure. It gives you complete control over your data, your users, and
your deployment.

## Features

- **Multi-blog**: Create and manage multiple blogs from a single console.
- **Rich editor**: A powerful block-based editor with support for images, videos, code blocks, and more.
- **Themes**: Fully customizable themes with template language, CSS, and JavaScript support.
- **Custom domains**: Serve each blog on its own custom domain.
- **Subdomain blogs**: Serve blogs on subdomains of your delivery domain.
- **Internationalization**: Publish content in multiple languages with built-in translation support.
- **Users & roles**: Manage authors and editors per blog with role-based access control.
- **SEO**: Built-in SEO tools including sitemaps, Open Graph, and canonical URLs.
- **API**: Console API and Delivery API for headless usage.
- **Webhooks**: Receive HTTP callbacks for post, tag, and member events.
- **Media**: Upload and manage images and files with S3-compatible storage backends.
- **OpenID Connect**: Use any OIDC provider (Google, GitHub, Keycloak, etc.) for authentication.

## Architecture

Hyvor Blogs runs as a single Docker container that listens on **port 80** for all HTTP traffic. The
container handles routing based on the incoming domain:

- **`DOMAIN_APP`** — serves the Hyvor Blogs app (console, API, marketing pages).
- **Subdomains of `DELIVERY_URL`** — serves blogs at their subdomain (e.g. `myblog.yourdomain.com`).
- **Any other domain** — attempts to serve a blog configured with that custom domain.

This means a single server and a single container can power your entire Hyvor Blogs installation.

## What to expect

Here is a short summary of what setting up Hyvor Blogs looks like:

1. **Provision a server** — any Linux server with Docker installed.
2. **Set up an OIDC provider** — Hyvor Blogs uses OpenID Connect for authentication. You can use
   Google, GitHub, Keycloak, Authentik, or any other OIDC-compliant provider.
3. **Configure DNS** — point your app domain and delivery domain to your server.
4. **Configure a reverse proxy** — use Caddy or nginx to terminate TLS and forward traffic to the
   container on port 80.
5. **Start the container** — fill in the `.env` file and run `docker compose up -d`.

See the [Deploy](/hosting/deploy) page for step-by-step instructions.
