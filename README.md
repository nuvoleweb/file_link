# File Link

This module provides a field that extends the core Link module field by storing metadata about the target file like size
and mime-type. The link URI must point to file not to a directory. The site builder can define a list of allowed target
file extensions.

# Dependencies

- Link module (core).
- File module (core).

# Supporting organizations

- UN World Food Programme - http://www.wfp.org
- Nuvole - http://nuvole.org

# Use Docker Compose

Setup:

```
$ cp docker-compose.yml.dist docker-compose.yml
$ docker-compose exec -u www-data php ./vendor/bin/phpunit
```

Run:

```
$ docker-compose up -d
```

Then:

```
$ docker-compose exec web composer install
$ docker-compose exec web ./vendor/bin/run drupal:site-install
```

Your test site will be available at [http://localhost:8080/build](http://localhost:8080/build).

Run coding style checks:

```
$ docker-compose exec web ./vendor/bin/grumphp run
```

Run tests:

```
$ docker-compose exec web ./vendor/bin/phpunit
```

You can disable HTTP redirect following on field validation by setting the following in you `settings.php`:

```
$settings['file_link.follow_redirect_on_validate'] = FALSE;
```

This could be useful to speed up bulk import content operations.

# Author

- Claudiu Cristea - https://www.drupal.org/u/claudiu.cristea
- Nuvole - http://nuvole.org
