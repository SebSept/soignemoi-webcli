# Ecf SoigneMoi - Client web

Demo.  
Projet pour l'évaluation en cours de formation de mon Bachelor.

## Installation

L'installation et l'infrastucture sont déclinées en 2 variantes, la variante développement et la variante production.  

Des exemples d'installation sont référencés dans le fichier .justfile et dans les workflows GitHub Actions.  
[.justfile](.justfile)  
[.github/workflows](.github/workflows)

Utiliser le task runner [just](https://just.systems/man/en/) est recommandé et simplifiera le travail (et le developpement).

### Exemple d'installation en développement

#### Avec _just_

- cloner le dépot
- créer le réseau docker 'partage' (une seule fois) : `docker network create partage` (qui sert à communiquer avec l'api en local)
- lancer la commande `just update`

#### Sans _just_

##### Première installation

- cloner ce dépôt
- créer le réseau docker 'partage' (une seule fois) : `docker network create partage` (qui sert à communiquer avec l'api en local)
- configurer l'accès à l'api en modifiant le fichier _.env.local_ (faire une copie de _.env_ au préalable) (utiliser l'api de prod au besoin).

##### Installations suivantes

- lancer la création de l'infrastructure : `docker compose -f compose-dev.yaml up -d --build` (la détruire si elle est mise à jour (voir les recettes dans le fichier .justfile))
- lancer l'installation du projet : `docker compose -f compose-dev.yaml exec -it php composer install`

Si vous avez des problèmes de droits de fichier, il faut donner que les fichiers et dossiers appartiennent à l'utilisateur www-data (id 82 généralement)
et/ou à votre utilisateur local, pour modifier les fichiers.
En cas de problème, référez-vous au worflow de déploiement GitHub Actions : [.github/workflows/deploy.yaml](.github/workflows/deploy.yaml)

---

Le projet est accessible à l'adresse http://localhost:$NGINX_PORT (voir fichier _.env_) (ou simplement avec la commande `just browser`).

## Création de la base de données

Pas de base de données pour le client.  
Seule l'API nécessite une base de données.

La base de données est sur un volume docker persistant.  
### Avec _just_
`just db-create db-fixtures-load`

### Sans _just_
```shell
docker compose -f compose-dev.yaml exec -it php ./bin/console doctrine:database:drop --quiet --no-interaction --if-exists --force
docker compose -f compose-dev.yaml exec -it php ./bin/console doctrine:database:create --quiet --no-interaction
docker compose -f compose-dev.yaml exec -it php ./bin/console doctrine:schema:create --quiet --no-interaction
docker compose -f compose-dev.yaml exec -it php ./bin/console doctrine:fixture:load --no-interaction
```

## Identifiants de connexion

Les identifiants de connexion sont définis dans les fixture (coté api).
- patient@patient.com : hello
- secretaire@secretaire.com : hello
- doctor@doctor.com : hello

## Charte Graphique

Je n'ai pas encore réalisé de charte graphique, je vais le faire plus tard.  

J'ai réalisé les [wireframes](https://github.com/SebSept/ecf-docs/blob/main/livrables/4.conception%20technique/Wireframe%20v1%20-%20svg.zip) 

## Gestion de projet

J'ai opéré une gestion de projet relativement classique, suivi d'une phase plus agile, à partir de la fin de l'étape , après la première livraison.

Les phases suivantes :

1. Pré-démarrage ([livrable](https://github.com/SebSept/ecf-docs/blob/main/livrables/1.pr%C3%A9d%C3%A9marrage.odt)) 
2. Cadrage ([livrable](https://github.com/SebSept/ecf-docs/blob/main/livrables/2.cadrage.odt))
3. Spécifications fonctionnelles, planification & chiffrage ([cahier des charges](https://github.com/SebSept/ecf-docs/blob/main/livrables/documents/cahier%20des%20charges%20v2.odt), [roadmap](https://github.com/SebSept/ecf-docs/blob/main/livrables/documents/Roadmap.gan), [planning](https://github.com/SebSept/ecf-docs/blob/main/livrables/documents/Planing.gan))
4. Conception technique ([livrables](https://github.com/SebSept/ecf-docs/tree/main/livrables/4.conception%20technique))
5. Réalisation technique
6. Tests et correctifs
7. Mise en production, formation, maintenance
8. Garanties

[depot dédié aux documents](https://github.com/SebSept/ecf-docs).  
J'ai également utilisé Kanboard pour la phase _agile_ (lien donné avec la copie du projet).

## Documentation technique

### Reflexion initiale technologique sur le sujet

Extrait de la note de prédémarrage :

> Les choix techniques seront réalisés de façon à produire une solution robuste, standard et maintenable. Dans un soucis de réduction des temps de développement et de formation, les technologies de référence seront préférées.

Pour la developpement et la mise en production j'ai décider de m'appuyer sur Docker pour simplifier le travail de déploiement et de réplication.

J'ai choisi _GitHub_ pour héberger le code et qui permet d'automatiser les taches de déploiement et de contrôle du code.

J'ai également décider d'adopter une approche _api first_ et de construire un client web complètement indépendant de l'api (machine différente).
Symfony répond aux éxigences de qualité et robusteste et maintenabilité évoquée plus haut. Api Platform est un choix pertinent (qualité, maturité) pour la construction de l'api avec Symfony.

En résumé ma première approche de la question s'est faite au regard de l'architecture et du déploiement et de la qualité du code.


### Configuration de l'environnement de travail

Pour l'environnement de travail, j'ai choisi de réduire au maximum les besoins sur les machines hotes (developpeur, ci, production). Je me suis donc appuyé sur Docker et Docker Compose.  
On a ainsi une configuration de l'environnement de travail très simple et complètement identique (à la différence des versions de Docker qui peuvent différer sans que cela ne pose de problème).

On a ainsi uniquement Docker, Docker Compose et git à installer sur la machine hote.

### MCD - Modèle Conceptuel de Données

J'ai réalisé un diagramme de classe.  
Je n'ai pas réalisé de MCD, car Doctrine génère ce qui concerne les tables de la base de données.  
Je vais réaliser ce diagramme prochainement pour la documentation.

Il sera aussi pertinent d'offir une représentation des données de l'api (api platform offre ces possibilités).

### Diagramme d'utilisation

Pas encore réalisé, prochainement.

## Plan de test

Nous avons les composant sécurité, routage, api plateform, validation et doctrine. La plupart de ces composants demandent uniquement de la configuration pour fonctionner, et fonctionner ensemble. Dans ce cadre, j'ai mis l'accent sur les tests d'intégration.  
J'ai developpé quelques tests unitaires, très peu étaient possible, d'autres  reposent cependant sur des mocks et sont ainsi moins pertinents.  
J'ai réalisé quelques tests fonctionnels (très basiques) pour vérifier que le client et le serveur communiquent correctement, notament au niveau de l'authentification (obtention de token). 

## Kanban

Lien donné dans la copie.