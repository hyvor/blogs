# Contributing

We welcome contributions to this project!

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
docker compose exec -it hyvor-blogs-backend bash -c "cd /app/symfony && bin/console dev:reset --seed"
```
