<?php
class Controller{

	public function getTokenValue(){
		return hash_hmac('sha256', 'index.php', $this->getSessionKey());
	}

	public function getSessionKey(){
		if (empty($_SESSION['key'])){
			return $_SESSION['key'] = $this->random(32);
		} else {
			return $_SESSION['key'];
		}
	}

	public function random($length){
		return bin2hex(random_bytes($length)); 
	}

	public function selectionAction($action) : array{

		$message = array($action);
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
		return $message;
	}

	public function socketHandler($msg){
		$host = "localhost";
		$port = 12800;

		// on encode le message en json pour pouvoir l'envoyer
		$message = json_encode($msg);
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
	}

	public function getImagesList(){
		$images_list = file_get_contents('/var/www/pageDeGestion/html/user/images_list');
		# images_list est un string dont chaque valeur est délimitée par ',' 
		# on le transforme en array en précisant ',' comme délimiteur
		$images_list = explode(',', $images_list);
		return $images_list;
	}

	public function uploadDockerfile($post, $files) : array {
		$imgTag = $post['imgName'];

		$target_dir = "/var/www/pageDeGestion/html/uploads/";
		$dockerfileToUpload = basename($files["dockerfileToUpload"]["name"]);
		$target_file = $target_dir .$dockerfileToUpload;
		$tmp_file = $files["dockerfileToUpload"]["tmp_name"];

		#variable qui va etre utiliser pour faire qlq test
		$uploadOk = 1;

		if ($uploadOk == 0) {
			echo "Sorry, your file was not uploaded.";
			// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($tmp_file, $target_file)) {
				echo "The file ".$dockerfileToUpload. " has been uploaded.";
			} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
		return array("buildImg", $dockerfileToUpload, $imgTag);
	}

	public function getContainersTotalNumber() {
		$xml = new DOMDocument("1.0", "UTF-8");
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;


		if(@$xml->load('/var/www/pageDeGestion/html/user/retour.xml')){
			$containersIDs = $xml->getElementsByTagName('id');

			$nbConteneursTotal = count($containersIDs);
		}
		return $nbConteneursTotal;
	}

	public function displayContainersInfo () : array {

		$xml = new DOMDocument("1.0", "UTF-8");
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;

		// une matrice qui va contenir toutes les infos des conteneurs présentent dans le fichier retour.xml
		$containersInfoMatrix = array(array());

		if(@$xml->load('/var/www/pageDeGestion/html/user/retour.xml')){
			// recupere tout les id des conteneurs crees
			$containersIDs = $xml->getElementsByTagName('id');

			// recupere tout les noms des conteneurs crees
			$containersNames = $xml->getElementsByTagName('name');

			// recupere tout les images utilisées pour les conteneurs crees
			$containersImages= $xml->getElementsByTagName('img');

			// recupere tout les etats des conteneurs crees
			$containersStatus= $xml->getElementsByTagName('status');

			// recupere les ip des conteneurs
			$containersIP= $xml->getElementsByTagName('ip');

			$nbConteneursTotal = count($containersIDs);


			if( $nbConteneursTotal != 0){

				for($i = 0; $i < $nbConteneursTotal ; $i++){
					$containersInfoMatrix[$i] = array($containersIDs[$i]->nodeValue,
						$containersNames[$i]->nodeValue,
						$containersImages[$i]->nodeValue,
						$containersStatus[$i]->nodeValue,
						$containersIP[$i]->nodeValue);
				}
			}
			//	else echo '<script>alert("Pas de conteneurs à détruire !");</script>';

		}
		else echo '<script>alert("Fichier retour.xml est vide !");</script>';
		return $containersInfoMatrix;
	}
}
?>
