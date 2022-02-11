<?php

    require_once "lib/autoload.php";

    $csrf = GenerateCSRF();
    $code = GenerateCode();

    $content = printHead();
    $content .= printNavbar();
    $content .= file_get_contents("./templates/artikel_form.html");
    $content = str_replace("@csrf@", $csrf, $content);
    $content = str_replace("@art_code@", $code, $content);

    echo $content;
    PrintFooter();
