$(function() {
  
  // Récupération des variables
  let joueur_id 	= $('#div_joueur').attr('data-joueur-id');
  let theme 		= $('#div_partie_infos').attr('data-theme');
  let difficulte 	= $('#div_partie_infos').attr('data-difficulte');
  let joueur_record = $('#max_score').text();
  let hms 			= $('#temps_partie').attr('data-temps');

  // Déclaration des variables
  let score = 0;
  let temps = 100;

  // On découpe le temps de partie sur ses ':' (on a récupéré la valeur time h:m:s dans data-temps de la div temps_partie)
  let t = hms.split(':');
  
  // On calcul le nombre de secondes dans le temps indiqué
  let secondes = (+t[0]) * 60 * 60 + (+t[1]) * 60 + (+t[2]); 

  // On calcule à combien équivaut une intervale du nombre de secondes en pourcentage basé sur 1000 qui est l'intervale d'une seconde de timer
  let intervale_temps = secondes / 100 * 1000;
  
  // On crée l'intervale de timer qui exécute la fonction frame (un peu plus bas) tous les 'intervale_temps' défini ci-dessus
  let interval = setInterval(frame, intervale_temps);
  

  // Au clic sur une carte
  $('.card').on('click', function(){
    
    // Si on a moins de 2 cartes retournées (0 ou 1) et que le temps n'est pas fini
    if($('.is-flipped').length < 2 && temps != 0) {

		if($(this).hasClass('is-flipped')) {
			// Si la carte est déjà retournée on la repasse à l'envers
			$(this).removeClass('is-flipped');
			// Pour éviter les soucis de perspective on cache et on affiche les sections nécessaires
			$(this).children( ".card__face--front" ).show();
			$(this).children( ".card__face--back" ).hide();
		} else {
		
			// Sinon, si la carte n'est pas déjà retournée, on la retourne
			$(this).addClass('is-flipped');
			// Pour éviter les soucis de perspective on cache et on affiche les sections nécessaires
			$(this).children( ".card__face--back" ).show();
			$(this).children( ".card__face--front" ).hide();
		}
    }

	// Si on a exactement deux cartes retournées
    if($('.is-flipped').length == 2) {
		
		// On pose un timeout sur la fonction pour que la carte ait le temps de s'afficher visuellement
		setTimeout(function(){

			// On définit une variable carte qui va récupérer la première valeur pour la comparer à la seconde
			let card = '';
			// Pour chacune des deux cartes
			$( ".is-flipped" ).each(function( index ) {

				// Si la variable card est toujours vide on renseigne la carte dedans
				if(card == '') {
					card = $(this).attr('data-card');
				} else {
					// Si la variable n'est pas vide, on effectue la comparaison de cartes
					// Si elles sont identiques
					if($(this).attr('data-card') == card) {
						// On ajoute les points au score (2 points plus un ratio sur le temps restant)
						// Note: On valorise les scores qui se sont faits plus rapidement, plus il reste de temps à la fin plus le score est élevé
						score += Math.round(2+(temps/10));

						// On modifie le contenu de la div qui affiche le score actuel
						$('#current_score').text(score);
						// On ajoute la classe found pour faire disparaitre visuellement les cartes trouvées
						$( ".is-flipped" ).addClass('found');

						// Si toutes les cartes ont été retournées, on termine la partie
						if($('.card').length == $('.found').length) {
							endGame();
						}
					} else {
						// Si les cartes ne sont pas identiques on les retourne face cachée
						$( ".is-flipped" ).children( ".card__face--front" ).show();
						$( ".is-flipped" ).children( ".card__face--back" ).hide();
					}
					// On retire la classe is-flipped
					$( ".is-flipped" ).removeClass('is-flipped');
				}
			});
		},1000); 
    }
  });

  // Lors du clic sur le bouton de retour à l'accueil on renvoie à l'index
  $('#retour_accueil').on('click', function(){
    window.location.replace('/');
  });

  // La fonction frame est appelée dans le setinterval
	function frame() {
		
		// Si le temps de la partie est écoulé on met fin au jeu
		if (temps <= 0) {
			endGame();
		} else {
			// Sinon à chaque seconde on décrémente le temps restant
			temps--;
			// Modification dans la div textuelle en haut de page
			$('#temps_partie').text(convertSecondesToTime(( temps * secondes / 100 )));
			// Décrémentation de la progressbar
			$("#barre").width(temps + "%");
		}
	}

	// Fonction de fin de partie
	function endGame() {
		// Requête ajax transmise au controlleur
		$.ajax({
			type: "POST", 
			url: "/finpartie",
			data: {joueur_id: joueur_id, difficulte: difficulte, theme: theme, score: score, temps_restant: '00:'+convertSecondesToTime( (100-temps) * secondes / 100  )},
			dataType: 'json',
			beforeSend: function () {
				// La partie étant terminée, on stoppe l'intervalle pour enregistrer le temps restant
				clearInterval(interval);
			},success: function (data) {
				// Si la requête vers l'API s'est bien déroulée et qu'on a un retour
				if (data) {
					// On initialise un message vide
					let message = '';

					// Note: Il n'y a que des participants, jamais de perdants! Dommage équivaut à "Perdu" mais les scores sont tout de même enregistrés!

					// Si toutes les cartes n'ont pas été trouvées on affiche un message personnalisé
					if($('.card').length != $('.found').length) {
						message += 'Dommage, tu n\'as pas réussi à finir à temps ...';
					} else {
						// Sinon on affiche un autre message
						message += 'Bravo! Tu as réussi à finir à temps !';
					}

					// Si le joueur a réussi à battre son record personnel, on l'en informe
					if(score > joueur_record) {
						message +='<br/>Incroyable! Tu as battu ton record personnel! Nouveau record à battre: ' + score;
					} 

					// Affichage du message dans la div prévue
					$('#message_fin_partie').html(message);

					// Affichage des 10 meilleurs scores
            		// On appelle la fonction de création du tableau bootstrap (meilleurs_scores.js)
					setTableauBootstrap(data);
					
					// On empêche l'utilisateur de sortir de la modale un fois le jeu terminé
					$('#fin_de_partie').modal({
						backdrop: 'static',
						keyboard: false
					});

					// On affiche la modale
					$('#fin_de_partie').modal('show');
				}
			},
		});
	}
});

// Fonction de conversion des secondes en temps string
function convertSecondesToTime(totalSeconds) {
	// Source: https://stackoverflow.com/questions/1322732/convert-seconds-to-hh-mm-ss-with-javascript

	totalSeconds %= 3600;
	let minutes = Math.floor(totalSeconds / 60);
	let seconds = totalSeconds % 60;


	// Si on veut que les strings restent préfixées par des zéros on utilise la fonction padStart
	minutes = String(Math.round(minutes)).padStart(2, "0");
	seconds = String(Math.round(seconds)).padStart(2, "0");

	// On retourne le temps sans les heures
	return minutes + ":" + seconds;
}
