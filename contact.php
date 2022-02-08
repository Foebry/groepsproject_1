<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once "lib/autoload.php";

PrintHead();
PrintNavbar();
?>

<div class="container">
    <div class="row">

<?php
$data = [0 => ["per_firstname" => "", "per_lastname" => "", "per_email" => "", "question" => ""]];

//get template
$output = file_get_contents("templates/contactform.html");

//add extra elements
$extra_elements['csrf'] = GenerateCSRF( "contact.php"  );

//merge
$output = MergeViewWithData( $output, $data );
$output = MergeViewWithExtraElements( $output, $extra_elements );
$output = MergeViewWithErrors( $output, $errors );
$output = RemoveEmptyErrorTags( $output, $data );

print $output;
?>

</div>
</div>

</body>
</html>
