<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Super Héros</title>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <link rel="stylesheet" href="css/style.css" type="text/css"/>
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css"/>
        <link rel="shortcup icon" href="fav.ico"/>
        <script src="js/jquery1_12.js"></script>
    </head>
    <body>  
        <p>Num : <input type="text" id="num"/></p>
        <p><button type="button" id="bttrad">Ok</button></p>
        <div id="traduc"></div>

        <script>
            $(document).ready(function()
            {
                $("#bttrad").click(function()
                {
                    num= $("#num").val()
                    if( isNaN(num)|| num<0  || (parseInt(num) - num)!=0)
                    {
                        $("#traduc").html("Le nombre n'est pas correct")
                        return false
                    }
                    lang="fr"
                    //Appelle la page actions.php
                    $.post("actions.php",{action:1, lang:lang, num:num}, function(data)
                    {
                        $("#traduc").html(data)
                        return false
                    })
                })
                
                
            })
        </script>
    </body>