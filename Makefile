CP=cp -r
PLUGIN=$(HOME)/wordpress/wp-content/plugins/kblog-include
FILES=kblog-include.php kblog-oai-pmh.php kblog-server.php kblog-clean.php kblog-include-cache.php


all:
	$(CP) $(FILES) $(PLUGIN)

russet: 
	rsync -vrtz $(FILES) kblog.ncl.ac.uk:russet/blog/wp-content/plugins/kblog-include
