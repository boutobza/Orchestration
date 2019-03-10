<?php
include ('Controller.php');

$controller = new Controller();

$containersInfoMatrix = $controller->displayContainersInfo();

$nbConteneursTotal = $controller->getContainersTotalNumber();

#images_list est un tableau qui contient toutes les images docker
$images_list = $controller->getImagesList();

# var pour la partie terminal car la redirection de cette fonction (terminal) est different des autres fonctions
$executeHeaderForTerminal = false;


if(isset($_GET['action']) or (!empty($_POST))){

	if(isset($_GET['action']))
	{
		$id = $_GET['id'];

		$containerIP = $_GET['ip']
			;
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
			$executeHeaderForTerminal = true;
			$message = array("terminal", $id, $containerIP);
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
			$imgTag = $_POST['imgName'];

			$target_dir = "/var/www/pageDeGestion/html/uploads/";
			$dockerfileToUpload = basename($_FILES["dockerfileToUpload"]["name"]);
			$target_file = $target_dir .$dockerfileToUpload;
			$tmp_file = $_FILES["dockerfileToUpload"]["tmp_name"];

			$controller->uploadDockerfile($dockerfileToUpload, $target_file, $tmp_file);
			$message = array("buildImg", $dockerfileToUpload, $imgTag);
		}
		elseif(isset($_POST['start_selection']))
		{
			$message = array("start_selection");
			$containers_list = '"';

			# on veut remplir containers_list avec tous les id des conteneurs qui ont été séléctionnés
			# pour la suite du processus avec ansible il faut que la liste commence et finisse par '"'	
			foreach($_POST['list_selected_id'] as $selected){
				$containers_list.=" ".$selected;
			}

			# on ajoute à la fin de la liste '"'
			$containers_list.='"';

			#on ajoute containers_list dans la deuxième case de notre tableau $message
			array_push($message, $containers_list);
		}
		elseif(isset($_POST['stop_selection']))
		{
			$message = array("stop_selection");
			$containers_list = '"';

			# on veut remplir containers_list avec tous les id des conteneurs qui ont été séléctionnés
			# pour la suite du processus avec ansible il faut que la liste commence et finisse par '"'	
			foreach($_POST['list_selected_id'] as $selected){
				$containers_list.=" ".$selected;
			}

			# on ajoute à la fin de la liste '"'
			$containers_list.='"';

			#on ajoute containers_list dans la deuxième case de notre tableau $message
			array_push($message, $containers_list);
		}
		elseif(isset($_POST['destroy_selection']))
		{
			$message = array("destroy_selection");
			$containers_list = '"';

			# on veut remplir containers_list avec tous les id des conteneurs qui ont été séléctionnés
			# pour la suite du processus avec ansible il faut que la liste commence et finisse par '"'	
			foreach($_POST['list_selected_id'] as $selected){
				$containers_list.=" ".$selected;
			}

			# on ajoute à la fin de la liste '"'
			$containers_list.='"';

			#on ajoute containers_list dans la deuxième case de notre tableau $message
			array_push($message, $containers_list);
		}
		elseif(isset($_POST['delete_img']))
		{
			$imgName = $_POST['image'];
			$message = array("delete_img", $imgName);
		}
	}

	if($executeHeaderForTerminal){

		$controller->socketHandler($message);	
		header('Location: http://192.168.56.102/terminal/'.$id.'/');
		exit;
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
				<legend>Gestion&Infos Conteneurs</legend>
				<select name="image" style="font-size:16px;">
					<?php
			 foreach($images_list as $image){
			 ?>
			 <option><?php echo $image;?></option>
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
						<th>N°</th>
						<th><input type="checkbox" id="check_ctr" onClick="checkAll();"></th>
						<th width="10%">ID</th>
						<th width="15%">NOM</th>
						<th width="10%">IMAGE</th>
						<th width="10%">ÉTAT</th>
						<th width="10%">IP</th>
						<th width="40%">ACTIONS</th>
					</thead>
					<tbody>
						<?php
	  //ici $i represente le nb de conteneurs qui sont creer il va etre recuperer depuis le fichier retour.xml pour des raisons de test j'ai mis un seul conteneur pour tester
	  if(!empty($containersInfoMatrix[0][0])){
	  for($i = 0; $i < $nbConteneursTotal ; $i++){
	  ?>
	  <tr>
		  <td>
			  <?php echo $i+1 ?>
		  </td>
		  <td>
			  <input type="checkbox" name="list_selected_id[]" value=<?php echo $containersInfoMatrix[$i][0]?>>
		  </td>
		  <?php
		  /* var $j nb info qu'on va afficher ici on a 4 ID, NOM, IMAGE, ETAT
			    * encore le prob de l'affichge existe parce que le tableau se charge avant que les infos 
			    * des conteneurs soit recuperer du fichier retour.xml
			    */
			    for($j = 0; $j < 5; $j++){
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
				    <a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=stop" class="button button2">ARRÊTER</a>
				    <a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=destroy" class="button button3">DÉTRUIRE</a>
				    <a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=terminal&amp;ip=<?php echo $containersInfoMatrix[$i][4]?>" class="button button5">TERMINAL</a>
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
