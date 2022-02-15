<?php
    require_once "autoload.php";

    const MIN_Q_LENGTH = 150;
    const MAX_Q_LENGTH = 500;

    // controleren van csrf-token.
    validateCSRF();

    // controleren van emailadres.
    validateUserEmail("per_email");

    // controleren van naam en voornaam.
    validateName("per_firstname");
    validateName("per_lastname");

    // controleren van lengte textarea
    $message = $_POST["question"];
    if (strlen($message) == 0){
        $msg = "Dit veld mag niet leeg zijn. Gelieve een bericht tussen ".MIN_Q_LENGTH." en ".MAX_Q_LENGTH." karakters in te geven.";
        $_SESSION["errors"]["question_error"] = $msg;
    }
    elseif (strlen($message) < MIN_Q_LENGTH){
        $msg = "Uw bericht is te kort. De minimum lengte is ". MIN_Q_LENGTH ." karakters.\n Uw bericht is slechts ".strlen($_POST["question"])." karakters lang";
        $_SESSION["errors"]["question_error"] = $msg;
    }
    elseif (strlen($message) > MAX_Q_LENGTH){
        $msg = "Uw bericht is te lang. De maximum lengte is ".MAX_Q_LENGTH." karakters.\n Uw bericht is ".strlen($_POST["question"])." karakters lang";
        $_SESSION["errors"]["question_error"] = $msg;
    }

    // indien errors aangetroffen in de contactform, keer terug naar contactform
    if (count($_SESSION["errors"]) > 0){
        exit(header("location:".$_SERVER["HTTP_REFERER"]));
    }

    // verzend email naar "ons" email-adres.
    //$to = "abc@gmail.com";
    //$subject = "contact";
    //$message = $_POST["question"];
    //$headers = "From: ".$_POST["per_email"];
    //exit(mail($to,$subject,$message,$headers));

    // verzend email naar gebruiker als confirmatie
    //$to = $_POST[$per_email];
    //$subject = "contact";
    //$message = $_POST["question"];
    //$headers = "From: ".$_POST["per_email"];
    //mail($to, $subject, $message, $headers);

    // keer terug naar index.
    $_SESSION["info"]["mail"] = "Bedankt om ons te contacteren!\nUw bericht werd goed ontvangen.";
    exit(header("location:".$_POST["next"]));
