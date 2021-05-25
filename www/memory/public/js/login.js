$(function() {
	// Lorsque la page est chargée on va récupérer les meilleurs scores via l'API
	// Note: Faute de temps j'aurais pu ajouter un petit loader pour que l'effet soit plus sympa
	$.ajax({
		type: "POST", 
		url: "/scores",
		dataType: 'json',
		success: function (data) {
            // On appelle la fonction de création du tableau bootstrap (meilleurs_scores.js)
			setTableauBootstrap(data);
		},
	});
});