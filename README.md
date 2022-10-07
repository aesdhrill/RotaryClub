# Rotary - platforma do generowania PDF rejestracji do wymiany RotaryClub

## Deployment

1. Download repository
```shell
git clone >address<
cd rotaryclub
```

2. Copy config files
```shell
cp symfony/.env symfony/.env.local
cp docker-compose.override.yml.dist docker-compose.override.yml
```

3. Fill `symfony/.env.local` and `docker-compose.override.yml` with actual configuration values

Important ones (example):
```
DOMAIN=http://localhost:8001

MAILER_USER=null
MAILER_PASS=null
MAILER_HOST=null
MAILER_PORT=42

MAILER_TECH_USER=null
MAILER_TECH_PASS=null
MAILER_TECH_HOST=null
MAILER_TECH_PORT=42

POSTGRES_USER=rotaryclub
POSTGRES_PASSWORD=rotaryclub
POSTGRES_DB=rotaryclub

```

4. (Optional to enable backups) Give permissions to backup folder
```
chown -R 999:999 backups/postgres
```

5. Create DB volume
```shell
docker volume create postgres-db-rotary
```

*. You can skip steps 6-11, by running:
```shell
sudo chmod +x cli/dev/update
./cli/dev/update
```

6. Rebuild images
```shell
docker-compose build
```

7. Restart containers
```shell
docker-compose down && docker-compose up -d
```

8. Install/update PHP packages
```shell
docker exec -it lama_php composer install --ignore-platform-reqs
```

9. Install/update JS packages
```shell
docker exec -u root -t lama_php yarn install
```

10. Migrate DB to latest version
```shell
docker exec -it lama_php bin/console doct:migr:migr
```

11. Build assets

```shell
./cli/dev/assets/watch
```
or
```shell
docker exec -u root -t lama_php yarn encore dev
```
If you want to refresh assets in real time add `--watch`

## Migrations

Create migration:
```shell
docker exec -it lama_php bin/console doctrine:migrations:diff
```

Migrate to latest version
```shell
docker exec -it lama_php bin/console doctrine:migrations:migrate
```

## Testing

1. Copy config files
```shell
cp symfony/.env.test symfony/.env.test.local
```

2. Fill `symfony/.env.test.local` with test configuration values

3. (optional) Drop test database:
```shell
docker exec -t lama_php bin/console doctrine:database:drop -e test --force --if-exists
```

4. Create test database:
```shell
docker exec -t lama_php bin/console doctrine:database:create -e test
```

5. Run migrations:
```shell
docker exec -it lama_php bin/console doctrine:migrations:migrate -e test
```

6. Load fixtures:
```shell
docker exec -it lama_php bin/console doctrine:fixtures:load -e test
```

7. Run tests:
```shell
docker exec -t lama_php ./vendor/bin/phpunit
```

You can add `--coverage-text` to show test coverage.

## Production

1. Pull changes
```shell
git pull origin master
```

2. Copy config files
```shell
cp -n docker-compose.prod.yml.dist docker-compose.prod.yml
```

3. Fill `docker-compose.prod.yml` with actual configuration values

4. Build images locally
```shell
docker build -t lama_php docker/php
docker build -t lama_cron docker/php
```

4. Reload stack
```shell
docker stack deploy -c docker-compose.yml -c docker-compose.prod.yml lamaindiab
```

4. Clear cache
```shell
docker exec -t lama_php bin/console c:c
```

5. Migrate DB to latest version
```shell
docker exec -it lama_php bin/console doct:migr:migr
```

6. Optimize Composer Autoloader
```shell
docker exec -t lama_php composer dump-autoload --no-dev --classmap-authoritative
```

7. Build assets
```shell
docker exec -u root -t lama_php yarn encore prod
```

x. Stop stack

```shell
docker stack down lamaindiab
```

# Documentation

## Sending emails using another email address:

1. Add configuration values to `.env.local`

2. Set transport by its name (from `config/packages/mailer.yaml`) before sending the message:
```php
# App\Service\MailSender.php

$this->setTransport('med');
```

## Defining virtual columns

1. Add `options={"virtual": true}` and `columnDefinition` to `@ORM\Column`

2. You can also optionally add `"join": "<FULL JOIN STATEMENT>"` to `options` is needed

Example:
```php
# App\Entity\*

/**
 * @ORM\Column(type="<TYPE>", options={
 *     "virtual": true,
 *     "join": "<FULL JOIN STATEMENT>"
 * }, columnDefinition="<DEFINITION AS IN SQL SELECT>")
 */
private readonly bool $virtualColumn;
```

*Note*: You can use `%TABLENAME%` and `%FQCN%` in `join` and `columnDefinition`;

3. Create and execute migration

4. Verify that the migration contains `CREATE OR REPLACE FUNCTION` for each virtual column
---
**NOTE**   
The first argument of the created function ***will be named the same as the table it refers to***.   
This can result in having multiple variables by the same name working in different scopes and referring to different things in nested subqueries.   
If you want to have a self-referring virtual column (eg. a column that stores a value from another table unique for this entity) please **keep in mind to rename any inner substitutions to the <u>desired table(entity) name</u>**   
Example:
```postgresql
BEGIN SELECT 
          (SELECT CASE .. END FROM
              (SELECT .. FROM table1 t1 WHERE t1.id = <table1>.id) 
          '<subquery alias>')
      INTO xx FROM <table1> t2 WHERE t2.id = $1.id;
RETURN xx; END;
```
---