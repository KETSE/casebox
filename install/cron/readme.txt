Cron should run under httpd user/group.
If apache is running under apache/apache, add the crontab.txt contents to apache crontab:

> crontab -e -u apache

and copy&paste crontab.txt