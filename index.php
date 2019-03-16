<?php
session_start();

include ('Controller.php');

$controller = new Controller();

$token_value = $controller->getTokenValue();

$containersInfoMatrix = $controller->displayContainersInfo();

$nbConteneursTotal = $controller->getContainersTotalNumber();

#images_list est un tableau qui contient toutes les images docker
$images_list = $controller->getImagesList();

# var pour la partie terminal car la redirection de cette fonction (terminal) est different des autres fonctions


if(isset($_GET['action']) AND !empty($_GET['action']) OR (!empty($_POST))){

	if(isset($_GET['action']) AND !empty($_GET['action']))
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
			if(hash_equals($token_value, $_GET['token'])){	
				$containerIP = $_GET['ip'];
				header('Location: terminal/'.$id.'/');
				exit;
			} else {
				header('Location: error.html');
			}
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
		elseif(isset($_POST['refresh']))
		{
			$message = array("refresh");
		}
		elseif(isset($_POST['upload']))
		{
			$message = $controller->uploadDockerfile($_POST, $_FILES);
		}
		elseif(isset($_POST['start_selection']))
		{
			$message = $controller->selectionAction("start_selection");
		}
		elseif(isset($_POST['stop_selection']))
		{
			$message = $controller->selectionAction("stop_selection");
		}
		elseif(isset($_POST['destroy_selection']))
		{
			$message = $controller->selectionAction("destroy_selection");
		}
		elseif(isset($_POST['delete_img']))
		{
			$imgName = $_POST['image'];
			$message = array("delete_img", $imgName);
		}
		}

		if($executeHeaderForTerminal){

		}
		else {
			$controller->socketHandler($message);	
			header('Location: index.php');
			exit;
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Page de gestion</title>
		<link rel="stylesheet" type="text/css" href="style.css">
		<script language="text/javascript" src="script.js"></script>
	</head>
	<body>
		<form name="myform" action="index.php" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>Création Image</legend>
				<label for="dockerfile">Choisir le fichier Dockerfile : </label><br>
				<input type="file" name="dockerfileToUpload" id="dockerfile"><br>
				<label for="imgName">Saisir le nom de l'image (tag) : </label><br>
				<input type="text" name="imgName" id="imgName"><br><br>
				<input type="submit" name="upload" value="CRÉER IMAGE" class="button button1">
			</fieldset>
			<fieldset>
				<legend>Gestion &amp; Infos Conteneurs</legend>
				<select name="image" style="font-size:16px;">
					<?php
			 foreach($images_list as $image){
			 ?>
			 <option><?= $image;?></option>
			 <?php
			 }
			 ?>
				</select>
				<select name="nb" style="font-size:16px;">
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
				</select><br>
				<fieldset>
					<legend>Actions Conteneurs</legend>
					<input type="submit" name="create" value="CRÉER" class='button button1'/>
					<input type="submit" name="refresh" value="ACTUALISER" class='button button4'/>
				</fieldset>
				<fieldset>
					<legend>Actions Conteneurs Sélectionnés</legend>
					<input type="submit" name="start_selection" value="LANCER SÉLÉCTION" class='button button1'/>
					<input type="submit" name="stop_selection" value="ARRÊTER SÉLÉCTION" class='button button2'/>
					<input type="submit" name="destroy_selection" value="DÉTRUIRE SÉLÉCTION" class='button button3'/>
				</fieldset>
				<fieldset>
					<legend>Actions Image</legend>
					<input type="submit" name="delete_img" value="SUPPRIMER IMAGE" class='button button3'/>
				</fieldset><br>
				<table border="1">
					<thead>
						<th width="3%">N°</th>
						<th width="3%"><input type="checkbox" id="check_ctr" onClick="checkAll();"></th>
						<th width="7%">ID</th>
						<th width="10%">NOM</th>
						<th width="9%">IMAGE</th>
						<th width="9%">ÉTAT</th>
						<th width="9%">IP</th>
						<th width="50%">ACTIONS</th>
					</thead>
					<tbody>
						<?php
	  //ici $i represente le nb de conteneurs qui sont creer il va etre recuperer depuis le fichier retour.xml pour des raisons de test j'ai mis un seul conteneur pour tester
	  if(!empty($containersInfoMatrix[0][0])){
	  for($i = 0; $i < $nbConteneursTotal ; $i++){
	  ?>
	  <tr>
		  <td>
			  <?= $i+1 ?>
		  </td>
		  <td>
			  <input type="checkbox" name="list_selected_id[]" value=<?= $containersInfoMatrix[$i][0]?>>
		  </td>
		  <?php
		  /* var $j nb info qu'on va afficher ici on a 4 ID, NOM, IMAGE, ETAT
			    * encore le prob de l'affichge existe parce que le tableau se charge avant que les infos 
			    * des conteneurs soit recuperer du fichier retour.xml
			    */
			    for($j = 0; $j < 5; $j++){
			    ?>
			    <td align='center'>
				    <?= $containersInfoMatrix[$i][$j]; ?>
			    </td>
			    <?php
			    }
			    // Les actions qu'on peut effectuer sur nos conteneurs
			    ?>
			    <td align='center'>
				    <a href="index.php?id=<?= $containersInfoMatrix[$i][0]?>&amp;action=start" class="button button1">LANCER</a>
				    <a href="index.php?id=<?= $containersInfoMatrix[$i][0]?>&amp;action=stop" class="button button2">ARRÊTER</a>
				    <a href="index.php?id=<?= $containersInfoMatrix[$i][0]?>&amp;action=destroy" class="button button3">DÉTRUIRE</a>
				    <a href="index.php?id=<?= $containersInfoMatrix[$i][0]?>&amp;action=terminal&amp;token=<?= $token_value; ?>" target="_blank" class="button button5">TERMINAL</a>
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
<script>
function checkAll(){
        var inputs = document.getElementsByTagName("input"); 
        for (var i = 0; i < inputs.length; i++) {  
                if (inputs[i].type == "checkbox" && document.getElementById('check_ctr').checked==true) {  
                        inputs[i].checked = true;  
		} else {
                        inputs[i].checked = false;  	
		}   
        }   
}
</script>
