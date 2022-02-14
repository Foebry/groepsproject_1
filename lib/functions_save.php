<?php
    function setGroceryHeaders(int $gro_id, int $next_row_id) :array{
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["next_row_id"] = $next_row_id;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_id"] = $gro_id;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_per_id"] = 6;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_name"] = null;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_date"] = date("Y-m-d", strtotime("today"));
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_amount"] = 0;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_description"] = null;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_pric"] = 0;

        return $_SESSION["boodschappen"][$gro_id]["headers"];

    }

    /**
    * Opdat de gebruiker zijn aan te maken boodschap niet zou verliezen moest deze navigeren naar
    * de detail pagina van een bepaald product, wordt de boodschap gecached vanaf het moment dat
    * de gebruiker het boodschap_detail formulier inzendt.
    */
    function cacheGrocery(int $gro_id) :void{
        //overloop alle rijen van de ingezonde boodschap
        foreach($_POST["data"] as $row_id => $data){
            //is een rij helemaal leeg, verijder de rij volledig
            //geen nut om een lege rij te onthouden
            if (count(array_unique($data)) == 1 && current($data) == ""){
                unset($_POST["data"][$row_id]);
                continue;
            }
            $_POST["data"][$row_id]["row_id"] = $row_id;
            $_POST["data"][$row_id]["row_art_id"] = (int) $_POST["data"][$row_id]["row_art_id"];
            $_POST["data"][$row_id]["row_sto_id"] = (int) $_POST["data"][$row_id]["row_sto_id"];
            $_POST["data"][$row_id]["row_gro_id"] = (int) $gro_id;
            $_POST["data"][$row_id]["row_pric"] = (float) $_POST["data"][$row_id]["row_pric"];
        }
        //bevat het ingezonden formulier geen data of is na verwijderen van lege rijen de data
        // een lege array geworden (enkel lege rijen ingezonden).
        // keer terug, want dit wordt niet aanvaard.
        if(!key_exists("data", $_POST) || count($_POST["data"]) == 0){
            $_POST["data"] = [];
            $_SESSION["errors"]["data_error"] = "Uw boodschap kan mag niet leeg zijn. Gelieve een artikel in te vullen";
        }

        // cache doorgestuurde headers (info over de boodschap) en data (info over de verschillende rijen)
        $_SESSION["boodschappen"][$gro_id]["headers"][0] = $_POST["headers"];
        $_SESSION["boodschappen"][$gro_id]["data"] = $_POST["data"];
        $_SESSION["next_row_id"] = $_POST["headers"]["next_row_id"] + 1;
    }


    /**
    *
    */
    function deleteFromCache() :int{
        if ($_POST["form"] == "boodschapdetail"){
            // verwijder de row-record uit de $_SESSION cache
            $row_id = (int) explode("-", $_POST["action"])[1];
            unset($_SESSION["boodschappen"][$gro_id]["data"][$row_id]);

            return $row_id;
        }
    }
