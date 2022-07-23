<?php
@session_start(); //toujours au debut pour message de session

include "incloud/fonctions.php";
if (isset($_SESSION['langue'])) {
    include "incloud/langue_" . $_SESSION['langue'] . ".php";
} else {
    $_SESSION['langue'] = "fr";
    include "incloud/langue_" . $_SESSION['langue'] . ".php";
}
include "params.php";
$langue = (isset($_REQUEST['langue'])) ? trim($_REQUEST['langue']) : $_SESSION['langue'];
$langue = ($langue != "fr" && $langue != "en") ? $_SESSION['langue'] : $langue;
$_SESSION['langue'] = $langue;
include "incloud/langue_" . $_SESSION['langue'] . ".php";

$bcoul = ["black", "black", "black"];
$coul = "red";
$msg = "";
$id = (isset($_REQUEST['id'])) ? (int)trim($_REQUEST['id']) : 0;
$idp = (isset($_POST['idpouvoir'])) ? (int)trim($_POST['idpouvoir']) : 0;
$power = (isset($_POST['pouvoir'])) ? trim($_POST['pouvoir']) : "";
$nom = (isset($_REQUEST['nom'])) ? trim($_REQUEST['nom']) : "";

if ($id > 0) {
    //Selectionne tous sur la table des superheros, (en concatenant les idpouvoir et les pouvoirs avec |, et en separant ces groupes par une virgule) en joignant 2 tables qui ont une rubrique commune, groupé par superheros pour eviter les répétitions
    $st_sql = "select s.*, GROUP_CONCAT(CONCAT(p.idpouvoir,'|',p.pouvoir)SEPARATOR ',') as super_pouvoir from superheros s LEFT JOIN pouvoirs p on s.id=p.idsh WHERE s.id = ? GROUP BY s.heros ";
    $ob_result = sql_pdo($st_sql, array($id)); //array($id,$..,$..) si ? dans la requete, on peut en mettre plusieurs($  ,$   ,$   )

    if (is_array($ob_result)) //si ca a marché, je veux recuperer les données
    {
        $ar_res = $ob_result[0]->fetchAll(PDO::FETCH_BOTH);
        $ar_resF = $ob_result[0];
        $n = $ar_resF->rowCount(); //nb total de fiche des superheros
        if ($n == 0) {
            header("location: index.php");
            exit;
        } else {
            $action = (isset($_POST['action'])) ? (int)trim($_POST['action']) : 0;
            ///Si un pirate essai de tricher il ne pourra rien faire
            if (isset($_SESSION["connect"])) {
                if ($action >= 1 && $action <= 5) {
                    if ($action == 1) //Supprimer une fiche heros avec son ou ses pouvoirs
                    {
                        $sup = suppression($id, "superheros"); //Appelle de la fonction suppression
                        if ($sup == 1) {
                            $msg = $tr_lang[35];
                        } else {
                            //session + redirect index + nom du héros supprimé
                            $nom = (isset($_REQUEST['nom'])) ? trim($_REQUEST['nom']) : "";

                            $_SESSION["message"] = $tr_lang[36] . " " . $nom . " " . $tr_lang[37] . ".";
                            $_SESSION["coul"] = "green";
                            header("location: index.php");
                            exit;
                        }
                    }
                    if ($action == 2) //Modifier le nom du heros, l'age, le sexe et/ou la photo
                    {
                        $autorise = "azertyuiopqsdfghjklmwxcvbnéèàùAZERTYUIOPQSDFGHJKLMWXCVBN '"; //Caracteres autorisés
                        $autorise_age = "1234567890";
                        $nom = (isset($_REQUEST['nom'])) ? trim($_REQUEST['nom']) : "";
                        $age = (isset($_REQUEST['age'])) ? trim($_REQUEST['age']) : 0;
                        $sexe = (isset($_REQUEST['sexe'])) ? trim(strtolower($_REQUEST['sexe'])) : "";
                        $sexe = ($sexe != "f" && $sexe != "h") ? "" : $sexe;
                        $photo = (isset($_FILES['photosh']) && $_FILES['photosh']['error'] == 0) ? $_FILES['photosh'] : "";

                        $num = $ar_res[0]['num'];
                        $nouveauNom = $ar_res[0]['photo'];
                        //$ajout dans script.js suvant ce qu'on a fait, bouton input dans detail qui envoie la valeur ajour
                        $ajout = (isset($_POST['ajout'])) ? (int)trim($_POST['ajout']) : 0;

                        $ajout = ($ajout != 0 && $ajout != 1 && $ajout != 2) ? 0 : $ajout;
                        //Envoie des parametres à la fonction modifier
                        $modif = modifier($id, "superheros", $nom, $age, $sexe, $autorise, $photo, $num, $ajout, $nouveauNom, $autorise_age);
                        $coul = $modif[1];
                        $msg = $modif[0];
                        $bcoul = $modif[2];
                    }
                    if ($action == 3) //Supprimer un pouvoir
                    {
                        $idp = (isset($_POST['idpouvoir'])) ? (int)trim($_POST['idpouvoir']) : 0;
                        $power = (isset($_POST['pouvoir_' . $idp])) ? trim($_POST['pouvoir_' . $idp]) : "";

                        //Envoie des parametres à la fonction suppression
                        $sup = suppression($idp, "pouvoirs");
                        if ($sup == 1) {
                            $msg = "<p style='text-align:center'>" . $tr_lang[38] . "</p>";
                        } else {
                            //session + redirect index + nom du héros supprimé
                            $msg = "<p style='text-align:center'>" . $tr_lang[39] . " " . $power . " " . $tr_lang[40] . "</p>";
                            $coul = "green";
                        }
                    }

                    if ($action == 4) //Modification d'un pouvoir
                    {
                        $autorise = "azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN -éàùè'";
                        $idp = (isset($_POST['idpouvoir'])) ? (int)trim($_POST['idpouvoir']) : 0;
                        $power = (isset($_POST['pouvoir_' . $idp])) ? trim($_POST['pouvoir_' . $idp]) : "";

                        //Envoie des parametres à la fonction modifier
                        $modif = modifier($idp, "pouvoirs", $power, 0, "", $autorise, "", "", "", "", "");
                        $coul = $modif[1];
                        $msg = $modif[0];
                        $bcoul = $modif[2];
                    }
                    if ($action == 5) //Ajouter un pouvoir
                    {
                        $autorise = "azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBNéàè -'";
                        $er = 0;
                        $idp = (isset($_POST['idpouvoir'])) ? (int)trim($_POST['idpouvoir']) : 0;
                        $power = (isset($_POST['pouvoir'])) ? trim($_POST['pouvoir']) : "";

                        if (strlen($power) < 2 || !verifRubrique($power, $autorise)) {
                            $msg = "<p style='text-align:center'>" . $tr_lang[41] . "</p>";
                        } else {
                            $st_sql = "insert into pouvoirs (idpouvoir, idsh, pouvoir) values (:idpouvoir, :idsh, :pouvoir)";
                            $ob_result = sql_pdo($st_sql, array(":idpouvoir" => $idp, ":idsh" => $id, ":pouvoir" => $power));

                            if (is_array($ob_result)) //si ça a marché, je veux recuperer les données
                            {
                                $ar_res = $ob_result[0];
                                $ar_resF = $ob_result[0];
                                $n = $ar_resF->rowCount(); //nb total de fiche des superheros 

                                if ($n == 0) {
                                    $msg = $tr_lang[42];
                                } else {
                                    $coul = "green";
                                    $msg = "<p style='text-align:center'>" . $tr_lang[43] . " " . $power . " " . $tr_lang[44] . "</p>";
                                }
                            } else {
                                if (strtolower(strpos($ob_result, "Duplicate entry"))) {
                                    $msg = "<p style='text-align:center'>" . $tr_lang[45] . " " . $power . " " . $tr_lang[46] . "</p>";
                                }
                                if (strtolower(strpos($ob_result, "Data too long"))) {
                                    $msg = "<p style='text-align:center'>" . $tr_lang[47] . "</p>";
                                }
                            }
                        }
                    }
                } else {
                    $msg = "";
                }
            } else {

                $msg = "";
            }
        }
    } else {
        header("location: index.php");
        exit;
    }
} else {
    header("location: index.php");
    exit;
}
//Selectionne tous sur la table des superheros, et l'idpouvoir et le pouvoir sur la table pouvoirs (en concatenant avec |, et en separant ces groupes par une virgule) en joignant 2 tables qui ont une rubrique commune, groupé par superheros pour eviter les répétitions
//format de $ar_res[0]['super_pouvoir']  => id|pouvoir  ex 158|vole
$st_sql = "select s.*, GROUP_CONCAT(CONCAT(p.idpouvoir,'|',p.pouvoir)SEPARATOR ',') as super_pouvoir from superheros s LEFT JOIN pouvoirs p on s.id=p.idsh WHERE s.id = ? GROUP BY s.heros ";
$ob_result = sql_pdo($st_sql, array($id)); //array() si ? dans la requete, on peut en mettre plusieurs($  ,$   ,$   )

if (is_array($ob_result)) //si ca a marché, je veux recuperer les données
{
    $ar_res = $ob_result[0]->fetchAll(PDO::FETCH_BOTH);
    $ar_resF = $ob_result[0];
    $n = $ar_resF->rowCount(); //nb total de fiche des superheros
    include "header.php";

    $esexe = "<select name='sexe' >";
    $ar_sexe = ["Femme", "Homme"];
    $n2 = count($ar_sexe);

    for ($i = 0; $i < $n2; $i++) {
        $premiere = strtolower(substr($ar_sexe[$i], 0, 1));
        $sel = ($ar_res[0]['sexe'] == $premiere) ? "selected" : "";
        $esexe .= "<option class='sexe' style='border:1px solid " . $bcoul[2] . "' value='" . $premiere . "' " . $sel . ">" . $ar_sexe[$i] . "</option>";
    }
    $esexe .= "</select>";
    $contenu = "<h1 class='titredetail'>" . $tr_lang[353] . "</h1>\n";
    $contenu .=  "<h1 style='color:" . $coul . "'>" . $msg . "</h1>";
    $contenu .= "<div class='principal'>\n";
    if (isset($_SESSION["connect"])) {
        $contenu .=  "<form method='post' name='form1' class='f1' enctype='multipart/form-data'>\n";
        $contenu .=  "<input type='hidden' name='ajout' id='ajout' value='0' />\n";
    }
    $contenu .= "<div id='blocphoto'>\n";
    $sup_photo = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
        <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
        </svg>';
    if (!empty($ar_res[0]['photo']) && $ar_res[0]['photo'] != null && file_exists("photos/" . $ar_res[0]['photo'])) { //file_exists pour savoir si le fichier (ou dossier) existe
        $chemin = "photos/" . $ar_res[0]['photo'];
        $contenu .= "<div id='blocimage' ><div id='imageheros' style='background-image:url(" . $chemin . ")'></div>\n";
        $infos_image = @getimagesize($chemin); //getimagesize : retourne la taille de l'image, retourne un tableau
        $larg = $infos_image[0];
        $haut = $infos_image[1];
        $ext = pathinfo($ar_res[0]['photo'], PATHINFO_EXTENSION); // retourne l'extension
        $poids = filesize($chemin) / 1024; //taille du fichier en octets // 1024, donne le poids en Ko
        $poids = number_format($poids, 2); //2 chiffres après la virgule
        $num = $ar_res[0]['num'];
        $contenu .= "<div id='detailphoto'>L : " . $larg . " - H : " . $haut . " - " . $ext . " - " . $poids . "Ko</div>\n"; //Affiche les détails en dessous de l'image
        $contenu .= "</div>";

        if (isset($_SESSION["connect"])) {

            $contenu .= "<div id='boutonphoto'>";
            $contenu .= "<input type='file' name='photosh' id='photosh' accept='.jpg,.png,.jpeg,.gif' />\n\n";
            $contenu .= "<button type='button' value='supprimer' id='supimg' name='supimg' >" . $sup_photo . "</button></div></div>\n";
        }
    } else {
        $contenu .= "<div id='blocimage' ><div id='imageheros' ></div></div>";
        if (isset($_SESSION['connect'])) {
            $contenu .= "<div id='boutonphoto'>";
            $contenu .= "<input type='file' name='photosh' id='photosh' accept='.jpg,.png,.jpeg,.gif'/>";
            $contenu .= "<button type='button' value='supprimer' id='supimg' name='supimg' style='display:none' >" . $sup_photo . "</button></div>\n";

            $contenu .= "<div id='msgerr'>\n</div></div>\n";
        }
    }
    if (isset($_SESSION["connect"])) {
        $contenu .= "<input type='hidden' id='poidmax' value='" . $poidMax . "'/>";
        $contenu .= "<input type='hidden' id='texterr1' value=\"" . $tr_lang[351] . "\"/>"; //Fichier trop gros
        $contenu .= "<input type='hidden' id='texterr2' value=\"" . $tr_lang[352] . "\"/>"; //Format de fichier non autorisé
        $contenu .= "<img id='waitImg' src='' style='display:none'/>\n"; //Rempli dans script  imageIsLoaded avec e.target.result(fichier téléchargé, l'image)
        $contenu .=  "<input type='hidden' name='id' value='" . $id . "' />\n";
        $contenu .=  "<input type='hidden' name='idpouvoir' />\n";
        $contenu .=  "<input type='hidden' name='action' />\n";


        //confirm (demande la confirmation avant suppression)
        $contenu .= "\n<input type='submit' id='boutonsup' value='&#10060' title='" . $tr_lang[49] . "' onclick=\"if(!confirm('" . $tr_lang[50] . "')){return false}else{document.forms['form1'].action.value='1';document.forms['form1'].id.value='" . $id . "'}\" />\n";
        $btnmod = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                    <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                    </svg>';
        $contenu .=  "<button  type='submit'  id='boutonmodif' value='Modifier' title='" . $tr_lang[51] . "' onclick=\"document.forms['form1'].action.value='2';document.forms['form1'].id.value='" . $id . "'\" >" . $btnmod . "</button>\n</div>\n<br /><br />\n";
    } else {
        $contenu .= "</div><br />";
    }
    $contenu .=  "<p class='para1'>" . $tr_lang[52] . " : <input type='text' class='detail' size='15' name='nom' value='" . $ar_res[0]['heros'] . "' style='border-radius:20px;border:1px solid " . $bcoul[0] . ";font-size:0.7em;text-align:center;padding:1%'/>\n</p>\n";
    $contenu .= "<p class='para1' >" . $tr_lang[53] . " : <input type='text' class='detail2' name='age' size='3' value='" . $ar_res[0]["age"] . "' style='border-radius:20px;border:1px solid " . $bcoul[1] . ";font-size:0.7em;text-align:right;padding:1%'/>\n</p>\n";
    $contenu .= "<p class='para2'>" . $tr_lang[54] . " : " . $esexe . "</p>\n";
    $contenu .=  "<p class='pouvoir'>" . $tr_lang[55] . " :</p> \n";

    $listep = "<div>\n";

    if ($ar_res[0]['super_pouvoir']) {
        $pouvoir = explode(",", $ar_res[0]['super_pouvoir']); //transforme en tableau
        $nb = count($pouvoir);
        for ($i = 0; $i < $nb; $i++) {
            $idp = explode('|', $pouvoir[$i])[0]; //transforme en tableau
            $power = explode('|', $pouvoir[$i])[1]; //transforme en tableau
            $btnmod = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                </svg>';
            $btnsup = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                </svg>';
            $listep .= "<div class='pouv'>\n";
            if (isset($_SESSION["connect"])) {
                $listep .= "<button type='submit' title='" . $tr_lang[56] . "'  value='Supprimer' onclick=\"document.forms['form1'].action.value='3';document.forms['form1'].id.value='" . $id . "';document.forms['form1'].idpouvoir.value='" . $idp . "'\">" . $btnsup . "</button>\n";
            }
            $listep .= "<input type='text'  name='pouvoir_" . $idp . "' value='" . $power . "'>\n";
            if (isset($_SESSION["connect"])) {
                $listep .= "<button type='submit' title='" . $tr_lang[57] . "' value='Modifier' onclick=\"document.forms['form1'].action.value='4';document.forms['form1'].idpouvoir.value='" . $idp . "'\">" . $btnmod . "</button>\n</div>\n<br />\n";
            }
        }
    }

    $btnajout = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>';

    if (isset($_SESSION["connect"])) {
        $listep .= "<div class='pouv'>\n&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp\n<input type='text' name='pouvoir' value='' size='10'/>\n";
        $listep .= "<button type='submit' title='" . $tr_lang[58] . "' value='ajouter' onclick=\"document.forms['form1'].action.value='5'\">" . $btnajout . "</button>\n</div>\n";
    }
    $listep .= "</div>\n";
    $contenu .= $listep;
    //file_get_content permet de lire un fichier dans une chaine 
    $Json = file_get_contents($urlOmdb . "?s=" . $ar_res[0]['heros'] . "&type=movie&apikey=" . $deepapikeyfilm);
    $myheros = json_decode($Json, true); //json_decode  transforme un array json en array php.
    if (!empty($myheros['Search'])) {
        if (is_array($myheros['Search'])  && count($myheros['Search']) > 0) {
            $contenu .= "<div id='bloc_film'><div id='affiche'><div id='titre_film'><b>" . $myheros['Search'][0]['Title'] . "</b></div>";
            $contenu .= "<div id='affiche_film'><img src='" . $myheros['Search'][0]['Poster'] . "'/></div><div id='annee_sortie'>" . $myheros['Search'][0]['Year'] . "</div></div>";
            $imdbID = strtolower($myheros['Search'][0]['imdbID']);
            if ($imdbID != "" && $imdbID != "n/a") {
                $Json2 = file_get_contents($urlOmdb . "?i=" . $imdbID . "&plot=full&type=movie&apikey=" . $deepapikeyfilm);
                $myheros2 = json_decode($Json2, true);
                //Afficher le synopsis du film 
                if ($myheros2['Plot'] != "" && strtolower($myheros2['Plot']) != "n/a") //plot c'est la description du film
                {
                    $contenu .= "<div id='description'>" . $myheros2['Plot'] . "</div>";
                    $contenu .= "<div id='descriptioncache' style='display:none'>" . $myheros2['Plot'] . "</div>";
                    $contenu .= "<div id='traducdescript'><input type='button' affiche='0' imdbID='" . $imdbID . "' value='traduction' id='trad' width='10'/></div>";

                    $contenu .= "<div id='errtraduc'></div>";
                }
            }
        }
    }
    $contenu .=  "</div><p  class='lien'>\n<a href='index.php'>" . $tr_lang[59] . "</a>\n</p></ br>\n";
    if (isset($_SESSION["connect"])) {
        $contenu .=  "</form>\n";
    }
    $contenu .=  "</div>";

    echo  $contenu;
} else {
    header("location: index.php"); //renvoie a la page index
    exit;
}
include "footer.php";
