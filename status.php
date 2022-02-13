<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once "lib/autoload.php";

$content = PrintHead();
$content .= PrintNavbar();
$content .= "@info@";

$content = MergeErrorInfoPlaceholders($content, $errors, $info);
$content = removeEmptyPlaceholders($content);
echo $content;

PrintFooter();

    ?>
</div>
</div>

</body>
</html>
