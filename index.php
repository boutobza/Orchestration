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
	if(isset($_REQUEST['create']))
	{
		$nb = $_REQUEST['nb'];
		$image = $_REQUEST['image'];

		$dataTag = $xml->createElement("data");

		$actionTag = $xml->createElement("action","create");
		$imageTag = $xml->createElement("image",$image);
		$nbTag = $xml->createElement("nb",$nb);

		$dataTag->appendChild($actionTag);
		$dataTag->appendChild($imageTag);
		$dataTag->appendChild($nbTag);

		$rootTag->appendChild($dataTag);

		$xml->save("demande.xml");
		echo "Création de $nb conteneur(s) $image.";
	}

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
		</form>
	</body>
</html>
';
echo $html;
