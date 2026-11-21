# hugo.alliau.me

My website, built with:

- [Symfony 8.1](https://symfony.com/8)
- [Vite](https://vite.dev/) with [Symfony Reprise](https://github.com/symfony/reprise)
- [Symfony UX CSS](https://github.com/symfony/ux-css)
- [PostgreSQL](https://www.postgresql.org/)
- [EasyAdminBundle](https://symfony.com/bundles/EasyAdminBundle/current/index.html)
- [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/)
- [FrankenPHP](https://frankenphp.dev/fr/)
- [RustFS](https://rustfs.com/) as S3-compatible storage for blog images
- [Imagor](https://github.com/cshum/imagor) as image server, through [Symfony UX Image](https://github.com/symfony/ux/tree/3.x/src/Image)
- [Symfony CLI](https://github.com/symfony-cli/symfony-cli)
- Deployed with [Coolify](https://coolify.io/)

## Requirements

- [Symfony CLI](https://symfony.com/download)
- Docker and Docker Compose
- PHP 8.5
- Node.js 26 and [pnpm](https://pnpm.io/)

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

4. Install the front-end dependencies

```shell
pnpm install
```

## Usage

Run the project:

```shell
make start
```

Run the Vite dev server:

```shell
pnpm dev
```
