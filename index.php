<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Page de gestion</title>
	</head>
<?php
if(isset($_REQUEST['create']))
{
	$nb = $_REQUEST['nb'];
	echo "création de $nb conteneur(s).";
}
?>
	<body>
		<form action="index.php" method="post">
			<select name="image">
				<option>Ubuntu</option>
				<option>Debian</option>
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
		</form>
	</body>
</html>
