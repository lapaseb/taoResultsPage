<?php
/**
 * TaoResults functions
 *
 * @author Franc Romain
 * @package taoResultsPage
 * @license GPL-2.0
 *
 */

/*
 * Retourne la liste des sessions d'examens
 * Fonction utilisée pour la page "Résultats"
 * @return array Tableau contenant la liste des sessions d'examens formé de l'identifiant et du nom
 */
function get_deliveries(){
	require("taoResultsPage/includes/database.php");	
	
	try {
		//Requête qui séléctionne la liste des sessions d'examens
        $results_storage = $db->query("SELECT DISTINCT delivery FROM results_storage")->fetchAll(PDO::FETCH_ASSOC);
		
		$deliveries = array();
		//Parcours les sessions d'examens
		foreach ($results_storage as $value) {
			//Requete de selection le label de la session d'examen
			$delivery = $db->query("SELECT object FROM statements WHERE subject = '" . $value['delivery'] . "' AND predicate = 'http://www.w3.org/2000/01/rdf-schema#label'")->fetchAll(PDO::FETCH_ASSOC);
			//Si la session d'examen n'a pas de label, cela veut dire que cette session n'existe plus
			if(count($delivery) != 0){
				array_push($deliveries, array($value['delivery'], $delivery[0]['object']));
			}
		}
    } catch (Exception $e) {
        echo "Impossible de charger les données de la base.";
        exit;
    }
    return $deliveries;
}

/*
 * Retourne la liste des sessions d'examens fusionés par session d'examens
 * Fonction utilisée pour la page "Résumé"
 * @return array Tableau contenant la liste fusionés des sessions d'examens
 */
function get_deliveries_resume(){
	//Récupère la liste des examens
	$deliveries = get_deliveries();
	//Création d'un tableau qui stockera la liste des examens fusionnés par session d'examen.
	$deliveries_formated = array();
	//Parcours les examens pour reformer un tableau
	foreach($deliveries as $value){
		//Si le nom de l'examen est formé comme "session - partie" alors la partie de droite du trait d'union sera récupérée.
		if(isset(explode("-", $value[1])[1])){
			$deliveryName = explode("-", $value[1])[1];
		} else {
			$deliveryName = null;
		}
		//Variable qui indique si l'examen existe et donc si il doit être push dans le nouveau tableau ou alors être fussioné
		$pushArray = false;
		//Parcours le nouveau tableau afin de detecter si l'occurence du vieux tableau y est présente, si oui, il sera fussionné
		foreach($deliveries_formated as &$delivery_formatted){
			if(explode("-", $delivery_formatted[0])[0] == explode("-", $value[1])[0]){
				$delivery_formatted[1] = $delivery_formatted[1] . " " . $value[0];
				$delivery_formatted[2] = $delivery_formatted[2] . " " . explode("#", $value[0])[1];
				array_push($delivery_formatted[3], explode("-", $value[1])[1]);
				$pushArray = true;
			}
		}
		
		//Si l'occurence du vieux tableau n'est pas présente dans le nouveau, alors on l'ajoute
		if(!$pushArray){
			array_push($deliveries_formated, array(
								explode("-", $value[1])[0], 
								$value[0], 
								explode("#", $value[0])[1],					
								array($deliveryName)
							));
		}
	}
	return $deliveries_formated;
}


/*
 * Retourne un tableau des résultats de chaque examen du 'delivery' (session d'examen) passé en paramètre
 * @param $delivery string identifiant du delivery (session d'examen)
 * @return array Tableau composé de l'identifiant du résultat, du score obtenu, du score total pouvant être obenu ainsi que du nom du test-taker
 */
function get_results_by_delivery($delivery){
	require("taoResultsPage/includes/database.php");	
	
	try {
		//Requête de séléction des résultats de l'examen passé en paramètre
        $results_storage = $db->query("SELECT result_id, test_taker FROM results_storage WHERE delivery = '" . $delivery . "'")->fetchAll(PDO::FETCH_ASSOC);
		//Initialisation du tableau qui sera retourné
		$allResults = array();
		//parcours chaque résultats de l'examen
		foreach ($results_storage as $value) {
			//Requête qui séléctionne le score de l'examen
			$results = $db->query("SELECT value, item FROM `variables_storage` WHERE results_result_id = '" . $value["result_id"] . /*"' AND identifier LIKE 'RESPONSE%'"*/ "' AND identifier = 'SCORE'")->fetchAll(PDO::FETCH_ASSOC);
			
			//Requête pour obtenir le nom du test-taker
			$testTaker = $db->query("SELECT object FROM statements WHERE  subject =  '" . $value["test_taker"] . "' AND  predicate = 'http://www.w3.org/2000/01/rdf-schema#label' LIMIT 1")->fetchAll(PDO::FETCH_ASSOC);
			
			$score = 0;
			$totalPoints = 0;
			$Responses_array = array();
			
			//parcours tous les résultats (examen)
			foreach($results as $result){
				//Additionne le score de chaque question (donnée brute en base 64)
				$scoreToAdd = base64_decode(explode('"', explode(";", $result["value"])[5])[1]);
				$score += $scoreToAdd;
				
				//Additionne le score pouvant être obtenu grâce à la fonction getObtainableScoreOfItem
				$itemId = explode("#", $result["item"])[1];
				$totalPointsToAdd = getObtainableScoreOfItem($itemId);
				$totalPoints += $totalPointsToAdd;

				//Requête qui séléctionne les reponses de l'examen
				$responses = $db->query("SELECT value FROM `variables_storage` WHERE results_result_id = '" . $value["result_id"] . "' AND identifier LIKE 'RESPONSE%' AND item = '" . $result["item"] . "'" /* "' AND identifier = 'SCORE'"*/)->fetchAll(PDO::FETCH_ASSOC);
				
				//Tableau qui stock toutes les réponses
				$userResponses_array = array();
				//Parcours tout les questions d'un examen
				foreach($responses as $response){
					//Création du tableau des réponses à une question
					array_push($userResponses_array, 
								array(
								"name" => explode('"', explode(";", $response["value"])[5])[1],
								"value" => base64_decode(explode('"', explode(";", $response["value"])[3])[1])
								)
								);
				}
				
				//Requête pour obtenir le label de la question
				$itemLabel = $db->query("SELECT object FROM statements WHERE  subject =  '" . $result["item"] . "' AND  predicate = 'http://www.w3.org/2000/01/rdf-schema#label' LIMIT 1")->fetchAll(PDO::FETCH_ASSOC);
				//Création du tableau des informations sur une question
				array_push($Responses_array, array("title" => getTitleOfItem($itemId), "label" => $itemLabel[0]['object'], "score" => $scoreToAdd, "totalScore" => $totalPointsToAdd, "responses" => $userResponses_array));
			}
			
			//Ajoute les valeurs au tableau qui sera retourné
			array_push($allResults, array("id" => $value["result_id"], "score" => $score, "totalScore" => $totalPoints, "testTakerName" => $testTaker[0]['object'], "responses_array" => $Responses_array));		
		}
    } catch (Exception $e) {
        echo "Impossible d'accéder aux données de la base.";
        exit;
    }
    return $allResults;
}

/*
 * Retourne un tableau des résultats fusionés de chaque examen du 'delivery' (session d'examen) passé en paramètre
 * @param $deliveries string identifiants des delivery séparé avec des espaces (session d'examen)
 * @return array Tableau restructuré des résultats des examens groupés par utilsateurs
 */
function get_results_by_delivery_resume($deliveries){
	//Initialisation du nouveau tableau qui sera retourné
	$filtredResults = array();
	//Parcours tous les examens passés en paramètre
	foreach(explode(" ", $deliveries) as $id){
		//Parcours tous les résultats de l'examen pour reformer le nouveau tableau
		foreach(get_results_by_delivery($id) as $result){
			$alreadyExist = false;
			//Parcours le nouveau tableau pour vérifier que le test-taker de l'examen que l'on parcourt 
			// actuellement n'y est pas présent sinon on ajoute le résultat au tableau du test-taker.
			foreach($filtredResults as &$filteredResult){
				if($filteredResult["testTakerName"] ==  $result["testTakerName"]){
					$filteredResult["score"] = $filteredResult["score"] + $result["score"];
					$filteredResult["totalScore"] = $filteredResult["totalScore"] + $result["totalScore"];
					array_push($filteredResult["results"], $result);
					
					$alreadyExist = true;
				}
			}
			// Si le test-taker ne figurait pas dans le tableau, on l'ajoute
			if(!$alreadyExist){
				array_push($filtredResults, array(
							"id" => $result["id"],
							"testTakerName" => $result["testTakerName"],
							"score" => $result["score"],
							"totalScore" => $result["totalScore"],
							"results" => array($result)
							));
			}
		}
	}
	return $filtredResults;
}

/*
 * Retourne le score pouvant être obtenu sur la question (item) passée en paramètre
 * @param $id string identifiant de l'item 
 * @return int Score pouvant être obtenu sur la question (item) passée en paramètre
 */
function getObtainableScoreOfItem($id){
	//Défini le lien afin d'obtenir le fichier de l'item
	$url = dirname(__FILE__). '/../../data/taoItems/itemData/' . $id . '/itemContent/en-US/qti.xml';

	//si le fichier existe
	if (file_exists($url)) {
		$score = 0;
	
		//Charge le ficher XML
		$xmlDoc = new DOMDocument(); 
		$xmlDoc->load( $url ); 
		
		//Parcours le fichier afin de trouver la balise "mapping"
		$mapping_array = $xmlDoc->getElementsByTagName( "mapping" ); 
		$responseDeclarations = $xmlDoc->getElementsByTagName( "responseDeclaration" );
		$index = 0;

		//Parcours les balises de la balise "mapping" afin de trouver le score pouvant être obtenu sur la question
		foreach($responseDeclarations as $responseDeclaration){
			$index += 1;
			
			$getFirstValue = false;
			//Si la cardinalité est égale à single, cela veut dire qu'une seule réponse est possible
			if($responseDeclaration->getAttribute("cardinality") == "single"){
				$getFirstValue = true;
			}
			
			//Parcours la balise mapEntry afin de calculer le score de la question
			$mapEntry_array = $responseDeclaration->getElementsByTagName("mapEntry");
			foreach($mapEntry_array as $mapEntry){
				$mappedValue = $mapEntry->getAttribute("mappedValue");
				//Si le score trouvé est plus grand que zéro on l'additionne au score total de la question
				if($mappedValue > 0 ){
					$score += $mappedValue;
					//Si qu'une seule réponse n'est possible, on casse la boucle foreach car on a deja obtenu le score
					if($getFirstValue){
						break;
					}
				}				
			}
		}	
		return $score;
	} else {
		echo "Erreur d'accès au fichier";
	}
}

/*
 * Retourne le titre de la question (item) passée en paramètre
 * @param $id string identifiant de l'item 
 * @return string Titre de la question (item) passée en paramètre
 */
function getTitleOfItem($id){
	//Défini le lien afin d'obtenir le fichier de l'item
	$url = dirname(__FILE__). '/../../data/taoItems/itemData/' . $id . '/itemContent/en-US/qti.xml';

	//si le fichier existe
	if (file_exists($url)) {
		//Charge le ficher XML
		$xmlDoc = new DOMDocument(); 
		$xmlDoc->load( $url ); 

		//Parcours le fichier afin de trouver la balise "assessmentItem"
		$assessmentItem = $xmlDoc->getElementsByTagName("assessmentItem"); 

		foreach($assessmentItem as $item){
			$title = $item->getAttribute("title");
		}

		//Si le résultat trouvé est une chaine de caractère et n'est pas vide, alors on la retourne
		if(is_string($title) && $title != ""){
			return $title;
		} else {
			return "Sans titre";
		}
	} else {
		echo "Erreur d'accès au fichier";
	}
}
