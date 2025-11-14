## Install dependencies

```bash
composer install

```

## Copy .env

```bash
cp .env.example .env
```

## Generate Keys

```bash
php artisan key:generate
```


## Run Migrations & Seed

```bash
php artisan migrate --seed
```