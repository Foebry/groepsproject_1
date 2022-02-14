<?php
require_once "autoload.php";
// csrf-token valideren
validateCSRF();

// boodschap cachen om gebruiksvriendelijkheid te vergroten.
// als gebruiker boodschap aan het invullen is, en na bv 5 boodschapregels naar de detailpagina
// van een artikel navigeert, zal de boodschap blijven bestaan tot het moment dat de gebruiker deze inzendt.
if ($_POST["form"] == "boodschapdetail"){
    $gro_id = $_POST["headers"]["gro_id"];
    cacheGrocery($gro_id);
}

// Gebruiker wil een record verwijderen
if( strpos($_POST["action"], "delete") !== false){
    $table = $_POST["table"];
    $key = $_POST["key"];

    $id = explode("delete-", $_POST["action"])[1];
    deleteFromCache($id);

    // verwijder record uit de databank
    $sql_delete = "delete from $table where $key = $id";
    ExecuteSQL($sql_delete);

    // zet een melding voor de gebruiker en keer terug naar het formulier
    $_SESSION["info"]["msg"] = $_POST["info-delete"];
    exit(header("location:".$_SERVER["HTTP_REFERER"]));
}


// gebruiker zendt een formulier in
if ( strpos($_POST["action"], "submit") !== false){
    $sql_statements = [];

    if($_POST["form"] == "boodschapdetail"){
        $gro_id = $_POST["headers"]["gro_id"];
        // door FKC moet boodschap eerst bestaan vooraleer er boodschapregels aangemaakt kunnen worden.
        $sql_statements[] = validateGroceryHeaders($gro_id);

        // data boodschapregels valideren
        $table = $_POST["table"];

        validateGroceryRows($gro_id, $sql_statements, $table);

        //verwijder boodschap uit cache. Deze hebben we niet meer nodig.
        unset($_SESSION["boodschappen"][$gro_id]);

    }
    elseif($_POST["form"] == "artikeldetail" OR $_POST["form"] == "artikel_form"){

        // gebruiker voegt artikel aan boodschap toe vanaf de artikeldetail pagina
        if(strpos($_POST["action"], "-") !== false){
            $result = checkGroceryForItemStoreCombination();

            if ($result){
                $_SESSION["info"]["add"] = "Deze winkel-artikel combinatie is al aanwezig in uw winkelmandje";
                exit(header("location:".$_SERVER["HTTP_REFERER"]));
            }
            $_SESSION["info"]["add"] = "Artikel aan winkelmand toegevoegd!";
            exit(header("location:".$_SERVER["HTTP_REFERER"]));
        }

        if ($_POST["form"] == "artikeldetail"){
            validateArtikelWinkelData($sql_statements);
        }

        if ($_POST["form"] == "artikel_form"){

            validateArticleData($sql_statements);
        }
    }
    foreach($sql_statements as $sql){
        ExecuteSQL($sql);
    }
}

exit(header("location:".$_POST["refer"]));
