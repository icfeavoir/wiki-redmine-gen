First update those in *consts.php* :

- PATH_TO_SVN: the local path to svn (/home/...)
- USERNAME: obvious
- PASSWORD: obvious
- USER_ID : your user_id - number (you can find it in the URL on your personnal redmine account);
- NAME: will be writtent at the top of your wiki
- WIKI_NAME: the file to update on redmine

- parentToIgnore: array for the parents that aren't useful (children will be saved anyway)

Needed
> php7.*
> php7-simplexml
> php7-curl

Then install dependencies:
>php composer.phar install

Finally run it on browser or in terminal:
>php doMyWiki.php

The file will be automatically generated on redmine.
