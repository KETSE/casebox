(how to install&configure solr, symlink to /opt/solr, then copy files below into correct folders and rename them...)

1. Download Solr 4.1 or higher from http://lucene.apache.org/solr/ and extract it to /usr/local/src for ex.
2. edit the file '$solrHome/example/multicore/solr.xml', see file solr.xml in this folder as an example
3. create data/solr/conf and data/solr/data, copy the contents of the conf/ directory from install to /data/solr/conf

==Starting on Windows==
use 'start_jetty_win.bat' and change the paths.
Once you get solr running, you can install it as a service, here is a guide: http://drupal.org/node/1359598
> nssm install jetty

Application: c:\Windows\System32\java.exe
Params: -jar c:/opt/solr/example/start.jar -Dsolr.solr.home=c:/opt/solr/example/multicore -Djetty.home=c:/opt/solr/example/ -Djava.util.logging.config.file=c:/opt/solr/example/etc/logging.properties
Name: jetty



NOTICE: you should edit the file c:/opt/solr/example/etc/logging.properties and specify an Absolute path for logging:
java.util.logging.FileHandler.pattern = c:/opt/solr/logs/solr%u.log




