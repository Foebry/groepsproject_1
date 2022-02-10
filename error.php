<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once "lib/autoload.php";

$content = PrintHead();
$content .= PrintNavbar();
$content .= PrintJumbo("Error");

echo $content;

    foreach ($_SESSION[status] as $msg) {
        print $msg;
    }
    PrintFooter();

    ?>
</div>
</div>

</body>
</html>
