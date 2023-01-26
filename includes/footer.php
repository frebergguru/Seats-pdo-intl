<br>
<!-- div menu start -->
<div class="menu">
    <?php if ($home != true) {echo '<a href="index.php">'.$langArray['home'].'</a> | ';}; if (!isset($_SESSION['nickname']) or $left == true) {echo '<a href="register.php">'.$langArray['register'].'</a> | <a href="login.php">'.$langArray['login'].'</a>';}else { echo '<a href="logout.php">'.$langArray['logout'].'</a>';}; if (!isset($_SESSION['nickname']) or $left == true) {echo' | <a href="forgot.php">'.$langArray['forgot_password'].'</a>';};
?>
<br>
<br>
<form name="langSelect" action="" method="get" >
	<select name="lang" id="lang" >
		<option value="<?php echo $lang; ?>"><?php echo $langArray['select_language']; ?></option>
		<option value="en">English</option>
		<option value="no">Norsk</option>
	</select> <button type="submit"><?php echo $langArray['language_btn']; ?></button>
</form>
</div>
<!-- div menu end -->
<!-- div wrapper end -->
</div>
<!-- div main_wrapper end -->
</div>
</body>
</html>
