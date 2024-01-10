<?php
	if (empty($_SESSION['user_id'])) {
		// redirect to index.php
		header('Location: index.php');
		exit;
	}
?>