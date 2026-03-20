## Backend

### API

- Controllers should only get and validate input, call services, and return output
- All APIs should return DTO objects (sudo API uses SudoObjectFactory), never entities directly
- Always use MapRequestPayload or MayQueryString for Input DTO. DTO should include Assert validations.

### Code

- Keep repositories empty, always use services.
- Do not DI repositories, instead DI EntityMangerInterface and get the repository.
- Prefer ClockAwareTrait and $this->now() instead of new DateTimeImmutable() for better testability.

## Commands

Run commands within the container:

```bash
# backend tests
docker compose exec hyvor-blogs-backend php bin/phpunit

# phpstan
docker compose exec hyvor-blogs-backend php vendor/bin/phpstan --memory-limit=1G

# prettier
docker compose exec hyvor-blogs-frontend npm run format
```
