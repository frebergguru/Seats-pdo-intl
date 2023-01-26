<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

$pwdwrong = false;
try {
	$nickname = addslashes(htmlspecialchars(filter_input(INPUT_POST, 'nickname', FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8'));
	$password = addslashes(htmlspecialchars(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8'));
	if (isset($nickname) && !empty($nickname) && isset($password) && !empty($password)) {
		$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];
		$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
		$stmt = $pdo->prepare("SELECT password FROM users WHERE nickname = :nickname");
		$stmt->execute(['nickname' => $nickname]);
		$results = $stmt->fetch(PDO::FETCH_ASSOC);
		if (isset($results["password"])) {
			if (password_verify($password, $results["password"])) {
				$_SESSION['nickname'] = $nickname;
				header('Location: '.dirname($_SERVER['REQUEST_URI']));
				exit;
			} else {
				include 'includes/header.php';
				print'<span class="srs-header">'.$langArray['wrong_username_or_password'].'</span><br><br><br>';
				$pwdwrong = true;
			}
		} else {
			include 'includes/header.php';
			print'<span class="srs-header">'.$langArray['wrong_username_or_password'].'</span><br><br><br>';
			$pwdwrong = true;
		}
	}
} catch (PDOException $e) {
	echo $langArray['error'].': '.$e->getMessage();
}{
	if ($pwdwrong == false) {
		include 'includes/header.php';
	};
	print'<form class="srs-container" method="POST" action="'.$_SERVER["PHP_SELF"].'">
        <span class="srs-header">'.$langArray['login'].'</span>

        <div class="srs-content">
            <label for="fullname" class="srs-lb">'.$langArray['nickname'].'</label><input name="nickname" value="'.$nickname.'" id="nickname" class="srs-tb"><br>
            <span id="statusfullname"></span><br>
            <label for="password" class="srs-lb">'.$langArray['password'].'</label><input name="password" id="password" type="password" class="srs-tb"><br>
        </div>
        <div class="srs-footer">
            <div class="srs-button-container">
                <input type="submit" class="submit" value="'.$langArray['login'].'">
            </div>
            <div class="srs-slope"></div>
        </div>
    </form>';
};
?>
<br>
<?php
include 'includes/footer.php';
?>
