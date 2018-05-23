First update those in *consts.php* :

- PATH_TO_SVN: the local path to svn (/home/...)
- USERNAME: obvious
- PASSWORD: obvious
- USER_ID : your user_id - number (you can find it in the URL on your personnal redmine account);
- NAME: will be writtent at the top of your wiki
- WIKI_NAME: the file to update on redmine
- PUBLISH: true if you want the generator to publish directly on redmine, false if you want to get the output without publishing.

- parentToIgnore: array for the parents that aren't useful (children will be saved anyway)

Needed
> php7.*
> php7-simplexml
> php7-curl

Then install dependencies:
>php composer.phar install

Finally run it on browser or in terminal:
>php doMyWiki.php

The file will be automatically generated on redmine if PUBLISH is set to true. It could take some time (around 1 minute).

If you think that it is not precise enough, don't forget to add parent issues to **parentToIgnore** like this :
$parentToIgnore = array(12345, 1020, 5602);
Then issues 12345, 1020 and 5602 will be exploded (only their child will be considered as parent issues).