# Rédigez un Readme qui explique comment lancer l'application à partir d'un serveur ou d'un PC neuf
## Résumé

Pour lancer l'application à partir d'un serveur ou d'un PC neuf, il faut effectuer les étapes suivantes :

## Clonez le projet

```bash
git clone https://github.com/CHAOUCHI/phase3-symfony-facturation.git
```

## Placez-vous dans le bon projet en ligne de commande
```bash 
cd /home/user/Bureau/phase3-symfony-facturation
```

## Ouvrez le projet via Visual Studio Code avec la commande suivante :
    ```bash
    code .
    ```
 
 ## Faites cette ligne de commande pour savoir si composer est installé et connaître sa version associée
 
 ```bash
    composer -v
   ```


## Faites cette ligne de commande pour savoir si composer est installé ( et connaître sa version associée)
 
```bash   
  composer install
  composer -v affiche Composer version 2.9.5 2026-01-29 11:40:53
  composer -v
  composer install
  composer -v affiche Composer version 2.9.5 2026-01-29 11:40:53
  ```

## Vérifiez les prérequis

```bash
  php -v
  symfony check:requirements                
  symfony -V affiche (Symfony CLI version 5.15.1 (c) 2021-2026 Fabien Potencier (2025-10-04T08:05:57Z - stable))
  symfony help
  installer les dépendances manquantes
  vérifier le .env
```


## Lancez le projet
```bash  
symfony server:start
symfony console tailwind:build --watch
symfony console clear:cache ci-besoin
travailler sur le projet
```


## Stoppez le projet
```bash
symfony server:stop
```


## Base de données MySqlite

### Ouvrez un terminal à part
```bash
cd  /home/user/Bureau/phase3-symfony-facturation
cd var/data_dev.db
sqlitebrowser var/data_dev.db
``` 



# Cahier des charges
Lien du Cahier des charges : https://github.com/CHAOUCHI/cdpi-dwwm/blob/phase3-symfony-docker-test/Phase%203%20-%20Symfony%20Docker%20et%20Test/Symfony/Projets/SaaSFacturation/Cahier%20des%20charges.md

## 1. Activé les issues dans le parametre du répo GitHub

## 2. Créer une branche de développement
- Créer une branche de développement à partir de la branche principale (main ou master) pour travailler sur les nouvelles fonctionnalités et les corrections de bugs sans affecter la branche principale.

## 3. Créer des issues pour chaque tâche
- Créer des issues pour chaque userstory (faite un copié collé des US et de leurs CA en tant que description de l'issue)
- Démarrer un github Project pour suivre l'avancement des issues (ex: To Do, In Progress, Done)