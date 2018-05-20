# github-release-notifier

An application to be notified by email of the latest release of github repositories.

The command line automatic installer prompts for all the configuration settings, including database bootstrap, SMTP credentials, cache driver, and notifiable email address.

## Server requirements

- PHP >= 7.2.0
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- [composer](https://getcomposer.org/)
- Mysql or MariaDB with a database ready to be used
- If you want to use Redis as your cache driver you need to install a Redis server

## Installation

```bash
git clone https://github.com/hedii/github-release-notifier.git
cd github-release-notifier && composer install
php artisan github:install
```

Add a cron job for the application (on a unix system `sudo crontab -e`)

```bash
* * * * * php /path/to/github-release-notifier/artisan schedule:run >> /dev/null 2>&1
```

## Usage

Add a Github repository to the watched repositories

```
php artisan github:watch-repo

What Github repository do you want to watch?:
 > hedii/github-release-notifier

The Github repository hedii/github-release-notifier has been added to the watched repositories
```

Manually trigger the release watch (if you setup a cron job, it is automatically run every 10 minutes), and get notified about new releases

```
php artisan github:watch-releases

Latest release 1.0.0 added to the Github repository hedii/github-release-notifier
```

## License

github-release-notifier is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
