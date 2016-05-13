Casebox
=======

Casebox is a Content Management Platform for record, file and task management.

Full documentation can be found on the website:
http://docs.casebox.org/en/latest/


Installation
------------

In order to try Casebox on your local machine, we recommend to use [casebox-vagrant](https://github.com/KETSE/casebox-vagrant.git) provision.


Made at Ketse & HURIDOCS
-------------------------

Casebox is being developed by [Ketse](https://www.ketse.com/) & [HURIDOCS](https://www.huridocs.org/).


Tests and Sniffers (for developer)
----------------------------------

Before run tests and sniffers, add new Casebox core from Casebox Admin UI and name it `test`.

After that login to vagrant using `vagrant ssh` command, and run `/bin/bash /var/www/casebox/tests/run.sh` command.

Tests and sniffers reports will be available in `/var/www/casebox/buil/logs` folder.


Code status
-----------

[![Build Status](https://travis-ci.org/KETSE/casebox.svg?branch=master)](https://travis-ci.org/KETSE/casebox)

[![Coverage Status](https://coveralls.io/repos/github/KETSE/casebox/badge.svg?branch=master)](https://coveralls.io/github/KETSE/casebox?branch=master)