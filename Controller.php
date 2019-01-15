<?php
include('Net/SSH2.php');
include('Net/SCP.php');
class Controller{


	public function getContainersTotalNumber() {
		$xml = new DOMDocument("1.0", "UTF-8");
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;
		

		if(@$xml->load('/var/www/pageDeGestion/html/retour.xml')){
		$containersIDs = $xml->getElementsByTagName('id');
		
		$nbConteneursTotal = count($containersIDs);
		}
		return $nbConteneursTotal;
	}

	// fonction qui permet d'envoyer le fichier demande.xml cree par la fonction createXMLFile()
	public function sendXMLFile(){
		
		$ssh = new NET_SSH2('192.168.56.101');
		if(!$ssh->login('user','user'))// ici on met le username & password de l'hote distant 'dockerengine'
		{
			exit('<p>Login Failed</p>');

		}
		$scp = new NET_SCP($ssh);

		//transfert du fichier XML au dockerengine via scp
		$scp->put('/home/user/DockerScripts/demande.xml', 'demande.xml', 1);
		#ATTENTION aucun garantie que le scp soit fini
		$ssh->exec('/home/user/DockerScripts/scriptTraiteXML');

	}
	//fonction qui permet d'envoyer le fichier demande_container_action.xml cree par la fonction createContainerActionXMLFile() qui contient l'action choisie depuis la table html des actions qu'on veut appliquer sur un conteneur
	public function sendContainerActionXMLFile(){


		$ssh = new NET_SSH2('192.168.56.101');
		if(!$ssh->login('user','user'))// ici on met le username & password de l'hote distant 'dockerengine'
		{
			exit('<p>Login Failed</p>');

		}
		$scp = new NET_SCP($ssh);

		//transfert du fichier XML au dockerengine via scp
		$scp->put('/home/user/DockerScripts/demande_container_action.xml', 'demande_container_action.xml', 1);
		#ATTENTION aucun garantie que le scp soit fini
		$ssh->exec('/home/user/DockerScripts/scriptTraiteContainerActions');

	}
	//fonction permet de creer un fichier xml pour les actions CREER et DETRUIRETOUT
	function createXMLFile($selectedAction, $selectedImage, $selectedNum){

		$xml = new DOMDocument("1.0", "UTF-8");
		$rootElem = $xml->createElement("containers");
		$containerElem = $xml->createElement("data");
		$actionElem = $xml->createElement("action", $selectedAction);
		$imageElem = $xml->createElement("image",$selectedImage);
		$numElem = $xml->createElement("nb",$selectedNum
		);
		$containerElem->appendChild($actionElem);
		$containerElem->appendChild($imageElem);
		$containerElem->appendChild($numElem);
		$rootElem->appendChild($containerElem);
		$xml->appendChild($rootElem);
		$xml->formatOutput = true;
		$xml->saveXML();
		$xml->save('demande.xml');
	}

	// fonction permet de un fichier xml qui contient seulement l' un des actions presentent dans le tableau HTML des conteneurs crees LANCER, ...
	function createContainerActionXMLFile($selectedAction, $containerID){
		$xml = new DOMDocument("1.0", "UTF-8");
                $rootElem = $xml->createElement("container");
                $actionElem = $xml->createElement("action", $selectedAction);
                $idElem = $xml->createElement("id",$containerID);
                $rootElem->appendChild($actionElem);
                $rootElem->appendChild($idElem);
                $xml->appendChild($rootElem);
                $xml->formatOutput = true;
                $xml->saveXML();
                $xml->save('demande_container_action.xml');

	}
	function displayContainersInfo () : array {
		
		$xml = new DOMDocument("1.0", "UTF-8");
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;
		
		// une matrice qui va contenir toutes les infos des conteneurs présentent dans le fichier retour.xml
		$containersInfoMatrix = array(array());

		if(@$xml->load('/var/www/pageDeGestion/html/retour.xml')){
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
