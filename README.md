# Casebox

Casebox is a Content Management Platform for record, file and task management.

Full documentation can be found on the website:
http://docs.casebox.org/en/latest/


## Installation

In order to try Casebox on your local machine, we recommend to use [casebox-vagrant](https://github.com/KETSE/casebox-vagrant.git) provision.


## Tests and Sniffers (for developer)

### CI

Build Status [![Build Status](http://ci.casebox.org:8080/buildStatus/icon?job=casebox/development)](http://ci.casebox.org:8080/job/casebox/job/development)

### Local

Before run tests and sniffers on your local computer, add new Casebox core and name it `test`.

After that login to vagrant using `vagrant ssh` command, and run `/bin/bash /var/www/casebox/tests/run.sh` command.

Tests and sniffers reports will be available in `/var/www/casebox/buil/logs` folder.


## Made at Ketse & HURIDOCS

Casebox is being developed by [Ketse](https://www.ketse.com/) & [HURIDOCS](https://www.huridocs.org/).
