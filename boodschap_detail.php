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

    $next_row_id_sql = "select row_id + 1 as next_row_id from row order by row_id desc limit 1";

    // opvragen van de data
    $gro_data = GetData($gro_sql);
    $rows_data = GetData($rows_sql);
    $next_row_id = GetData($next_row_id_sql);

    // indien een boodschap opgrvraagd wordt waarvan de id niet bestaat, wordt de gebruiker herleid
    // naar error.php met volgende status message
    if (!$gro_data && $id > 0){
        $_SESSION["status"]["404"] ="Helaas! Deze boodschap kan niet gevonden worden!";
        exit(header("location: ./error.php"));
    }

    // indien opgevraagde id gelijk is aan 0 wordt een leeg formulier gecreëerd
    elseif ($id == -1){
        $gro_data = [0 =>
                        [
                            "gro_name"=>"Geef een naam",
                            "gro_desc"=>"Geef een beschrijving",
                            "gro_amount" => 0,
                            "gro_price"=>0.00,
                            "gro_date"=> date("Y-m-d", strtotime("today"))
                            ]
                        ];
    }

    // pagina opbouwen
    PrintHead("MyWeGo");
    PrintNavbar();

    $content = MergeViewWithData("boodschap_detail.html", $gro_data);

    // verschillende rows aanmaken + 1 rij voor de knop nieuw toevoegen
    $rows = MergeViewWithData("boodschap_detail_row.html", $rows_data);
    $rows .= MergeViewWithData("boodschap_detail_add_row.html", $next_row_id);

    // placeholder vervangen door de gegenereerde rows.
    $content =  str_replace("@grocery_rows@", $rows, $content);

    echo $content;
    echo '<script src="../js/index.js"></script>';
