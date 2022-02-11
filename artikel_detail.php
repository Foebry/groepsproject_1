<?php
require_once "./lib/autoload.php";
$id = $_GET["id"];

//sql query voor artikeldetail en artikeldetailrow
$artDetail_sql="select art_id, art_name, art_code, art_img from article where art_id = $id";

$artDetailRow_sql= "select s.sto_name, aps.pri_value, uni_name from stores as s
    inner join art_price_sto aps on s.sto_id = aps.pri_sto_id
    inner join article a on aps.pri_art_id = a.art_id
    inner join units u on a.art_uni_id = u.uni_id
where a.art_id = $id
group by s.sto_id;";

//data ophalen uit DB
$artDetail_data= GetData($artDetail_sql);
$artDetailRow_data= GetData($artDetailRow_sql);
//html template bestanden
$artDetail_temp = "artikeldetail.html";
$artDetailRow_temp = "artikeldetailrow.html";

//pagina opbouw
$csrf = GenerateCSRF();

$content = PrintHead();
$content .= PrintNavbar();
//samenstellen van data en templates
$artDetailRow = MergeViewWithData($artDetailRow_temp,$artDetailRow_data);
$artDetailRow .= file_get_contents("./templates/artikeldetail_addrow.html");
$content .= MergeViewWithData( $artDetail_temp, $artDetail_data );
$content = str_replace("@artikel_list@", $artDetailRow, $content);
<<<<<<< HEAD
$content = str_replace("@csrf@", $csrf, $content);
=======
>>>>>>> dev
$content = MergeErrorInfoPlaceholders($content, $errors, $info);
$content = removeEmptyPlaceholders($content);

echo $content;
echo "<script src='./js/index.js'></script>";

PrintFooter();
