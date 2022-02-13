<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once "lib/autoload.php";

$statuscode = array_keys($status)[0];

$content = PrintHead();
$content .= PrintNavbar();
$content .= "<div class='status'>@status@<div class='status_imgholder'><img src='./images/$statuscode.jpg'></div></div>";


$content = MergeStatusPlaceholders($content, $status);
$content = removeEmptyPlaceholders($content);
echo $content;

PrintFooter();

    ?>
</div>
</div>

</body>
</html>
