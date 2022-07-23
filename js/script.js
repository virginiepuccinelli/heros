//On met les fonctions en premier
//Si dureeconnect existe?
var dureeConnect = (document.getElementById("dureeConnect")) ? document.getElementById("dureeConnect").value : -1
if (dureeConnect != -1) {
    var cptTemps = dureeConnect
    traducDeconnect = document.getElementById("traducDeconnect").value //vaut 'deconnexion dans'
    document.getElementById("text_connect").innerHTML = traducDeconnect + " : " + cptTemps + " mn ";
}
function decrementationTemps() {
    cptTemps = cptTemps - 1//On decremente cptTemps
    if (cptTemps <= 0) {
        window.location.href = "index.php?finConnexion=1"
        //appelle la page index.php et lui envoie en get une information finConnexion qui vaut 1
    }
    traducDeconnect = document.getElementById("traducDeconnect").value
    limitDeconnect = document.getElementById("limitDeconnect").value

    couleurAffichage = (cptTemps > (dureeConnect * limitDeconnect / 100)) ? "black" : "red"// Le message devient rouge quand il reste 1mn

    //Deconnexion dans : 
    document.getElementById("text_connect").innerHTML = "<span style='color:" + couleurAffichage + "' >" + traducDeconnect + " : " + cptTemps + " mn</span>"
}


function imageIsLoaded(e)//Image chargée, e = le fichier telechargé mais pas encore validée
{
    /////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\//////////////////////////////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\    
    src = e.target.result// rempli src avec le fichier
    //

    $("#waitImg").attr("src", src)// Pour pouvoir recuperer la largeur et la hauteur de l'image
    $("#imageheros").css({ "border": "1px solid black", "backgroundImage": "url('" + src + "')" })
    $("#supimg").css({ "display": "block" }) //On affiche la poubelle
    setTimeout("imageTermine()", 1500)//Delai avant execution : 1500 millisecondes, pour qu'il est le temps de charger l'image
}
function rechercher()//Fonction pour le bouton rechercher
{
    rech = document.getElementById("rech")
    rech.style.border = "1px solid #8f8f9d"//On met la bordure en gris et 
    rech.setCustomValidity("")//setCustomValidity(met un message vide dans ma zone de texte) on vide le message

    nombre = document.getElementById("nombre")
    nombre.innerHTML = ""//Mettre une chaine vide sur l'erreur

    heros = document.querySelectorAll("div[id='liste'] b")//Liste de tous les elements b qui sont dans un div qui a pour id 'liste' et on parcours tous es éléments
    heros.forEach((obj, i) => {
        obj.style.backgroundColor = ""
        obj.style.border = ""
    })

    if (rech.value.trim() == "") //Si champs vide, message: Remplissez!
    {
        rech.style.border = "1px solid red"
        rech.setAttribute("placeholder", "" + $("#trad87").val() + "!")
        return false
    }
    else {
        premier = -1
        nbr = 0

        heros.forEach((obj, i) => {
            //Si dans la chaine en cours, on trouve la position de ce qui est recherché et inversement
            if ((obj.innerHTML.trim().toLowerCase().indexOf(rech.value.trim().toLowerCase(), 0) != -1) || (rech.value.trim().toLowerCase().indexOf(obj.innerHTML.trim().toLowerCase(), 0) != -1)) {
                nbr++ //Pour connaitre le nbre de fiche qui  correspondent
                obj.style.backgroundColor = "white"
                obj.style.border = "1px solid black"
                premier = (premier == -1) ? i : premier//


                if (nbr == 1) {
                    //Pour envoyer l'ascenceur directement au 1er element trouvé dans la recherche
                    nombre.innerHTML = $("#trad83").val()
                    window.location.href = "#ancre" + premier
                    //Afficher combien trouvé nbr

                }
                if (nbr > 1) {
                    //Pour envoyer l'ascenceur directement au 1er element trouvé dans la recherche
                    nombre.innerHTML = $("#trad84").val() + " " + nbr + " " + $("#trad85").val() + "."
                    window.location.href = "#ancre" + premier
                    //Afficher combien trouvé nbr
                }

            }

            if (nbr == 0) {
                nombre.innerHTML = "" + $("#trad86").val() + "." //Message 'aucun héros ne correspond'
            }
        })

    }
}
function imageTermine()//Fonction pour afficher les details de la photo
{
    //Information de l'image
    info = "L : " + $("#waitImg").css("width") + " - H : " + $("#waitImg").css("height") + " - " + $("#waitImg").attr("ext") + " - " + $("#waitImg").attr("poids") + " Kb"
    $("#detailphoto").html(info)

}


$(document).ready(function () {
    //Temps de deconnection
    if (document.getElementById("dureeConnect")) {//Si dureeConnect existe
        //setInterval(nom fonction qu'on appelle, temps en milliseconde avant d'appeler la fonction) toutes les mn on appelle la fonction
        cptInterval = setInterval(decrementationTemps, 60000)// 60000 millisecondes vaut  1 minute
        $(this).mouseover(function (e)//si on bouge la souris, on remet la deconnexion a 10mn
        {
            traducDeconnect = document.getElementById("traducDeconnect").value
            cptTemps = document.getElementById("dureeConnect").value //vaut 10mn
            document.getElementById("text_connect").innerHTML = traducDeconnect + " : " + cptTemps + " mn"
        })
        $(this).keypress(function (e)//Si une touche est pressée on remet la deconnexion à 10mn
        {
            traducDeconnect = document.getElementById("traducDeconnect").value
            cptTemps = document.getElementById("dureeConnect").value//Vaut 10 mn
            document.getElementById("text_connect").innerHTML = traducDeconnect + " : " + cptTemps + " mn"
        })
    }
    //pour programmer la touche entrée pour la fonction recherche
    $("#rech").on("keypress", function () {
        codetouche = event.which
        if (codetouche == 13) {
            rechercher()
        }
    })
    //Suppression de la photo au click sur la corbeille
    $("#supimg").on("click", function () {
        $("#imageheros").css({ "border": "1px solid black", "backgroundImage": "none" })//On efface l'image
        $("#detailphoto").html("")//On efface les details
        //WaitImge est u champs caché qui sert a stocker les information de la photo chargée avant qu'elle soit validée
        $("#waitImg").attr("src", "")//On met les attribut avec "" comme valeur
        $("#waitImg").attr("poids", "")
        $("#waitImg").attr("ext", "")
        $("#photosh").val("")//Valeur du bouton parcourir
        $("#supimg").css({ "display": "none" })//On  cache la poubelle
        $("#ajout").val("1")//Valeur du champ caché à 1
        $(":file[id='photosh']").val('')
    })
    //Recherche tous les objets du formulaire qui sont file mais qui ont l'id photosh
    //Modifier l'image du super heros
    $(":file[id='photosh']").change(function () {
        //Au click on verifie l'image
        if (this.files && this.files[0]) {
            nomfichier = this.files[0].name//Recupere le nom du fichier
            poids = this.files[0].size //poids du fichier en octets
            taille_maxi = $("#poidMax").val()//Recupere la valeur de poidsmax
            texterr1 = $("#texterr1").val()//Message d'erreur, fichier trop gros
            if (poids > taille_maxi) {
                $("#msgerr").html(texterr1)
                $(this).val("")
                return false
            }
            texterr2 = $("#texterr2").val()//format pas autorisé
            typemime = this.files[0].type//type mime du fichier
            if (typemime.indexOf("image/gif") == -1 && typemime.indexOf("image/png") == -1 && typemime.indexOf("image/jpeg") == -1) {
                $("#msgerr").html(texterr2)
                $(this).val("")
                return false
            }

            //reader=new Image

            var reader = new FileReader();//Lire le contenu du fichier image
            reader.onload = imageIsLoaded;// onload: au chargemennt Appelle la fonction image
            reader.readAsDataURL(this.files[0]);
            poids = (poids / 1024)//Je transforme les octets en Ko
            poids = poids.toFixed(2)//2 chiffres apres la virgule
            ext = nomfichier.split("."); ext = ext[ext.length - 1]//ext est egal à l'array ext[nbr d'element(3) et je veux l'element array n°2]
            $("#waitImg").attr("poids", poids)//Je rajoute un attribut
            $("#waitImg").attr("ext", ext)
            $("#ajout").val("2")

        }

    })
    //Traduction du synopsis
    $("#trad").on("click", function () {
        affiche = $(this).attr("affiche")

        texte = $("#descriptioncache").html()
        if (texte != "") {
            if (affiche == 0) {
                //Appel AJAX (en arriere plan, appel une page php), page action fait l'analyse, et renvoie le result
                $.post("actions.php", { action: 77, textAtraduire: texte }, function (result) {
                    res = result.split("|")
                    if (res[0] == 1) {
                        $("#description").html(res[1])
                        $("#trad").attr("affiche", "1")
                    }
                    else {
                        $("#errtraduc").html("Traduction impossible")
                    }
                })
            }
        }
        if (affiche == 1) {
            $("#trad").attr("affiche", "0")
            $("#description").html($("#descriptioncache").html())
        }
    })



})
