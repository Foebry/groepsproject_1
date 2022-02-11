<?php
require_once "autoload.php";


function createOptions($data, $selected_pos=0){
    $selection = "";
    foreach($data as $key => $row){
        $selected = $key == $selected_pos ? 'selected="selected"' : "";
        $value = $row["id"];
        $name = $row["name"];
        $selection .= "<option value='$value' $selected>$name</option>";
    }
    return $selection;
}

function MakeSelect( $fkey, $value, $sql )
{
    $select = "<select id=$fkey name=$fkey value=$value>";
    $select .= "<option value='0'></option>";

    $data = GetData($sql);

    foreach ( $data as $row )
    {
        if ( $row[0] == $value ) $selected = " selected ";
        else $selected = "";

        $select .= "<option $selected value=" . $row[0] . ">" . $row[1] . "</option>";
    }

    $select .= "</select>";

    return $select;
}
//function next() {
//    print "Next was clicked";
//}
//function previous() {
//    echo "Previous was clicked";
//}
function MakeCheckbox( )
{

}
