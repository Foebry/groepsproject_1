<?php
function PrintHead()
{
    $head = file_get_contents("templates/head.html");
    $head .= file_get_contents("templates/header.html");
    print $head;
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
    $navbar = file_get_contents("templates/navbar.html");

    print $navbar;
}

function MergeViewWithData( $template, $data ){
    $return_template = "";

    foreach ( $data as $row )
    {
        $item = file_get_contents("./templates/$template");
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
