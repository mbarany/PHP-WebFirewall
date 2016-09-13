# PHP-WebFirewall
Small PHP App to interface with Linux iptables. It uses the Silex micro-framework.

## Install
1. Clone this repo
1. Install Dependencies `composer install`
1. Tweak `scripts/iptables-default.sh` to your liking and run it `sudo ./scripts/iptables-default.sh`
1. Run Install/Update script `sudo ./bin/update`
1. Setup webserver webroot to point to `webroot`
1. Add a User `php ./scripts/user-manager.php add michael`

## Development / Testing
You can use the PHP built-in server
```bash
php -S localhost:8000 -t "webroot/" -d "date.timezone=America/New_York"
```
To enable Silex App debugging set the following environment variable `DEBUG=1`
```bash
DEBUG=1 php -S localhost:8000 -t "webroot/" -d "date.timezone=America/New_York"
```

## Update
1. Pull in latest changes from Github `git pull origin master`
1. Run Install/Update script `sudo ./bin/update`
