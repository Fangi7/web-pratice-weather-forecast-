<?php
    header("refresh:5; url=/hw3/login.php ");
    echo "<h1>You will be signed out after five seconds.</h1>";
    echo "<h3><a href='/hw3/login.php'>If you not successfully logged out, please click here.</a></h3>";

    // set cookie
    setcookie("logged", false);
    setcookie("loginName", "");
    setcookie("pwd", "");
?>