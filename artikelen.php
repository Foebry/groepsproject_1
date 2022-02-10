<?php

    require_once "lib/autoload.php";

    $where = key_exists("name", $_GET) ? "where art_name like '%".$_GET["name"]."%'" : "";

    $artikelen_sql = "select art_id, art_name, art_code, art_img from article $where";

    $artikelen = getData($artikelen_sql);

    $content = printHead();
    $content .= printNavBar();

    $artikelen_list = file_get_contents("./templates/artikelen.html");
    $artikelen = MergeViewWithData("productcard.html", $artikelen);
    $content .= str_replace("@artikelen@", $artikelen, $artikelen_list);

    echo $content;
