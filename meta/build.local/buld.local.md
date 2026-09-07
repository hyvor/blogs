This builds locally and runs a self-contained instance that works with hyvor/dev Traefik setup. Visit https://blogs.build.hyvor.localhost to test. Use this as an easy way to test a full build of the app with all dependencies.

```bash
docker compose -f meta/build.local/compose.build.yaml up --build
```

This depends on one shared service from `../dev` (not started by `compose.build.yaml` itself,
since it doesn't persist any data and it's simpler to share one instance):

- **dex** (OIDC provider) - NOT started by `./services` by default. Start it manually first, from
  the `../dev` directory:
  ```bash
  docker compose --profile external up -d hyvor-service-dex
  ```

Notes:

- It uses OIDC authentication (simulating self-hosted env), via the statically-configured
  [dex](https://dexidp.io/) instance above (config at `../dev/meta/compose/dex/dex.config.yaml`).
  Log in with `admin@hyvor.com` / `admin`.
- Real-time features are backed by the built-in Mercure hub (`MERCURE_INTERNAL=true` by default,
  same as self-hosted deploys), not `../dev`'s shared Mercure instance.
