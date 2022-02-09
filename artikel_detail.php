<?php
require_once "./lib/autoload.php";

//sql query voor artikeldetail en artikeldetailrow
$artDetail_sql="select art_id, art_name, art_code, art_img from article ";

$artDetailRow_sql="select sto_name, row_pric, uni_name from stores as s
inner join row r on s.sto_id = r.row_sto_id
inner join units u on u.uni_id";

//data ophalen uit DB
$artDetail_data= GetData($artDetail_sql);
$artDetailRow_data= GetData($artDetailRow_sql);
//html template bestanden
$artDetail_temp= file_get_contents("./templates/artikeldetail.html");
$artDetailRow_temp= file_get_contents("./templates/artikeldetailrow.html");

//pagina opbouw
PrintHead();
PrintNavbar();
//samenstellen van data en templates
$artDetailRow = MergeViewWithData($artDetailRow_temp,$artDetailRow_data);
$content = MergeViewWithData( $artDetail_temp, $artDetail_data );
$content = str_replace("@artikel_list@", $artDetailRow, $content);

echo $content;

