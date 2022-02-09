<?php
    require_once "./lib/autoload.php";

    $id = $_GET["id"];

    // indien data nog niet ingeladen, laadt data in vanuit databank.
    if(!array_key_exists($id, $_SESSION["boodschappen"])){
        // sql query voor de gegevens specifiek aan de boodschap
        $gro_sql = "select gro_id, gro_name, gro_date, gro_description, gro_per_id,
                        (select sum(row_pieces) from row where row_gro_id = $id) as gro_amount,
                        round((select sum(row_pieces * row_pric) from row where row_gro_id = $id), 2) as gro_pric,
                        (select row_id + 1 from row order by row_id desc limit 1) as next_row_id
                    from grocery where gro_id = $id;";

        // sql query voor de gegevens specifiek aan de verschillende rijen van de boodschap
        $rows_sql = "select row_pieces, round(row_pric,2) row_pric, row_id, row_sto_id, row_art_id,
                        (select sto_name from stores where sto_id = row_sto_id) as sto_name,
                        (select art_name from article where art_id = row_art_id) as art_name
                    from row where row_gro_id = $id";


        $gro_data = GetData($gro_sql);
        $rows_data = GetData($rows_sql);
    }
    else{
        $gro_data = $_SESSION["boodschappen"][$id]["headers"];
        $rows_data = $_SESSION["boodschappen"][$id]["data"];
    }
    /*print("<pre>");
    var_dump($rows_data);
    print("</pre>");
    exit();*/


    $articles_sql = "select art_id, art_name from article";
    $stores_sql = "select sto_id, sto_name from stores";
    $next_row_id_sql = "select row_id from row order by row_id desc limit 1";

    // opvragen van de data
    $articles_data = GetData($articles_sql);
    $stores_data = GetData($stores_sql);
    $next_row_id = GetData($next_row_id_sql)[0]["row_id"];

    // indien een boodschap opgrvraagd wordt waarvan de id niet bestaat, wordt de gebruiker herleid
    // naar error.php met volgende status message
    if($id <= 0){
        $_SESSION["status"]["404"] ="Helaas! Deze boodschap kan niet gevonden worden!";
        exit(header("location: ./error.php"));
    }

    // indien opgevraagde id gelijk is aan -1 wordt een leeg formulier gecreëerd
    elseif (!$gro_data && $id > 0){
        $gro_data = [$id =>
                        [
                            "gro_name"=>"Nieuwe boodschap",
                            "gro_description"=>"Geef een beschrijving",
                            "gro_amount" => 0,
                            "gro_pric"=>0.00,
                            "gro_date"=> date("Y-m-d", strtotime("today")),
                            "gro_id" => $id,
                            "next_row_id" => $next_row_id+1,
                            "gro_per_id" => 6
                            ]
                        ];
    }

    // pagina opbouwen
    PrintHead();
    PrintNavbar();

    $articles = MergeViewWithData("article_list_item.html", $articles_data);
    $stores = MergeViewWithData("store_list_item.html", $stores_data);

    // verschillende rows aanmaken + 1 rij voor de knop nieuw toevoegen
    $add_row = file_get_contents("./templates/boodschap_detail_add_row.html");

    $rows = MergeViewWithData("boodschap_detail_row.html", $rows_data);
    $rows .= str_replace("@next_row_id@", $next_row_id, $add_row);

    // placeholder vervangen door de gegenereerde rows.
    $content = MergeViewWithData("boodschap_detail.html", $gro_data);
    $content = str_replace("@csrf@", GenerateCSRF(), $content);
    $content =  str_replace("@grocery_rows@", $rows, $content);
    $content = str_replace("@article_list@", $articles, $content);
    $content = str_replace("@store_list@", $stores, $content);
    $content = MergeErrorInfoPlaceholders($content, $errors, $info);
    $content = removeEmptyPlaceholders($content);

    echo $content;
    echo '<script src="./js/index.js"></script>';
