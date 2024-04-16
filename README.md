# hugo.alliau.me

My website.

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
symfony serve
```