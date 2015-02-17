== SSL support ==
by default CaseBox is using HTTPS, you may remove SSL entries from ssl_casebox.conf file
and use HTTP mode.

Casebox uses following url style : https://yourdomain.com/coreName/...
If you need to make redirect from subdomain to correct url then
  use ssl_casebox_redirects.conf example.
Both files should be included in apache and you will probably need to use NameVirtualHost directive
for both config to work correctly.
But take into account that apache warns in logs:
[warn] Init: Name-based SSL virtual hosts only work for clients with TLS server name indication support (RFC 4366)

Also another virtual host config is used for webdav support (ssl_casebox_webdav.conf)

== hosts file ==
If you plan to run CaseBox on a local/development server, then add this line to your hosts file:
127.0.0.1       www.yourdomain.com

Win:  C:\Windows\System32\drivers\etc
Unix: /etc/hosts

== HTTPD changes ==
Adjust ssl_casebox.conf and copy it to /etc/httpd/conf/conf.d/ (Unix) or $Apache\conf\extra\ (Win) folder,
then include the file in the SSL configuration, for Win it should be httpd-ssl.conf:

Include conf/extra/ssl_casebox.conf


For Win users: http://wiki.apache.org/httpd/SSLSessionCache
how to enable SSL in Apache: http://blog.lifebloodnetworks.com/?p=677





