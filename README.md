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