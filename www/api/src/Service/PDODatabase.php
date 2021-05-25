<?php
// src/Service/PDODatabase.php
namespace App\Service;

use \PDO;

/**
 * PDODatabase - Service permettant de créer un modèle PDO sans passer par le modèle entity repository de Doctrine
 */
class PDODatabase extends PDO {    
    /**
     * __construct - Constructeur crée la connexion à la base de données depui les variables d'environnement définies dans la conf Apache
     *
     * @return void
     */
    function __construct() {
        try {
            $dsn = 'mysql:dbname='.$_ENV['DB_NAME'].';host='.$_ENV['DB_HOST'].';port='.$_ENV['DB_PORT'];
            parent::__construct($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
            $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * getNiveauxDifficulte - Retourne la liste des niveaux de difficulté
     *
     * @return array
     */
    public function getNiveauxDifficulte() {
        $query = $this->prepare('SELECT `id_difficulte`, `nom_difficulte`, `temps_difficulte` 
                                    FROM `difficulte`');
        $query->execute();
        return $query->fetchAll();
    }
    
    /**
     * getNiveauDifficulteById - Retourne un niveau de difficulté depuis son id
     *
     * @param  int $id_difficulte
     * @return array
     */
    public function getNiveauDifficulteById($id_difficulte) {
        $query = $this->prepare('SELECT `id_difficulte`, `nom_difficulte`, `temps_difficulte` 
                                    FROM `difficulte`
                                    WHERE `id_difficulte` = :id_difficulte');
        $query->bindParam(':id_difficulte', $id_difficulte, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchObject();
    }
    
    /**
     * getThemes - Retourne la liste des thèmes
     *
     * @return array
     */
    public function getThemes() {
        $query = $this->prepare('SELECT `id_theme`, `nom_theme` 
                                    FROM `theme`');
        $query->execute();
        return $query->fetchAll();
    }
 
    /**
     * getThemeById - Retourne un thème depuis son id
     *
     * @param  int $id_theme
     * @return array
     */
    public function getThemeById($id_theme) {
        $query = $this->prepare('SELECT `id_theme`, `nom_theme` 
                                    FROM `theme`
                                    WHERE `id_theme` = :id_theme');
        $query->bindParam(':id_theme', $id_theme, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchObject();
    }
    
    /**
     * setJoueur - Crée un joueur et retourne ses informations
     *
     * @param  string $pseudo
     * @param  string $avatar
     * @return array
     */
    public function setJoueur($pseudo, $avatar) {
            $query = $this->prepare('INSERT INTO joueur(`pseudo_joueur`, `avatar_joueur`)
                                        VALUES(:pseudo, :avatar)
                                        ON DUPLICATE KEY UPDATE `avatar_joueur` = :avatar');
            $query->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
            $query->bindParam(':avatar', $avatar, PDO::PARAM_STR);
            $query->execute();

            $joueur_existant = $this->prepare('SELECT j.`id_joueur`, j.`pseudo_joueur`, j.`avatar_joueur`, MAX(s.`score`) max_score 
                                                FROM `joueur` j LEFT OUTER JOIN `score` s ON j.`id_joueur` = s.`id_joueur`
                                                WHERE `pseudo_joueur` = :pseudo AND `avatar_joueur` = :avatar');
            $joueur_existant->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
            $joueur_existant->bindParam(':avatar', $avatar, PDO::PARAM_STR);
            $joueur_existant->execute();
            return $joueur_existant->fetchObject();
    }
    
    /**
     * insertScore - Enregistre une partie
     *
     * @param  int $id_joueur
     * @param  int $score
     * @param  int $id_theme
     * @param  int $id_difficulte
     * @param  string $temps_final
     * @return array
     */
    public function insertScore($id_joueur, $score, $id_theme, $id_difficulte, $temps_final) {
            $query = $this->prepare('INSERT INTO score(`id_joueur`, `id_difficulte`, `id_theme`, `date_score`, `score`, `temps_final`)
                                        VALUES(:id_joueur, :id_difficulte, :id_theme, NOW(), :score, :temps_final)');
            $query->bindParam(':id_joueur', $id_joueur, PDO::PARAM_INT);
            $query->bindParam(':score', $score, PDO::PARAM_INT);
            $query->bindParam(':id_theme', $id_theme, PDO::PARAM_INT);
            $query->bindParam(':id_difficulte', $id_difficulte, PDO::PARAM_INT);
            $query->bindParam(':temps_final', $temps_final, PDO::PARAM_STR);
            
            $query->execute();
            return true;
    }
        
    /**
     * getMeilleursScoresCurrentParams - Retourne les 10 meilleurs scores pour les paramètres en cours (difficulté/thème)
     *
     * @param  int $id_theme
     * @param  int $id_difficulte
     * @return array
     */
    public function getMeilleursScoresCurrentParams($id_theme, $id_difficulte) {
        $query = $this->prepare('SELECT j.`pseudo_joueur`, j.`avatar_joueur`, s.`date_score`, s.`score`, s.`temps_final`, d.`nom_difficulte`, t.`nom_theme`
                                    FROM `joueur` j JOIN `score` s ON j.`id_joueur` = s.`id_joueur`
                                    JOIN `theme` t ON s.`id_theme` = t.`id_theme`
                                    JOIN `difficulte` d ON s.`id_difficulte` = d.`id_difficulte`
                                    WHERE d.`id_difficulte` = :id_difficulte AND t.`id_theme` = :id_theme 
                                    ORDER BY d.`temps_difficulte`, s.`score` DESC, s.`temps_final` DESC
                                    LIMIT 10');
                                    
        $query->bindParam(':id_theme', $id_theme, PDO::PARAM_INT);
        $query->bindParam(':id_difficulte', $id_difficulte, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * getMeilleursScores - Retourne la liste des 10 meilleurs scores
     *
     * @return array
     */
    public function getMeilleursScores() {
        $query = $this->prepare('SELECT j.`pseudo_joueur`, j.`avatar_joueur`, s.`date_score`, s.`score`, s.`temps_final`, d.`nom_difficulte`, t.`nom_theme`
                                    FROM `joueur` j JOIN `score` s ON j.`id_joueur` = s.`id_joueur`
                                    JOIN `theme` t ON s.`id_theme` = t.`id_theme`
                                    JOIN `difficulte` d ON s.`id_difficulte` = d.`id_difficulte`
                                    ORDER BY d.`temps_difficulte`, s.`score` DESC, s.`temps_final` DESC
                                    LIMIT 10');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
}

