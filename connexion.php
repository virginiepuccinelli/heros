<?php
@session_start(); //toujours au debut pour message de session
// Connexion et deconnexion
include "incloud/fonctions.php";
if (isset($_SESSION['langue'])) {
	include "incloud/langue_" . $_SESSION['langue'] . ".php";
} else {

	$_SESSION['langue'] = "fr";
	include "incloud/langue_" . $_SESSION['langue'] . ".php";
}

$langue = (isset($_REQUEST['langue'])) ? trim($_REQUEST['langue']) : $_SESSION['langue'];
$langue = ($langue != "fr" && $langue != "en") ? $_SESSION['langue'] : $langue;
$_SESSION['langue'] = $langue;



$msg = "";
if (isset($_SESSION["erreur"]) && !empty($_SESSION["erreur"])) {
	$msg = $_SESSION["erreur"];
}
unset($_SESSION["erreur"]);
function entete()
{
	include "header.php";
	$entete = "<div class='contconnect'>\n";

	echo $entete;
}
function form1($msg = "")
{
	include "incloud/langue_" . $_SESSION['langue'] . ".php";

	$form1 = "<h2 >" . $msg . "</h2>\n";
	$form1 .= "<div class='conn1'> <form name='form3' method='post'  class='msgconn' >\n";
	$form1 .= "<p>" . $tr_lang[88] . ": <input type='text' name='login'/></p>\n";
	$form1 .= "<p>" . $tr_lang[89] . ": <input type='password' name='passe' /></p>\n"; //type password pour cacher le mot de passe
	$form1 .= "<input type='submit' value='" . $tf_lang[950] . "' onclick=\"\"/>\n";
	$form1 .= "</form></ br>\n";
	$form1 .= "<a href='index.php' class='connexion'>" . $tr_lang[90] . "</a></div>\n ";
	$form1 .= "</div>";
	$form1 .= "</body>";
	$form1 .= "</html>";

	echo $form1;
}
function form2($msg = "")
{
	include "incloud/langue_" . $_SESSION['langue'] . ".php";

	$form2 = "<h2>" . $msg . "</h2>";
	$form2 .= "<div class='deconnect' ><form name='form4' method='post'  >";
	$form2 .= "<input type='hidden' name='deconnect' value='2'/>";
	$form2 .= "" . $tr_lang[91] . " : <input type='submit' value='" . $tf_lang[950] . "'/>";
	$form2 .= "</form></div>";
	$form2 .= "</div>";
	$form2 .= "</body>";
	$form2 .= "</html>";

	echo $form2;
}
if (isset($_SESSION['connect'])) //est ce que la variable de session connect existe
{

	$deconnect = (isset($_POST['deconnect'])) ? (int)trim($_POST['deconnect']) : -1;


	if ($deconnect == 2) {
		unset($_SESSION['connect']);
		header("location: index.php");
		exit;
	} else {
		entete();
		include "incloud/langue_" . $_SESSION['langue'] . ".php";

		form2();
		exit;
	}
} else {
	$login = (isset($_POST['login'])) ? trim($_POST['login']) : "[\]";
	$passe = (isset($_POST['passe'])) ? trim($_POST['passe']) : "[\]";
	if ($login == "[\]") {
		entete();
		include "incloud/langue_" . $_SESSION['langue'] . ".php";

		form1();
		exit;
	} else {
		if (strlen($login) < 2 || strlen($passe) < 2) {
			$msg = $tr_lang[94];
			entete();
			include "incloud/langue_" . $_SESSION['langue'] . ".php";

			form1($msg);
			exit;
		} else {
			$st_sql = "select idutilisateur, passe from utilisateurs where login=?"; //SElectionne le mot de passe si login = login
			$ob_result = sql_pdo($st_sql, array($login));
			if (is_array($ob_result)) //si ca a marché, je veux recuperer les données
			{
				$ar_res4 = $ob_result[0]->fetchAll(PDO::FETCH_BOTH); //[0]n° de la ligne.Je recupere les résultats(BOTH),lignes et colonnes.Contient la liste des utilisateurs
				$ar_resF = $ob_result[0]; //permet le nombre de fiche correspondante, met fin a la connexion
				$n = $ar_resF->rowCount(); //le nbr d'element du array
				if ($n == 0) {
					$msg = $tr_lang[95];
					entete();
					include "incloud/langue_" . $_SESSION['langue'] . ".php";

					form1($msg);
					exit;
				} else {
					if (password_verify($passe, $ar_res4[0]['passe'])) //Verification du mot de passe crypté et non crypté
					{
						$_SESSION['connect'] = $login . "_" . $ar_res4[0]['idutilisateur'];
						header("location: index.php");
						exit;
					} else {
						$msg = $tr_lang[95];
						entete();
						include "incloud/langue_" . $_SESSION['langue'] . ".php";

						form1($msg);
						exit;
					}
				}
			} else {
				$msg = $tr_lang[95];
				entete();
				include "incloud/langue_" . $_SESSION['langue'] . ".php";

				form1($msg);
				exit;
			}
		}
	}
}
