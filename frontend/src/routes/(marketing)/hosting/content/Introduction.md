<script lang="ts">
   import { Table, TableRow } from '@hyvor/design/components';
</script>

# Hosting

[Hyvor Blogs](https://blogs.hyvor.com) is a fully-featured, open-source blogging platform you can
self-host on your own infrastructure.

## What to expect

Here is a short summary of what self-hosting Hyvor Blogs looks like:

- You deploy the Hyvor Blogs container via Docker Compose. It depends on PostgreSQL for data storage, a S3-compatible storage for media, and an OIDC provider for authentication.
- You configure two domains to point to your server: one for the app, and one for subdomain hosting for blogs. Custom domains are also supported out of the box.
- You and your team can log in to the console and start blogging.

## Self-hosting vs. Cloud

<Table columns="1fr 1fr" style="bordered">
   <TableRow head>
      <div>Self-hosting</div>
      <div>Cloud</div>
   </TableRow>
   <TableRow>
      <div>Your own servers. Data never leaves your infrastructure</div>
      <div>Hosted on HYVOR's secure servers</div>
   </TableRow>
   <TableRow>
      <div>Requires Docker, PostgreSQL, S3-compatible storage, and an OIDC provider</div>
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

## License

Hyvor Blogs is licensed under the AGPL-3.0 License. We also offer [enterprise licenses](https://hyvor.com/enterprise) for organizations that require a commercial license, priority support, or do not wish to comply with the AGPLv3 terms. Both licenses include the same product features. See HYVOR's [Self-Hosting License FAQ](https://hyvor.com/docs/hosting-license) for more information.

See the [Deploy](/hosting/deploy) page for step-by-step instructions.
