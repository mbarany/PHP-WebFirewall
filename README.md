# PHP-WebFirewall
Small PHP App to interface with Linux iptables

## Install / Update
- Install Dependencies
```bash
composer install
```
- Add a User
```bash
php ./scripts/user-manager.php add michael
```
- Run Install/Update script
```bash
sudo ./bin/update
```
- Setup webroot to point to `webroot`
- Tweak `scripts/iptables-default.sh` to your liking and run it
