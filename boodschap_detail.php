<?php
    require_once "./lib/autoload.php";

    $id = $_GET["id"];

    // sql query voor de gegevens specifiek aan de boodschap
    $gro_sql = "select gro_name, gro_date, gro_description gro_desc,
                    (select sum(row_pieces) from row where row_gro_id = $id) as gro_amount,
                    round((select sum(row_pieces * row_pric) from row where row_gro_id = $id), 2) as gro_pric,
                    (select row_id + 1 from row order by row_id desc limit 1) as next_row_id
                from grocery where gro_id = $id;";

    // sql query voor de gegevens specifiek aan de verschillende rijen van de boodschap
    $rows_sql = "select row_pieces, round(row_pric,2) row_pric, row_id,
                    (select art_id from article where art_id = row_art_id) as art_id,
                    (select sto_name from stores where sto_id = row_sto_id) as sto_name,
                    (select art_name from article where art_id = row_art_id) as art_name
                from row where row_gro_id = $id";

    $articles_sql = "select art_id, art_name from article";
    $stores_sql = "select sto_id, sto_name from stores";

    // opvragen van de data
    $gro_data = GetData($gro_sql);
    $rows_data = GetData($rows_sql);
    $articles_data = GetData($articles_sql);
    $stores_data = GetData($stores_sql);
    $next_row_id = $gro_data[0]["next_row_id"];

    // indien een boodschap opgrvraagd wordt waarvan de id niet bestaat, wordt de gebruiker herleid
    // naar error.php met volgende status message
    if (!$gro_data && $id != -1){
        $_SESSION["status"]["404"] ="Helaas! Deze boodschap kan niet gevonden worden!";
        exit(header("location: ./error.php"));
    }

    // indien opgevraagde id gelijk is aan -1 wordt een leeg formulier gecreëerd
    elseif ($id == -1){
        $gro_data = [0 =>
                        [
                            "gro_name"=>"Geef een naam",
                            "gro_desc"=>"Geef een beschrijving",
                            "gro_amount" => 0,
                            "gro_pric"=>0.00,
                            "gro_date"=> date("Y-m-d", strtotime("today"))
                            ]
                        ];
    }

    // pagina opbouwen
    PrintHead();
    PrintNavbar();

    $add_row = file_get_contents("./templates/boodschap_detail_add_row.html");
    $article_list_item = "<li class='article__list__item' id=@art_id@>@art_name@</li>";
    $articles = "";
    foreach($articles_data as $row){
        $list_item = str_replace("@art_id@", $row["art_id"], $article_list_item);
        $articles .= str_replace("@art_name@", $row["art_name"], $list_item);
    }
    $store_list_item = "<li class='store__list__item' id=@sto_id@>@sto_name@</li>";
    $stores = "";
    foreach($stores_data as $row){
        $list_item = str_replace("@sto_id@", $row["sto_id"], $store_list_item);
        $stores .= str_replace("@sto_name@", $row["sto_name"], $list_item);
    }

    $content = MergeViewWithData("boodschap_detail.html", $gro_data);
    $content = str_replace("@csrf@", GenerateCSRF(), $content);

    // verschillende rows aanmaken + 1 rij voor de knop nieuw toevoegen
    $rows = MergeViewWithData("boodschap_detail_row.html", $rows_data);
    $rows .= str_replace("@next_row_id@", $next_row_id, $add_row);

    // placeholder vervangen door de gegenereerde rows.
    $content =  str_replace("@grocery_rows@", $rows, $content);
    $content = str_replace("@article_list@", $articles, $content);
    $content = str_replace("@store_list@", $stores, $content);

    echo $content;
    echo '<script src="../js/index.js"></script>';
