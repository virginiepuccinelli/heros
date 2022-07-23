<?php
@session_start(); //toujours au debut pour message de session
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") {
    $url = "https";
} else {
    $url = "http";
}
$url .= "://";
$url .= $_SERVER['HTTP_HOST']; //vaut localhost en local

include "incloud/fonctions.php";
include "params.php";
//Fin de connexion au bout de 10mn sans activité (script)
$finConnexion = (isset($_GET["finConnexion"])) ? (int)trim($_GET["finConnexion"]) : -1;
if ($finConnexion == 1 && isset($_SESSION['connect']) && !empty($_SESSION['connect'])) {
    unset($_SESSION['connect']);
}
//Inclure les fichiers langues
if (isset($_SESSION['langue'])) {
    include "incloud/langue_" . $_SESSION['langue'] . ".php";
} else {
    $_SESSION['langue'] = "fr"; //Langue par defaut
    include "incloud/langue_" . $_SESSION['langue'] . ".php";
}

//si il existe un message de session tri et si il n'est pas vide , tri = message de session 
if (isset($_SESSION['tri']) && !empty($_SESSION['tri'])) {
    $tri = (isset($_POST['tri'])) ? (int)trim($_POST['tri']) : $_SESSION['tri'];
} else { //sinon tri =tri ou par defaut 1
    $tri = (isset($_POST['tri'])) ? (int)trim($_POST['tri']) : 1;
}
if ($tri != 1 && $tri != 2 && $tri != 3) {
    if (isset($_SESSION['tri']) && !empty($_SESSION['tri'])) {
        $tri = $_SESSION['tri'];
    } else {
        $tri = 1;
    }
}

$_SESSION['tri'] = $tri;
$ar_tri = ["", "Heros", "Age", "Sexe"];

//si il existe un message de session filtre et si il n'est pas vide , filtre = message de session 
if (isset($_SESSION['filtre']) && !empty($_SESSION['filtre'])) {
    $filtre = (isset($_POST['filtre'])) ? (int)trim($_POST['filtre']) : $_SESSION['filtre'];
} else {
    //sinon filtre =filtre ou par defaut 1
    $filtre = (isset($_POST['filtre'])) ? (int)trim($_POST['filtre']) : 1;
}
if ($filtre != 1 && $filtre != 2 && $filtre != 3) {
    if (isset($_SESSION['filtre']) && !empty($_SESSION['filtre'])) {
        $filtre = $_SESSION['filtre'];
    } else {
        $filtre = 1;
    }
}
$_SESSION['filtre'] = $filtre;
$ar_filtre = ["", "Tous", "f", "h"];
$ar_filtre2 = ["", "Tous", "Femmes", "Hommes"];


$langue = (isset($_REQUEST['langue'])) ? trim($_REQUEST['langue']) : $_SESSION['langue'];
$langue = ($langue != "fr" && $langue != "en") ? $_SESSION['langue'] : $langue;
$_SESSION['langue'] = $langue;
include "incloud/langue_" . $_SESSION['langue'] . ".php";

//Renomme les fichiers pdf et json avec la date et heure du jour
$_SESSION['pdf'] = "pdf/" . date("YmdHi") . ".pdf";
$_SESSION['json'] = "pdf/" . date("YmdHi") . ".json";

$coul = "red";
if (isset($_SESSION['filtre']) && !empty($_SESSION['filtre']) && $_SESSION['filtre'] == 1) {
    //selectionner tous les superheros et grouper les pouvoirs pour chacun en mettant une virgule pour separer, et trier en fonction de tri.
    $st_sql = "select s.*, GROUP_CONCAT(CONCAT(p.pouvoir)SEPARATOR ',') as super_pouvoir from superheros s LEFT JOIN pouvoirs p on s.id=p.idsh GROUP BY s.heros order by " . $ar_tri[$tri] . "";
    $ob_result = sql_pdo($st_sql); //variable qui stock le resultat de la requete- j'appelle la fonction avec (parametre sql, 2eme param si ca provient d'un formulaire)
} else {
    $st_sql = "select s.*, GROUP_CONCAT(CONCAT(p.pouvoir)SEPARATOR ',') as super_pouvoir from superheros s LEFT JOIN pouvoirs p on s.id=p.idsh where s.sexe='" . $ar_filtre[$filtre] . "' GROUP BY s.heros order by " . $ar_tri[$tri] . "";
    $ob_result = sql_pdo($st_sql); //variable qui stock le resultat de la requete- j'appelle la fonction avec (parametre sql, 2eme param si ca provient d'un formulaire)
}
if (is_array($ob_result)) //si ca a marché, je veux recuperer les données
{
    $ar_res = $ob_result[0]->fetchAll(PDO::FETCH_BOTH); //[0]n° de la ligne.Je recupere les résultats, BOTH(retourne un tableau), recupere les 2 resultats, lignes et colonnes.Contient la liste des super heros
    $ar_resF = $ob_result[0]; //permet le nombre de fiche correspondante, met fin a la connexion
    $n = $ar_resF->rowCount(); //le nbr d'element du array
    include "header.php";

    echo "<h1 class='titre'>" . $tr_lang[4] . " : " . $n . "</h1>\n<br />\n";

    $loupe = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>';
    //Bouton rechercher
    $rech = "<div>\n";
    $rech .= "<div class='pousse'></div>\n<div class='rech' >\n<input type='text' id='rech' />\n";
    $rech .= "<button  onclick='rechercher()'>" . $loupe . "</button>\n</div>\n<br />\n"; //onclick:appelle la fonction recherche()
    $rech .= "<div id='nombre' ></div></div>\n";

    $rech .= '<input type="hidden" id="trad83" value="' . $tr_lang[83] . '"/>';
    $rech .= '<input type="hidden" id="trad84" value="' . $tr_lang[84] . '"/>';
    $rech .= '<input type="hidden" id="trad85" value="' . $tr_lang[85] . '"/>';
    $rech .= '<input type="hidden" id="trad86" value="' . $tr_lang[86] . '"/>';
    $rech .= '<input type="hidden" id="trad87" value="' . $tr_lang[87] . '"/>';

    echo $rech;
    if (isset($_SESSION["message"])) {
        echo "<p class='msg' style='color:" . $_SESSION["coul"] . "'>\n " . $_SESSION["message"] . "</p>\n";
        $_SESSION["message"] = "";
        $_SESSION["coul"] = "";
    }
    //si la variable de session connect existe alors j'affiche le lien vers ajout.php
    if (isset($_SESSION["connect"])) {
        echo "<p >\n<a  class='ajouter' href='ajout.php'>" . $tr_lang[5] . "</a>\n</p>\n";
    }
    echo "<h3 id='err'></h3>\n";
    $liste = "<form name='form3' method='post' style='text-align:center;color:blue;font-size:1.5em'>\n";
    $liste .= "<p style='font-size:0.8em;color:rgb(40, 16, 105)'>\n" . $tr_lang[921] . "  \n<select  name='filtre' onchange=\"document.forms['form3'].submit()\">\n";
    //Deconnexion temps
    if (isset($_SESSION["connect"])) {
        echo "<input type='hidden' id='dureeConnect' value='" . $duree_connect . "'/>";
        echo "<input type='hidden' id='traducDeconnect' value='" . $tr_lang[103] . "'/>";
        echo "<input type='hidden' id='limitDeconnect' value='" . $limit_deconnect . "'/>";
    }
    for ($i = 1; $i < 4; $i++) {
        $select = ($i == $filtre) ? "selected" : "";
        $liste .= "<option value='" . $i . "' " . $select . " >" . $ar_filtre2[$i] . "</option>\n";
    }
    $liste .= "</select></p>\n";
    //Formulaire de tri
    $liste .= "<p style='font-size:0.8em;color:rgb(40, 16, 105)'>\n" . $tr_lang[6] . " : \n<select  name='tri' onchange=\"document.forms['form3'].submit()\">\n";
    for ($i = 1; $i < 4; $i++) {
        $select = ($i == $tri) ? "selected" : "";
        $liste .= "<option value='" . $i . "' " . $select . " >" . $ar_tri[$i] . "</option>\n";
    }
    $liste .= "</select></p>\n";
    $liste .= "</form>\n";
    echo "<p>" . $liste . "</p>\n";
    echo "<div id='liste'>\n";
    echo "<ul>\n";
    include 'fpdf184/fpdf.php';
    $pdf_liste = scandir("pdf"); //liste de tous les fichiers qu'il y a dans pdf
    //Permet de supprimer les fichiers qui ont été crées il y a plus d'une heure et de le remplacer par un nouveau.
    for ($i = 0; $i < count($pdf_liste); $i++) {
        if (strpos($pdf_liste[$i], "pdf", 0) || strpos($pdf_liste[$i], "json", 0)) {
            $pdf_unit = explode(".", $pdf_liste[$i]);
            $pdf_nom_fich = $pdf_unit[0];
            $date_fic = substr($pdf_nom_fich, 0, strlen($pdf_nom_fich) - 2); //-2 correspond aux minutes dans le nom du fichier, on ne supprime que si l'heure, le jour, le mois et l'année sont les mêmes
            if ($date_fic < date("YmdH")) {
                if (file_exists("pdf/" . $pdf_nom_fich . ".pdf")) {
                    unlink("pdf/" . $pdf_nom_fich . ".pdf"); //supprime le fichier pdf
                }
                if (file_exists("pdf/" . $pdf_nom_fich . ".json")) {
                    unlink("pdf/" . $pdf_nom_fich . ".json"); //Supprime le fichier json
                }
            }
        }
    }

    //Création du fichier pdf
    $pdf = new FPDF('p', 'mm', 'A4');

    $pdf->AddPage(); //Ajoute une nouvelle page 
    $pdf->SetSubject($tr_lang[96]); //Sujet du fichier pdf
    $pdf->SetAuthor("Virginie - Developpeuse Web"); //L'auteur
    $pdf->SetTitle("Web-Dev 2022"); //Titre
    //On affiche -Ecriture de la page
    $num_page = 1; //n° de la feuille
    $hauteur = 10;
    $pdf->SetTextColor(0, 0, 0); //Couleur du texte(0,0,0)(Rouge,Vert,Bleu)
    $pdf->SetFont('Helvetica', '', 12); //nom de la police, mise en forme(b,i,u) si on veut rien on met "",taille du caractère
    $pdf->SetXY(163, $hauteur); //place le pointeur au debut
    $pdf->Cell(30, 10, "Page " . $num_page, "", 0, "C"); //cell(larg, haut, "txt", bordure, pointeur, alignement)

    $hauteur = $hauteur + 25; //Hauteur entre le bord haut de la feuille et l'image +hauteur de l'image 10
    $pdf->SetXY(55, $hauteur); //Place le pointeur
    $pdf->SetFont('Helvetica', 'u', 30); //Police, mise en forme,taille
    $nom = $tr_lang[97];
    $pdf->setFillColor(0, 0, 0); //couleur de remplissage
    $pdf->Cell(95, 15, $nom, 0, 0, "C", false); // titre : La liste des supers Heros////cell(larg, haut, "txt", bordure, pointeur, alignement)
    $hauteur = 60; //50
    $pdf->SetFont('Helvetica', '', 12);
    //création du fichier json, chr(34) ajoute des guillemets
    $json = "{" . chr(34) . $tr_lang[98] . chr(34) . ":[";
    for ($i = 0; $i < $n; $i++) {
        $json .= "{";
        if ($ar_res[$i]['photo'] != null && file_exists("photos/" . $ar_res[$i]['photo'])) {
            $json .= chr(34) . $tr_lang[101] . chr(34) . ":" . chr(34) . $url . "/superheros/photos/" . $ar_res[$i]['photo'] . chr(34) . ",";
        }
        $json .= chr(34) . $tr_lang[76] . chr(34) . ":" . chr(34) . $ar_res[$i]['heros'] . chr(34) . ",";
        $json .= chr(34) . $tr_lang[77] . chr(34) . ":" . chr(34) . $ar_res[$i]['age'] . chr(34) . ",";
        $json .= chr(34) . $tr_lang[78] . chr(34) . ":" . chr(34) . $ar_res[$i]['sexe'] . chr(34) . ",";
        $json .= chr(34) . $tr_lang[99] . chr(34) . ":[";
        if ($ar_res[$i]['super_pouvoir']) {
            $pouvoir = explode(",", $ar_res[$i]['super_pouvoir']);
            $nbpouv = count($pouvoir);
            $jpouv = "";
            for ($k = 0; $k < $nbpouv; $k++) {
                $jpouv .= chr(34) . $pouvoir[$k] . chr(34) . ",";
            }
            $jpouv = (!empty($jpouv)) ? substr($jpouv, 0, strlen($jpouv) - 1) : "";
            $json .= $jpouv . "]";
        } else {
            $json .= "]";
        }
        $json_fin = "";
        $json_fin = ($i == $n - 1) ? "}" : "},"; //si dernier élément, on retire la virgule
        $json .= $json_fin;


        $oeil = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                </svg>';
        $coulsexe = "blue";
        $coulsexe = ($ar_res[$i]['sexe'] == "f") ? "#dc2a88" : $coulsexe;
        $ar_res[$i]['super_pouvoir'] = ($ar_res[$i]['super_pouvoir'] == NULL) ? $tr_lang[7] : $ar_res[$i]['super_pouvoir'];
        //Affichage de la liste des supers-héros
        echo "<div>\n<li class='heros' style='color:" . $coulsexe . "'>";
        echo "<span class='recherche'>\n<a  name='ancre" . $i . "' ></a>";
        echo "\n<a href='detail.php?id=" . $ar_res[$i]['id'] . "&langue=" . $_SESSION['langue'] . "'>\n" . $oeil . "</a>&nbsp"; //lien page detail
        echo "<b>" . $ar_res[$i]['heros'] . "</b>\n</span>";
        if ($ar_res[$i]['photo'] != null && file_exists("photos/" . $ar_res[$i]['photo'])) {
            $chemin = "photos/" . $ar_res[$i]['photo'];
            echo "<p class='index_photo' style='background-image: url($chemin)'></p>";
            $pdf->SetXY(24, $hauteur);
            $photo = "photos/" . $ar_res[$i]['photo'];
            $pdf->Image($photo, 24, $hauteur, 40, 30);
        } else {
            $pdf->SetXY(24, $hauteur);
            $pdf->Cell(40, 30, "", 1); //Si pas de photo cadre vide dans pdf
        }
        if ($ar_res[$i]['sexe'] == "f") {
            $pdf->SetTextColor(231, 78, 180);
        } else {
            $pdf->SetTextColor(90, 90, 204);
        }
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetXY(68, $hauteur + 5);
        //$pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(45, 10, $ar_res[$i]['heros'], "", 0, "");
        $pdf->SetXY(130, $hauteur + 5);
        $an = ($ar_res[$i]['age'] > 1) ? $tr_lang[8] : $tr_lang[100];
        $pdf->Cell(20, 10, $ar_res[$i]['age'] . " " . $an, "", 0, "");
        $pdf->SetXY(68, $hauteur + 15);
        $pdf->Cell(20, 10, $ar_res[$i]['super_pouvoir'], "", 0, "");
        $hauteur = $hauteur + 35;
        //Si la hauteur atteind 240, je rajoute une page
        if ($hauteur > 240) {
            $pdf->AddPage();
            $pdf->SetSubject($tr_lang[96]); //Sujet du fichier pdf
            $pdf->SetAuthor("Virginie - Developpeuse Web"); //L'auteur
            $pdf->SetTitle("Web-Dev 2022"); //Titre
            $num_page = $num_page + 1;
            $hauteur = 10; //10mm car j'ai deja configurer plus haut en mm
            $pdf->SetTextColor(0, 0, 0); //Couleur du texte(0,0,0)(Rouge,Vert,Bleu)
            $pdf->SetFont('Helvetica', '', 12); //nom de la police, mise en forme(b,i,u) si on veut rien on met "",taille du caractère
            $pdf->SetXY(163, $hauteur); //place le pointeur au debut
            $pdf->Cell(30, 10, "Page " . $num_page, "", 0, "C");

            $hauteur = 40; //50

        }
        $an = ($ar_res[$i]['age'] > 1) ? $tr_lang[8] : $tr_lang[100];
        echo "<p class='age'><br /> \n(" . $ar_res[$i]['age'] . " " . $an . ")</p>";
        echo "<p style='line-height:50px;font-size:0.8em'>" . $ar_res[$i]['super_pouvoir'] . "\n</p></li>";
        echo "<br /></div><hr  width=25% style='color:black;margin:auto'>\n";
    }

    $json .= "]}";
    $fjson = fopen($_SESSION['json'], "w");
    fputs($fjson, $json); //création du fichier json
    fclose($fjson);
    $pdf->Output("F", $_SESSION['pdf']); //Création du fichier pdf
    echo "</ul>\n";
    echo "</div>\n";
    $ar_resF->closeCursor(); //Arrete le processus de la requete!!
    unset($st_sql, $ob_result, $ar_res, $ar_resF, $n); //supprime les variables
} else {
    echo "<p>" . $tr_lang[9] . "</p>\n"; //en mode production
    //$ar = explode("|", $ob_result);
    //echo "sql :" . $ar[1]; //ne pas afficher en mode production!!!
}
include "footer.php";
