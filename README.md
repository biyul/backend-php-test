AskNicely PHP backend skill test
==========================

### Issues

* PHP5.x is EOL (should be 8).
* Silex is EOL.  Docs are [not even online anymore](https://silex.symfony.com/doc/1.3/), but might be deep in packagist.org somewhere.
* Error handling can be improved, in order to avoid displaying technical errors to the user.
* php.ini needs configuring particularly with default timezone.  It's currently hardcoded to "Pacific/Auckland" in index.php.
  
### Improvements
* Need more validations, better exceptions, and automated tests to confirm them.
* This should not be running on a host machine.  Either put it on Vagrant or Docker (done in this repo)
* Strict typing, type hints (from 7/8)
* Lints/sniffers (eg. phpcs)

### Setup

This should work fine if you already have your own setup on your host machine (PHP5.3, MySQL).

But if you want to use Docker:

1. Install Docker Compose (https://docs.docker.com/compose/install/)
2. Run the following:
```bash
cd /path/to/code/root
cp config/config.yml.dist config/config.yml
make build
make up
make asknicely
```

3. Change config/config.yml
```yaml
database:
    host    : mysql-bill12345
    dbname  : ac_todos
    user    : appdb
    password: letmein
```

4. Manually load migrations to within the container (couldn't automate this bit).
   You will be prompted for the password each time, which is "letmein".
```bash
make ssh-mysql
mysql -u root -p ac_todos < /home/database.sql
mysql -u root -p ac_todos < /home/fixtures.sql
mysql -u root -p ac_todos < /home/task2.sql
exit
```

5. Load the website:  http://docker.local:8080

Let me know if you encounter any issue.
