<?php
require_once "autoload.php";

// csrf-token valideren
validateCSRF();

// boodschapdetail in $_SESSION opslaan
if ($_POST["form"] == "boodschapdetail"){
    // lege rijen verwijderen
    foreach($_POST["data"] as $row_id => $data){
        if(count(array_unique($data)) == 1 && current($data) == ""){
            unset($_POST["data"][$row_id]);
            continue;
        }
        $_POST["data"][$row_id]["row_id"] = $row_id;
    }
    $gro_id = $_POST["gro_id"];
    $_SESSION["boodschappen"][$gro_id]["headers"][0] = $_POST["headers"];
    $_SESSION["boodschappen"][$gro_id]["data"] = $_POST["data"];

    // als de gebruiker wil navigeren naar een andere pagina.
    if ($_POST["action"] == "refer"){
        exit(header("location:".$_POST["refer"]));
    }
}

SaveFormData();

function SaveFormData()
{
    if ( $_SERVER['REQUEST_METHOD'] == "POST" )
    {
        //controle CSRF token
        if ( ! key_exists("csrf", $_POST)) die("Missing CSRF");
        if ( ! hash_equals( $_POST['csrf'], $_SESSION['lastest_csrf'] ) ) die("Problem with CSRF");

        $_SESSION['lastest_csrf'] = "";

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
