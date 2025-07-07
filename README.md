# php-api-service

The `php-api-service` is the core REST API responsible for exposing and managing pornstar
entities and their associated image metadata. It serves as the main interface for clients
to interact with the system's data.

## 🧠 Responsibilities

- Provides RESTful endpoints to list, create, update, and delete `Pornstar` records.
- Manages related entities such as aliases, thumbnails, and thumbnail URLs.
- Listens to the `pornstar-events` queue to synchronize ingested pornstar data into the database.
- Listens to the `image-update` queue to update image metadata (e.g., `local_path`) after caching.
- Optionally retries dead-lettered messages via the `consume:image-update-dead` command.

## ⚙️ Tech Stack

- PHP 8.4
- Laravel 12
- MySQL (with support for master-replica reads)
- RabbitMQ (via AMQP)
- Docker

## 🚀 Usage

This service is automatically started with the Docker infrastructure and exposes a REST API
on:

```
http://localhost:8080/api/v1
```

## 🧪 Testing

Run the test suite inside the container:

```bash
docker exec -it  infra-deployment-php-api-service-1 php artisan test
```

## 📂 Environment

Set the required environment variables via `.env` or using the mounted file
`.envs/php-api-service.env`.

## 🔗 Related

- Consumes feed events from: `php-feed-ingestor`
- Consumes image updates from: `php-image-worker`
- Exposes data to: external clients via REST API
