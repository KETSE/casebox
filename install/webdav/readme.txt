== WEBDAV support ==

By default we use dav.casebox.org as server name. You need to change ServerName option in webdav conf files.
You can access to webdav typing https://dav.casebox.org/coreName

You need to add one more(at least) virtual host into your apache config, to enable WebDav support.

Copy webdav conf file to your apache conf directory (/etc/httpd/conf by default)
    copy webdav_ssl_casebox.conf, for SSL support
    copy webdav_casebox.conf, for Non-SSL support

Update this conf files with your email, or domain if you want.
Include them to your apache config, by adding to httpd.conf (bottom is the right place):
    Include conf/webdav_ssl_casebox.conf
    Include conf/webdav_casebox.conf


We use SSL certificates that store in [ssl] folder by default. Replace them with your own certificates.
Using webdav service with wrong or expired certificates may cause errors.