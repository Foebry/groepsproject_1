<?php
    function setGroceryHeaders(int $gro_id, int $next_row_id) :array{
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["next_row_id"] = $next_row_id;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_id"] = $gro_id;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_per_id"] = 6;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_name"] = "Nieuwe boodschap";
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_date"] = date("Y-m-d", strtotime("today"));
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_amount"] = 0;
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_description"] = "Geef een beschrijving";
        $_SESSION["boodschappen"][$gro_id]["headers"][0]["gro_pric"] = 0;

        return $_SESSION["boodschappen"][$gro_id]["headers"];

    }
