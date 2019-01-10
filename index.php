<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Page de gestion</title>
	</head>
<?php
include('Net/SSH2.php'); 
include('Net/SCP.php');

if(isset($_REQUEST['create']))
{
	$selectedNum = $_REQUEST['nb'];
	echo "Création de $selectedNum conteneur(s).";

	$selectedImage = $_POST['image'];
	echo "<p>Image selectionnée : ".$selectedImage."</p>";

	//appel au fonction pour le fichier XML
	createXMLFile($selectedImage, $selectedNum);
	
	//appel au fonction pour creer conteneur(s)
	newContainer();

}

function newContainer(){
	
	
	$ssh = new NET_SSH2('192.168.0.129');

	if(!$ssh->login('docker','bot'))// ici on met le username & password de l'hote distant 'dockerengine'
	{
		exit('<p>Login Failed</p>');
	
	}

	$scp = new NET_SCP($ssh);
	
	//transfert du fichier XML au dockerengine via scp
	$scp->put('/home/docker/DockerScripts/container.conf.xml', 'container.conf.xml', 1);
	
	echo '<p>Conteneur crée avec succes !</p>';
}

function createXMLFile($selectedImage, $selectedNum){
	;
	$xml = new DOMDocument("1.0", "UTF-8");
	$containerElem = $xml->createElement("container");
	$imageElem = $xml->createElement("image");
	$imageElem->nodeValue=$selectedImage;
	$numElem = $xml->createElement("number");
	$numElem->nodeValue=$selectedNum;

	$containerElem->appendChild($imageElem);
	$containerElem->appendChild($numElem);
	$xml->appendChild($containerElem);

	$xml->formatOutput = true;
	$xmlString = $xml->saveXML();
	$xml->save('container.conf.xml');
	echo '<p>Fichier XML crée avec succes !</p>';
}
?>
	<body>
		<form action="index.php" method="post">
			<select name="image">
				<option>Ubuntu</option>
				<option>Debian</option>
				<option>CentOS</option>
				<option value="nginx">Nginx</option>
			</select>
			<select name="nb">
				<option>1</option>
				<option>2</option>
				<option>3</option>
				<option>4</option>
				<option>5</option>
				<option>6</option>
				<option>7</option>
				<option>8</option>
				<option>9</option>
				<option>10</option>
			</select>
			<input type="submit" name="create" value="Créer"/>
		</form>
	</body>
</html>
