#!/usr/bin/bash

################################
######## AUTOMATISATION ########
################################


    # Crée le volume sae103
    docker volume create sae103

    # Pull l'image clock
    docker image pull bigpapoo/clock

    # Pull l'image html2pdf
    docker image pull bigpapoo/sae103-html2pdf

    # Pull l'image sae103-php
    docker image pull bigpapoo/sae103-php

    # Run le conteneur 'bigpapoo/clock' ('clock' à l'iut) en tâche de fond (-d), le renomme 'sae103-forever' et monte le volume 'sae103' dessus.
    # --rm auto-détruit le conteneur à son arret
    docker container run -d --name sae103-forever --rm -v sae103:/work bigpapoo/clock

    # Copie les fichiers '.c' locaux vers le conteneur sae103-forever.
    docker cp ./*.c sae103-forever:/work/

    # Copie les fichiers '.md' locaux vers le conteneur sae103-forever.
    docker cp ./*.md sae103-forever:/work/

    # Copie le fichier 'genedoc.php' local vers le conteneur sae103-forever.
    docker cp ./gendoc-tech.php sae103-forever:/work/

    # Crée un répertoire 'documentation' dans le volume sae103 
    docker exec sae103-forever mkdir /work/documentation
    

    # Pour chaque fichier '.c' execute gendoc-tech.php afin de crée une documentation html dans le volume sae103
    files="$(docker exec sae103-forever ls /work/ | egrep '\.c')"

    for fichier in $files
    do
    echo $fichier
        docker container run -d --rm -v sae103:/work bigpapoo/sae103-php sh -c "php -f /work/gendoc-tech.php "$fichier" > doc-tech.html"
    done


    # Pour chaque fichier '.md' execute gendoc-user.php afin de crée une documentation html dans le volume sae103
    files="$(docker exec sae103-forever ls /work/ | egrep '\.md')"

    for fichier in $files
    do
        docker container run -d --rm -v sae103:/work bigpapoo/sae103-php sh -c "php -f /work/gendoc-user.php "$fichier" > doc-user.html"
    done

    # Pour chaque fichier '.html' crée une documentation pdf à partir de la documentation html dans le repertoire 'documentation' dans sae103
    files="$(docker exec sae103-forever ls /work/ | egrep '\.html')"

    for fichier in $files
    do
        docker container run -d --rm -v sae103:/work bigpapoo/sae103-html2pdf "html2pdf $fichier /work/documentation/$fichier.pdf"
    done

    # Crée une archive du dossier 'documentation'
    docker exec sae103-forever tar czvf work/archive.tar.gz work/documentation

    # Copie l'archive dans le répertoire local
    docker cp sae103-forever:/work/archive.tar.gz ./

    # Stop le conteneur 'sae103-forever'
    docker stop sae103-forever

    # Supprime le volume sae103
    docker volume rm sae103



# ESTERLINGOT Joseph
# V2


