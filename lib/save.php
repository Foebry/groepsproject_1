<?php
require_once "autoload.php";
// csrf-token valideren
validateCSRF();


if ($_POST["form"] == "boodschapdetail"){
    $gro_id = $_POST["headers"]["gro_id"];
    // Boodschap mag niet leeg zijn
    if (!key_exists("data", $_POST)){
        $_POST["data"] = [];
        $_SESSION["errors"]["data_error"] = "Uw boodschap kan helaas niet leeg zijn. Gelieve een artikel in te vullen";
    }

    // lege rijen verwijderen
    foreach($_POST["data"] as $row_id => $data){
        if(count(array_unique($data)) == 1 && current($data) == ""){
            unset($_POST["data"][$row_id]);
            continue;
        }
        $_POST["data"][$row_id]["row_id"] = $row_id;
        $_POST["data"][$row_id]["row_art_id"] = (int) $_POST["data"][$row_id]["row_art_id"];
        $_POST["data"][$row_id]["row_sto_id"] = (int) $_POST["data"][$row_id]["row_sto_id"];
        $_POST["data"][$row_id]["row_gro_id"] = (int) $gro_id;
        $_POST["data"][$row_id]["row_pric"] = (float) $_POST["data"][$row_id]["row_pric"];
    }

    // boodschapdetail in $_SESSION opslaan
    $_SESSION["boodschappen"][$gro_id]["headers"][0] = $_POST["headers"];
    $_SESSION["boodschappen"][$gro_id]["data"] = $_POST["data"];

    // indien de gebruiker wil navigeren naar een andere pagina.
    if ($_POST["action"] == "refer"){
        exit(header("location:".$_POST["refer"]));
    }
    //indien de gebruiker een row-record wil verwijderen
    elseif( strpos($_POST["action"], "delete") !== false){

        // verwijder de row-record uit de $_SESSION cache
        $row_id = (int) explode("-", $_POST["action"])[1];
        unset($_SESSION["boodschappen"][$gro_id]["data"][$row_id]);

        // verwijder de row-record uit de databank
        $sql_delete = "delete from row where row_id = $row_id";
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
            $sql = "select * from row where row_id = $row";
            $sql_data = getData($sql);
            $statement = $sql_data ? "update $table set " : "insert into $table set ";
            $where = $sql_data ? " where row_id = $row" : "";

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

        $_SESSION["info"]["success"] = $_POST["info-submit"];

        // navigeer naar homepagina
        exit(header("location:".$_POST["refer"]));
    }
}
else{
    $sql_statements = [];
    $table = $_POST["table"];
    $headers = getHeaders($_POST["table"]);
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

    exit(var_dump($sql_statements));

    if (count($_SESSION["errors"]) > 0){
        exit(header("location:".$_SERVER["HTTP_REFERER"]));
    }

    foreach($sql_statements as $sql){
        ExecuteSQL($sql);
    }

    $_SESSION["info"]["success"] = $_POST[$_POST["key"]] > 0 ? $_POST["info-update"] : $_POST["info-add"];

    //$_SESSION["info"]["success"] = $_POST["info-success"];
    exit(header("location:".$_POST["refer"]));
}
SaveFormData();

function SaveFormData()
{
    if ( $_SERVER['REQUEST_METHOD'] == "POST" )
    {
        //sanitization
        $_POST = StripSpaces($_POST);
        $_POST = ConvertSpecialChars($_POST);

        $table = $pkey = $update = $insert = $where = $str_keys_values = "";

        //get important metadata
        if ( ! key_exists("table", $_POST)) die("Missing table");
        if ( ! key_exists("pkey", $_POST)) die("Missing pkey");

        $table = $_POST['table'];
        $pkey = $_POST['pkey'];

        //validation
        $sending_form_uri = $_SERVER['HTTP_REFERER'];
        CompareWithDatabase( $table, $pkey );

        //terugkeren naar afzender als er een fout is
        if ( count($_SESSION['errors']) > 0 ) { header( "Location: " . $sending_form_uri ); exit(); }

        //insert or update?
        if ( $_POST["$pkey"] > 0 ) $update = true;
        else $insert = true;

        if ( $update ) $sql = "UPDATE $table SET ";
        if ( $insert ) $sql = "INSERT INTO $table SET ";

        //make key-value string part of SQL statement
        $keys_values = [];

        foreach ( $_POST as $field => $value )
        {
            //skip non-data fields
            if ( in_array( $field, [ 'table', 'pkey', 'afterinsert', 'afterupdate', 'csrf' ] ) ) continue;

            //hashing password
            if ($field == "user_password"){
                $value =  password_hash($value,PASSWORD_DEFAULT);
            }

            //handle primary key field
            if ( $field == $pkey )
            {
                if ( $update ) $where = " WHERE $pkey = $value ";
                continue;
            }

            //all other data-fields
            $keys_values[] = " $field = '$value' " ;
        }

        $str_keys_values = implode(" , ", $keys_values );

        //extend SQL with key-values
        $sql .= $str_keys_values;

        //extend SQL with WHERE
        $sql .= $where;

        //run SQL
        $result = ExecuteSQL( $sql );

        //output if not redirected
        print $sql ;
        print "<br>";
        print $result->rowCount() . " records affected";

        //redirect after insert or update
        if ( $insert AND $_POST["afterinsert"] > "" ) header("Location: ../" . $_POST["afterinsert"] );
        if ( $update AND $_POST["afterupdate"] > "" ) header("Location: ../" . $_POST["afterupdate"] );
    }
}
