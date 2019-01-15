<?php
include ('Controller.php');
#quand on a submit quelque chose on prépare notre fichier xml

$controller = new Controller();

$containersInfoMatrix = $controller->displayContainersInfo();

$nbConteneursTotal = $controller->getContainersTotalNumber();

if(isset($_GET['action']))
{
	if(strcmp($_GET['action'], 'start') == 0){
		$controller->createContainerActionXMLFile('start',$_GET['id']);
	}
	elseif(strcmp($_GET['action'], 'stop') == 0){
		$controller->createContainerActionXMLFile('stop',$_GET['id']);
	}
	elseif(strcmp($_GET['action'], 'destroy') == 0){
		$controller->createContainerActionXMLFile('destroy',$_GET['id']);
	}
	elseif(strcmp($_GET['action'], 'terminal') == 0){
	//	$controller->createContainerActionXMLFile('terminal',$_GET['id']);
	}

	$controller->sendContainerActionXMLFile();
	header('Location: index.php');
	exit;
}
if(!empty($_POST))
{
	if(isset($_POST['create']))
	{
		$nb = $_POST['nb'];
		$image = $_POST['image'];

		$controller->createXMLFile('create',$image,$nb);
		echo '<script>alert("'.$nb.' conteneur(s) '.$image.' crée(s) avec succes !");</script>';
	}
	elseif(isset($_POST['destroyall']))
	{
		$action = $_POST['destroyall'];
		$controller->createXMLFile('destroyall',0,0);
		echo '<script>alert("Tout les conteneurs sont détruits");</script>';
	}

		$controller->sendXMLFile();
		header('Location: index.php');
		exit;

}
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
			<input type="submit" name="create" value="Créer" id='MyButton'/>
			<input type="submit" name="destroyall" value="Tout détruire"/>
			<table border="1" width="100%">
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
						if(!empty($containersInfoMatrix[0][0])){
					for($i = 0; $i < $nbConteneursTotal ; $i++){
					?>
					<tr>
<?php
						/* var $j nb info qu'on va afficher ici on a 4 ID, NOM, IMAGE, ETAT
						* encore le prob de l'affichge existe parce que le tableau se charge avant que les infos 
						* des conteneurs soit recuperer du fichier retour.xml
						 */
					for($j = 0; $j < 4; $j++){
					?>
					<td align='center'>
<?php
					echo $containersInfoMatrix[$i][$j];
					?>
					</td>
					<?php
					}
					// Les actions qu'on peut effectuer sur nos conteneurs
					?>
					<td align='center'>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=start" class="button" onClick='this.disabled = true' >Lancer</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=stop" class="button">Arreter</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=destroy" class="button">Détruire</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=terminal" class="button">Terminal</a>
					</td>
					</tr>
					<?php
					}}
					?>
				</tbody>
			</table>
		</form>


<?php // afficher les liens LANCER, DETRUIRE ... comme! des boutons?>
<style>

.button {
  font: bold 12px Arial;
  text-decoration: none;
  background-color: #EEEEEE;
  color: #333333;
  padding: 2px 6px 2px 6px;
  border-top: 1px solid #CCCCCC;
  border-right: 1px solid #333333;
  border-bottom: 1px solid #333333;
  border-left: 1px solid #CCCCCC;
}
a:hover { 
  color: blue;
}
</style>
<script>




</script>
	</body>
</html>

