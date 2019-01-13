<?php
include ('Controller.php');
#quand on a submit quelque chose on prépare notre fichier xml
if(!empty($_POST))
{
	# nécessite le paquet php-xml
	# aussi chown www-data demande.xml
		
	#var_dump(file_exists("./Controller.php"));
	#


	$controller = new Controller();
	
	$containersInfoMatrix;

	if(isset($_REQUEST['create']))
	{
		$nb = $_REQUEST['nb'];
		$image = $_REQUEST['image'];
		$action = $_REQUEST['create'];

		$controller->createXMLFile('create',$image,$nb);

		echo "Création de $nb conteneur(s) $image.";

		$containersInfoMatrix = $controller->displayContainersInfo();

		
		
		
		

	}
	elseif(isset($_REQUEST['destroyall']))
	{
		echo "tout détruire";
		$action = $_REQUEST['destroyall'];
		$controller->createXMLFile('destroyall',0,0);
	}

	$controller->sendXMLFile();

}
?>
<?php//on met tout le html dans une variable que l'on echo
/*
 *pour var $html je l'ai supprimé pour facilité les choses que 
 *je viens de faire 'creation du table quit contient les infos des conteneurs du maniere dynamique' 
 *mais si t'as qu'il a un importance STP dis mois
 */
?>

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

			<table border="1" width="80%">
				<thead>
					<th>ID</th>
					<th>NOM</th>
					<th>IMAGE</th>
					<th>ETAT</th>
					<th>ACTIONS</th>
				</thead>
				<tbody>
					<?php
				//ici $i represente le nb de conteneurs qui sont creer il va etre recuperer depuis le fichier retour.xml pour des raisons de test j'ai mis un seul conten					eur pour tester
					for($i = 0; $i < 1; $i++){
					?>
					<tr>
<?php
						/* var $j nb info qu'on va afficher ici on a 4 ID, NOM, IMAGE, ETAT
						* encore le prob de l'affichge existe parce que le tableau se charge avant que les infos 
						* des conteneurs soit recuperer du fichier retour.xml
						 */
					for($j = 0; $j < 4; $j++){
					?>
					<td>
					<?php
					$containersInfoMatrix[$i][$j];
					?>
					</td>
					<?php
					}
					// Les actions qu'on peut effectuer sur nos conteneurs
					?>
					<td>
					<a href="#">Lancer</a>
					<a href="#">Arrèter</a>
					<a href="#">Détruire</a>
					<a href="#">Info</a>
					</td>
					</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		</form>
	</body>
</html>

