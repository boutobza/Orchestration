<?php
class Controller{

	public function getImagesList(){
		$images_list = file_get_contents('/var/www/pageDeGestion/html/user/images_list');
		# images_list est un string dont chaque valeur est délimitée par ',' 
		# on le transforme en array en précisant ',' comme délimiteur
		$images_list = explode(',', $images_list);
		return $images_list;
	}


	public function getContainersTotalNumber() {
		$xml = new DOMDocument("1.0", "UTF-8");
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;
		

		if(@$xml->load('/var/www/pageDeGestion/html/user/retour.xml')){
		$containersIDs = $xml->getElementsByTagName('id');
		
		$nbConteneursTotal = count($containersIDs);
		}
		return $nbConteneursTotal;
	}

	function displayContainersInfo () : array {
		
		$xml = new DOMDocument("1.0", "UTF-8");
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;
		
		// une matrice qui va contenir toutes les infos des conteneurs présentent dans le fichier retour.xml
		$containersInfoMatrix = array(array());

		if(@$xml->load('/var/www/pageDeGestion/html/user/retour.xml')){
		// recupere tout les id des conteneurs crees
		$containersIDs = $xml->getElementsByTagName('id');
		
		// recupere tout les noms des conteneurs crees
		$containersNames = $xml->getElementsByTagName('name');

		// recupere tout les images utilisées pour les conteneurs crees
		$containersImages= $xml->getElementsByTagName('img');

		// recupere tout les etats des conteneurs crees
		$containersStatus= $xml->getElementsByTagName('status');

		$nbConteneursTotal = count($containersIDs);


		if( $nbConteneursTotal != 0){
		
		for($i = 0; $i < $nbConteneursTotal ; $i++){
			$containersInfoMatrix[$i] = array($containersIDs[$i]->nodeValue,
							  $containersNames[$i]->nodeValue,
							  $containersImages[$i]->nodeValue,
							  $containersStatus[$i]->nodeValue);
		}
		}
	//	else echo '<script>alert("Pas de conteneurs à détruire !");</script>';

		}
		else echo '<script>alert("Fichier retour.xml est vide !");</script>';
		return $containersInfoMatrix;
	}
}
?>
