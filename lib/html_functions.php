<?php

if (!isset($_SESSION)) session_start();

function PrintHead()
{
    $head = file_get_contents("templates/head.html");
    $head .= file_get_contents("templates/header.html");
    return $head;
}

function PrintJumbo( $title = "", $subtitle = "" )
{
    $jumbo = file_get_contents("templates/jumbo.html");

    $jumbo = str_replace( "@jumbo_title@", $title, $jumbo );
    $jumbo = str_replace( "@jumbo_subtitle@", $subtitle, $jumbo );

    print $jumbo;
}

function PrintNavbar( )
{
    $next_gro_id = $_SESSION["next_gro_id"];

    $navbar = file_get_contents("templates/navbar.html");
    $navbar = str_replace("@next_gro_id@", $next_gro_id, $navbar);

    return $navbar;
}

function PrintFooter( )
{
    $footer = file_get_contents("templates/footer.html");

    print $footer;
}


function MergeViewWithData( $template, $data ){
    $return_template = "";

    foreach ( $data as $row )
    {
        $item = file_get_contents("templates/$template");
        foreach( array_keys($row) as $field )  //eerst "img_id", dan "img_title", ...
        {
            $item = str_replace( "@$field@", $row["$field"], $item );
        }
        $return_template .= $item;
    }

    return $return_template;
}

function MergeViewWithExtraElements( $template, $elements )
{
    foreach ( $elements as $key => $element )
    {
        $template = str_replace( "@$key@", $element, $template );
    }
    return $template;
}

function MergeViewWithErrors( $template, $errors )
{
    foreach ( $errors as $key => $error )
    {
        $template = str_replace( "@$key@", "<p style='color:red'>$error</p>", $template );
    }
    return $template;
}

function RemoveEmptyErrorTags( $template, $data )
{
    foreach ( $data as $row )
    {
        foreach( array_keys($row) as $field )  //eerst "img_id", dan "img_title", ...
        {
            $template = str_replace( "@$field" . "_error@", "", $template );
        }
    }

    return $template;
}


function getTagsFromTemplate(string $templatestr, int $offset=0){
    $placeholders = [];
    // @ duidt de start van een placeholder aan
    // zoek naar eerste positie waar een @ voorkomt.
    $offset = strpos($templatestr, "@", $offset);

    // zolang placeolders gevonden worden, voeg deze toe aan de placeholders array
    while ($offset){
        $start = $offset+1;
        $end = strpos($templatestr, "@", $start);

        //indien geen closing @ gevonden, zijn er geen verdere placeholders meer en eindigt de while loop
        if ($end == 0) break;
        //indien wel een gevonden, voeg deze toe aan de placeholders array
        $placeholders[] = substr($templatestr, $start, $end-$start);
        // zet offset gelijk aan de positie van de volgende opening @
        $offset = strpos($templatestr, "@", $end+1);
    }
    return $placeholders;

}

function removeEmptyPlaceholders(string $templatestr){
    $placeholders = getTagsFromTemplate($templatestr);

    foreach($placeholders as $placeholder){
         $templatestr = str_replace("@$placeholder@", "", $templatestr);
     }

    return $templatestr;
}


function mergeErrors(string $templatestr, array $errors){
    $messages = "";
    foreach($errors as $key => $value){
        $error_message = file_get_contents("./templates/error_message.html");
        $messages .= str_replace("@message@", $value, $error_message);
    }

    $templatestr = str_replace("@error@", $messages, $templatestr);
    return $templatestr;
}


function MergeStatusPlaceholders(string $templatestr, array $status){
    $messages = "";
    foreach($status as $key => $value){
        $status_message = file_get_contents("./templates/status_message.html");
        $messages .= str_replace("@message@", $value, $status_message);
    }

    $templatestr = str_replace("@status@", $messages, $templatestr);
    return $templatestr;
}


function mergeInfo(string $templatestr, array $info){
    $messages = "";
    foreach($info as $key => $value){
        $info_message = file_get_contents("./templates/info_message.html");
        $messages .= str_replace("@message@", $value, $info_message);
    }

    $templatestr = str_replace("@info@", $messages, $templatestr);
    return $templatestr;
}


function MergeErrorInfoPlaceholders(string $templatestr, array $errors, array $info){
    $templatestr = mergeErrors($templatestr, $errors);
    $templatestr = mergeInfo($templatestr, $info);

    return $templatestr;
}

/**
* functie om een random code te genereren van 8 characters
*/
function GenerateCode() :string{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    $code = "";
    for($i=0; $i<8; $i++){
        $position = rand(0, strlen($characters)-1);
        $code .= rand(0,100) > 50 ? $characters[$position] : strtoupper($characters[$position]);
    }
    return $code;
}
