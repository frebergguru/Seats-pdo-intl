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
require 'config.php';

if (isset($_GET['lang']) && !empty($_GET['lang'])) {
    $_SESSION['langID'] = htmlspecialchars($_GET['lang']);
}

include 'i18n/' . $_SESSION['langID'] . '.php';
?>