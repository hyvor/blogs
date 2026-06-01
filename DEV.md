> [!IMPORTANT]
> We are undergoing a migration from Laravel to Symfony. Laravel code (/backend) should not be modified, and all new
> code should be written in Symfony (/symfony). If a certain part is not yet migrated to Symfony, work on the migration
> first before adding new features or fixing bugs.

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
cd /app/symfony && bin/console dev:reset --seed

# from host machine:
docker compose exec -it backend bash -c "cd /app/symfony && bin/console dev:reset --seed"
```

### Checks

```bash
# backend tests
docker compose exec backend bash -c "cd /app/backend && php bin/phpunit"

# symfony tests
docker compose exec backend bash -c "cd /app/symfony && php bin/phpunit"

# phpstan
docker compose exec backend bash -c "cd /app/symfony && php vendor/bin/phpstan --memory-limit=1G"

# prettier
docker compose exec frontend npm run format
```

### Supported VSCode Extensions

- Prettier (Svelte and TS files)
- Coverage Gutters (Symfony code coverage)
