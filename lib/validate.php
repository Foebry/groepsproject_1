<?php
require_once "autoload.php";

function CompareWithDatabase( $table, $pkey ): void
{
    $data = GetData( "SHOW FULL COLUMNS FROM $table" );

    //overloop alle in de databank gedefinieerde velden van de tabel
    foreach ( $data as $row )
    {
        //haal veldnaam en veldtype uit de databank
        $fieldname = $row['Field']; //bv. img_title
        $can_be_null = $row['Null']; //bv. NO / YES

        list( $type, $length, $precision ) = GetFieldType( $row['Type'] );

        //zit het veld in $_POST?
        if ( key_exists( $fieldname, $_POST) )
        {
            $sent_value = $_POST[$fieldname];

            //INTEGER type
            if ( in_array( $type, explode("," , "INTEGER,INT,SMALLINT,TINYINT,MEDIUMINT,BIGINT" ) ) )
            {
                //is de ingevulde waarde ook een int?
                if ( ! isInt($sent_value) ) //nee
                {
                    $msg = $sent_value . " moet een geheel getal zijn";
                    $_SESSION['errors'][ "$fieldname" . "_error" ] = $msg;
                }
                else //ja
                {
                    $_POST[$fieldname] = (int) $sent_value;
                }
            }

            //FLOAT/DOUBLE type
            if ( in_array( $type, explode("," , "FLOAT,DOUBLE" ) ) )
            {
                //is de ingevulde waarde ook numeriek?
                if ( ! is_numeric($sent_value) ) //nee
                {
                    $msg = $sent_value . " moet een getal zijn (eventueel met decimalen)";
                    $_SESSION['errors'][ "$fieldname" . "_error" ] = $msg;
                }
                else //ja
                {
                    $_POST[$fieldname] = (float) $sent_value;
                }
            }

            //STRING type
            if ( in_array( $type, explode("," , "VARCHAR,TEXT" ) ) )
            {
                //is de tekst niet te lang?
                if ( strlen($sent_value) > $length )
                {
                    $msg = "Dit veld kan maximum $length tekens bevatten";
                    $_SESSION['errors'][ "$fieldname" . "_error" ] = $msg;
                }
            }

            //DATE type
            if ( $type == "DATE" )
            {
                if ( ! isDate( $sent_value) )
                {
                    $msg = $sent_value . " is geen geldige datum";
                    $_SESSION['errors'][ "$fieldname" . "_error" ] = $msg;
                }
            }

            //other types ...
        }
    }
}

function isInt($value) {
    return is_numeric($value) && floatval(intval($value)) === floatval($value);
}

function isDate($date) {
    return date('Y-m-d', strtotime($date)) === $date;
}

function GetFieldType( $definition )
{
    $length = 0;
    $precision = 0;

    //zit er een haakje in de definitie?
    if ( strpos( $definition, "(" ) !== false )
    {
        $type_parts = explode(  "(", $definition );
        $type = $type_parts[0];
        $between_brackets = str_replace( ")", "", $type_parts[1] );

        //zit er een komma tussen de haakjes?
        if ( strpos( $between_brackets, "," ) !== false )
        {
            list( $length, $precision ) = explode( ",", $between_brackets);
        }
        else $length = (int) $between_brackets; //cast int type
    }
    //geen haakje
    else $type = $definition;

    $type = strtoupper( $type ); //bv. INTEGER

    return [ $type, $length, $precision ];
}

/**
* Validates a name field to make sure it only contains regular characters.
* If a character different from a-Z, é, ë, è, ç or à is detected in the name field,
* an error is set to the $field_error key.
* param $field: name of the field
*/
function validateName(string $field) :void{
    $name = $_POST[$field];

    if ((preg_match("/[^a-z, A-Z, é, ë, è, ç, à]/", $name) > 0 ) or $name == ""){
        $msg = "Sorry, maar dit is geen geldige naam.";
        $_SESSION["errors"][$field."_error"] = $msg;
    }
}

/**
* Validates an email field to make sure it is a correct email adress.
* If it's not a correct email adress, an error is set to the $field_error key.
* param $field: name of the field
*/
function validateUserEmail(string $field) :void{
    $email = $_POST[$field];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) or $email == ""){
        $msg = "Dit is geen geldig e-mailadres!";
        $_SESSION["errors"][$field."_error"] = "Geen geldig e-mailadres!";
    };
}

/**
* validates a csrf-token in $_POST.
* If not set or not correct, send user to status.php
*/
function validateCSRF() :void{

    if (!key_exists("csrf", $_POST) or !hash_equals($_POST["csrf"], $_SESSION["last_csrf"])){
        $_SESSION["status"]["csrf"] = "U bent niet gemachtigd om deze bewerking uit te voeren";
        exit(header("location:../status.php"));
    }
}
