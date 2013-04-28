(php.ini variables to be changed)

enable:
extension=php_mbstring.dll

upload_tmp_dir="C:\Windows\Temp"
session.save_path="C:\Windows\Temp"

also make sure all paths that are used by CaseBox are added to
php_admin_value open_basedir

here is for Windows:
php_admin_value open_basedir "c:/var/www/casebox/;c:/windows/temp;"
