-- Dans la mesure ou le joueur n'est pas identifié, on part du principe que son pseudo est unique pour éviter d'avoir de multiples entrées pour un même joueur
USE oclock_memory;

CREATE TABLE IF NOT EXISTS  joueur(
	id_joueur INT NOT NULL AUTO_INCREMENT,
	pseudo_joueur VARCHAR(50) UNIQUE NOT NULL,
	avatar_joueur TEXT NOT NULL,
	CONSTRAINT PK_joueur PRIMARY KEY(id_joueur)
);

CREATE TABLE IF NOT EXISTS  difficulte(
	id_difficulte INT NOT NULL AUTO_INCREMENT,
	nom_difficulte VARCHAR(50) NOT NULL,
	temps_difficulte TIME NOT NULL,
	CONSTRAINT PK_difficulte PRIMARY KEY(id_difficulte)
);

CREATE TABLE IF NOT EXISTS  theme(
	id_theme INT NOT NULL AUTO_INCREMENT,
	nom_theme VARCHAR(50) NOT NULL,
	CONSTRAINT PK_theme PRIMARY KEY(id_theme)
);

CREATE TABLE IF NOT EXISTS  score(
	id_score INT NOT NULL AUTO_INCREMENT,
	id_joueur INT NOT NULL,
	id_difficulte INT NOT NULL,
	id_theme INT NOT NULL,
	date_score DATE NOT NULL,
	score INT NOT NULL,
	temps_final TIME NOT NULL,
	CONSTRAINT PK_score PRIMARY KEY(id_score),
	CONSTRAINT FK_score_joueur FOREIGN KEY(id_joueur) REFERENCES joueur(id_joueur),
	CONSTRAINT FK_score_difficulte FOREIGN KEY(id_difficulte) REFERENCES difficulte(id_difficulte),
	CONSTRAINT FK_score_theme FOREIGN KEY(id_theme) REFERENCES theme(id_theme)
	
);

INSERT INTO difficulte(nom_difficulte, temps_difficulte)
VALUES('Facile', '00:05:00'),('Moyen', '00:02:00'),('Difficile', '00:01:00'), ('Impossible', '00:00:30');

INSERT INTO theme(nom_theme)
VALUES('Memes'), ('Game of Thrones'), ('Streamers'), ('Formes');