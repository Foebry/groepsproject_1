<?php
require_once "autoload.php";

function CreateConnection($db="LIVE"){
    $root = $_SERVER["DOCUMENT_ROOT"];
    // json file inlezen en omzetten naar associatieve array
    if ($root === 'C:/xl/htdocs') $root.= '/groepsproject_1';
    $file = file_get_contents("./config.json");
    $config = json_decode($file, true)["DATABASE"][$db];

    $servername = $config["host"];
    $dbname = $config["dbname"];
    $username = $config["username"];
    $password = $config["password"];

    // Create and check connection
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }
    catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}

function GetData( $sql ){
    // create connection
    $conn = CreateConnection();

    //define and execute query
    $result = $conn->query( $sql );

    // return all rows if data found else return an empty array
    return $result->rowCount() > 0 ? $result->fetchALL(PDO::FETCH_ASSOC) : [];

}

function ExecuteSQL( $sql ){
    // create connection
    $conn = CreateConnection( );

    //define and execute query
    $result = $conn->query( $sql );

    return $result;
}


/**
* functie die de tabelhoofdingen van de tabel opvraagt en teruggeeft.
* @param $table: tabel waarvoor de hoofdingen gevraagd wordt.
* @type $table: string
*
* @return: array(string => array(string => string|int))
*/
function getHeaders($table): array{
        $headers = [];
        // aanmaken connectie & query
        $conn = CreateConnection();
        $db = getData("select database()")[0]["database()"];
        $query = "select * from information_schema.columns where table_name = '$table' and table_schema = '$db'";

        // opvragen data ahv bovenstaande query
        $data = GetData($query);

        // voor iedere rij (gevevens van 1 kolom) nagaan en uithalen wat van belang is.
        foreach($data as $row){
            // belangrijke eigenschappen van de rij (gegevens van 1 kolom) zijn:
            // COLUMN_NAME - DATA_TYPE - COLUMN_KEY - CHARACTER_MAXIMUM_LENGTH - IS_NULLABLE
            $column = $row["COLUMN_NAME"];
            $column_datatype = $row["DATA_TYPE"];
            $column_key = $row["COLUMN_KEY"];
            $column_max_length = $row["CHARACTER_MAXIMUM_LENGTH"];
            $is_null = $row["IS_NULLABLE"];

            // nieuwe associatieve array aanmaken met nodige data. en toevoegen aan de $headers array
            $headers[$column] = [];
            $headers[$column]["datatype"] = $column_datatype;
            $headers[$column]["key"] = $column_key;
            $headers[$column]["max_size"] = $column_max_length;
            $headers[$column]["can_be_null"] = $is_null;

        }
        $_POST["DB_HEADERS"] = $headers;
        return $headers;
    }


    function buildStatement($statement, $table=null, $array=null){
        $table = $table ? $table : $_POST["table"];
        $array = $array ? $array : $_POST;
        $headers = $_POST["DB_HEADERS"];
        $sql_values = [];

        foreach($headers as $key => $values){
            $key_type = $headers[$key]["key"];

            // primary key overslaan.
            if($key_type == "PRI") continue;

            $value = $array[$key];

            // $values aanvullen met veld en doorgestuurde waarde
            $sql_values[] = "$key = '$value'";
        }

        $sql_values = implode(", ", $sql_values);
        return $statement .$sql_values;

    }
