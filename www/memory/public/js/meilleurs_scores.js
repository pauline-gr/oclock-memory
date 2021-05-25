// Pour appeler une fonction depuis un fichier externe, on la déclare de cette manière
setTableauBootstrap = function setTableauBootstrap(data) {
	if (data) {
		// Affichage des 10 meilleurs scores - Même procédé que sur card_flip.js
		// Il faudra déporter cette section sur une seule fonction à part pour éviter la redondance de code

		// On définit deux tableaux vides selon les tailles d'écran (le responsive n'étant pas propre sur les tableaux bootstrap)
		let titres 	= '';
		let tableau = '';

		// Si on est sur un écran dont la largeur dépasse 600 on affiche les en-têtes de tableau normales
		if ($(window).width() > 600) {
			titres = '<th scope="col">Joueur</th>\
						<th scope="col">Date</th>\
						<th scope="col">Score</th>\
						<th scope="col">Temps</th>\
						<th scope="col">Difficulté</th>\
						<th scope="col">Thème</th>';
		} else {
			// Sinon on définit une version synthétique des en-têtes
			titres = '<th scope="col">Joueur</th>\
						<th scope="col">Parties</th>';
		}

		// On récupère toutes les données retournées dans le tableau
		for(var i in data) {
			// Si on a bien du contenu json dans le tableau on enregistre la ligne un cran au dessus
			if(data[i] instanceof Object) {
				data = data[i];
			}
		}

		// Pour chaque ligne on renseigne le contenu du tableau bootstrap selon la taille d'écran
		$.each(data, function(i, item){
			if ($(window).width() > 600) {
				tableau += '<tr>\
							<th class="content-flex-center" scope="row"><img class="avatar" src="images/avatars/'+item.avatar_joueur+'"><br/>'+item.pseudo_joueur+'</th>\
							<td>'+item.date_score+'</td>\
							<td>'+item.score+'</td>\
							<td>'+item.temps_final+'</td>\
							<td>'+item.nom_difficulte+'</td>\
							<td>'+item.nom_theme+'</td>\
							</tr>';
			} else {
				tableau += '<tr>\
							<th class="content-flex-center" scope="row"><img class="avatar" src="images/avatars/'+item.avatar_joueur+'"><br/>'+item.pseudo_joueur+'</th>\
							<td>Date: '+item.date_score+' - Score: '+item.score+'<br/>Temps: '+item.temps_final+'<br/>Difficulte: '+item.nom_difficulte+'<br/>Thème: '+item.nom_theme+'</td>\
							</tr>';  
			}
		});
		// On affiche le contenu dans les divs prévues sur le template
		$('#meilleurs_scores_titres').html(titres);
		$('#meilleurs_scores_content').html(tableau);
	}
}
