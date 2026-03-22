Getting started (within the Docker container):

```bash
cp .env.dev .env

cd /app/symfony && bin/console dev:reset && \
  cd /app/backend && php artisan db:seed
```
