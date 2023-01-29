<?php
/*
    Copyright 2023 Morten Freberg

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

$pwdwrong = false;

if (!empty($_POST['nickname'])) {
	$nickname = htmlspecialchars($_POST['nickname']);
}
if (!empty($_POST['password'])) {
	$password = htmlspecialchars($_POST['password']);
}
try {
	if (isset($nickname) && !empty($nickname) && isset($password) && !empty($password)) {
		$dsn = DB_DRIVER.":host=".DB_HOST.";dbname=".DB_NAME;
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];
		$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
		$stmt = $pdo->prepare("SELECT password FROM users WHERE nickname = :nickname");
		$stmt->bindParam(":nickname", $nickname, PDO::PARAM_STR);
		$stmt->execute();
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
	error_log($langArray['invalid_query'].' '.$e->getMessage() . '\n'. $langArray['whole_query'].' '. $stmt->queryString, 0);
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
