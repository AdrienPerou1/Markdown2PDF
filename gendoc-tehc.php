#!/usr/bin/php

<?php 

# Récupération du nom de fichier et place le contenu dans '$lignes'.

$nom_fichier = $argv[1];

$lignes = file($nom_fichier);


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Documentation technique</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            
        }
        
        header {
            background-color: #f2f2f2;
            padding: 20px;
        }
        
        h1, h2, h3 {
            color: #333;
        }
        
        section {
            margin: 20px auto;
            max-width: 600px;
            padding: 20px;
            text-align: left;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        nav{
            margin: 20px auto;
            max-width: 600px;
            padding: 20px;
            text-align: left;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        ul {
            list-style-type: none;
            padding: 0;
        }
        
        li {
            margin-bottom: 10px;
            
        }
        
        code {
            background-color: #f2f2f2;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: Consolas, monospace;
        }
    </style>
</head>

<body>

    <header>
        <h1> <?php echo $nom_fichier?></h1>
        <p> 
        <?php 

            ### Description du fichier .c

            $in_comment = false;

            $commentaire = '';

            foreach ($lignes as $ligne){

                if (strpos($ligne, '*/') !== false){
                    $in_comment = false;
                    break;
                }

                if ($in_comment === true){

                    $commentaire = substr($ligne, 2);

                    echo $commentaire;
                }

                if (strpos($ligne, '/**') !== false){
                    $in_comment = true;
                }
            }       
        ?>
        </p>
    </header>   

    <nav>
        <h2>Table des matières</h2>
        <ul>
            <li><a href="#defines">Defines</a></li>
            <li><a href="#variables">Variables</a></li>
            <li><a href="#structures">Structures</a></li>
            <li><a href="#fonctions">Fonctions</a></li>
        </ul>
    </nav>


    <section id="defines">
        <h2>Defines</h2>
        <p>

        <?php 

            ### Récupère le nom des défines et les décrits

            $commentaire = '';

            $nom_define = '';

            foreach ($lignes as $ligne){

                if (strpos($ligne, '#define') === 0){

                    $nom_define = substr($ligne, 7, strpos($ligne, ' ', 8) - 7);

                    echo "<strong>" . $nom_define . ":" . "</strong>";

                    if (strpos($ligne, "/**") !== false){

                        $commentaire = substr($ligne, strpos($ligne, "/**")+3, strpos($ligne, "*/") - strpos($ligne, "/**")-3);

                        echo $commentaire . "\n" . "<br>\n";
                        
                    }
                }
            }
        ?>
        
        </p>
    </section>

    <section id="variables">
        <h2>Variables</h2>
        <p>
            
        <?php 

            ### Récupère le nom des variables globales et les décrits

            $commentaire = '';

            $nom_variable = '';

            $in_main = false;

            foreach ($lignes as $ligne){

                if (strpos($ligne, 'int main()') === 0){
                    $in_main = true;
                }

                if ($in_main === true && strpos($ligne, '}') === 0){
                    $in_main = false;
                    break;
                }

                if ($in_main === true && strpos($ligne, "/**") !== false){

                    $debut_nom = strpos($ligne, " ", 5);

                    if (strpos($ligne, "=") !== false){
                        $fin_nom = strpos($ligne, "=");
                    } else {
                        $fin_nom = strpos($ligne, ";");
                    }

                    $nom = substr($ligne, $debut_nom, $fin_nom - $debut_nom);

                    $debut_commentaire = strpos($ligne, "/**")+3;

                    $fin_commentaire = strpos($ligne, "*/");

                    $commentaire = substr($ligne, $debut_commentaire, $fin_commentaire - $debut_commentaire);

                    echo "<strong>" . $nom . ": </strong>" . $commentaire . "\n";

                }
                    
            }
        ?>

        </p>
    </section>

    <section id="structures">
        <h2>Structures</h2>
        <p>
            
        <?php 

            ### Récupère le nom des chauqe structure et décrit chaque attributs

            $f = fopen($nom_fichier, "r");

            $commentaire = '';

            $nom_struct = '';

            $ligne_boucle;

            $nb_ligne = 0;

            $pointeur = 0;

            $in_struct = false;

            foreach ($lignes as $ligne){

                $nb_ligne++;
                while ($pointeur < $nb_ligne){
                    $ligne_boucle = fgets($f);
                    $pointeur++;
                }

                if (strpos($ligne, 'typedef struct') === 0){

                    $in_struct = true;

                    while (strpos($ligne_boucle, "}") !== 0){

                        $ligne_boucle = fgets($f);
                        $pointeur++;
                    }

                    $nom_struct = substr($ligne_boucle, 1, strpos($ligne_boucle, ";") - 1);

                    echo "<h3><u>" . $nom_struct . "</u></h3>" . "\n";
                }

                if ($in_struct === true){
                    if (strpos($ligne, "/**") !== false){

                        $nom_attribut = substr($ligne, strpos($ligne, " ", 5), strpos($ligne, ";") - strpos($ligne, " ", 5));

                        $commentaire = substr($ligne, strpos($ligne, "/**")+3, strpos($ligne, "*/") - strpos($ligne, "/**")-3);

                        echo "<strong>" . $nom_attribut . ": </strong>";

                        echo $commentaire . "\n";

                        echo "<br>\n";
                    }

                    if (strpos($ligne, "}") === 0){
                        $in_struct = false;
                    }
                }
            }
        fclose($f);
        ?>

        </p>
    </section>

    <section id="fonctions">
        <h2>Fonctions</h2>

        <section>
        <?php

### Récupère le nom des fonctions et décrit rapidement ce qu'elle fait puis décirt sa valeur de retour ansi que ces paramètres

$file = fopen($nom_fichier, "r");

$pointeur = 0;
$commentaire = "";

while (($line = fgets($file)) !== FALSE){
    $pointeur++;

    while ((strpos($line, "\detail") === false) && (strpos($line, "\\return") === false) && (strpos($line, "\param") === false) && (strpos($line, "\brief") === false) && ($pointeur < $nb_ligne)){

        $line = fgets($file);
        $pointeur++;
    }

    while ((strpos($line, "*/") === false) && ($pointeur < $nb_ligne)){

        $line = fgets($file);
    }

    while ((strpos($line, "{") === false)&& ($pointeur < $nb_ligne)){

        $line = fgets($file);
    }

    $debut_nom = strpos($line, " ");

    $fin_nom = strpos($line, "(");

    $nom_fonction = substr($line, $debut_nom, $fin_nom - $debut_nom);

    echo "<h3><u>" . $nom_fonction . "</u></h3>\n";

    fclose($file);

    $file = fopen($nom_fichier, "r");

    $i = 0;

    while ($i < $pointeur){
        $line = fgets($file);
        $i ++;
    }

    while (strpos($line, "*/") === false && ($pointeur < $nb_ligne)){
        $commentaire = "";

        if (strpos($line, "\brief") !== false){

            $commentaire = $commentaire . substr($line, strpos($line,"\brief")+6);
            $line = fgets($file);
            $pointeur++;

            while ((strpos($line, "detail") === false) && (strpos($line, "\brief") === false) && (strlen($line) > 3) && (strpos($line, "\\return") === false) && (strpos($line, "\param") === false) && (strpos($line, "*/") === false)){
                $commentaire = $commentaire . substr($line, 2);   
                    
                $line = fgets($file);
                $pointeur++;
                    
            }

            echo "<strong>Brief :</strong>" . $commentaire . "\n<br>";
            $commentaire = "";
        }




        if (strpos($line, "\detail") !== false){

            $commentaire = $commentaire . substr($line, strpos($line,"\detail")+7);
            $line = fgets($file);
            $pointeur++;

            while ((strpos($line, "\detail") === false) && (strpos($line, "\brief") === false) && (strlen($line) > 3) && (strpos($line, "\\return") === false) && (strpos($line, "\param") === false) && (strpos($line, "*/") === false)){

                $commentaire = $commentaire . substr($line, 2);
                $line = fgets($file);
                $pointeur++;
            }

            echo "<strong> Detail :</strong>" . $commentaire . "\n<br>";
            $commentaire = "";
        }



        if (strpos($line, "\\return") !== false){

            $commentaire = $commentaire . substr($line, strpos($line,"\\return")+7);
            $line = fgets($file);
            $pointeur++;

            while ((strpos($line, "\detail") === false) && (strpos($line, "\brief") === false) && (strlen($line) > 3) && (strpos($line, "\\return") === false) && (strpos($line, "\param") === false) && (strpos($line, "*/") === false)){

                $commentaire = $commentaire . substr($line, 2);
                $line = fgets($file);
                $pointeur++;
            }

            echo "<strong> Retourne :</strong>" . $commentaire . "\n<br>";
            $commentaire = "";
        }



        if (strpos($line, "\param") !== false){

            $commentaire = $commentaire . substr($line, strpos($line,"\param")+6);
            $line = fgets($file);
            $pointeur++;

            while ((strpos($line, "\detail") === false) && (strpos($line, "\brief") === false) && (strlen($line) > 3) && (strpos($line, "\\return") === false) && (strpos($line, "\param") === false) && (strpos($line, "*/") === false)){


                $commentaire = $commentaire . substr($line, 2);   
                    
                $line = fgets($file);
                $pointeur++;
                    
            }

            echo "<strong> Paramètres :</strong>" . $commentaire . "\n<br>";
            $commentaire = "";
        }



        if ((strpos($line, "\detail") === false) && (strpos($line, "\\return") === false) && (strpos($line, "\param") === false) && (strpos($line, "\brief") === false) && (strpos($line, "*/") === false)){

            $line = fgets($file);
            $pointeur++;
        }
    }
    
}


fclose($file);
?>
 

</section>

</body>

</html>