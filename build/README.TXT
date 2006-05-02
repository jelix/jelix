jBuildTools : scripts de génération de distribution de sources


preprocess.php
    pré-processeur de fichier texte (PHP, HTML ou autre..)
    voir http://jelix.org/articles/hackersmanual/preprocesseur
   usage :
     php preprocess.php fichier_source fichier_cible


mkdist.php
   générateur d'arborescence
   voir http://jelix.org/articles/hackersmanual/mkdist
   usage :
      php mkdist.php [-v] fichier_manifest.mn repertoire_source repertoire_cible

mkmanifest.php
   générateur de fichier manifest pour mkdist
   php mkmanifest.php [-v] repertoire_source [base_chemin] fichier.mn

