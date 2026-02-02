## Hyvor Blogs
*All-in-one Blogging Platform*

Hyvor Blogs is a platform to create a blog, manage it, and grow it without having to worry about managing servers, databases, and other technical stuff.

## Contributing

Directory structure:

- `/backend`: Laravel API backend
- `/frontend`: SvelteKit frontend

### Project setup
#### Step 1: Setup hyvor/dev
Visit [hyvor/dev](https://github.com/hyvor/dev) to set up the HYVOR development environment.
#### Step 2: Setup .env file
Copy `.env.dev` to `.env`, run `cp .env.dev .env`. Update the `.env` file with the correct values.
#### Step 2: Setup hyvor/blogs
From hyvor/dev directory, run `./run blogs` to start Hyvor Blogs at `https://blogs.hyvor.localhost`.
#### Step 3: Run database migrations
Connect to the hyvor-blogs-backend container and run `php artisan refresh:dev`.
The first time you run this command, it will take a while to download the themes from GitHub.

That's it! You can now access the Hyvor Blogs at `https://blogs.hyvor.localhost`.