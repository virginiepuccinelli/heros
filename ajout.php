<?php
@session_start(); //toujours au debut pour message de session

include "incloud/fonctions.php";
include "params.php";

if (isset($_SESSION["connect"])) {
    //Si session connect existe, On recupere l'id en cours, $_SESSION['connect']= login_id
    $idencours = explode("_", $_SESSION["connect"])[1];

    $st_sql = "select idutilisateur from utilisateurs where idutilisateur=?";
    $ob_result = sql_pdo($st_sql, array($idencours));

    if (is_array($ob_result)) //si ça a marché, je veux recuperer les données
    {
        $ar_res = $ob_result[0];
        $ar_resF = $ob_result[0];
        $n = $ar_resF->rowCount(); //nb total de fiche des superheros 

        if ($n == 0) {
            unset($_SESSION['connect']);
            header("location: index.php");
            exit;
        } else {

            if (isset($_SESSION['langue'])) {
                include "incloud/langue_" . $_SESSION['langue'] . ".php";
            } else {
                $_SESSION['langue'] = "fr";
                include "incloud/langue_" . $_SESSION['langue'] . ".php";
            }
            $langue = (isset($_REQUEST['langue'])) ? trim($_REQUEST['langue']) : $_SESSION['langue'];
            $langue = ($langue != "fr" && ($langue != "en")) ? $_SESSION['langue'] : $langue;
            $_SESSION['langue'] = $langue;
            include "incloud/langue_" . $_SESSION['langue'] . ".php";






            $msg = "";
            $coul = "red";
            //On récupère l'action
            $action = (isset($_POST['action'])) ? (int)trim($_POST['action']) : 0;

            if ($action == 3) {
                $autorise = "azertyuiopqsdfghjklmwxcvbnéèàùAZERTYUIOPQSDFGHJKLMWXCVBN '";
                $autorise_age = "1234567890";
                $nom = (isset($_POST['nom'])) ? trim($_POST['nom']) : "";
                $age = (isset($_POST['age'])) ? trim($_POST['age']) : 0;

                $sexe = (isset($_POST['sexe'])) ? trim(strtolower($_POST['sexe'])) : "";
                $er = 0;

                $maxRubrique = maxRub("superheros", "heros");


                if (!ExistNom($nom)) {
                    $msg = $tr_lang[63] . " " . $nom . " " . $tr_lang[64];
                }
                if (strlen($nom) < 2 || strlen($nom) > $maxRubrique ||  !verifRubrique($nom, $autorise)) {
                    $er = 1;
                }


                if (!verifAge($age, $autorise_age) || $age < 1 || $age > 9999) {
                    $er += 2;
                }
                if ($sexe != "f" && $sexe != "h" || empty($sexe)) {
                    $er += 4;
                }
                if ($er == 0) {
                    $st_sql = "insert into superheros (heros, age, sexe) values (:heros, :age, :sexe)";
                    $ob_result = sql_pdo($st_sql, array(":heros" => $nom, ":age" => $age, ":sexe" => $sexe));

                    if (is_array($ob_result)) //si ça a marché, je veux recuperer les données
                    {
                        $ar_res = $ob_result[0];
                        $ar_resF = $ob_result[0];
                        $n = $ar_resF->rowCount(); //nb total de fiche des superheros 


                        if ($n == 0) {
                            $msg = $tr_lang[60];
                        } else {
                            $coul = "green";
                            $msg = $tr_lang[61] . " " . $nom . " " . $tr_lang[62];
                        }
                    } else {

                        if (strtolower(strpos($ob_result, "Duplicate entry"))) {
                            $msg = $tr_lang[63] . " " . $nom . " " . $tr_lang[64];
                        }
                        if (strtolower(strpos($ob_result, "Data too long"))) {
                            $msg = $tr_lang[65];
                        }
                        if (strtolower(strpos($ob_result, "Out of range"))) {
                            $msg = $tr_lang[66];
                        }
                    }
                }
                if ($er != 0) {
                    if ($er == 1) {
                        $msg = $tr_lang[67];
                    }
                    if ($er == 2) {
                        $msg = $tr_lang[68];
                    }
                    if ($er == 3) {
                        $msg = $tr_lang[69];
                    }
                    if ($er == 4) {
                        $msg = $tr_lang[70];
                    }
                    if ($er == 5) {
                        $msg = $tr_lang[71];
                    }
                    if ($er == 6) {
                        $msg = $tr_lang[72];
                    }
                    if ($er == 7) {
                        $msg = $tr_lang[73];
                    }
                }
            }


            include "header.php";


            $contenu = "<div id='ajoutheros'>\n<h1 class='ajout'>" . $tr_lang[75] . "</h1><br /><br />\n";
            $contenu .= "<h1 style='color:" . $coul . "' class='erreur'>" . $msg . "</h1>\n";
            $contenu .= "<form name='form2' method='post' class='form2' >\n";
            $contenu .= "<input type='hidden' name='action' value='3'/>\n";
            $contenu .= "<p>" . $tr_lang[76] . " : <input type='text' name='nom' value='' size='20'/></p>\n";
            $contenu .= "<p>" . $tr_lang[77] . " : <input type='text' name='age' size='4'/></p>\n";
            $contenu .= "<p>" . $tr_lang[78] . " : <select name='sexe'><option value='f'>" . $tr_lang[79] . "</option><option value='h'>" . $tr_lang[80] . "</option></select></p>\n";
            $btnajout = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                </svg>';
            $contenu .= "<p>\n<button class='bouton' type='submit' value='Ajouter' title='" . $tr_lang[81] . "' onclick=\"if(document.forms['form2'].nom.value=='\"\"'){return false}\">" . $btnajout . "</button></p><br />\n";
            $contenu .= "</form>\n";
            $contenu .= "<p  class='lienajout'><a href='index.php' >" . $tr_lang[82] . "</a>\n</p>\n</div><br />\n";

            echo $contenu;
            include "footer.php";
        }
    } else {
        unset($_SESSION['connect']);
        header("location: index.php");
        exit;
    }
} else {
    header("location: index.php");
    exit;
}
