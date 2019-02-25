<?php
include ('Controller.php');

$controller = new Controller();

$containersInfoMatrix = $controller->displayContainersInfo();

$nbConteneursTotal = $controller->getContainersTotalNumber();

#images_list est un tableau qui contient toutes les images docker
$images_list = $controller->getImagesList();

$host    = "localhost";
$port    = 12800;

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
if(!empty($_POST))
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

	// on encode le message en json pour pouvoir l'envoyer
	$message = json_encode($message);
	// create socket
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create socket\n");
	// connect to server python
	$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");
	// send message to server
	socket_write($socket, $message, strlen($message)) or die("Could not send data to server\n");
	// next instruction supposed to wait message from server python
	socket_recv($socket, $buffer, 1024, MSG_WAITALL)or die ("Could not receive from server python");
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
	</head>
	<body>
		<form action="index.php" method="post">
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
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=start" class="button button1">LANCER</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=stop" class="button button2">ARRETER</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=destroy" class="button button3">DETRUIRE</a>
					<a href="index.php?id=<?php echo $containersInfoMatrix[$i][0]?>&amp;action=terminal" class="button button5">TERMINAL</a>
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
  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 7px 14px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 10px;
  margin: 4px 2px;
  -webkit-transition-duration: 0.4s; /* Safari */
  transition-duration: 0.4s;
  cursor: pointer;
}

.button1 {
  background-color: white; 
  color: black; 
  border: 2px solid #4CAF50;
}

.button1:hover {
  background-color: #4CAF50;
  color: white;
}

.button2 {
  background-color: white; 
  color: black; 
  border: 2px solid #f4a742;
}

.button2:hover {
  background-color: #f4a742;
  color: white;
}

.button3 {
  background-color: white; 
  color: black; 
  border: 2px solid #f44336;
}

.button3:hover {
  background-color: #f44336;
  color: white;
}

.button4 {
  background-color: white;
  color: black;
  border: 2px solid #e7e7e7;
}

.button4:hover {background-color: #e7e7e7;}

.button5 {
  background-color: white;
  color: black;
  border: 2px solid #555555;
}

.button5:hover {
  background-color: #555555;
  color: white;
}
table {
  border-collapse: collapse;
  width: 60%;
}

th, td, th {
  padding: 2px;
  text-align: center;
  border-bottom: 1px solid #ddd;
}

tr:hover {background-color:#f5f5f5;}

</style>
		<script>




		</script>
	</body>
</html>

