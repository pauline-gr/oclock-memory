<?php
 
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Service\PDODatabase;

class DefaultController extends AbstractController {

    /**
     * @Route("/", name="index")
     * 
     * index - Retourne une erreur pour absence de route
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse {

        return new JsonResponse(array('message' => 'Route introuvable','code' => '500'));
    }

    /**
     * @Route("/difficultes", name="difficultes")
     * 
     * allDifficultes - Retourne la liste de toutes les difficultés
     *
     * @return JsonResponse
     */
    public function allDifficultes(): JsonResponse {

        // Appel au service PDODatabase pour l'exécution de la requête PDO
        $bdd = new PDODatabase();

        // Récupération des résultats
        $difficultes = $bdd->getNiveauxDifficulte();

        // On retourne les résultats en json
        return new JsonResponse($difficultes, 200);
    }

    /**
     * @Route("/difficulte/{id_difficulte}", name="difficulte")
     * 
     * difficulte - Retourne une difficulté donnée par id
     *
     * @param  int $id_difficulte identifiant de la difficulté recherchée
     * @return JsonResponse
     */
    public function difficulte($id_difficulte): JsonResponse {

        // Appel au service PDODatabase pour l'exécution de la requête PDO
        $bdd = new PDODatabase();

        // Récupération du résultat
        $difficulte = $bdd->getNiveauDifficulteById($id_difficulte);
        
        // On retourne le résultat en json
        return new JsonResponse($difficulte, 200);
    }

    /**
     * @Route("/themes", name="themes")
     * 
     * allThemes - Retourne la liste des thèmes
     *
     * @return JsonResponse
     */
    public function allThemes(): JsonResponse {
        
        // Appel au service PDODatabase pour l'exécution de la requête PDO
        $bdd = new PDODatabase();
        
        // Récupération des résultats
        $themes = $bdd->getThemes();

        // On retourne les résultats en json
        return new JsonResponse($themes, 200);
    }

    /**
     * @Route("/theme/{id_theme}", name="theme")
     * 
     * theme - Retourne un thème par id
     *
     * @param  int $id_theme identifiant du thème recherché
     * @return JsonResponse
     */
    public function theme($id_theme): JsonResponse {
        
        // Appel au service PDODatabase pour l'exécution de la requête PDO
        $bdd = new PDODatabase();

        // Récupération du résultat
        $theme = $bdd->getThemeById($id_theme);
        
        // On retourne le résultat en json
        return new JsonResponse($theme, 200);
    }
    
    /**
     * @Route("/addjoueur", name="addjoueur", methods={"GET", "POST"})
     * 
     * addJoueur - Ajoute un joueur et retourne les infos pour ce joueur incluant son id
     *
     * @param  Request $request les données envoyées en POST
     * @return JsonResponse
     */
    public function addJoueur(Request $request): JsonResponse {
        $pseudo = $request->get('pseudo');
        $avatar = $request->get('avatar');
        if(empty($pseudo) || empty($avatar)) {
            return new JsonResponse(array('Erreur' => 'Un élément est manquant'));
        }
        $bdd = new PDODatabase();
        $joueur = $bdd->setJoueur($pseudo, $avatar);
        return new JsonResponse($joueur, 200);
    }

    /**
     * @Route("/addscore", name="addscore", methods={"GET", "POST"})
     * 
     * addScore - Enregistre une partie et retourne la liste des meilleurs scores pour la difficulté et le thème en cours
     *
     * @param  Request $request les données envoyées en POST
     * @return JsonResponse
     */
    public function addScore(Request $request): JsonResponse {
        $joueur_id = $request->get('joueur_id');
        $joueur_score = $request->get('joueur_score');
        $temps_restant = $request->get('temps_restant');
        $theme = $request->get('theme');
        $difficulte = $request->get('difficulte');

        $bdd = new PDODatabase();

        if($joueur_score != 0) {
            if(!$bdd->insertScore($joueur_id, $joueur_score, $theme, $difficulte, $temps_restant)) {
                return new JsonResponse(array('code' => 500, 'message' => 'Une erreur est survenue'), 500);
            }
        }        

        $meilleurs_scores = $bdd->getMeilleursScoresCurrentParams($theme, $difficulte);

        return new JsonResponse($meilleurs_scores, 200);
    }

    /**
     * @Route("/scores", name="scores")
     * 
     * getScores - Retourne la liste des meilleurs scores toutes catégories confondues
     *
     * @return JsonResponse
     */
    public function getScores(): JsonResponse {
        $bdd = new PDODatabase();
        $meilleurs_scores = $bdd->getMeilleursScores();
        return new JsonResponse($meilleurs_scores, 200);
    }
}