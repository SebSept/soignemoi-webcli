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

@todo

## Gestion de projet

J'ai opéré une gestion de projet relativement classique, avec les phases suivantes :

1. Pré-démarrage ([livrable](https://github.com/SebSept/ecf-docs/blob/main/livrables/1.pr%C3%A9d%C3%A9marrage.odt)) 
2. Cadrage ([livrable](https://github.com/SebSept/ecf-docs/blob/main/livrables/2.cadrage.odt))
3. Spécifications fonctionnelles, planification & chiffrage ([cahier des charges](https://github.com/SebSept/ecf-docs/blob/main/livrables/documents/cahier%20des%20charges%20v2.odt), [roadmap](https://github.com/SebSept/ecf-docs/blob/main/livrables/documents/Roadmap.gan), [planning](https://github.com/SebSept/ecf-docs/blob/main/livrables/documents/Planing.gan))
4. Conception technique ([livrables](https://github.com/SebSept/ecf-docs/tree/main/livrables/4.conception%20technique))
5. Réalisation technique
6. Tests et correctifs
7. Mise en production, formation, maintenance
8. Garanties

[depot dédié aux documents](https://github.com/SebSept/ecf-docs).

## Documentation technique

- Reflexion initiale technologique initiales sur le sujet @todo
- Configuration de l'env de travail @todo
- MCD @todo
- Diagramme d'utilisation @todo
- Plan de test @todo

Kanban : lien donné dans la copie. @todo : remettre ici