<?php
include('Net/SSH2.php');
include('Net/SCP.php');
class Controller{
	function sendXMLFile(){


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
	function createXMLFile($selectedAction, $selectedImage, $selectedNum){

		$xml = new DOMDocument("1.0", "UTF-8");
		#$docElem = $xml->createElement("document");
		$containerElem = $xml->createElement("data");
		$actionElem = $xml->createElement("action", $selectedAction);
		$imageElem = $xml->createElement("image",$selectedImage);
		$numElem = $xml->createElement("nb",$selectedNum
		);
		$containerElem->appendChild($actionElem);
		$containerElem->appendChild($imageElem);
		$containerElem->appendChild($numElem);
		$xml->appendChild($containerElem);
		$xml->formatOutput = true;
		$xml->saveXML();
		$xml->save('demande.xml');
		echo '<p>Fichier XML crée avec succes !</p>';
	}
	function displayContainersInfo () : array {
		
		$xml = new DOMDocument("1.0", "UTF-8");
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;
		
		$xml->load('retour.xml');

		// recupere tout les id des conteneurs crees
		$containersIDs = $xml->getElementsByTagName('id');
		
		// recupere tout les noms des conteneurs crees
		$containersNames = $xml->getElementsByTagName('name');

		// recupere tout les images utilisées pour les conteneurs crees
		$containersImages= $xml->getElementsByTagName('img');

		// recupere tout les etats des conteneurs crees
		$containersStatus= $xml->getElementsByTagName('status');

		// une matrice qui va contenir toutes les infos des conteneurs présentent dans le fichier retour.xml
		$containersInfoMatrix = array(array());
	
		for($i = 0; $i < count($containersIDs); $i++){
			$containersInfoMatrix[$i] = array($containersIDs[$i]->nodeValue,
							  $containersNames[$i]->nodeValue,
							  $containersImages[$i]->nodeValue,
							  $containersStatus[$i]->nodeValue);
		}

		return $containersInfoMatrix;
	}
}
?>
