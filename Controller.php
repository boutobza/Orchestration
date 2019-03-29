<?php
class Controller{
	public function getTokenValue(){
		return hash_hmac('sha256', 'index.php', $this->getSessionKey());
	}

	public function deleteContainer($containerID){
		$containersInfoMatrix = $this->getContainersInfo();
		for($i = 0; $i < count($containersInfoMatrix); $i++){
			if(strcmp($containersInfoMatrix[$i][0], $containerID) == 0){
				unset($containersInfoMatrix[$i]);
				$containersInfoMatrix = array_values($containersInfoMatrix);
				$_SESSION['containers'] = $containersInfoMatrix;
				break;
			}
		}
	}

	public function deleteContainers($method){

		$containersIDs = $method['list_selected_id']; 
		
		$containersInfoMatrix = $this->getContainersInfo();

		foreach($containersIDs as $containerID){

			for($i = 0; $i < count($containersInfoMatrix); $i++){
				if(strcmp($containersInfoMatrix[$i][0], $containerID) == 0){
					unset($containersInfoMatrix[$i]);
					$containersInfoMatrix = array_values($containersInfoMatrix);
					break;
				}
			}
		}
		$_SESSION['containers'] = $containersInfoMatrix;
	
	}

	public function getContainersInfo(){
		if (empty($_SESSION['containers'])){
			return $_SESSION['containers'] = array(array());
		} else {
			return $_SESSION['containers'];
		}
	}

	public function random($length){
		return bin2hex(random_bytes($length)); 
	}

	public function selectionAction($action) : array{

		$message = array($action);
		$containers_ids = '"';
		$containers_tokens = '"';
		$nbContainers = 0;
		# on veut remplir containers_list avec tous les id des conteneurs qui ont été séléctionnés
		# pour la suite du processus avec ansible il faut que la liste commence et finisse par '"'      
		foreach($_POST['list_selected_id'] as $selected){
			$containersInfoMatrix = $this->getContainersInfo();
			$containers_ids.=" ".$selected;
			for($i = 0; $i < count($containersInfoMatrix); $i++){
				if(strcmp($containersInfoMatrix[$i][0], $selected) == 0){
					$containers_tokens.=" ".$containersInfoMatrix[$i][5];
					$nbContainers += 1;
					break;
				}
			}
		}

		# on ajoute à la fin de la liste '"'
		$containers_ids.='"';
		$containers_tokens .= '"';
	
	
		if($action === 'destroy_selection'){
			array_push($message, $containers_ids, $containers_tokens, $nbContainers);
			$this->deleteContainers($_POST);
		}
		elseif($action === 'stop_selection')
			array_push($message, $containers_ids, $containers_tokens, $nbContainers);
		else
			array_push($message, $containers_ids, $containers_tokens);

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

		$nbConteneursTotal = 0;

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
		$containersInfoMatrix = $this->getContainersInfo();

		if(@$xml->load('/var/www/pageDeGestion/html/user/retour.xml')){
			// recupere tout les id des conteneurs crees
			$containersIDs = $xml->getElementsByTagName('id');

			// recupere tout les noms des conteneurs crees
			$containersNames = $xml->getElementsByTagName('name');

			// recupere tout les images utilisées pour les conteneurs crees
			$containersImages = $xml->getElementsByTagName('img');

			// recupere tout les etats des conteneurs crees
			$containersStatus = $xml->getElementsByTagName('status');

			// recupere les ip des conteneurs
			$containersIPs = $xml->getElementsByTagName('ip');

			$containersTokens = $xml->getElementsByTagName('token');

			//nbConteneursTotal reperesente le nb de conteneurs present dans le fichier retour.xml
			$nbConteneursTotal = count($containersIDs);

			if($nbConteneursTotal != 0){
				// containersInfoMatrix c'est une matrice qui contient les infos des conteneurs qui sont crees auparavant
				if(empty($containersInfoMatrix[0][0])){
					for($i = 0; $i < $nbConteneursTotal ; $i++){
						$containersInfoMatrix[$i] = array($containersIDs[$i]->nodeValue,
							$containersNames[$i]->nodeValue,
							$containersImages[$i]->nodeValue,
							$containersStatus[$i]->nodeValue,
							$containersIPs[$i]->nodeValue,
							$this->random(32));	
					}
				}
				else {
					$initialLength = count($containersInfoMatrix);

					for($i = 0; $i < $nbConteneursTotal ; $i++){
					//on assume que l ID de conteneur existe deja dans le containersInfoMatrix, le containersInfoMatrix en realite contient la variable session 'containers' voir plus haut la fonction getContainersInfo() et la ligne de code qui permet d'affecter la valeur de cette variable au containersInfoMatrix
					$idExist = true;
					$length = count($containersInfoMatrix);

					//dans cette boucle on va comparer les IDs des nouveaux conteneurs cree avec celle qui sont deja stockes dans la variable de session 'containers' conteneur par conteneur si l'id existe deja dans la variable de session on break la boucle sinon on continue la comparaison jusqu'aux la fin de tous les IDs conteneurs stocke dans la variable de session
						for($j = 0; $j < $initialLength; $j++){
							if(strcmp($containersIDs[$i]->nodeValue, $containersInfoMatrix[$j][0]) != 0){
								$idExist = false;
							}
							else {
								$idExist = true;
								// on fait l'actualisation des infos de conteneur et ça tres important surtout lorsque on lance un conteneur
								$containersInfoMatrix[$j] = array($containersInfoMatrix[$j][0],
												  $containersInfoMatrix[$j][1],
												  $containersInfoMatrix[$j][2],
												  $containersStatus[$i]->nodeValue,
												  $containersIPs[$i]->nodeValue,
											  	  $containersInfoMatrix[$j][5]);
								break;
							}
						
						}

// si l'id de nouveaux conteneur n'existe pas deja dans la variable de session 'containers' qui est affecté au containersInfoMatrix, on va recuperer ces infos à partir de fichier retour.xml et on le donne un nouveau token unique et  comme ça nous sommes certain que les token des conteneurs sont unique et ne changent pas lors de l'actualisation ou lors la redirection de page 
						if($idExist == false){
							$containersInfoMatrix[$length] = array($containersIDs[$i]->nodeValue,
								$containersNames[$i]->nodeValue,
								$containersImages[$i]->nodeValue,
								$containersStatus[$i]->nodeValue,
								$containersIPs[$i]->nodeValue,
								$this->random(32));
						}


					}
				}


// on fait l'affectation de containersInfoMatrix au variable de session pour faire la mise à jour de cette derniere
				$_SESSION['containers'] = $containersInfoMatrix;

			}
		}

		return $_SESSION['containers'];
	}

}
?>
