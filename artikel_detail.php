<?php
require_once "./lib/autoload.php";
$id = $_GET["id"];


//sql query voor artikeldetail en artikeldetailrow
$artDetail_sql="select art_id, art_name, art_code, art_img from article where art_id = $id";

$artDetailRow_sql="select pri_id, pri_art_id, pri_sto_id, sto_name, art_name, pri_value, uni_name from stores as s
join art_price_sto aps on s.sto_id = aps.pri_sto_id
join article a on aps.pri_art_id = a.art_id
join units u on u.uni_id = a.art_uni_id
where pri_art_id = $id
order by pri_value;";

$stores_data_sql = "select sto_id, sto_name from stores where sto_id not in(
    select pri_sto_id from art_price_sto
    where pri_art_id = $id)";

//data ophalen uit DB
$stores_data= GetData($stores_data_sql);
$artDetail_data= GetData($artDetail_sql);
$artDetailRow_data= GetData($artDetailRow_sql);
//html template bestanden
$artDetail_temp = "artikeldetail.html";
$artDetailRow_temp = "artikeldetailrow.html";

//pagina opbouw
$csrf = GenerateCSRF();
$stores = MergeViewWithData("store_list_item.html", $stores_data);

$content = PrintHead();
$content .= PrintNavbar();
//samenstellen van data en templates
$artDetailRow = MergeViewWithData($artDetailRow_temp,$artDetailRow_data);
$artDetailRow .= file_get_contents("./templates/artikeldetail_addrow.html");

$content .= MergeViewWithData( $artDetail_temp, $artDetail_data );
$content = str_replace("@artikel_list@", $artDetailRow, $content);
$content = str_replace("@csrf@", $csrf, $content);
$content = str_replace("@store_list@", $stores, $content);
$content = MergeErrorInfoPlaceholders($content, $errors, $info);
$content = removeEmptyPlaceholders($content);

echo $content;
echo "<script src='./js/index.js'></script>";

PrintFooter();
