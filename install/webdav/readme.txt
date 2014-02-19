== WEBDAV support ==

We use SSL certificates that store in [ssl] folder by default. Replace them with your own certificates.
Using WebDav service with wrong or expired certificates may cause errors.

We support two ways to work with Casebox over WebDav protocol.

1. Ordinary exploring your files&folders from WebDav-client, or windows explorer.
   Just connect, or map network drive.
   You can connect (or map drive) by url:
        https://yourdomain.com/dav-[coreName]

2. Direct link to file
   You can download, or edit your file with Word or LibreOffice
        https://yourdomain.com/edit/[coreName]/[fileId]/[fileName]


3. INSERT into `config` (`param`,`value`) VALUES ('webdav_url', 'https://{core_name}.casebox.org/edit/{core_name}/{node_id}/{name}');