<?php
    require_once "./lib/autoload.php";

    $id = $_GET["id"];


    $next_row_id = $_SESSION["next_row_id"]; //GetData($next_row_id_sql)[0]["row_id"];


    // indien data nog niet ingeladen, laadt data in vanuit databank.
    if(!array_key_exists($id, $_SESSION["boodschappen"])){
        // sql query voor de gegevens specifiek aan de boodschap

        $gro_sql = "select gro_id, gro_name, gro_date, gro_description, gro_per_id,
                    (select sum(row_pieces) from row where row_gro_id = $id) as gro_amount,
                    round((select sum(row_pieces * pri_value) from row join article a on a.art_id = row.row_art_id
                    join art_price_sto aps on a.art_id = aps.pri_art_id where (pri_sto_id= row_sto_id and row_gro_id = $id)), 2) as gro_pric,
                    (select row_id + 1 from row order by row_id desc limit 1) as next_row_id
                    from grocery where gro_id = $id;";


        //sql query voor de gegevens specifiek aan de verschillende rijen van de boodschap
        $rows_sql = "select row_pieces, round(pri_value,2) as pri_value, row_id, row_sto_id, row_art_id,
                        (select sto_name from stores where sto_id = row_sto_id) as sto_name,
                        (select art_name from article where art_id = row_art_id) as art_name
                    from row
                        join article a on row.row_art_id = a.art_id
                        join art_price_sto aps on a.art_id = aps.pri_art_id
                    where (pri_sto_id= row_sto_id and row_gro_id = $id)";



        $rows_data = GetData($rows_sql);
        $gro_data = GetData($gro_sql);

    }
    else{
        $gro_data = key_exists("headers", $_SESSION["boodschappen"][$id]) ? $_SESSION["boodschappen"][$id]["headers"] : setGroceryHeaders($id, $next_row_id);
        $rows_data = key_exists("data", $_SESSION["boodschappen"][$id]) ? $_SESSION["boodschappen"][$id]["data"] : [];
    }


    //$articles_sql = "select art_id, art_name from article";
    $articles_sql = "select art_id, sto_id, art_name, sto_name from art_price_sto
                        join article a on art_price_sto.pri_art_id = a.art_id
                        join stores s on art_price_sto.pri_sto_id = s.sto_id";

    // opvragen van de data
    $articles_data = GetData($articles_sql);
    $stores_data = $articles_data;

    // indien een boodschap opgrvraagd wordt waarvan de id niet bestaat, wordt de gebruiker herleid
    // naar error.php met volgende status message
    if($id <= 0){
        $_SESSION["status"]["404"] ="Helaas! Deze boodschap kan niet gevonden worden!";
        exit(header("location: ./error.php"));
    }

    // opevraagde grocery bestaat nog niet
    if (!$gro_data) $gro_data = setGroceryHeaders($id, $next_row_id);
    if (!$rows_data) $rows_data = [];

    // pagina opbouwen
    $content = PrintHead();
    $content .= PrintNavbar();

    $articles = MergeViewWithData("article_list_item.html", $articles_data);
    $stores = MergeViewWithData("store_list_item.html", $stores_data);

    // verschillende rows aanmaken + 1 rij voor de knop nieuw toevoegen
    $add_row = file_get_contents("./templates/boodschap_detail_add_row.html");

    $rows = MergeViewWithData("boodschap_detail_row.html", $rows_data);
    $rows .= str_replace("@next_row_id@", $next_row_id, $add_row);

    // placeholder vervangen door de gegenereerde rows.
    $content .= MergeViewWithData("boodschap_detail.html", $gro_data);
    $content = str_replace("@csrf@", GenerateCSRF(), $content);
    $content =  str_replace("@grocery_rows@", $rows, $content);
    $content = str_replace("@article_list@", $articles, $content);
    $content = str_replace("@store_list@", $stores, $content);
    $content = MergeErrorInfoPlaceholders($content, $errors, $info);
    $content = removeEmptyPlaceholders($content);

    echo $content;
    echo '<script src="./js/index.js"></script>';

    printFooter();
