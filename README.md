First update those in *doMyWiki.php* :

- PATH_TO_SVN: the local path to svn (/home/...)
- USERNAME: obvious
- PASSWORD: obvious
- NAME: will be writtent at the top of your wiki
- WIKI_NAME: the file to update on redmine

Then install dependencies:
>php composer.phar install

Finally run it on browser or in terminal:
>php doMyWiki.php

The file will be automatically generated on redmine.
