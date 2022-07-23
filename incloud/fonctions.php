<?php

//Requete PDO - gestion requête SQL si requete de type select alors utiisation de ? dans $sql et $param est array et contient valeur du ?
//si requete de type update, insert alors utilisation de :rubrique dans $sql et $param est array et contient "rubrique"=>$rubrique
function sql_pdo($sql, $params = "")
{
	// version 1.0
	include "connect.php";
	include "incloud/langue_" . $_SESSION['langue'] . ".php";

	try {
		//on instancie la class PDO en lui envoyant des parametres
		$pdo_gf = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbDb . ";charset=utf8;", $dbUser, $dbPass, array(PDO::ATTR_PERSISTENT => false));
		$pdo_gf->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //initialisation des paramètres pour les exceptions
		$pdo_gf->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // par defaut sur true , le mettre sur false!!!!   
	} catch (Exception $e) {
		//changer de message en mode production, ne pas mettre $e!!!!!!!!!!
		die("" . $tr_lang[10] . " !"); // en ligne mettre donnée indisponible.
	}

	$gfexe = $pdo_gf->prepare($sql); //on prepare la requete, le resultat est stoché dans $gfexe
	if (is_array($params)) {
		$associatif = false;
		foreach ($params as $cle => $valeur) {
			if (is_string($cle)) {
				// La clé est une chaîne alors c'est un tableau associatif et on arrête tout.
				$associatif = true;
				break;
			}
		}
		unset($cle, $valeur);

		if (!$associatif) {
			for ($ik = 0; $ik < count($params); $ik++) {
				$jk = $ik + 1;
				if (is_numeric($params[$ik])) {
					$gfexe->bindParam($jk, $params[$ik], PDO::PARAM_INT);
				}
				if (is_string($params[$ik])) {
					$gfexe->bindParam($jk, $params[$ik], PDO::PARAM_STR);
				}
				if (is_bool($params[$ik])) {
					$gfexe->bindParam($jk, $params[$ik], PDO::PARAM_BOOL);
				}
				if (is_null($params[$ik])) {
					$gfexe->bindParam($jk, $params[$ik], PDO::PARAM_NULL);
				}
			}
			unset($ik, $jk);
		} else {
			reset($params);
			for ($ik = 0; $ik < count($params); $ik++) {
				$jk = $ik + 1;
				$index = key($params);
				if (is_numeric($params[$index])) {
					$gfexe->bindParam($jk, $params[$index], PDO::PARAM_INT);
				}
				if (is_string($params[$index])) {
					$gfexe->bindParam($jk, $params[$index], PDO::PARAM_STR);
				}
				if (is_bool($params[$index])) {
					$gfexe->bindParam($jk, $params[$index], PDO::PARAM_BOOL);
				}
				if (is_null($params[$index])) {
					$gfexe->bindParam($jk, $params[$index], PDO::PARAM_NULL);
				}
				next($params);
			}
			unset($ik, $jk, $index);
		}
		try {
			$gfexe->execute($params); //execute la requête
			unset($associatif, $params, $sql);
			return array($gfexe, $pdo_gf);
		} catch (Exception $e) {

			unset($pdo_gf, $gfexe, $associatif);
			return "0|"; //En mode production il faut enlever $e contient toute l'erreur!! mettre un message d'erreur
		}
	} else {
		try {
			$gfexe->execute();
			unset($sql);
			return array($gfexe, $pdo_gf);
		} catch (Exception $e) {
			unset($sql, $pdo_gf, $gfexe);
			return "0|"; //En mode production il faut enlever $e contient toute l'erreur!! mettre un message d'erreur
		}
	}
}

//Fonction pour verifier les caracteres interdits dans $rubrique
function verifRubrique($rubrique, $autorise)
{
	$ok = true;
	$n = strlen($rubrique);

	for ($i = 0; $i < $n; $i++) {
		if (strpos($autorise, substr(strtolower($rubrique), $i, 1)) === false) {
			$ok = false;
			break;
		}
	}
	unset($autorise, $rubrique, $n, $i);
	return $ok;
}

//Supprimer la fiche super-heros
function suppression($idsup, $table)
{
	if ($table == "superheros") {
		//Suprimme tout sur la table superheros et sur la table pouvoirs meme ceux qui n'ont pas de pouvoirs
		$st_sql = "delete s.*,p.* from " . $table . " s left join pouvoirs p on s.id=p.idsh where s.id=?";
		$ob_result = sql_pdo($st_sql, array($idsup)); //variable qui stock le resultat de la requete- j'appelle la fonction avec (parametre sql, 2eme param si ca provient d'un formulaire)
		if (is_array($ob_result)) //si ca a marché(array), je veux recuperer les données
		{
			$ar_res = $ob_result[0]->fetchAll(PDO::FETCH_BOTH); //[0]n° de la ligne.Je recupere les résultats, BOTH, recupere les 2 resultats, lignes et colonnes.Contient la liste des super heros
			$ar_resF = $ob_result[0]; //permet le nombre de fiche correspondante, met fin a la connexion
			$n = $ar_resF->rowCount(); //le nbr d'element du array
			if ($n == 0) {
				return 1;
			} else {
				return 2;
			}
		} else {
			return 1;
		}
	} else {
		//supprime dans la table pouvoirs, le pouvoir correspondant a l'id du pouvoir
		$st_sql = "delete from " . $table . " where idpouvoir = ?";
		$ob_result = sql_pdo($st_sql, array($idsup)); //variable qui stock le resultat de la requete- j'appelle la fonction avec (parametre sql, 2eme param si ca provient d'un formulaire)
		if (is_array($ob_result)) //si ca a marché, je veux recuperer les données
		{
			$ar_res = $ob_result[0]->fetchAll(PDO::FETCH_BOTH); //[0]n° de la ligne.Je recupere les résultats, BOTH, recupere les 2 resultats, lignes et colonnes.Contient la liste des super heros
			$ar_resF = $ob_result[0]; //permet le nombre de fiche correspondante, met fin a la connexion
			$n = $ar_resF->rowCount(); //le nbr d'element du array
			if ($n == 0) {
				return 1;
			} else {
				return 2;
			}
		} else {
			return 1;
		}
	}
}

//Fonction pour verifier les caracteres interdits dans l'age
function verifAge($age, $autorise_age)
{
	$ok = true;
	$n = strlen($age);

	for ($i = 0; $i < $n; $i++) {
		if (strpos($autorise_age, substr($age, $i, 1)) === false) {
			$ok = false;
			break;
		}
	}
	unset($autorise_age, $age, $n, $i);
	return $ok;
}

//Modifier la fiche du super heros
function modifier($idobj, $table, $rub, $age, $sexe, $autorise, $photo, $num, $ajout, $nouveauNom, $autorise_age)
{
	include "params.php";
	include "incloud/langue_" . $_SESSION['langue'] . ".php";
	$er = 0;
	if (strlen($rub) < 2 || !verifRubrique($rub, $autorise)) {
		$er = 1;
	}
	if ($table == "superheros") {

		if (!verifAge($age, $autorise_age) || $age < 1 || $age > 9999) {
			$er += 2;
		}
		if (($sexe != "f" && $sexe != "h") || empty($sexe)) {
			$er += 4;
		}
		//Modifier la photo du super heros
		if ($ajout == 2) {
			if (is_array($photo)) {

				$err = "";
				$msg = "";
				$couleur = "";
				//on autorise 2 megas dans params.php
				if ($photo['size'] > $max_upload) {
					$er += 8;
				} else {
					$ext = pathinfo($photo['name'], PATHINFO_EXTENSION); //Retourne l'extension, la derniere
					$ext_autorise = ["jpg", "jpeg", "png"];
					if (!in_array(strtolower($ext), $ext_autorise)) {
						$er += 16;
					} else {
						if ($nouveauNom != null) {
							$chemin = "photos/" . $nouveauNom;
							if (file_exists($chemin)) {
								unlink($chemin);
							}
						}

						$num = $num + 1;
						$nouveauNom = $idobj . "_" . $num . "." . $ext; //si plusieurs photos on change le nom en les incrementants
						$destination = "photos/" . $nouveauNom;
						$ok = @copy($photo['tmp_name'], $destination); //Fait une copie du fichier et si il existe il l'ecrase $photo['tmp_name']:chemin d'accés, chemin vers le fichier de destination
						if ($ok) {
							$msg = "Photo transferée avec succés";
						} else {
							$er += 32;
						}
					}
				}
			} else {
				$coul = "red";
				$bcoul = ["black", "black", "black"];
				$msg = "Impossible de transferer la photo !";
				return array($msg, $coul, $bcoul);
			}
		}
		//Suppression de la photo au click sur la corbeille
		if ($ajout == 1 && $nouveauNom != null) {
			$chemin = "photos/" . $nouveauNom;
			if (file_exists($chemin)) //Verifie si le chemin existe
			{
				unlink($chemin);
			}
			$nouveauNom = null;
		}
	}
	if ($er == 0) {
		if ($table == "superheros") {
			//Modifier le nom, l'age le sexe et ou la photo de la table superheros si l'id est egal à l'id
			$st_sql = "update " . $table . " set heros=:heros, age=:age, sexe=:sexe, photo=:photo, num=:num where id=:id";
			$ob_result = sql_pdo($st_sql, array(":heros" => $rub, ":age" => $age, ":sexe" => $sexe, ":photo" => $nouveauNom, ":num" => $num, ":id" => $idobj,));
		} else {
			//Modifier le pouvoir si l'idpouvoir est egal à l'idpouvoir envoyé
			$st_sql = "update " . $table . " set pouvoir=:pouvoir where idpouvoir=:idpouvoir";
			$ob_result = sql_pdo($st_sql, array(":pouvoir" => $rub, ":idpouvoir" => $idobj));
		}
		if (is_array($ob_result)) //si ça a marché, je veux recuperer les données
		{
			$ar_res = $ob_result[0];
			$n = $ar_res->rowCount(); //nb total de fiche des superheros 
			if ($n == 0) {
				$msg = ($table == "superheros") ? "<p style='text-align:center'>" . $tr_lang[11] . "</p>" : "<p style='text-align:center'>" . $tr_lang[12] . "</p>";
				$coul = "red";
				$bcoul = ["black", "black", "black"];
				return array($msg, $coul, $bcoul);
			} else {
				/////////////////////Message fiche modifiée
				$msg = ($table == "superheros") ? "<p style='text-align:center'>" . $tr_lang[13] . " " . $rub . " " . $tr_lang[14] . "</p>" : "<p style='text-align:center'>" . $tr_lang[15] . " " . $rub . " " . $tr_lang[16] . "</p>";
				$bcoul = ["black", "black", "black"];
				$coul = "green";
				return array($msg, $coul, $bcoul);
			}
		} else {
			$msg = ($table == "superheros") ? "<p style='text-align:center'>" . $tr_lang[17] . "</p>" : "<p style='text-align:center'>" . $tr_lang[18] . "</p>";
			$bcoul = ["black", "black", "black"]; //

			if (strtolower(strpos($ob_result, "Duplicate entry"))) {
				$bcoul = ($table == "superheros") ? ["red", "black", "black"] : ["black", "black", "black"];
				$msg = ($table == "superheros") ? "<p style='text-align:center'>" . $tr_lang[20] . " " . $rub . " " . $tr_lang[21] . "</p>" : "<p style='text-align:center'>" . $tr_lang[22] . " " . $rub . " " . $tr_lang[23] . "</p>";
			}
			if (strtolower(strpos($ob_result, "Data too long"))) {
				$bcoul = ["red", "black", "black"]; //
				$msg = ($table == "superheros") ? "<p style='text-align:center'>" . $tr_lang[24] . "</p>" : "<p style='text-align:center'>" . $tr_lang[25] . "</p>";
			}
			if (strtolower(strpos($ob_result, "Out of range"))) {
				$bcoul = ["black", "red", "black"]; //
				$msg = "<p style='text-align:center'>" . $tr_lang[26] . "</p>";
			}
			$coul = "red";
			return array($msg, $coul, $bcoul);
		}
	} else //Si erreur
	{
		if ($er == 1) {
			$bcoul = ($table == "superheros") ? ["red", "black", "black"] : ["black", "black", "black"];
			$coul = "red";
			$msg = ($table == "superheros") ? "<p style='text-align:center'>" . $tr_lang[27] . "</p>" : "<p style='text-align:center'>" . $tr_lang[28] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 2) {
			$bcoul = ["black", "red", "black"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[29] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 3) {
			$bcoul = ["red", "red", "black"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[30] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 4) {
			$bcoul = ["black", "black", "red"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[31] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 5) {
			$bcoul = ["red", "black", "red"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[32] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 6) {
			$bcoul = ["black", "red", "red"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[33] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 7) {
			$bcoul = ["red", "red", "red"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[34] . "s</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 8) {
			$bcoul = ["black", "black", "black"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[590] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 16) {
			$bcoul = ["black", "black", "black"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[591] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 32) {
			$bcoul = ["black", "black", "black"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[592] . "</p>";
			return array($msg, $coul, $bcoul);
		}
		if ($er == 64) {
			$bcoul = ["black", "black", "black"];
			$coul = "red";
			$msg = "<p style='text-align:center'>" . $tr_lang[593] . "</p>";
			return array($msg, $coul, $bcoul);
		}
	}
}

//Verifie si le nom du superhéros existe deja dans la bdd
function ExistNom($nom)
{
	//Selectionne tout le champs heros de la table superheros en concatenant avec ;
	$st_sql = "SELECT GROUP_CONCAT(heros SEPARATOR ';') from superheros ";
	$ob_result = sql_pdo($st_sql);
	if (is_array($ob_result)) {
		$ar_res = $ob_result[0]->fetchAll(PDO::FETCH_BOTH); //[0]n° de la ligne.Je recupere les résultats, BOTH, recupere les 2 resultats, lignes et colonnes.Contient la liste des super heros
		$ar_resF = $ob_result[0]; //permet le nombre de fiche correspondante, met fin a la connexion
		$n = $ar_resF->rowCount(); //le nbr d'element du array

		if ($n == 0) {
			return  1;
		} else {

			$ok = true;
			if (in_array($nom, $ar_res[0])) {
				$ok = false;
			}
		}
	} else {
		return  1;
	}
}
// Pour récuperer le nombre de caractère maximum autorisés dans la bdd
function maxRub($table = "superheros", $rub)
{
	$st_sql = "SELECT   CHARACTER_MAXIMUM_LENGTH  as info  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME =?  and column_name=?";
	$ob_result = sql_pdo($st_sql, array($table, $rub));
	if (is_array($ob_result)) //si ca a marché, je veux recuperer les données
	{
		$ar_res = $ob_result[0]->fetchAll(PDO::FETCH_BOTH); //[0]n° de la ligne.Je recupere les résultats, BOTH, recupere les 2 resultats, lignes et colonnes.Contient la liste des super heros
		$ar_resF = $ob_result[0]; //permet le nombre de fiche correspondante, met fin a la connexion
		$n = $ar_resF->rowCount(); //le nbr d'element du array
		if ($n == 0) {
			return "[|]";
		} else {

			return $ar_res[0]["info"];
		}
	} else {
		return "[|]";
	}
}
