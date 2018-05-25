**Wiki-redmine-gen** a été développé dans le but de simplifier la création des Wiki dans le contexte du ProSE. Il est réalisé en PHP.

### Exigences
> php7.*

Pour savoir la version utilisée : **php −−version**
Pour installer php : **sudo apt-get install php**

> php7-simplexml

> php7-curl

Pour installer les packages importants : **sudo apt-get install php7.0-dev php7.0-curl php7.0-xml**

### Concernant votre projet

Vos commits doivent contenir le nom de la tâche associée précédé d'un "#", par exemple #1234.

Seules les tâches dans lesquelles vous avez loggué du temps seront affichées.

Le pourcentage effectué pour une tâche est calculé à partir du ratio de votre nombre d'heures loggués pour cette tâche par le nombre total d'heures loggués pour cette tâche.

Si votre dépôt local n'est pas à jour, il se peut que certaines références vers des commits ne soient pas pris en compte.

### Utilisation de wiki-redmine-gen
#### 1. Installation des dépendances
Après avoir téléchargé le projet, il faut installer les dépendances nécessaires : **php composer.phar install**

Cette commande doit être executée dans le dossier contenant le projet et ne doit pas retourner d'erreur.

#### 2. Modification des constantes

Les valeurs du fichier *consts.php* doivent être modifiées. Voici la liste ainsi que leur signification :

- PATH_TO_SVN: le chemin local vers votre svn (/home/...)
- USERNAME: votre nom d'utilisateur Redmine
- PASSWORD: votre mot de passe Redmine
- USER_ID : votre numéro d'utilisateur redmine. Pour le connaître, allez sur Redmine puis cliquez sur **Connecté en tant que ...**. Votre id est présent dans l'url indiqué (.../redmine/user/[id])
- NAME: le nom qui sera affiché en haut de votre wiki. Vous pouvez mettre ce que vous voulez ici
- WIKI_NAME: le nom du fichier de votre wiki. Pour le connaître, allez sur votre wiki et récupérez le nom dans l'url (par exemplle Prenom_NOM)
- PUBLISH: Mettez *true* si vous voulez le wiki directement publié sur Redmine, *false* si vous voulez récupérer le wiki générer.

- parentToIgnore: liste de tous les parents à ignorer. Le générateur va regrouper les tâches par tâche parente. Si une tâche parente est trop "vaste" et que vous voulez la rediviser, vous pouvez l'ajouter ici (exemple plus bas)
- parentToCreate: liste des parents à créer, contenant eux-même une liste de parents à leur ajouter (exemple plus bas)

#### 3. Génération du Wiki

**php doMyWiki.php**

Le fichier sera automatiquement publié sur Redmine si la constante *PUBLISH* est *true*. Cela peut prendre du temps en fonction de la connexion internet (moins d'une minute normalement).

#### 4. Exemples

Exemple d'utilisation de *parentToIgnore* pour rediviser une tâche :

```php
$parentToIgnore = array(12345, 1020, 5602);
```
Les tâches 12345, 1020 et 5602 seront divisés et leurs tâches filles directent seront considérées comme tâches parentes.

Exemple d'utilisation de *parentToCreate*.
``` php
parentToCreate = array(
  'Autres'=>array(1234, 7890),
);
``` 
Voici un exemple si vous voulez regrouper toutes les tâches 1234 et 7890, ainsi que leurs sous-tâches dans une nouvelle tâches nommées *Autres*.
