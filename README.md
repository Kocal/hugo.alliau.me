# hugo.alliau.me

My website, built with:
- [Symfony 7](https://symfony.com/7)
- [Asset Mapper](https://symfony.com/doc/current/frontend/asset_mapper.html)
- [Tailwind CSS](https://tailwindcss.com/)
- [PostgreSQL](https://www.postgresql.org/)
- [EasyAdminBundle](https://symfony.com/bundles/EasyAdminBundle/current/index.html)
- [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/)
- [FrankenPHP](https://frankenphp.dev/fr/)
- [Symfony CLI](https://github.com/symfony-cli/symfony-cli)
- Deployed with [Coolify](https://coolify.io/)

## Requirements

- [Symfony CLI](https://symfony.com/download)
- Docker and Docker Compose
- PHP 8.3

## Installation

1. Clone the repository
2. Create a `.env.local` file and configure the environment (if needed):
```shell
cp .env .env.local
```
3. Install the environment and project
```shell
make install
```

## Usage

Run the project:

```shell
make start
```