<?php

    /**
    * Aanmaken van een nieuwe boodschap;
    */
    function setGroceryHeaders(int $gro_id, int $next_row_id) :array{
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["next_row_id"] = $next_row_id;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_id"] = $gro_id;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_per_id"] = 6;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_name"] = null;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_date"] = date("Y-m-d", strtotime("today"));
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_amount"] = 0;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_description"] = null;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_pric"] = 0;

        return $_SESSION["boodschappen"][$gro_id]["headers"];

    }

    /**
    * Opdat de gebruiker zijn aan te maken boodschap niet zou verliezen moest deze navigeren naar
    * de detail pagina van een bepaald product, wordt de boodschap gecached vanaf het moment dat
    * de gebruiker het boodschap_detail formulier inzendt.
    */
    function cacheGrocery(int $gro_id) :void{
        //overloop alle rijen van de ingezonde boodschap
        foreach($_POST["data"] as $row_id => $data){
            //is een rij helemaal leeg, verijder de rij volledig
            //geen nut om een lege rij te onthouden
            if (count(array_unique($data)) == 1 && current($data) == ""){
                unset($_POST["data"][$row_id]);
                continue;
            }
            $_POST["data"][$row_id]["row_id"] = $row_id;
            $_POST["data"][$row_id]["row_art_id"] = (int) $_POST["data"][$row_id]["row_art_id"];
            $_POST["data"][$row_id]["row_sto_id"] = (int) $_POST["data"][$row_id]["row_sto_id"];
            $_POST["data"][$row_id]["row_gro_id"] = (int) $gro_id;
            $_POST["data"][$row_id]["pri_value"] = (float) $_POST["data"][$row_id]["pri_value"];
        }
        //bevat het ingezonden formulier geen data of is na verwijderen van lege rijen de data
        // een lege array geworden (enkel lege rijen ingezonden).
        // keer terug, want dit wordt niet aanvaard.
        if(!key_exists("data", $_POST) || count($_POST["data"]) == 0){
            $_POST["data"] = [];
            $_SESSION["errors"]["data_error"] = "Uw boodschap kan mag niet leeg zijn. Gelieve een artikel in te vullen";
        }

        // cache doorgestuurde headers (info over de boodschap) en data (info over de verschillende rijen)
        $_SESSION["boodschappen"][$gro_id]["headers"][0] = $_POST["headers"];
        $_SESSION["boodschappen"][$gro_id]["data"] = $_POST["data"];
        $_SESSION["next_row_id"] = $_POST["headers"]["next_row_id"] + 1;
    }


    /**
    * Wanneer de gebruiker een boodschapregel verwijdert, verwijder eerst deze regel uit de ceched boodschap.
    */
    function deleteFromCache(int $row_id) :void{
        if ($_POST["form"] == "boodschapdetail"){
            // verwijder de row-record uit de $_SESSION cache
            $gro_id = $_POST["headers"]["gro_id"];
            unset($_SESSION["boodschappen"][$gro_id]["data"][$row_id]);
        }
    }


    /**
    * Door de foreign key constraint moet de boodschap eerst bestaan vooraleer er boodschapregels aan
    * de boodschap toegevoegd kunnen worden.
    * Valideer dan eerst de boodschapgegevens en update / insert deze.
    */
    function validateGroceryHeaders(int $gro_id) :string{
        $table = "grocery";
        $headers = getHeaders($table);
        validateData($headers);
        // boodcshap bestaan al? maak update statement anders insert statement
        $gro_sql = "select * from grocery where gro_id = $gro_id";
        $gro_data = getData($gro_sql);
        $statement = $gro_data ? "update $table set " : "insert into $table set ";
        $where = $gro_data ? " where gro_id = $gro_id" : "";

        // sql statement aanmaken
        $sql = buildStatement($statement, $table, $_POST["headers"]);

        return $sql.$where;
    }


    /**
    * Na valideren van boodschapheaders valideer nu ook iedere boodschapregel en maak de verschillende
    * sql-queries aan.
    */
    function validateGroceryRows(int $gro_id, array &$sql_statements, $table){
        $headers = getHeaders($table);
        foreach($_POST["data"] as $row => $data){
            $array = $_SESSION["boodschappen"][$gro_id]["data"][$row];

            foreach($headers as $key => $values){
                $key_type = $_POST["DB_HEADERS"][$key]["key"];

                if(key_exists($key, $data) AND ($key_type === "PRI")) continue;

                $_SESSION["boodschappen"][$gro_id]["data"][$row] = validate($key, $values, $array);
            }

            // boodschapdetail regel bestaat reeds? update anders insert
            $db_data = getData("select * from grocery where gro_id = $gro_id");
            $msg = $db_data ? $_POST["info-update"] : $_POST["info-insert"];
            $statement = $db_data ? "update $table set " : "insert into $table set ";
            $where = $db_data ? " where row_id = $row" : "";

            $_SESSION["info"]["success"] = $msg;

            // sql statement aanmaken voor boodschapdetail regel
            $sql = buildStatement($statement, $table, $data);
            $sql_statements[] = $sql.$where;
        }
    }


    /**
    * Gebruiker wil een nieuw artikel toevoegen.
    * Valideer ingestuurde data en maak sql statement aan.
    */
    function validateArticleData(&$sql_statements){
        $table = $_POST["table"];
        $headers = getHeaders($table);

        validateData($headers);

        $sql = "select pri_id from $table where pri_sto_id = $sto_id and pri_art_id = $art_id";
        $in_db = getData($sql);
        $statement = $in_db ? "update $table set " : "insert into $table set ";
        $where = $in_db ? " where pri_id = $pri_id" : "";
        $_SESSION["info_success"] = $in_db ? $_POST["info-update"] : $_POST["info-insert"];

        $sql = buildStatement($statement, $table);
        $sql_statements[] = $sql.$where;
    }


    /**
    * Gebruiker zit op artikeldetail pagina en wil een nieuwe winkelprijs toevoegen of een bestaande
    * winkelprijs aanpassen.
    * valideer dan de doorgestuurde gegevens en maak het sql-statement aan.
    */
    function validateArtikelWinkelData(&$sql_statements){
        $table = $_POST["table"];
        $headers = getHeaders($table);

        validateData($headers);

        $sto_id = $_POST["pri_sto_id"];
        $art_id = $_POST["pri_art_id"];

        $sql = "select pri_id from $table where pri_sto_id = $sto_id and pri_art_id = $art_id";
        $in_db = getData($sql);
        $pri_id = $in_db[0]["pri_id"];

        $statement = $in_db ? "update $table set " : "insert into $table set ";
        $where = $in_db ? " where pri_id = $pri_id" : "";
        $_SESSION["info_success"] = $in_db ? $_POST["info-update"] : $_POST["info-insert"];

        $sql = buildStatement($statement, $table);
        $sql_statements[] = $sql.$where;
    }


    /**
    * Doorgestuurde data valideren.
    */
    function validateData(array $headers){
        foreach($headers as $key => $values){
            $key_type = $_POST["DB_HEADERS"][$key]["key"];
            if (key_exists($key, $_POST["headers"]) OR ($key_type === "PRI")) continue;
            validate($key, $values, $_POST["headers"]);
        }
        // foutieve data gevonden? keer terug naar formulier.
        if (count($_SESSION["errors"]) > 0){
            exit(header("location:".$_SERVER["HTTP_REFERER"]));
        }
    }



    /**
    * Gebruiker zit op artikeldetail pagina en wil het artikel uit een bepaalde winkel toevoegen
    * aan zijn nieuwe boodschap.
    * Eerst nagaan of deze nog niet aanwezig is in de nieuwe boodschap.
    */
    function checkGroceryForItemStoreCombination(){
        $elements = explode("-", $_POST["action"]);
        $art_id = $elements[1];
        $sto_id = $elements[2];
        $art_name = $elements[3];
        $sto_name = $elements[4];
        $price = $elements[5];

        $next_gro_id = $_SESSION["next_gro_id"];
        $next_row_id = $_SESSION["next_row_id"];

        foreach($_SESSION["boodschappen"] as $gro_id => $gro_data){
            if (!key_exists("headers", $gro_data)){
                setGroceryHeaders($gro_id, $next_row_id);
            }
            foreach($gro_data["data"] as $row_id => $row_data){
                if ($row_data["row_art_id"] == $art_id && $row_data["row_sto_id"] == $sto_id){

                    return True;
                }
            }
        }

        // data zetten voor een nieuwe rij
        $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id] = [];
        $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["row_art_id"] = $art_id;
        $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["row_sto_id"] = $sto_id;
        $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["art_name"] = $art_name;
        $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["sto_name"] = $sto_name;
        $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["pri_value"] = $price;
        $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["row_id"] = $next_row_id;
        $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["gro_id"] = $next_gro_id;
        $_SESSION["next_row_id"] += 1;

        return False;
    }
