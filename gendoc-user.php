#!/usr/bin/php


<?php

/*
 * Déclaration des drapeaux
 */
$html = '';
$inList = false;
$inCode = false;
$inTable = false;
$ligneTable = [];

$html .= '<!DOCTYPE html>';
$html .= '<html lang="fr">';
$html .= '<head>';
$html .= '<title>Documentation utilisateur</title>';
$html .= '</head>';
$html .= '<body>';

// Lecture du fichier Markdown
$filename = $argv[1]; // nom du fichier
$lines = file($filename, FILE_IGNORE_NEW_LINES);

if ($lines === false) {
    // Le fichier n'a pas pu être ouvert
    echo "Impossible d'ouvrir le fichier $filename";
    exit;
}

foreach ($lines as $ligne) {

    /*********************************
     * Transformation des ``` en code *
     *********************************/

    // Vérifie si nous sommes dans un bloc de code
    if ($inCode) {
        // Vérifie si le bloc de code doit être fermé
        if (substr(trim($ligne), -3) === '```') {
            $html .= '</pre></code>';
            $inCode = false;
        } else {
            // Ajoute la ligne de code formatée à la chaîne HTML
            $html .= '<code>' . htmlspecialchars($ligne) . '</code>';
        }
    } else if (substr($ligne, 0, 3) === '```') {
        // Ouvre un bloc de code et enregistre le flag
        $html .= '<pre><code>';
        $inCode = true;

    /********************************
     * Transformation des - en liste *
     ********************************/    

    } else if ($inList) {
        // Ajoute un élément de liste si nous sommes déjà dans une liste
        if (substr($ligne, 0, 1) !== '-') {
            // Ferme la liste et ajoute le contenu de la ligne
            $html .= '</ul>';
            $inList = false;
            $html .= $ligne;
        } else {
            // Ajoute un nouvel élément à la liste
            $ligneListe = trim(substr($ligne, 1));
            $html .= '<li>' . $ligneListe;
        }
    } else if (substr($ligne, 0, 1) === '-' && !$inList) {
        // Ouvre une liste non ordonnée et ajoute le premier élément
        $html .= '<ul>';
        $inList = true;
        $ligneListe = trim(substr($ligne, 1));
        $html .= '<li>' . $ligneListe;
    }

    /********************************
     * Transformation des | en table *
     ********************************/
    else if ($inTable) {
        // Ajoute une ligne de tableau
        $ligneTable[] = trim($ligne);
        if (substr(trim($ligne), -1) === '|') {
            // Ferme la table et ajoute les lignes de tableau au HTML
            $html .= '</table>';
            $inTable = false;
            $html .= '<thead>';
            $ligneHeader = array_shift($ligneTable);
            $html .= '<tr>';
            $cells = explode('|', $ligneHeader);
            foreach ($cells as $cell) {
                $html .= '<th>' . trim($cell) . '</th>';
            }
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            foreach ($ligneTable as $row) {
                $html .= '<tr>';
                $cells = explode('|', $row);
                foreach ($cells as $cell) {
                    $html .= '<td>' . trim($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }
    
    } else if (substr($ligne, 0, 1) === '|' && !$inTable) {
        // Ouvre une table et enregistre le flag
        $html .= '<table>';
        $inTable = true;
        $tableLigne = [];
        if (substr_count($ligne, '|') >= 3) {
            // Première ligne de la table : en-têtes
            $headerLigne = explode('|', $ligne);
            $html .= '<thead>';
            $html .= '<tr>';
            foreach ($headerLigne as $headerCell) {
                $html .= '<th>' . trim($headerCell) . '</th>';
            }
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
        }
    
    

    /********************************
     * Transformation des # en titre *
     ********************************/

    } else if (substr($ligne, 0, 1) === '#') {
        // Ajoute un en-tête de niveau correspondant au nombre de '#'
        $headerLevel = substr_count($ligne, '#');
        $html .= '<h' . $headerLevel . '>';
        $html .= trim(substr($ligne, $headerLevel + 1));
        if ($headerLevel > 0) {
            $html .= '</h' . $headerLevel . '>';
        }


        // Remplace le [texte](lien) en HTML
    } else if (preg_match('/^\[(.*?)\]\((.*?)\)$/', $ligne, $url)) {
         $html .= '<a href="' . $url[2] . '">' . $url[1] . '</a>';


        // Ajoute du texte en italique
    } else if (preg_match('/\*([^*]+)\*/', $ligne, $italique)) {
        $html .= '<em>' . $italique[1] . '</em>';


        // Ajoute du texte en gras
    } else if (preg_match('/\*\*([^*]+)\*\*/', $ligne, $gras)) {
        $html .= '<strong>' . $gras[1] . '</strong>';
    } 
}

// Ferme les balises ouvertes
if ($inList) {
    $html .= '</ul>';
}
if ($inTable) {
    $html .= '</table>';
}
$html .= '</body>';
$html .= '</html>';
// Nom du fichier où sera écrit la documentation
$outputFile = "doc-user.html";

// Ecrit le contenu à l'aide de la concaténation avec $html
file_put_contents($outputFile, $html);

?>

