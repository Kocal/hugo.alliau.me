# hugo.alliau.me

My website.

## Requirements

- [Symfony CLI](https://symfony.com/download)
- Docker and Docker Compose
- PHP 8.3

## Installation

1. Clone the repository and configure the environment:
```shell
symfony local:server:ca:install
symfony local:proxy:domain:attach hugo.alliau.me
```

2. Create a `.env.local` file and configure the environment (if needed):
```shell
$ cp .env .env.local
```

3. Install the dependencies with Composer:
```shell
$ symfony composer install
```

## Usage

Run the project:

```shell
$ docker compose up -d
$ symfony serve
```
