# Docker And GitHub Actions

This project includes a Docker image for the Laravel application and a Compose stack for local development/testing with MySQL, Redis, and Mailpit.

## Prerequisites

- Docker Desktop
- Git
- A local `.env` file copied from `.env.example`

## First-Time Docker Setup

1. Copy the environment file:

   ```bash
   cp .env.example .env
   ```

2. Set these local Docker database values in `.env`:

   ```text
   APP_URL=http://localhost:8000
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=gtu_itr
   DB_USERNAME=root
   DB_PASSWORD=secret
   ```

3. Build and start the containers:

   ```bash
   docker compose up -d --build
   ```

4. Generate the Laravel app key:

   ```bash
   docker compose exec app php artisan key:generate
   ```

5. Run database migrations and seeders:

   ```bash
   docker compose exec app php artisan migrate --seed
   ```

6. Create the storage symlink:

   ```bash
   docker compose exec app php artisan storage:link
   ```

7. Open the app:

   ```text
   http://localhost:8000
   ```

Mailpit is available at:

```text
http://localhost:8025
```

## Daily Commands

Start containers:

```bash
docker compose up -d
```

Stop containers:

```bash
docker compose down
```

View logs:

```bash
docker compose logs -f app
```

Run tests:

```bash
docker compose exec app php artisan test
```

Rebuild after dependency or Dockerfile changes:

```bash
docker compose up -d --build
```

## GitHub Actions

The workflow in `.github/workflows/ci.yml` runs on pushes to `main` and on pull requests.

It performs:

1. PHP dependency install with Composer
2. Node dependency install with npm
3. Asset build with Vite
4. Laravel test suite against MySQL
5. Docker image build verification

To use it:

1. Commit and push the repository to GitHub.
2. Open the repository on GitHub.
3. Go to the `Actions` tab.
4. Select the `CI` workflow.
5. Confirm the latest run is green.

No Docker image is pushed by default. The workflow only verifies that the image builds successfully.
