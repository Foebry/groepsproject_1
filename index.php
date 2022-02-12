<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once "lib/autoload.php";

?>

<?php
$result = PrintHead("MyWeGo");
$result .= PrintNavbar();
//slider
$sql = 'select row_art_id, art_id, art_name, a.art_img, sum(row_pieces) aankopen, row_gro_id from row
join article a on row.row_art_id = a.art_id
group by row_art_id
order by aankopen desc
limit 5';

$data = GetData("$sql");

$output = MergeViewWithData('productcard.html', $data);
$template = file_get_contents("templates/slider.html");
$result .= str_replace("@slider@", $output, $template);

//boodschappen
$where = isset($_GET['search']) ? 'where gro_name like "%'.$_GET['search'].'%"': '';

$sql = "select g.gro_id, g.gro_name, g.gro_date, p.per_firstname, p.per_lastname,sum(row_pieces) as aantal,
(select round(sum(aps.pri_value * row_pieces)) from row r
inner join article a on r.row_art_id = a.art_id
join art_price_sto aps on a.art_id = aps.pri_art_id
where (pri_sto_id= row_sto_id and r.row_gro_id = g.gro_id)) as totaal from row r
join grocery g on g.gro_id = r.row_gro_id
join person p on p.per_id = g.gro_per_id".
    $where."group by g.gro_id
order by gro_id desc";


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
