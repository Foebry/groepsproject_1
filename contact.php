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

//add extra elements
$extra_elements['csrf'] = GenerateCSRF( "contact.php"  );

//merge
$output = MergeViewWithData( 'contactform.html', $data );
$output = MergeViewWithExtraElements( $output, $extra_elements );
$output = MergeViewWithErrors( $output, $errors );
$output = RemoveEmptyErrorTags( $output, $data );

print $output;
?>

</div>
</div>

</body>
</html>
