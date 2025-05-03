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
<div class="menu">
    <?php
    if (empty($home)) {
        echo '<a href="index.php">' . htmlspecialchars($langArray['home'], ENT_QUOTES, 'UTF-8') . '</a>';
    }
    if (!empty($_SESSION['nickname'])) {
        echo ' | <a href="logout.php">' . htmlspecialchars($langArray['logout'], ENT_QUOTES, 'UTF-8') . '</a>';
        echo ' | <a href="deluser.php">' . htmlspecialchars($langArray['delete_account'], ENT_QUOTES, 'UTF-8') . '</a>';
    } else {
        echo ' | <a href="register.php">' . htmlspecialchars($langArray['new_user_menu'], ENT_QUOTES, 'UTF-8') . '</a>';
        echo ' | <a href="login.php">' . htmlspecialchars($langArray['login'], ENT_QUOTES, 'UTF-8') . '</a>';
        echo ' | <a href="forgot.php">' . htmlspecialchars($langArray['forgot_password'], ENT_QUOTES, 'UTF-8') . '</a>';
    }
    ?>
    <br><br>
    <form name="langSelect" id="langSelect" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" method="get">
        <select name="lang" id="lang" onchange="document.getElementById('langSelect').submit();">
            <option value="<?php echo htmlspecialchars($_SESSION['langID'] ?? 'en', ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($langArray['select_language'], ENT_QUOTES, 'UTF-8'); ?>
            </option>
            <?php
            // Dynamically generate language options
            $languages = ['en' => 'English', 'no' => 'Norsk']; // Add more languages as needed
            foreach ($languages as $code => $name) {
                echo '<option value="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</option>';
            }
            ?>
        </select>
    </form>
</div>
</div>
</div>
</body>
</html>