### PRESENTATION

Plateforme de communication politique spécialisé pour les sourds

### STACK  

###### BACK  
**Serveur** : PHP 7.1+  
**Base de donnée** : MySQL  
**Framework** : Flex / Symfony 3.3+

###### FRONT  
**CSS** :  
**Outils** :   

### INSTALLATION

###### WITH DOCKER

- check docker-compose is installed
- containers installation : `make`
- open a console in php container : `make php`
- project installation : `make install`

###### WITHOUT DOCKER

- check php version >= 7.1
- `cp .env.dist .env`
- install composer
- `composer install`
- edit .env file 
    - replace <mysql_user>, <mysql_password>, <mysql_database>
        `DATABASE_URL=mysql://<mysql_user>:<mysql_password>@127.0.0.1:3306/<mysql_database>`
- for dev
    - `make serve`
    - go to `http://localhost:8000/admin`
- if rights problem : `make init`