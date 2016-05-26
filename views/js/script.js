// Au clique sur un élément du menu, ajoute la class selected afin de savoir lequel est séléctionné
$('.menu li a').click(function(e) {
	$(this).closest('ul').find('.selected').removeClass('selected');
	$(this).parent().addClass('selected');
});

// Au clique sur un élément du menu bleu en haut, rééxecute le code du clique sur un élément du menu 
//  pour éviter un bug d'affichage
$('.tab-container li').click(function(e) {
	$('.menu li:first-child a').click();
});

// Fonction d'affichage avec animation des bon résultats par rapport au delivery séléctioné
function showDelivery(id){
	//OUTPUT
	$(".taoResultsPage_content").find("div.show_content").each(function(){
		$(this).removeClass("show_content");
	});
	
	id.split(" ").forEach(function(item, index){
		//INPUT
		$("." + item)
		.css('left','-100vw')
		.addClass("show_content")
		.animate({
			left: "0vw"
		}, 300, 'swing');
	});
}

// Fonction d'affichage des détails d'un résultats avec animation
function showResults(id){
	if ( $("." + id).is( ":hidden" ) ) {
		$("." + id).addClass("show_flex_results");
		$("." + id).css('height',$("." + id).css('height'));
		$("." + id).addClass("show_results ");

	} else {
		$("." + id).removeClass("show_results show_flex_results");
	}
}
