<?php
include ('Controller.php');

$controller = new Controller();

$containersInfoMatrix = $controller->displayContainersInfo();

$nbConteneursTotal = $controller->getContainersTotalNumber();

#images_list est un tableau qui contient toutes les images docker
$images_list = $controller->getImagesList();

$host    = "localhost";
$port    = 12800;

if(isset($_GET['action']) or (!empty($_POST))){

	if(isset($_GET['action']))
	{
		$id = $_GET['id'];
		if(strcmp($_GET['action'], 'start') == 0){
			$message = array("start", $id);
		}
		elseif(strcmp($_GET['action'], 'stop') == 0){
			$message = array("stop", $id);
		}
		elseif(strcmp($_GET['action'], 'destroy') == 0){
			$message = array("destroy", $id);
		}
		elseif(strcmp($_GET['action'], 'terminal') == 0){
			//	$controller->createContainerActionXMLFile('terminal',$_GET['id']);
		}
	}

	elseif(!empty($_POST))
	{

		if(isset($_POST['create']))
		{
			$nb = $_POST['nb'];
			$image = $_POST['image'];
			$message = array("create", $nb, $image);
		}
		elseif(isset($_POST['destroyall']))
		{
			$message = array("destroyall");
		}
		elseif(isset($_POST['upload']))
		{
			$target_dir = "/var/www/pageDeGestion/html/uploads/";
	                $fileNameToUpload = basename($_FILES["dockerFileToUpload"]["name"]);
	                $target_file = $target_dir . basename($_FILES["dockerFileToUpload"]["name"]);
			$tmp_target_file = $_FILES["dockerFileToUpload"]["tmp_name"];

			$imgTag = $_POST['imgName'];

			$controller->uploadDockerfile($target_dir, $fileNameToUpload, $target_file, $tmp_target_file);
			$message = array("buildImg", $fileNameToUpload, $imgTag);

		}
	}

	// on encode le message en json pour pouvoir l'envoyer
	$message = json_encode($message);
	// create socket
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create socket\n");
	// connect to server python
	$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");
	// send string to server
	socket_write($socket, $message, strlen($message)) or die("Could not send data to server\n");
	// next instruction supposed to wait message from server python
	socket_recv($socket, $message, 1024, MSG_WAITALL)or die ("Could not receive from server python");
	// close socket
	socket_close($socket);

	header('Location: index.php');
	exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Page de gestion</title>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<form enctype="multipart/form-data" action="index.php" method="post">

		<fieldset>
			<legend>Création Images</legend>
			<label for="dockerfile">Choisir le fichier Dockerfile : </label> <br/>
			<input name="dockerFileToUpload" type="file" id="dockerfile"> <br/>
			<label for="imgName">Saisir le nom de l'image (tag) : </label> <br/>
			<input name="imgName" type="text" id="imgName"> <br/><br/>
			<input type="submit" id="upload" name="upload" value="CREER IMAGE" class="button button1">
		</fieldset>
		<fieldset>
			<legend>Gestion Conteneurs</legend>
			<select name="image">
			<?php
			
				foreach($images_list as $image){
			?>
					<option><?php echo $image;?></option>
			<?php
				}
			?>
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
			<input type="submit" name="create" value="CREER" class='button button1'/>
			<input type="submit" name="destroyall" value="TOUT DETERUIRE" class='button button3'/>
		</fieldset>
		<fieldset>
			<legend>Informations Conteneurs</legend>
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
//ici $i represente le nb de conteneurs qui sont creer il va etre recuperer depuis le fichier retour.xml pour des raisons de test j'ai mis un seul conteneur pour tester
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
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=start" class="button button1">LANCER</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=stop" class="button button2">ARRETER</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=destroy" class="button button3">DETRUIRE</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=terminal" class="button button5">TERMINAL</a>
					</td>
					</tr>
<?php
	}
}
?>
				</tbody>
			</table>
</fieldset>
		</form>
	</body>
</html>

