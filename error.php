<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once "lib/autoload.php";

PrintHead();
PrintNavbar();
PrintJumbo("Error");
?>

<div class="container">
    <div class="row">
    <?php 
    foreach ($_SESSION[status] as $msg) {
        print $msg;
    }
    ?>
</div>
</div>

</body>
</html>