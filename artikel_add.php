<?php

    require_once "lib/autoload.php";

    $csrf = GenerateCSRF();
    $code = GenerateCode();
    $next_art_id = $_SESSION["next_art_id"];
    $unit_data_sql = "select uni_id id, uni_name name from units";
    $unit_data = getData($unit_data_sql);

    $unit_options = createOptions($unit_data, 2);

    $content = printHead();
    $content .= printNavbar();
    $content .= file_get_contents("./templates/artikel_form.html");
    $content = str_replace("@next_art_id@", $next_art_id, $content);
    $content = str_replace("@csrf@", $csrf, $content);
    $content = str_replace("@art_code@", $code, $content);
    $content = str_replace("@unit_options@", $unit_options, $content);

    $content = MergeErrorInfoPlaceholders($content, $errors, $info);
    $content = removeEmptyPlaceholders($content);

    echo $content;
    PrintFooter();
