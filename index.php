<?php
include ('Controller.php');

$controller = new Controller();

$containersInfoMatrix = $controller->displayContainersInfo();

$nbConteneursTotal = $controller->getContainersTotalNumber();

#images_list est un tableau qui contient toutes les images docker
$images_list = $controller->getImagesList();


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
			$imgTag = $_POST['imgName'];
			
			$target_dir = "/var/www/pageDeGestion/html/uploads/";
			$dockerfileToUpload = basename($_FILES["dockerfileToUpload"]["name"]);
			$target_file = $target_dir .$dockerfileToUpload;
			$tmp_file = $_FILES["dockerfileToUpload"]["tmp_name"];

			$controller->uploadDockerfile($dockerfileToUpload, $target_file, $tmp_file);
			$message = array("buildImg", $dockerfileToUpload, $imgTag);
		}
	}

	$controller->socketHandler($message);	

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
		<form action="index.php" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>Création Image</legend>
				<label for="dockerfile">Choisir le fichier Dockerfile : </label><br>
				<input type="file" name="dockerfileToUpload" id="dockerfile"><br>
				<label for="imgName">Saisir le nom de l'image (tag) : </label><br>
				<input type="text" name="imgName" id="imgName"><br><br>
				<input type="submit" name="upload" value="CREER IMAGE" class="button button1">
			</fieldset>
			<fieldset>
				<legend>Gestion&Info Conteneurs</legend>
			<select name="image">
			<?php
			
				if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}}foreach($images_list as $image){
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

