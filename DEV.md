## Development Setup

### Running Hyvor Blogs

- First, configure [hyvor/dev](https://github.com/hyvor/dev?tab=readme-ov-file#first-time-setup), our development
  environment.
- Then, run the following command to start Hyvor Blogs locally:

```bash
./run blogs
```

### Database setup

Run the following to reset the database and seed it with sample data:

```bash
# from docker container:
bin/console dev:reset --seed

# from host machine:
docker compose exec -it backend bash -c "bin/console dev:reset --seed"

# note: themes:sync downloads the themes from hyvor/hyvor-blogs-themes Github repository
```

### Checks

```bash
# backend tests
docker compose exec backend bash -c "bin/phpunit"

# phpstan
docker compose exec backend bash -c "vendor/bin/phpstan --memory-limit=1G"

# prettier (format)
docker compose exec frontend npm run format

# svelte-check
docker compose exec frontend npm run check
```

### Supported VSCode Extensions

- Prettier (Svelte and TS files)
- Coverage Gutters (Symfony code coverage)
