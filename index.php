<?php
include ('Controller.php');
#quand on a submit quelque chose on prépare notre fichier xml
if(!empty($_POST))
{
	# nécessite le paquet php-xml
	# aussi chown www-data demande.xml
		
	var_dump(file_exists("./Controller.php"));
	$controller = new Controller();
	if(isset($_REQUEST['create']))
	{
		$nb = $_REQUEST['nb'];
		$image = $_REQUEST['image'];
		$action = $_REQUEST['create'];

		$controller->createXMLFile('create',$image,$nb);

		echo "Création de $nb conteneur(s) $image.";
	}
	elseif(isset($_REQUEST['destroyall']))
	{
		echo "tout détruire";
		$action = $_REQUEST['destroyall'];
		$controller->createXMLFile('destroyall',0,0);
	}

	$controller->sendXMLFile();

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
