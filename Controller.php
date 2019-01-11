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

		//shell_exec('chown www-data containers.info.xml');
		$xml = simplexml_load_file('containers.info.xml');
		$containersInfo = array($xml->id,
			$xml->nom,
			$xml->image,
			$xml->etat);
		foreach($containersInfo as $value){
			//echo '<p>'.$value.'</p>';	
		}
		echo '<p>DONE!</p>';
		return $containersInfo;
	}
}
?>
