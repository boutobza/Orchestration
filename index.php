<?php
#quand on a submit quelque chose on prépare notre fichier xml
if(!empty($_POST))
{
	# nécessite le paquet php-xml
	# aussi chown www-data demande.xml
	$xml = new DOMDocument("1.0","UTF-8");
	$xml->load("demande.xml");

	$rootTag = $xml->getElementsByTagName("document")->item(0);

	#suppression de l'information précédente
	while($rootTag->hasChildNodes())
	{
		$rootTag->removeChild($rootTag->firstChild);
	}
	$dataTag = $xml->createElement("data");
	if(isset($_REQUEST['create']))
	{
		$nb = $_REQUEST['nb'];
		$image = $_REQUEST['image'];


		$actionTag = $xml->createElement("action","create");
		$imageTag = $xml->createElement("image",$image);
		$nbTag = $xml->createElement("nb",$nb);

		$dataTag->appendChild($actionTag);
		$dataTag->appendChild($imageTag);
		$dataTag->appendChild($nbTag);

		$rootTag->appendChild($dataTag);

		echo "Création de $nb conteneur(s) $image.";
	}
	elseif(isset($_REQUEST['destroyall']))
	{
		echo "tout détruire";
		$actionTag = $xml->createElement("action","destroyall");
		$dataTag->appendChild($actionTag);

		$rootTag->appendChild($dataTag);
	}

	#on enregistre la demande dans le fichier
	$xml->save("demande.xml");

	#on envoie demande.xml à la machine docker
	#https://unix.stackexchange.com/questions/182483/scp-without-password-prompt-using-different-username
	#cp -r ~/.ssh/ /var/www/
	#chown www-data /var/www/.ssh
	#chown www-data /var/www/.ssh/*
	#chmod 777 /var/www/.ssh pour que ça marche une première fois
	#chmod 755 /var/www/.ssh on remet des bons droits

	shell_exec('scp /var/www/pageDeGestion/html/demande.xml user@192.168.56.101:/home/user/DockerScripts');

}

#on met tout le html dans une variable que l'on echo
$html='
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Page de gestion</title>
	</head>
	<body>
		<form action="index.php" method="post">
			<select name="image">
				<option>Ubuntu</option>
				<option>Debian</option>
				<option>CentOS</option>
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
			<input type="submit" name="destroyall" value="Tout détruire"/>
		</form>
	</body>
</html>
';
echo $html;
