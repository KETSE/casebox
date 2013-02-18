== hosts file ==
If you plan to run CaseBox on a local/development server, then add this line to your hosts file:
127.0.0.1       ww2.casebox.org

Win:  C:\Windows\System32\drivers\etc
Unix: /etc/hosts

== HTTPD changes ==
Adjust ssl_casebox.conf and copy it to /etc/httpd/conf/conf.d/ (Unix) or $Apache\conf\extra\ (Win) folder, 
then include the file in the SSL configuration, for Win it should be httpd-ssl.conf:

Include conf/extra/ssl_casebox.conf

For Win users: http://wiki.apache.org/httpd/SSLSessionCache
how to enable SSL in Apache: http://blog.lifebloodnetworks.com/?p=677





