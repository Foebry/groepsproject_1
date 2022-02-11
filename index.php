<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once "lib/autoload.php";

?>

<?php
$result = PrintHead("MyWeGo");
$result .= PrintNavbar();
//slider
$sql = 'select row_art_id art_id, art_name, a.art_img, sum(row_pieces) aankopen, row_gro_id from row
join article a on row.row_art_id = a.art_id
group by row_art_id
order by aankopen desc, art_name
limit 5';

$data = GetData("$sql");

$output = MergeViewWithData('productcard.html', $data);
$template = file_get_contents("templates/slider.html");
$result .= str_replace("@slider@", $output, $template);

//boodschappen
$where = isset($_GET['search']) ? 'where gro_name like "%'.$_GET['search'].'%"': '';

$sql = "select gro_id, gro_name, gro_date, per_firstname, per_lastname, sum(row_pieces) as aantal,
(select round(sum(art_price_sto.pri_value * row_pieces)) from row
where row_gro_id = gro_id) as totaal from grocery
inner join person on grocery.gro_per_id = person.per_id
inner join row on grocery.gro_id = row.row_gro_id 
inner join article on article.art_id = row.row_art_id 
inner join art_price_sto on article.art_id = art_price_sto.pri_art_id ".
$where
." group by gro_id
order by gro_id desc;";


$data = GetData("$sql");

$output = MergeViewWithData( 'boodschapcard.html', $data );
$template = file_get_contents("templates/boodschappen.html");
$result .= str_replace("@list@", $output, $template);
$result = str_replace("@next_gro_id@", $_SESSION["next_gro_id"], $result);

print $result;
PrintFooter();

?>
</body>
</html>
