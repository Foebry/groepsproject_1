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

//slider
$sql = 'select article.art_id, article.art_name, article.art_img from row
inner join article on row.row_art_id = article.art_id';

$data = GetData("$sql");

$output = MergeViewWithData('productcard.html', $data);
$template = file_get_contents("templates/slider.html");
$result = str_replace("@slider@", $output, $template);

//boodschappen
$where = isset($_GET['search']) ? 'where gro_name like "%'.$_GET['search'].'%"': '';

$sql = "select gro_id, gro_name, gro_date, per_firstname, per_lastname, sum(row_pieces) as aantal,
(select round(sum(row_pric * row_pieces)) from row
where row_gro_id = gro_id) as totaal from grocery
inner join person on grocery.gro_per_id = person.per_id
inner join row on grocery.gro_id = row.row_gro_id ".
$where
." group by gro_id;";

$next_gro_id_sql = "select gro_id+1 next_gro_id from grocery group by gro_id desc limit 1";
$next_gro_id = GetData($next_gro_id_sql)[0]["next_gro_id"];
$data = GetData("$sql");

$output = MergeViewWithData( 'boodschapcard.html', $data );
$template = file_get_contents("templates/boodschappen.html");
$result .= str_replace("@list@", $output, $template);
$result = str_replace("@next_gro_id@", $next_gro_id, $result);

print $result;

?>

</div>
</div>

</body>
</html>
