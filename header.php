<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Super Héros</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/style.css" type="text/css" />
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css" />
    <link rel="shortcup icon" href="fav.ico" />
    <script src="js/jquery1_12.js"></script>
</head>

<body>
    <div class='contain'>
        <?php

        include "incloud/langue_" . $_SESSION['langue'] . ".php";
        include "params.php";

        $fermer = '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
         <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
       </svg>';
        $ouvert = '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor" class="bi bi-unlock" viewBox="0 0 16 16">
         <path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2zM3 8a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H3z"/>
       </svg>';
        //si la variable de session connect existe, alors cadenas fermé et renvoie vers connexion.php
        if (isset($_SESSION["connect"])) {
            $connection = "<div class='btnouvert2'></div>\n<div class='btnouvert'>\n<a href='connexion.php' title='" . $tr_lang[2] . "' >" . $ouvert . "</a>\n</div>\n<br />\n";
        } else {
            $connection = "<div class='btnferme2'></div>\n<div class='btnferme'> <a href='connexion.php' title='" . $tr_lang[3] . "' >" . $fermer . "</a>\n</div>\n<br />\n";
        }
        echo $connection;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") {
            $url = "https";
        } else {
            $url = "http";
        }
        $url .= "://";
        $url .= $_SERVER['HTTP_HOST']; //vaut localhost en local           
        $x = $url . $_SERVER['REQUEST_URI'];
        $pageencours = $url . $_SERVER['PHP_SELF'];

        //$x vaut http://localhost/superheros/index.php?langue=en
        //$pageencours vaut http://localhost/superheros/detail.php


        if ((strpos($x, "langue=", 1)) && (strpos($x, "id=", 1))) {
            $y = explode("&", $x);
            $x = $y[0] . "&langue=";
            if ($_SESSION['langue'] == "fr") {
                $btnlang = "<a href='" . $x . "en'><img class='imgen' src='incloud/en.png' width=40 height=30/></a>\n";
            } else {
                $btnlang = "<a href='" . $x . "fr'><img class='imgfr' src='incloud/fr.png' width=40/></a>\n";
            }
        }
        if ((strpos($x, "langue=", 1) == false) && (strpos($x, "id=", 1))) {
            if ($_SESSION['langue'] == "fr") {
                $btnlang = "<a href='" . $pageencours . "&langue=en'><img class='imgen' src='incloud/en.png' width=40 height=30/></a>\n";
            } else {
                $btnlang = "<a href='" . $pageencours . "&langue=fr'><img class='imgfr' src='incloud/fr.png' width=40/></a>\n";
            }
        }
        if ((strpos($x, "langue=", 1)) && (strpos($x, "id=", 1) == false)) {
            if ($_SESSION['langue'] == "fr") {
                $btnlang = "<a href='" . $pageencours . "?langue=en'><img class='imgen' src='incloud/en.png' width=40 height=30/></a>\n";
            } else {
                $btnlang = "<a href='" . $pageencours . "?langue=fr'><img class='imgfr' src='incloud/fr.png' width=40/></a>\n";
            }
        }
        if ((strpos($x, "langue=", 1) == false) && (strpos($x, "id=", 1) == false)) {
            if ($_SESSION['langue'] == "fr") {
                $btnlang = "<a href='" . $pageencours . "?langue=en'><img class='imgen' src='incloud/en.png' width=40 height=30/></a>\n";
            } else {
                $btnlang = "<a href='" . $pageencours . "?langue=fr'><img class='imgfr' src='incloud/fr.png' width=40/></a>\n";
            }
        }
        echo "<div class='langues'>" . $btnlang . "</div>";
        echo "<div class='date' style='margin-left:3%;color:blue'><b>&nbsp" . date("d/m/Y") . "</b></div>";
        $btpdf = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-filetype-pdf" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5ZM1.6 11.85H0v3.999h.791v-1.342h.803c.287 0 .531-.057.732-.173.203-.117.358-.275.463-.474a1.42 1.42 0 0 0 .161-.677c0-.25-.053-.476-.158-.677a1.176 1.176 0 0 0-.46-.477c-.2-.12-.443-.179-.732-.179Zm.545 1.333a.795.795 0 0 1-.085.38.574.574 0 0 1-.238.241.794.794 0 0 1-.375.082H.788V12.48h.66c.218 0 .389.06.512.181.123.122.185.296.185.522Zm1.217-1.333v3.999h1.46c.401 0 .734-.08.998-.237a1.45 1.45 0 0 0 .595-.689c.13-.3.196-.662.196-1.084 0-.42-.065-.778-.196-1.075a1.426 1.426 0 0 0-.589-.68c-.264-.156-.599-.234-1.005-.234H3.362Zm.791.645h.563c.248 0 .45.05.609.152a.89.89 0 0 1 .354.454c.079.201.118.452.118.753a2.3 2.3 0 0 1-.068.592 1.14 1.14 0 0 1-.196.422.8.8 0 0 1-.334.252 1.298 1.298 0 0 1-.483.082h-.563v-2.707Zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638H7.896Z"/>
                 </svg>';
        $btjson = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-filetype-json" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M14 4.5V11h-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5ZM4.151 15.29a1.176 1.176 0 0 1-.111-.449h.764a.578.578 0 0 0 .255.384c.07.049.154.087.25.114.095.028.201.041.319.041.164 0 .301-.023.413-.07a.559.559 0 0 0 .255-.193.507.507 0 0 0 .084-.29.387.387 0 0 0-.152-.326c-.101-.08-.256-.144-.463-.193l-.618-.143a1.72 1.72 0 0 1-.539-.214 1.001 1.001 0 0 1-.352-.367 1.068 1.068 0 0 1-.123-.524c0-.244.064-.457.19-.639.128-.181.304-.322.528-.422.225-.1.484-.149.777-.149.304 0 .564.05.779.152.217.102.384.239.5.41.12.17.186.359.2.566h-.75a.56.56 0 0 0-.12-.258.624.624 0 0 0-.246-.181.923.923 0 0 0-.37-.068c-.216 0-.387.05-.512.152a.472.472 0 0 0-.185.384c0 .121.048.22.144.3a.97.97 0 0 0 .404.175l.621.143c.217.05.406.12.566.211a1 1 0 0 1 .375.358c.09.148.135.335.135.56 0 .247-.063.466-.188.656a1.216 1.216 0 0 1-.539.439c-.234.105-.52.158-.858.158-.254 0-.476-.03-.665-.09a1.404 1.404 0 0 1-.478-.252 1.13 1.13 0 0 1-.29-.375Zm-3.104-.033a1.32 1.32 0 0 1-.082-.466h.764a.576.576 0 0 0 .074.27.499.499 0 0 0 .454.246c.19 0 .33-.055.422-.164.091-.11.137-.265.137-.466v-2.745h.791v2.725c0 .44-.119.774-.357 1.005-.237.23-.565.345-.985.345a1.59 1.59 0 0 1-.568-.094 1.145 1.145 0 0 1-.407-.266 1.14 1.14 0 0 1-.243-.39Zm9.091-1.585v.522c0 .256-.039.47-.117.641a.862.862 0 0 1-.322.387.877.877 0 0 1-.47.126.883.883 0 0 1-.47-.126.87.87 0 0 1-.32-.387 1.55 1.55 0 0 1-.117-.641v-.522c0-.258.039-.471.117-.641a.87.87 0 0 1 .32-.387.868.868 0 0 1 .47-.129c.177 0 .333.043.47.129a.862.862 0 0 1 .322.387c.078.17.117.383.117.641Zm.803.519v-.513c0-.377-.069-.701-.205-.973a1.46 1.46 0 0 0-.59-.63c-.253-.146-.559-.22-.916-.22-.356 0-.662.074-.92.22a1.441 1.441 0 0 0-.589.628c-.137.271-.205.596-.205.975v.513c0 .375.068.699.205.973.137.271.333.48.589.626.258.145.564.217.92.217.357 0 .663-.072.917-.217.256-.146.452-.355.589-.626.136-.274.205-.598.205-.973Zm1.29-.935v2.675h-.746v-3.999h.662l1.752 2.66h.032v-2.66h.75v4h-.656l-1.761-2.676h-.032Z"/>
                </svg>';

        $fichier = "<br />";
        $fichier .= "<p class='pdf'><a href=\"" . $_SESSION['pdf'] . "\" target='_blank'>" . $btpdf . "</a><a href=\"" . $_SESSION['json'] . "\" target='_blank'>" . $btjson . "</a></p>";
        if ($pageencours == $url . "/superheros/index.php") {
            echo $fichier;
        }
        echo $fichier;
        //Affichage du temps avant deconnexion en cours
        $deconnect = "<div id='text_connect' class='text_connect'></div>";
        echo $deconnect;
        ?>