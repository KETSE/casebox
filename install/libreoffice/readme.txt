(how to install libreoffice, the unoconv connector and the start-office script to launch it)

Windows:
Add to PATH (this will add python.exe from LibreOffice):
C:\Program Files (x86)\LibreOffice 4.0\program

== Get latest UNOCONV ==
At the moment of writing this readme, the original https://github.com/dagwieers/unoconv is outdated (no support for Python3), here is a fork that fixes the issue:
https://github.com/xrmx/unoconv

extract to any folder on your machine, and change /httpsdocs/config.php UNOCONV setting.

To run LibreOffice as a service, run:
> unoconv --listener &