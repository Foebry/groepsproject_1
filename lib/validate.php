<?php
require_once "autoload.php";

function validate($field, $values, &$array=null){
    $not_null = $_POST["DB_HEADERS"][$field]["can_be_null"] == "NO";
    $unique = $_POST["DB_HEADERS"][$field]["key"] == "UNI";
    $array = $array ? $array : $_POST;

    $fields = [
        "gro_date" => "De datum van de boodschap",
        "gro_desc" => "De beschrijving van de boodschap",
        "gro_name" => "De naam van de boodschap",
        "art_name" => "De naam van het artikel",
        "sto_name" => "De naam van de winkel",
        "row_pieces" => "Het aantal voor dit artikel",
        "row_pric" => "De prijs voor dit artikel",
        "pri_value" => "De prijs",
        "row_art_id" => "Gelieve een artikel uit de lijst te selecteren.",
        "row_sto_id" => "Gelieve een winkel uit de lijst te selecteren.",
        "pri_sto_id" => "De naam van de winkel",
    ];
    // indien de doorgegeven waarde van field leeg is, zet ze gelijk aan "null"
    $array[$field] = $array[$field] == "" ? "null" : $array[$field];

    // indien de ingevoerde waarde leeg is, ga na of dit veld in de databank leeg mag zijn,
    // zoniet, zet de correcte error message en return;
    if ($not_null and $array[$field] == "null"){
        $_SESSION["errors"][$field."_error"] = "$fields[$field] mag niet leeg zijn.";
        if ($field == "row_art_id" || $field == "row_sto_id") $_SESSION["errors"][$field."_error"] = $fields[$field];
        $array["$field--error"] = "col--error";
        return $array;
    }
    # indien het veld uniek is in de databank, ga na of deze waarde nog niet bestaat.
    # indien wel het geval, zet de correcte error message en return.
    if ($unique){
        if (getData("select $field from ".$array["table"]." where $field = "."'".$array[$field]."'")){
            $_SESSION["errors"][$field."_error"] = "$fields[$field] is al in gebruik.";
            $array["$field--error"] = "col--error";
            return $array;
        }
    }
    $value = $array[$field];

    if ($values["datatype"] == "int") $array = validateInteger($value, $field, $fields, $array);
    elseif ($values["datatype"] == "varchar") $array = validateString($value, $field, $fields, $array);
    elseif ($values["datatype"] == "double") $array = validateFloat($value, $field, $fields, $array);
    elseif ($values["datatype"] == "date") $array = validateDate($value, $field, $fields, $array);

    return $array;
}


function validateInteger($value, string $field, array $fields, array $array){
    if (!is_numeric($value)) {
        $msg = key_exists($field, $fields) ? "$fields[$field] is een numeriek veld en mag enkel numerieke waarden bevatten." : "";

        if ($field == "row_art_id") $msg = "Gelieve een artikel uit de lijst te selecteren.";
        elseif ($field == "row_sto_id") $msg = "Gelieve een winkel uit de lijst te selecteren";

        $array["$field--error"] = "col--error";

        $_SESSION["errors"][$field."_error"] = $msg;

    }

    return $array;
}


function validateString($value, string $field, array $fields, array $array){
    $value = htmlentities(trim($value), ENT_QUOTES);

    $unique = $_POST["DB_HEADERS"][$field]["can_be_null"] == "NO";
    $unique = $_POST["DB_HEADERS"][$field]["key"] == "UNI";
    $max_length = $_POST["DB_HEADERS"][$field]["max_size"];
    $min_length = key_exists($field."_min", $array) ? $array[$field."_min"] : 0;

    $strlen = strlen($value);

    // indien de lengte van de ingevoerde waarde langer is dan de toegelaten lengte,
    // of net te kort, zet de correcte error messages voor de respectievelijke fouten.
    if(strlen($value) < $min_length){
        $_SESSION["errors"][$field."_error"] = "$fields[$field] moet minstens $min_length tekens bevatten";
        $array["$field--error"] = "col--error";
    }
    elseif (strlen($value) > $max_length) {
       $_SESSION["errors"][$field."_error"] = "$fields[$field] is $strlen lang, maar mag maximaal $max_length lang zijn.";
       $array["$field--error"] = "col--error";
   }
   return $array;
}


function validateFloat($value, string $field, array $fields, array $array){
    if ( ! is_numeric($value) OR $value != (float) $value){
        $_SESSION["errors"][$field."_error"] = "$fields[$field] moet een getal zijn, eventueel met decimalen";
        $array["$field--error"] = "col--error";
    }
    if (key_exists($field, $_POST)) $_POST[$field] = (float) $value;
    //$array[$field] = (float) $value;
    return $array;
}


function validateDate($date, string $field, array $fields, array $array){
    if (date('Y-m-d', strtotime($date)) !== $date){
        $_SESSION["errors"][$field."_error"] = "$fields[$field] is een datum veld gelieve een formaat yyyy-mm-dd te gebruiken";
        $array["$field--error"] = "col--error";
    }
    return $array;
}

/**
* Validates a name field to make sure it only contains regular characters.
* If a character different from a-Z, é, ë, è, ç or à is detected in the name field,
* an error is set to the $field_error key.
* param $field: name of the field
*/
function validateName(string $field) :void{
    $name = trim($_POST[$field]);

    if ((preg_match("/[^a-z, A-Z, é, ë, è, ç, à]/", $name) > 0 ) or $name == ""){
        $msg = "Sorry, maar dit is geen geldige naam.";
        $_SESSION["errors"][$field."_error"] = $msg;
    }
    $_POST[$field] = $name;
}

/**
* Validates an email field to make sure it is a correct email adress.
* If it's not a correct email adress, an error is set to the $field_error key.
* param $field: name of the field
*/
function validateUserEmail(string $field) :void{
    $email = trim($_POST[$field]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) or $email == ""){
        $msg = "Dit is geen geldig e-mailadres!";
        $_SESSION["errors"][$field."_error"] = "Geen geldig e-mailadres!";
    }
    $_POST[$field] = $email;
}

/**
* validates a csrf-token in $_POST.
* If not set or not correct, send user to status.php
*/
function validateCSRF() :void{
    if (!key_exists("csrf", $_POST) or !hash_equals($_POST["csrf"], $_SESSION["latest_csrf"])){
        $_SESSION["status"]["401"] = "U bent niet gemachtigd om deze bewerking uit te voeren";
        exit(header("location:../status.php"));
    }
    $_SESSION['latest_csrf'] = "";
}
