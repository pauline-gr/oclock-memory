<?php
// src/Service/AccessToApi.php
namespace App\Service;


/**
 * AccessToApi - Service d'accès à l'API
 */
class AccessToApi {    
    /**
     * _response
     *
     * @var array|object $_response
     */
    private $_response;
    
    /**
     * __construct
     *
     * @param  string       $pointeur   Pointeur d'accès qui récupère la route (Exemple '/theme/1')
     * @param  string|null  $donnees    Données à transmettre en POST au format JSON
     * @param  string       $methode    'POST' ou 'GET'
     * 
     * Le constructeur de AccessToApi construit un objet qui va pouvoir lancer des requêtes vers l'API et récupérer le retour
     * 
     * @return void
     */
    function __construct($pointeur, $donnees, $methode) {
        try {
            // Initialisation de curl
            ini_set("allow_url_fopen", 1);
            $ch = curl_init();

            // Définition des paramètres curl
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Si on est sur une méthode POST
            if($methode == 'POST') {
                // On le déclare en paramètres
                curl_setopt($ch, CURLOPT_POST, 1);
                // On transmet les données en data post
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_decode($donnees));
            } 
            
            // On fait pointer la requête API vers la bonne route
            curl_setopt($ch, CURLOPT_URL, 'api/'.$pointeur);

            // On récupère le résultat de la requête
            $result = curl_exec($ch);

            // Clôture de curl
            curl_close($ch);

            // On récupère les données retournées et on les enregistre dans $_reponse
            $obj = json_decode($result);

            // On set _reponse avec le contenu de la réponse
            $this->setResponse($obj);

        } catch (\Exception $e) {
            // En cas d'erreur ou coupe l'exécution pour afficher le message
            die('erreur'.$e->getMessage());
        }
    }
    
    /**
     * getResponse - Getter de _response
     *
     * @return array|object
     */
    public function getResponse() {
        return $this->_response;
    }
    
    /**
     * setResponse - Setter de _reponse
     *
     * @param  array|object $reponse
     * @return void
     */
    public function setResponse($reponse) {
        $this->_response = $reponse;
    }
}