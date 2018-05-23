First update those in *consts.php* :

- PATH_TO_SVN: the local path to svn (/home/...)
- USERNAME: obvious
- PASSWORD: obvious
- USER_ID : your user_id - number (you can find it in the URL on your personnal redmine account);
- NAME: will be writtent at the top of your wiki
- WIKI_NAME: the file to update on redmine
- PUBLISH: true if you want the generator to publish directly on redmine, false if you want to get the output without publishing.

- parentToIgnore: array for the parents that aren't useful (children will be saved anyway)
- parentToCreate: array of parents to create (with list of existing parent inside)

Needed
> php7.*

**sudo apt-get install php**

> php7-simplexml

> php7-curl

> install all important php packages

**sudo apt-get install php7.0-dev php7.0-curl php7.0-xml**

>Then install dependencies:
**php composer.phar install**

Finally run it on browser or in terminal:
**php doMyWiki.php**

The file will be automatically generated on redmine if PUBLISH is set to true. It could take some time (around 1 minute).

If you think that it is not precise enough, don't forget to add parent issues to **parentToIgnore** like this :
```php
$parentToIgnore = array(12345, 1020, 5602);
```
Then issues 12345, 1020 and 5602 will be exploded (only their child will be considered as parent issues).

You can create you own parent issues if you want to group some parents with **parentToCreate**. For example if you want to group all issues child of 1234 and all issues child of 7890, you can do :
``` php
parentToCreate = array(
  'Others'=>array(1234, 7890),
);
```
