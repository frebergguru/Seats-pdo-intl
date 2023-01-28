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
?>
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
