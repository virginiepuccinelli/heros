<?php
//traduction du synopsis du film avec l'API deepl
function traduction($texteAtraduire, $destinationLangue = "fr")
{
    include "params.php";
    $new_texte = urlencode($texteAtraduire); //urlencode : Encode une chaîne en URL
    $st_api_deepLink .= $st_Api_Key_deep; //lien API suivi de la clé API

    //fil_get_content : Lie tout le fichier dans une chaîne
    $Json = file_get_contents($st_Api_url_deep . $st_api_deepLink . "&text=" . $new_texte . "&target_lang=" . $destinationLangue);
    $myTraduction = json_decode($Json, true); //json_decode : decode une chaine json


    return $myTraduction["translations"][0]["text"]; //retourne le texte à traduire
}
$action = (isset($_POST["action"])) ? $_POST["action"] : 0; //envoie de l'action dans script.js
if ($action == 77) {
    $texteAtraduire = (isset($_POST['textAtraduire'])) ? $_POST['textAtraduire'] : "";
    if (!empty($texteAtraduire)) {
        $traduction = traduction($texteAtraduire);
    } else {
        echo "0|";
        exit;
    }


    if (empty($traduction) || strtolower($traduction == "n/a")) {
        echo "0|";
        exit;
    } else {
        echo "1|" . $traduction;
        exit;
    }
}
