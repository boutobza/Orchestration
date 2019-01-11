<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Page de gestion</title>
	</head>
<?php
include('Net/SSH2.php'); 
include('Net/SCP.php');

$containersInfo = array();

if(isset($_POST['create']))
{
	$selectedNum = $_POST['nb'];
	echo "Création de $selectedNum conteneur(s).";

	$selectedImage = $_POST['image'];
	echo "<p>Image selectionnée : ".$selectedImage."</p>";

	//appel au fonction pour le fichier XML
	createXMLFile($selectedImage, $selectedNum);
	
	//appel au fonction pour creer conteneur(s)
	newContainer();

	//appel au fonction qui permet d'afficher les infos de conteneur(s)
	$containersInfo = displayContainersInfo();


	foreach($containersInfo as $value){
		echo '<p>'.$value.'</p>';	
	}

		echo '<p>'.$containersInfo[0].'</p>';	
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
	
	$xml = new DOMDocument("1.0", "UTF-8");
	$containerElem = $xml->createElement("container");
	$actionElem = $xml->createElement("action", "create");
	$imageElem = $xml->createElement("image");
	$imageElem->nodeValue=$selectedImage;
	$numElem = $xml->createElement("number");
	$numElem->nodeValue=$selectedNum;


	$containerElem->appendChild($actionElem);
	$containerElem->appendChild($imageElem);
	$containerElem->appendChild($numElem);
	$xml->appendChild($containerElem);

	$xml->formatOutput = true;
	$xmlString = $xml->saveXML();
	$xml->save('container.conf.xml');
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
?>
	<body>
		<form action="index.php" method="post">
			<select name="image">
				<option value="ubuntu">Ubuntu</option>
				<option value="debian">Debian</option>
				<option value="centos">CentOS</option>
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
			
			<table border="1" width="70%">
				<tr>
					<th>ID Conteneur</th>
					<th>Nom</th>
					<th>Image</th>
					<th>Etat</th>
					<th>Actions</th>
				</tr>
				<tr> 
					<td><?php $containersInfo[0] ?></td>
					<td><?php $containersInfo[1] ?></td>
					<td><?php $containersInfo[3] ?></td>
					<td><?php $containersInfo[4] ?></td>
					<td>
						<input type="submit" name="Stop" value="Arreter"/>
						<input type="submit" name="Start" value="Lancer"/>
						<input type="submit" name="Delete" value="Supprimer"/>
						<input type="submit" name="Info" value="Info"/>
					</td>
				</tr>
			</table>
			
		</form>
	</body>
</html>
