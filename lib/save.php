<?php
require_once "autoload.php";
// csrf-token valideren
validateCSRF();


if ($_POST["form"] == "boodschapdetail"){
    $gro_id = $_POST["headers"]["gro_id"];
    cacheGrocery($gro_id);
}

// Gebruiker wil navigeren naar een andere de detail pagina van een product.
if ($_POST["action"] == "refer"){
    exit(header("location:".$_POST["refer"]));
}

// Gebruiker wil een row-record verwijderen
if( strpos($_POST["action"], "delete") !== false){
    $table = $_POST["table"];
    $key = $_POST["key"];
    // Gebruiker wil boodschapdetail regel verwijderen
    $row_id = deleteFromCache();

    $id = $row_id ? $row_id : null;

    // verwijder record uit de databank
    $sql_delete = "delete from $table where $key = $id";
    ExecuteSQL($sql_delete);

    // zet een melding voor de gebruiker en keer terug naar het formulier
    $_SESSION["info"]["msg"] = $_POST["info-delete"];
    exit(header("location:".$_SERVER["HTTP_REFERER"]));
}

    // inzending van het formulier
    elseif ($_POST["action"] == "submit"){
        // valideer gegevens van de boodschap zelf
        $table = "grocery";
        $headers = getHeaders($table);
        $sql_statements = [];

        // Valideer de waarde van iedere key overeenkomend met de headers van de tabel
        // sla de primary en foreign keys over
        foreach($headers as $key => $values){
            $key_type = $_POST["DB_HEADERS"][$key]["key"];
            if (key_exists($key, $_POST["headers"]) AND ($key_type === "PRI")) continue;
            validate($key, $values, $_POST["headers"]);
        }
        if (count($_SESSION["errors"]) > 0){
            exit(header("location:".$_SERVER["HTTP_REFERER"]));
        }
        // bepalen of het een insert of update statment moet zijn.
        $gro_sql = "select * from grocery where gro_id = $gro_id";
        $gro_data = getData($gro_sql);
        $statement = $gro_data ? "update $table set " : "insert into $table set ";
        $where = $gro_data ? " where gro_id = $gro_id" : "";

        // sql statement aanmaken en uitvoeren
        $sql = buildStatement($statement, $table, $_POST["headers"]);
        $sql_statements[] = $sql.$where;


        // data van rijen valideren
        $table = $_POST["table"];
        $headers = getHeaders($table);

        foreach($_POST["data"] as $row => $data){
            $array = $_SESSION["boodschappen"][$gro_id]["data"][$row];

            foreach($headers as $key => $values){
                $key_type = $_POST["DB_HEADERS"][$key]["key"];

                if (key_exists($key, $data) AND ($key_type === "PRI" )) continue;

                $_SESSION["boodschappen"][$gro_id]["data"][$row] = validate($key, $values, $array);
            }

            // bepalen of het een insert of update statment moet zijn.
            $db_data = getData("select * from grocery where gro_id = $gro_id");
            $msg = $db_data ? $_POST["info-update"] : $_POST["info-insert"];
            $statement = $db_data ? "update $table set " : "insert into $table set ";
            $where = $sql_data ? " where row_id = $row" : "";

            $_SESSION["info"]["success"] = $msg;
            // sql statement aanmaken en uitvoeren
            $sql = buildStatement($statement, $table, $data);
            $sql_statements[] = $sql.$where;
        }

        if (count($_SESSION["errors"]) > 0){
            exit(header("location:".$_SERVER["HTTP_REFERER"]));
        }
        foreach($sql_statements as $sql){
            ExecuteSQL($sql);
        }

        // verwijder boodschap uit cache
        unset($_SESSION["boodschappen"][$gro_id]);

        // navigeer naar homepagina
        exit(header("location:".$_POST["refer"]));
    }

else{
    $sql_statements = [];
    $table = $_POST["table"];
    $headers = getHeaders($_POST["table"]);

    if (strpos($_POST["action"], "delete") !== false){
        $key = $_POST["key"];
        //var_dump(explode())
        $id = explode("delete-", $_POST["action"])[1];

        $sql_delete = "delete from $table where $key = $id";
        ExecuteSQL($sql_delete);

        $_SESSION["info"]["delete"] = $_POST["info-delete"];

        exit(header("location:".$_POST["refer"]));
    }
    elseif (strpos($_POST["action"], "add") !== false){
        // nagaan of deze combinatie nog niet in de nieuwe boodschap zit
        $result = checkGroceryForItemStoreCombination();


        if ($result){
            $_SESSION["info"]["add"] = "Deze winkel-artikel combinatie zit reeds in uw winkelmandje";
            exit(header("location:".$_SERVER["HTTP_REFERER"]));
        }

        $_SESSION["info"]["add"] = "Artikel aan winkelmand toegevoegd!";
        exit(header("location:".$_SERVER["HTTP_REFERER"]));

    }
    //$data = [0=>$headers];
    foreach($headers as $key => $values){
        $key_type = $headers[$key]["key"];
        if (!key_exists($key, $_POST)) continue;
        validate($key, $values, $_POST);
    }
    $statement = $_POST[$_POST["key"]] > 0 ? "update $table set " : "insert into $table set ";
    $where = $_POST[$_POST["key"]] > 0 ? " where ".$_POST["key"]." = ".$_POST[$_POST["key"]] : "";

    $sql = buildStatement($statement, $table);
    $sql_statements[] = $sql.$where;

    if (count($_SESSION["errors"]) > 0){
        exit(header("location:".$_SERVER["HTTP_REFERER"]));
    }

    foreach($sql_statements as $sql){
        ExecuteSQL($sql);
    }

    $_SESSION["info"]["success"] = $_POST[$_POST["key"]] > 0 ? $_POST["info-update"] : $_POST["info-insert"];

    //$_SESSION["info"]["success"] = $_POST["info-success"];
    exit(header("location:".$_POST["refer"]));
}



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
    $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["row_pric"] = $price;
    $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["row_id"] = $next_row_id;
    $_SESSION["boodschappen"][$next_gro_id]["data"][$next_row_id]["gro_id"] = $next_gro_id;
    $_SESSION["next_row_id"] += 1;

    return False;
}
