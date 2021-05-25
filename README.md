# Memory O’Clock

## Introduction

Memory O’Clock est un jeu de mémory développé en HTML / SCSS / JQuery / Symfony / PHP en utilisant PDO pour l’accès aux données.

Le projet docker est composé de quatre conteneurs :

 - php : Le conteneur de l’interface front web. Il est développé avec Symfony/Webpack/JQuery/Bootstrap afin de simplifier le routing et l’utilisation de Sass.

	Port utilisé par php : 80

 - api: Le conteneur de l’API qui va faire la liaison avec la base de données. Il est développé avec un package light de Symfony uniquement pour la simplification des routes.

	Port utilisé par api : 81

 - db: Le conteneur de base de données. Il est composé d’une image base de données MySql.

	Port utilisé par db : 3306

 - phpmyadmin: Le conteneur phpmyadmin. Il contient une image PhpMyAdmin pour permettre aux utilisateurs d’accéder à la visualisation des données.

	Port utilisé par php : 8181


*La structure des deux projets Symfony reste sur une base Symfony avec un système de routes utilisant les annotations.*

*Les deux projets fonctionnent comme un projet Symfony habituel à l’exception que la conception des formulaires est faite manuellement et ne passe pas par le système FormType de Symfony.*

*Je n’ai pas utilisé Doctrine sur ce projet, le considérant comme trop lourd pour l’utilisation qu’on allait en avoir et pour offrir des exemples de requêtes SQL dans le code sans ORM. L’accès aux données se fait donc via un service PDODatabase qui crée les requêtes en objet.*

# Installation du projet

The file explorer is accessible using the button in left corner of the navigation bar. You can create a new file by clicking the **New file** button in the file explorer. You can also create folders by clicking the **New folder** button.

## Pré-requis

Si Docker n’est pas installé sur votre poste :

[https://www.docker.com/get-started](https://www.docker.com/get-started)

Assurez-vous que les ports 80, 81, 3306 et 8181 sont disponibles (par exemple si vous avez un wamp, un bitnami ou même un skype de lancé, il est possible que cela bloque le port).

Si un des ports est utilisé et que vous souhaitez le modifier, rendez-vous dans le docker-compose et modifiez les ports définis dans le fichier. Pour 80 :80 remplacez par exemple par 82 :80.

Note : J’ai utilisé powershell pour mes commandes, je n’ai pas eu le temps de tester via bash, utilisez donc powershell de préférence si ça vous est possible 😊

## Installation

 - Clonez le repo git sur votre machine :
	```
	git clone https://github.com/pauline-gr/oclock-memory.git
	```
 - Se positionner dans le repo et lancer les commandes suivantes l’une après l’autre :
	```
    docker-compose build
    docker-compose up
	```
 - Une fois les commandes terminées, certaines commandes docker ne passent pas en automatique, listez donc les conteneurs dans un **autre terminal** :
	```
	docker ps
	```
![enter image description here](https://zupimages.net/up/21/21/8g3i.png)

 - Rendez-vous dans le conteneur php en exécutant:
	```
	docker exec -it oclock-memory_php_1 /bin/bash
	```
***oclock-memory_php_1 étant le nom du conteneur php dans mon docker ps, il peut être différent chez vous. Remplacez le s’il est différent!***
 - Vous vous situez alors dans le conteneur, exécutez successivement :
	```
	composer install

	yarn install

	yarn build
	```

 - Lorsque les installations sont terminées, quitter le conteneur en utilisant
	```
	exit
	```

 - Rendez-vous ensuite dans le conteneur api en exécutant :
	```
	docker exec -it oclock-memory_api_1 /bin/bash
	```
***oclock-memory_api_1 étant le nom du conteneur api dans mon docker ps, il peut être différent chez vous. Remplacez le s’il est différent!***

 - Vous vous situez alors dans le conteneur, exécutez:
	```
	composer install
	```
	
**Le projet peut être lancé !**

## Jouer au Mémory

Maintenant que le projet est configuré,

 - Rendez-vous sur [http://localhost/](http://localhost/) ou [http://127.0.0.1/](http://127.0.0.1/)

La page d’accueil s’affiche.
![enter image description here](https://zupimages.net/up/21/21/2170.png)
Un avatar est rattaché à un pseudonyme: Comme il n’y a pas de connexion, l’avatar peut être modifié par n’importe quel utilisateur qui utilise le même pseudonyme.
Évolution possible : Créer un système de login. La base de données est adaptée à cette possibilité.

 - Sélectionnez votre avatar dans la liste
![enter image description here](https://zupimages.net/up/21/21/4b5t.png)
 - Renseignez un pseudonyme
 
 - Choisissez une difficulté
	*La difficulté détermine la durée de la partie. Il est possible de la modifier dans la base de données.*
 - Choisissez un thème :
	*Le thème détermine les images utilisées pour les cartes. Pour le moment il existe 4 thèmes renseignés en base de données.*

 - Lorsque toutes les informations sont renseignées, cliquez sur 'C'est parti!'

 - Le jeu s’affiche :
![enter image description here](https://zupimages.net/up/21/21/77t6.png)

 - La section du haut affiche les informations du joueur:
 ![enter image description here](https://zupimages.net/up/21/21/735f.png)

### Fonctionnement
 - Le temps restant se décrémente automatiquement. Il en va de même avec la barre de temps située en bas de l’écran.
 - Cliquer sur une carte la retourne, si la paire est valide, elle disparait du plateau :
![enter image description here](https://zupimages.net/up/21/21/66qm.png)
![enter image description here](https://zupimages.net/up/21/21/z7vq.png)
 - Si la paire n’est pas valide, les cartes se retournent face cachée.
 
 - Le score est calculé sur une base de 2 points multipliés par un ratio sur le temps restant. **Plus vous finissez la partie rapidement, plus vous gagnez de points**.
 
 - La partie se termine si toutes les cartes sont retournées ou si le temps est écoulé.
 
 - À la fin de la partie une modale s’affiche pour donner le récapitulatif des informations de la partie :

![enter image description here](https://zupimages.net/up/21/21/hnrm.png)
 - Le message affiche si la personne a gagné ou non. Tous les scores sont enregistrés à partir du moment où ils ne sont pas à 0.
 
 - Si le record personnel précédent est battu, un message personnalisé s’affiche.
 
 - La liste des meilleurs scores s’affiche en dessous.
 
 - Un bouton permet de retourner à l’accueil après une partie.

# Base de données

La création de la base de données et l’insertion des difficultés et thèmes de base se situe dans initdb/creation_tables.sql

La liste des requêtes utilisées se situe dans le modèle api/src/Service/PDODatabase.php

Le fichier est exécuté par Docker lors de son build, uniquement si la base n’existe pas déjà.

Il existe quatre tables dans le projet qui sont conçues sur le schéma suivant :
![enter image description here](https://zupimages.net/up/21/21/jz47.png)

Ce schéma est un MCD (Modèle Conceptuel de Données) basé sur la méthode française Merise 2 [https://fr.wikipedia.org/wiki/Merise_(informatique)](https://fr.wikipedia.org/wiki/Merise_(informatique))

Le MCD permet de passer de simples phrases logiques à une conception optimisée en respectant les propriétés ACID ([https://fr.wikipedia.org/wiki/Propri%C3%A9t%C3%A9s_ACID](https://fr.wikipedia.org/wiki/Propri%C3%A9t%C3%A9s_ACID))

Voici les phrases logiques originales que l’on peut lire en regardant ce schéma :

> Un joueur enregistre un score avec une ou plusieurs difficultés sur un
> ou plusieurs thèmes.   Un thème peut être utilisé par un ou plusieurs
> joueurs sur une ou plusieurs difficultés.
> 
> Une difficulté peut être sélectionnée par un ou plusieurs joueurs sur
> un ou plusieurs thèmes
> 
> Pour une partie enregistrée comporte ces trois paramètres et pour
> chaque partie on a une date, un score et un temps final.

Ces phrases répondant correctement à notre projet, on peut créer la base.

Le modèle logique est le suivant :
---

joueur(**id_joueur**, pseudo_joueur, avatar_joueur),

difficulte(**id_difficulte**, nom_difficulte, temps_difficulte),

theme(**id_theme**, nom_theme),

score(**id_score**, #id_joueur, #id_difficulte, #id_theme, date_score, score, temps_final)

---

# Modification du thème 

Si vous souhaitez créer un nouveau thème, il faudra l’insérer dans la base de données (PhpMyAdmin disponible sur [http://localhost:8181/](http://localhost:8181/) ) et créer le dossier d’images correspondant à son nom en minuscule.

Par exemple si vous souhaitez créer un thème Fruits, il faudra créer un dossier memory/public/images/cards/fruits dans lequel vous pourrez déposer les images que vous souhaitez.

Si vous souhaitez ajouter des cartes, j’ai laissé un modèle de carte vierge à la racine de memory/public/images/cards qui s’appelle vierge.png.

Vous pouvez aussi modifier le dos.png qui correspond au dos de l’image si vous souhaitez mettre quelque chose de plus sympa.

--- 
## (っ▀¯▀)つ En espérant que ça vous plaira ƪ(ړײ)‎ƪ​​
--- 
