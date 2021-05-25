<?php

// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\AccessToApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DefaultController
 */
class DefaultController extends AbstractController {
    /**
     * @Route("/", name="index")
     * 
     * La route index retourne la page où le joueur va choisir son pseudo et son avatar ainsi 
     * que le thème et la difficulté de sa partie
     * 
     * @return Response
     */
    public function index(): Response {
        
      // On récupère le dossier qui contient les avatars et on crée un tableau vide
      $handle = opendir("images/avatars/");
      $array_avatars = array();

      // On récupère chaque avatar (on vérifie qu'on est pas sur un path . ou .. qui sont listés sur les systèmes Linux)
      while($file = readdir($handle)){
        if($file !== '.' && $file !== '..'){
          // On dépose chaque avatar dans le tableau
          $array_avatars[] = $file;
        }
      }

      // On utilise le service d'appel à l'API pour récupérer les différents niveaux de difficulté
      $req = new AccessToApi('difficultes', null, 'GET');
      $difficultes = $req->getResponse();

      // On utilise le service d'appel à l'API pour récupérer la liste des différents thèmes
      $req = new AccessToApi('themes', null, 'GET');
      $themes = $req->getResponse();

      // On retourne le template de login en envoyant en paramètres twig ce dont on a besoin
      return $this->render('memory/login.html.twig', ['liste_avatars' => $array_avatars, 
                                                        'difficultes' => $difficultes, 
                                                        'themes' => $themes]);
    }
    /**
     * @Route("/memory", name="memory", methods={"GET", "POST"})
     * 
     * La route memory envoie le joueur sur le jeu après avoir vérifié qu'il dispose bien des paramètres obligatoires
     * 
     * @param		Request $request la requête post
     * @return	Response
     */
    public function memory(Request $request): Response {
      
      // Si on n'a pas d'avatar et de pseudo de transmis, on renvoie vers l'index
      if(empty($request->get('avatar_pick')) || empty($request->get('pseudo_pick'))) {
          return $this->redirectToRoute('index');
      }

      // Sinon (comme le return sort de la fonction s'il proc), on récupère les données du post
      $data = json_encode(array('pseudo'=> $request->get('pseudo_pick'), 'avatar' => $request->get('avatar_pick')));

      // On utilise le service d'appel à l'API pour ajouter le joueur
      $req = new AccessToApi('addjoueur', $data, 'POST');
      // L'ajout du joueur retourne son identifiant, on le récupère
      $joueur = $req->getResponse();

      // On utilise le service d'appel à l'API pour récupérer les paramètres de la difficulté sélectionnée
      $req = new AccessToApi('difficulte/'.$request->get('difficulte_pick'), null, 'GET');
      $difficulte = $req->getResponse();

      // On utilise le service d'appel à l'API pour récupérer les paramètres du thème sélectionné
      $req = new AccessToApi('theme/'.$request->get('theme_pick'), null, 'GET');
      $theme = $req->getResponse();

      // On ouvre le dossier qui contient les cartes pour le thème choisit par le joueur et on crée un tableau vide
      $handle = opendir("images/cards/".strtolower($theme->nom_theme));
      $array_cartes = array();

      // Pour chaque carte dans le dossier (on vérifie qu'on est pas sur un path . ou .. qui sont listés sur les systèmes Linux)
      while($file = readdir($handle)){
        if($file !== '.' && $file !== '..'){
          // On dépose chaque carte dans le tableau
          $array_cartes[] = $file;
        }
      }

      // On merge le tableau avec lui même pour créer des paires
      $array_cartes = array_merge($array_cartes, $array_cartes);
      // On mélange le tableau pour que les cartes ne sortent pas toujours dans le même ordre
      shuffle($array_cartes);

      // On retourne le template du jeu en envoyant en paramètres twig ce dont on a besoin
      return $this->render('memory/memory.html.twig', ['joueur' => $joueur, 
                                                          'difficulte' => $difficulte, 
                                                          'theme' => $theme,
                                                          'liste_cartes' => $array_cartes]);
    }

    /**
     * @Route("/finpartie", name="finpartie", methods={"GET", "POST"})
     * 
     * La route memory est utilisée en Ajax et envoie le joueur sur le jeu après avoir vérifié qu'il dispose bien des paramètres obligatoires
     * 
     * @param		Request $request la requête post
     * @return JsonResponse
     */
    public function finpartie(Request $request): JsonResponse {

      // On récupère les paramètres transmis en ajax
      $joueur_id = $request->get('joueur_id');
      $joueur_score = $request->get('score');
      $temps_restant = $request->get('temps_restant');
      $theme = $request->get('theme');
      $difficulte = $request->get('difficulte');

			// On vérifie que tous les paramètres nécessaires sont disponibles
      if(!isset($joueur_id) || !isset($joueur_score) || !isset($temps_restant) || !isset($theme) || !isset($difficulte)) {
				// S'ils ne le sont pas on renvoie une erreur en json (pas de traitement côté front à l'heure actuelle)
        return new JsonResponse(array('code' => 500, 'message' => 'Une donnée est manquante'), 500);
      }

			// On enregistre les données dans un tableau json
      $data = json_encode(array('joueur_id' => $joueur_id, 
                                  'joueur_score' => $joueur_score, 
                                  'temps_restant' => $temps_restant,
                                  'theme' => $theme,
                                  'difficulte' => $difficulte
                                ));

      // On utilise le service d'appel à l'API pour sauvegarder la partie et récupérer les meilleurs scores
			// Note: Un score équivalent à 0 ne sera pas sauvegardé mais retournera tout de même les meilleurs scores
      $req = new AccessToApi('addscore', $data, 'POST');
			// On récupère les meilleurs scores pour le thème et la difficulté en cours retournés par la requête
      $meilleurs_scores = $req->getResponse();

			// On retourne le tableau JSON des meilleurs scores
      return new JsonResponse(array($meilleurs_scores));
  }    

  /**
   * @Route("/scores", name="scores", methods={"GET", "POST"})
   * 
	 * La route scores est utilisée en Ajax pour récupérer la liste des meilleurs scores globale
	 * 
   * @return JsonResponse
   */
  public function scores(): JsonResponse {

		// On utilise le service d'appel à l'API pour récupérer les meilleurs scores
    $req = new AccessToApi('scores', null, 'GET');
    $meilleurs_scores = $req->getResponse();

		// On retourne le tableau JSON des meilleurs scores
    return new JsonResponse(array($meilleurs_scores));
}
}