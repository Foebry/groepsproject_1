<?php
require_once "./lib/autoload.php";
$id = $_GET["id"];

//sql query voor artikeldetail en artikeldetailrow
$artDetail_sql="select art_id, art_name, art_code, art_img from article where art_id = $id";

$artDetailRow_sql="select sto_name, row_pric, uni_name from stores as s
inner join row r on s.sto_id = r.row_sto_id
inner join article a on r.row_art_id = a.art_id
inner join units u on u.uni_id =a.art_uni_id
where r.row_art_id = $id
group by s.sto_id";

//data ophalen uit DB
$artDetail_data= GetData($artDetail_sql);
$artDetailRow_data= GetData($artDetailRow_sql);
//html template bestanden
$artDetail_temp = "artikeldetail.html";
$artDetailRow_temp = "artikeldetailrow.html";

//pagina opbouw
$content = PrintHead();
$content .= PrintNavbar();
//samenstellen van data en templates
$artDetailRow = MergeViewWithData($artDetailRow_temp,$artDetailRow_data);
$content .= MergeViewWithData( $artDetail_temp, $artDetail_data );
$content = str_replace("@artikel_list@", $artDetailRow, $content);

echo $content;
