<?php


//connection à la base de donnée
include './inc/connect.php';
include './inc/fonctions.php';
//afficher les erreurs php 
toggleErrorReporting(true);
demarrerSessionSiNecessaire();

include 'compteur_visite_duree_et_bloquage.php';

$bouton_ajouter = "";
$cv = "";
$h = "";
$count_position = 0;
$metier_search = "";
$salaire_souhaite = "";
$distance_max = "";
$alerte = "";
$e1 = '';
$add_un = "";
$add_deux = "";
$gps_contructor = "";
$gps_contructor_deux = "";
$metier = "";
$job = "";
$adresse = "moi";
$max_distance = 50;
$s = "";
$zone = "proche de d'ici";
$today = date("Y");
$experience = 'débutant';
$exp_plus = 0;
$date_inscription = "";
$realisations = "";
$result = "";
$realisations_carrousel = "";
$get = "";
$choix = "";
$distance = "";
$btn_search_cv = "";
$d = "Triez par";
$c = "Contrat ?";
$g = "Genre ?";
$filtrer = "";
$filtration  = "ORDER BY date_inscription DESC";
$etoile_count_conseil = "";
$photo = "";
$first_name = "";
$last_name = "";
$nbr = 20;
$limit = 'LIMIT ' . $nbr;
$candidat = "";
$and = "";
$type_contrat = "";
$selected_values = "";
$recruteur_OK = "";
$maxChecked = 0;
$hidden = "";
$categorie_contrat = "";
$bouton_category = "";
$bouton_genre = "";
$bouton_filtre = "";
$contact_par_mail = '';
$contact_par_tel = '';
$sms_confimer = '';
$autres = "";
$social = "";
$chemin_du_fichier='';


if (empty($profil_OK) && !empty($_SESSION["auth"]["ID"])) {
    $profil = $bdd->prepare("SELECT ID FROM resho_users WHERE ID = ? AND user_email LIKE ? AND user_activation_key LIKE ? ");
    $profil->execute(array($_SESSION["auth"]["ID"], $_SESSION["auth"]["user_email"], $_SESSION["auth"]["user_activation_key"]));
    $profil_OK = $profil->fetch();
    
}

// $profil_OK = fetchUserProfile($bdd);

if (!empty($_POST["p"])) {
    $nbr = $nbr + $_POST["p"];
    $limit = 'LIMIT ' . $nbr;
    unset($_POST["p"]);
}

if (!empty($_GET["adresse"])) {
    $_POST["adresse"] = $_GET["adresse"];
}
if (!empty($_GET["latlng"])) {
    $_POST["latlng"] = $_GET["latlng"];
}

if (empty($_POST["latlng"]) && !empty($_POST["adresse"])) {

    $lat = $bdd->prepare("SELECT um1.meta_key AS adresse_key, um1.meta_value AS adresse_value, um2.meta_key AS latitude_key, um2.meta_value AS latitude_value, um3.meta_key AS longitude_key, um3.meta_value AS longitude_value, um1.user_id FROM resho_usermeta AS um1 LEFT JOIN resho_usermeta AS um2 ON um1.user_id = um2.user_id AND um2.meta_key = 'latitude' LEFT JOIN resho_usermeta AS um3 ON um1.user_id = um3.user_id AND um3.meta_key = 'longitude' WHERE um1.meta_key LIKE 'adresse' AND um1.meta_value LIKE ? GROUP BY um1.user_id, um2.meta_value, um3.meta_value LIMIT 1");
    $lat->execute(['%' . $_POST["adresse"] . '%']);
    $lat_lng = $lat->fetch();
    if (!empty($lat_lng["latitude_value"])) {
        $_POST["latlng"] = $lat_lng["latitude_value"] . ',' . $lat_lng["longitude_value"];
    } else {

        $string = $_POST["adresse"];   //récduére la 1ère lettre en majuscule
        $words = preg_split('/\s+/', $string); // diviser la chaîne en mots
        $first_word = ucfirst(strtolower($words[0])); // extraire le premier mot et le réduire en majuscule

        $lat = $bdd->prepare("SELECT um1.meta_key AS adresse_key, um1.meta_value AS adresse_value, um2.meta_key AS latitude_key, um2.meta_value AS latitude_value, um3.meta_key AS longitude_key, um3.meta_value AS longitude_value, um1.user_id FROM resho_usermeta AS um1 LEFT JOIN resho_usermeta AS um2 ON um1.user_id = um2.user_id AND um2.meta_key = 'latitude' LEFT JOIN resho_usermeta AS um3 ON um1.user_id = um3.user_id AND um3.meta_key = 'longitude' WHERE um1.meta_key LIKE 'adresse' AND um1.meta_value LIKE ? GROUP BY um1.user_id, um2.meta_value, um3.meta_value LIMIT 1");
        $lat->execute(['%' . $first_word . '%']);
        $lat_lng = $lat->fetch();
        if (!empty($lat_lng) && !empty($lat_lng["latitude_value"]) && !empty($lat_lng["longitude_value"])) {
            $_POST["latlng"] = $lat_lng["latitude_value"] . ',' . $lat_lng["longitude_value"];

            $e1 = '<div class="alert alert-warning alert-dismissible">
                <strong>Précision</strong> Le résultat affiché, est peut-être erroné, car vous n\'avez pas validé la ville suggérée, alors nous avons ciblé sur <strong>' . $first_word . '</strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
}


if (!empty($profil_OK)) {
    include 'employer_connexion_et_droits.php';
 
    // Si la variable $cv_visible n'est pas définie, la définir avec une valeur par défaut
    if (!isset($cv_visible)) {
        $cv_visible = null;
    }



    if (!empty($sms_OK) && $sms_OK["CREDIT_SMS_RESTANT"] >= 1) {
        $maxChecked =   $sms_OK["CREDIT_SMS_RESTANT"];
    }
    if(!empty($_GET['selected_values'])){
        $autres = ' <input type="hidden" name="selected_values" value="'.$_GET['selected_values'].'">';
    }else if(!empty($_POST['selected_values'])){
        $autres = ' <input type="hidden" name="selected_values" value="'.$_POST["selected_values"].'">';
    }


    // Vérification de l'existence de la variable $_POST["selected_values"] et du nombre de candidats sélectionnés
    if (!empty($_POST["selected_values"]) && $maxChecked >= 1) {

        // Préparation pour l'envoi des SMS
        $selected_values = $_POST["selected_values"];
        $insert_count = 0; // Compteur d'insertions initialisé à 0

        // Récupération de la liste des identifiants de candidats à partir de la variable $_POST["selected_values"]
        $candidate_ids = explode(',', $selected_values);

        // Boucle pour l'insertion des enregistrements dans la table resho_sms_tracabilite pour chaque candidat sélectionné
        foreach ($candidate_ids as $candidate_id) {
            if (strpos($candidate_id, 'id:') === 0) {
                $candidate_id = substr($candidate_id, 3);
                $insert_sms = $bdd->prepare('INSERT INTO resho_sms_tracabilite 
          ( ID_USER_RECRUTEUR, ID_USER_CANDIDAT, SELECT_NUMBER_TEL, ENVOI_PROGRAMME ) 
          VALUES ( ?, ?, ?, ?)');
                $insert_sms->execute(array($profil_OK["ID"], $candidate_id, $_POST["selected_number"], $_POST["sms-datetime"]));
                $insert_count++; // Incrémentation du compteur d'insertions
            }
        }

        // Vérification du nombre d'insertions réussies
        if (!empty($insert_count)) {
            // Mise à jour de la table resho_sms
            $update_sms = $bdd->prepare('UPDATE resho_sms SET CREDIT_SMS_RESTANT = CREDIT_SMS_RESTANT - ?, DATE_DERNIERE_UTILISATION = ? WHERE ID = ?');
            $update_sms->execute(array($insert_count, date("Y-m-d H:i:s"), $sms_OK["ID"]));

            // Formatage de la date et de l'heure de départ des SMS en français
            $sms_datetime = $_POST["sms-datetime"];
            $sms_datetime_formatted = date('d/m/Y à H:i:s', strtotime($sms_datetime));

            // Message de confirmation pour l'utilisateur
            $alerte = ' <div class="alert alert-success alert-dismissible">
            <strong>SMS en route!</strong> 
            Les ' . $insert_count . ' SMS sont programmés à partir du ' . $sms_datetime_formatted . '.
            <br>Les candidats pourrons vous recontacter au ' . $_POST["selected_number"] . '.
        <br>    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        }
    }
}

if (!empty($_GET["a"])) {
    if ($_GET["a"] == 1) {
        $alerte = ' <div class="alert alert-warning alert-dismissible">
        <strong>Connexion obligatoire!</strong> 
        Afin de respecter chaque utilisateur, aucune information personnel est transmise sans connexion entreprise.
        <br><a href="login.php" target="_blank">Connexion</a>        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recherchez des candidats du monde entier sur CVDuMonde.">
    <meta name="keywords" content="recherche, candidats, CV, monde">
    <meta name="author" content="Votre nom ou le nom de votre entreprise">
    <title>Recherche de Candidats - CVDuMonde</title>
    <link rel="canonical" href="https://www.cvdumonde.com/candidate-list.php">

</head>
<?php
if (!empty($_GET["selected_values"])) {
    // ICI nous traitons la demande d'envois de SMS
    // Décomposer la chaîne de caractères en un tableau en utilisant la virgule comme séparateur
    $selected_values = explode(",", $_GET["selected_values"]);
    // Filtrer les éléments vides du tableau en utilisant la fonction array_filter
    $selected_values = array_filter($selected_values, function ($value) {
        // Retourner seulement les valeurs qui ne sont pas vides
        return $value !== "";
    });

    // Joindre les éléments du tableau en utilisant la virgule comme séparateur
    $placeholders = implode(",", array_fill(0, count($selected_values), "?"));

    // récupération des informations de l'expéditeur
    // le nom, le genre, le numéro de téléphone de l'entreprise 

    $donnees_expediteur_sms = $bdd->prepare("SELECT u.user_id,
        MAX(CASE WHEN meta_key LIKE 'genre' THEN meta_value END) AS genre,
        MAX(CASE WHEN meta_key LIKE 'last_name' THEN meta_value END) AS last_name,
        MAX(CASE WHEN meta_key LIKE 'first_name' THEN meta_value END) AS first_name,
        MAX(CASE WHEN meta_key LIKE 'telephone' THEN meta_value END) AS telephone
        FROM resho_usermeta u 
        WHERE u.user_id = ?
        GROUP BY u.user_id");
    $donnees_expediteur_sms->execute(array($profil_OK["ID"]));
    $donnees_expediteur_sms_OK = $donnees_expediteur_sms->fetch();

    $tel_expediteur = $donnees_expediteur_sms_OK["telephone"];
    $tel_expediteur_9_caracteres = substr($tel_expediteur, -9);

    $tel_choix = $bdd->prepare('SELECT DISTINCT(telephone) FROM resho_offres_emploi WHERE ID_USER = ? AND telephone NOT LIKE ?');
    $tel_choix->execute(array($profil_OK["ID"], '%' . $tel_expediteur_9_caracteres . '%'));
    $tel_choix_OK = $tel_choix->fetchAll();

    if (!empty($tel_choix_OK["telephone"])) {
        //affiche un numéros a séléctionner
        // dans un formulaire renvoyant toutes le données POST et GET
        ?>
<form id="FormSms" action="candidate-list.php" method="post">
    <?= $autres; ?>
    <h3 style="text-align: center; margin-top: 20px;">CVdumonde.com</h3>
    <div class="sms-section">
        <p>Bonjour [Genre du candidat] [Nom du candidat], nous avons consulté votre expérience.
            Nous aimerions échanger avec vous si vous êtes ouvert à une proposition d'emploi.
            Joignable au [numéro de téléphone]. Nous avons hâte de vous entendre ! - [Votre nom] </p>
        <label for="selected_number">Sélectionnez un numéro :</label>
        <select name="selected_number" id="selected_number">
            <option value="<?= $tel_expediteur ?>"><?= $tel_expediteur ?></option>
            <?php foreach ($tel_choix_OK as $number) : ?>
            <option value="<?= $number["telephone"] ?>"><?= $number["telephone"] ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Envoyer le(s) SMS">
    </div>
    <!-- <div class="camera-front"></div>
                    <div class="camera-back"></div> -->
    <div class="home-button"></div>
    <!-- <div class="power-button"></div> -->
</form>
<br>
<hr>
<br>
<?php
        } else {
           
        ?>
<h1 style="text-align: center; margin-top: 20px;">Voici le sms type que le candidat s'apprête à recevoir</h1>
<form id="FormSms" action="candidate-list.php" method="post">
    <h3 style="text-align: center; margin-top: 20px;">CVdumonde.com</h3>
    <div class="sms-section-fini">
        <p>Bonjour [Genre du candidat] [Nom du candidat], nous avons consulté votre expérience. Nous aimerions échanger
            avec vous si vous êtes ouvert à une proposition d'emploi. Joignable au <?= $tel_expediteur ?>. Nous avons
            hâte de vous entendre ! <br>Cdlt
            <strong><?= $donnees_expediteur_sms_OK["genre"] . ' ' . $donnees_expediteur_sms_OK["last_name"] . ' ' . $donnees_expediteur_sms_OK["first_name"]; ?></strong>
        </p>
        <input type="hidden" name="selected_number" value="<?= $tel_expediteur ?>">
        <label for="sms-datetime">Heure d'envoi:</label>
        <?php
                date_default_timezone_set('Europe/Paris');
                $currentDateTime = date('Y-m-d\TH:i');
            ?>
        <input type="datetime-local" id="sms-datetime" name="sms-datetime" value="<?= $currentDateTime ?>">
        <?= $autres; ?>

        <input type="submit" value="Envoyer le(s) SMS">
    </div>
    <!-- <div class="camera-front"></div>
        <div class="camera-back"></div> -->
    <div class="home-button"></div>
    <!-- <div class="power-button"></div> -->
</form>
<br>
<hr>
<br>
<?php
    }
    // le numéro de téléphone des offres d\'emplois ainsi que leurs ID
    // en cas de plusieurs numéros, fair choisir le numéro sur lequel il souhaite être rappelé
}

if (!empty($_GET["manque_contrat"])) {
    $alerte = ' <div class="alert alert-warning alert-dismissible">
    <strong>Contrat obligatoire!</strong> 
    Afin de d\'ajouter ce candidat dans votre liste de contact, nous devons lui associer une proposition de contrat.
<br>    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}

if (!empty($_POST["d"])) {
    if ($_POST["d"] == 1) {
        $d = "+ Proche";
        $filtrer = "&filtre=ORDER BY DISTANCE ASC";
        $filtration = "ORDER BY DISTANCE ASC";
    } elseif ($_POST["d"] == 2) {
        $d = "- Proche";
        $filtrer = "&filtre=ORDER BY DISTANCE DESC";
        $filtration  = "ORDER BY DISTANCE DESC";
    } elseif ($_POST["d"] == 3) {
        $d = "+ Expérience";
        $filtrer = "&filtre=ORDER BY experience ASC";
        $filtration  = "ORDER BY experience ASC";
    } elseif ($_POST["d"] == 4) {
        $d = "- Expérience";
        $filtrer = "&filtre=ORDER BY experience DESC";
        $filtration  = "ORDER BY experience DESC";
    } elseif ($_POST["d"] == 5) {
        $d = "- Salaire";
        $filtrer = "&filtre=ORDER BY salaire_souhaite ASC";
        $filtration  = "ORDER BY salaire_souhaite ASC";
    } elseif ($_POST["d"] == 6) {
        $d = "+ Salaire";
        $filtrer = "&filtre=ORDER BY salaire_souhaite DESC";
        $filtration  = "ORDER BY salaire_souhaite DESC";
    }
}

if (!empty($_GET["search_Metiers"])) {
    $_POST["search_Metiers"] = $_GET["search_Metiers"];
}

if(!empty($_GET["latlng"])){
    $_POST["latlng"] = $_GET["latlng"];
}
if(!empty($_GET["adresse"])){
    $_POST["adresse"] = $_GET["adresse"];
}
// exit();
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $url = "https";
else
    $url = "http";

// Ajoutez // à l'URL.
$url .= "://";

// Ajoutez l'hôte (nom de domaine, ip) à l'URL.
$url .= $_SERVER['HTTP_HOST'];

// Ajouter l'emplacement de la ressource demandée à l'URL
$url .= $_SERVER['REQUEST_URI'];

// Afficher l'URL
if (isset($url)) {
    if (strpos($url, 'dist_p=1&c=') === false) {
        if (strpos($url, '&c=1') !== false) {
            $url = str_replace("&c=1", "",  $url);
            header("Location: $url");
            exit;
        } else if (strpos($url, '&c=2') !== false) {
            $url = str_replace("&c=2", "",  $url);
            header("Location: $url");
            exit;
        } else if (strpos($url, '&c=3') !== false) {
            $url = str_replace("&c=3", "",  $url);
            header("Location: $url");
            exit;
        } else if (strpos($url, '&c=4') !== false) {
            $url = str_replace("&c=4", "",  $url);
            header("Location: $url");
            exit;
        } else if (strpos($url, '&c=5') !== false) {
            $url = str_replace("&c=5", "",  $url);
            header("Location: $url");
            exit;
        } else if (strpos($url, '&c=6') !== false) {
            $url = str_replace("&c=6", "",  $url);
            header("Location: $url");
            exit;
        } else if (strpos($url, '&c=7') !== false) {
            $url = str_replace("&c=7", "",  $url);
            header("Location: $url");
            exit;
        }
    }
}

if (!empty($_POST["choix"]) && $_POST["choix"] == '2') {

    $_POST["btn_search_cv"] = '2';
}

if (!empty($_POST["category"]) or !empty($_POST["category_bis"])) {
    $categorie_contrat = $_POST["category"];
    if (empty($categorie_contrat)) {
        $categorie_contrat =  $_POST["category_bis"];
    }

    if ($categorie_contrat == 1) {
        $c = 'CDI';
    } elseif ($categorie_contrat == 2) {
        $c = 'CDD';
    } elseif ($categorie_contrat == 3) {
        $c = 'Interim';
    } elseif ($categorie_contrat == 4) {
        $c = 'Freelance';
    } elseif ($categorie_contrat == 5) {
        $c = 'Extra';
    } elseif ($categorie_contrat == 6) {
        $c = 'Apprentissage';
    } elseif ($categorie_contrat == 7) {
        $c = 'Stage';
    }
    // Récupération des données du formulaire
    $get = $get . '&c=' . (int)$categorie_contrat;
    $and = "AND type_contrat LIKE '%" . $c . "%'";
    $metier_search = $_POST['search_Metiers'];
    $job = "&type_contrat=" . $c;
}

if (!empty($_POST["g"])) {
    if ($_POST["g"] == 1) {
        $g = 'Homme';
        $get = $get . '&g=' . (int)$_POST["g"];
        $and = $and . " AND genre LIKE '%Monsieur%'";
        $metier_search = $_POST['search_Metiers'];
        $job = "&genre=Monsieur";
    } elseif ($_POST["g"] == 2) {
        $g = 'Femme';
        $get = $get . '&g=' . (int)$_POST["g"];
        $and = $and . " AND genre LIKE '%Madame%'";
        $metier_search = $_POST['search_Metiers'];
        $job = "&genre=Madame";
    } elseif ($_POST["g"] == 3) {
        $and = $and . " AND genre IS NOT NULL";
        $metier_search = $_POST['search_Metiers'];
    }
}

if (!empty($_GET["distance_max"])) {
    $_POST["distance_max"] = $_GET["distance_max"];
}
if (!empty($_GET["choix"])) {
    $_POST["choix"] = $_GET["choix"];
}
if (!empty($_GET["btn_search_cv"])) {
    $_POST["btn_search_cv"] = $_GET["btn_search_cv"];
}

if (isset($_POST['search_Metiers'])) {
    // Récupération des données du formulaire
    $metier_search = $_POST['search_Metiers'];
    $job = "?job=" . $metier_search . $filtrer . $job;
}

if (!empty($_GET["demenage"]) or !empty($_POST["demenage"])) {
    $adresse = 'France';
    $_POST["adresse"] = $adresse;
    $adresse = addslashes($adresse);
    $get = $get . '&adresse=' . $adresse;
    $_POST["distance_max"] = 5000;
}
if (empty($_POST["distance_max"])) {
    $_POST["distance_max"] = 50;
}

if (!empty($_POST["search_Metiers"])) {
    $metier_search = $_POST["search_Metiers"];
    $get = $get . '&search_Metiers=' . $metier_search;
}
if (!empty($_POST["adresse"])) {
    $adresse = $_POST["adresse"];
    $adresse = addslashes($adresse);
    $get = $get . '&adresse=' . $adresse;
}

if (!empty($_POST["latlng"])) {
    $latlng = $_POST["latlng"];
    $get = $get . '&latlng=' . $latlng;
}
if (!empty($_POST["distance_max"])) {
    $distance = $_POST["distance_max"];
    $get = $get . '&distance_max=' .  $distance;
}
if (!empty($_POST["choix"])) {
    $choix = $_POST["choix"];
    $get = $get . '&choix=' .  $choix;
}
if (isset($_POST["btn_search_cv"])) {
    $_POST["btn_search_cv"] = 1;
    $btn_search_cv = $_POST["btn_search_cv"];
    $get = $get . '&btn_search_cv=' .  $btn_search_cv;
}

if ((isset($_POST['btn_search_cv']) or !empty($_POST['btn_search_cv']))  && empty($e1)) {
    // Récupération de l'URL du lien
    $result = $metier_search;
    $string = $metier_search;
    $result = substr($string, 0, strpos($string, ' /'));
    if (empty($result)) {
        $result = $metier_search;
    }
}

if (!empty($metier_search)) {
    $string = $metier_search;
    if (mb_strpos($string, ' /') !== false) {
        $parts = explode(" /", $string);
        $string = $parts[0];
    }
    $string = mb_substr($string, 0, 15);
} else {
    $string = "";
}

if (isset($_SESSION["auth"]["user_status"]) && $_SESSION["auth"]["user_status"] == 2) {
    header("Location: candidate-dashboard.php");
    exit;
} elseif (isset($_SESSION["auth"]["user_status"]) && $_SESSION["auth"]["user_status"] == 4) {

    require 'employer_connexion_et_droits.php';
}

if (!empty($_GET["e"]) && $_GET["e"] == 1) {
    $e1 = '<div class="alert alert-success alert-dismissible">
    <strong>Success!</strong> Votre compte est fonctionnel.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
} elseif (!empty($_GET["e"]) && $_GET["e"] == 2) {
    $e1 = '<div class="alert alert-danger alert-dismissible">
    Votre compte à mis trop de temps pour valider l\'email.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}

if (!empty($_POST["distance_max"])) {
    $distance_max = 'à ' . $_POST["distance_max"] . ' KM.';
    $max_distance = (int)$_POST["distance_max"];
}

if (!empty($_POST["dist_p"])) {

    $max_distance = (int)$_POST["distance_max"] + 10;
    $distance_max = 'à ' . $max_distance . ' KM.';
    $get = $get . '&distance_max=' .  $max_distance;
    $_POST["distance_max"]= $max_distance ;
}

if (!empty($_GET["dist_p"])) {

    $max_distance = (int)$_POST["distance_max"] + 10;
    $distance_max = 'à ' . $max_distance . ' KM.';
    $get = $get . '&distance_max=' .  $max_distance;
}
if (!empty($_POST)) {
    foreach ($_POST as $key => $values) {
        $autres .= '<input type="hidden" name="' . $key . '" value="' . $values . '">';
    }
}
if (!empty($_POST)) {
    foreach ($_POST as $key => $values) {
        $autres .= '<input type="hidden" name="' . $key . '" value="' . $values . '">';
    }
}

if (!empty($_GET)) {
    foreach ($_GET as $key => $values) {
        $autres .= '<input type="hidden" name="' . $key . '" value="' . $values . '">';
    }
}

if (empty($latlng) && !empty($max_distance)) {


    if (!empty($_POST["search_Metiers"])) {
        $e1 = 1;
        $e1 = '<div class="alert alert-danger alert-dismissible">
    Nous ne pouvons pas traiter votre recherche sans le secteur géographique précis.<br> Merci de valider le secteur géographique parmi les champs suggérés .
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
    }
}

if (!empty($adresse)) {
    if (!empty($latlng)) {
        $parts = explode(",", $latlng);

        // Récupération du premier élément du tableau (ce qui se trouve avant la première virgule)
        $gps_contructor = $parts[0];
        $gps_contructor_deux = $parts[1];
        $pas_de_click_sur_adresse = "";
    } else {
        $geoloc = $bdd->prepare(" SELECT um.user_id,
        COALESCE(l.latitude, '') AS latitude,
        COALESCE(lo.longitude, '') AS longitude,
        COALESCE(a.adresse, '') AS adresse,
        COALESCE(v.ville, '') AS ville
        FROM resho_usermeta um LEFT JOIN (
            SELECT user_id, meta_value AS latitude
            FROM resho_usermeta
            WHERE meta_key = 'latitude'
        ) l ON l.user_id = um.user_id    LEFT JOIN (
            SELECT user_id, meta_value AS longitude
            FROM resho_usermeta
            WHERE meta_key = 'longitude'
        ) lo ON lo.user_id = um.user_id   LEFT JOIN (
            SELECT user_id, meta_value AS adresse
            FROM resho_usermeta
            WHERE meta_key = 'adresse'
        ) a ON a.user_id = um.user_id   LEFT JOIN (
            SELECT user_id, meta_value AS ville
            FROM resho_usermeta
            WHERE meta_key = 'ville'
        ) v ON v.user_id = um.user_id
        WHERE (um.meta_key = 'ville' AND um.meta_value = ?) OR ( um.meta_key = 'adresse' AND um.meta_value = ? ) AND latitude !=''
        GROUP BY um.user_id LIMIT 1 ");
        $geoloc->execute(array($adresse, $adresse));
        $geolocalisation = $geoloc->fetch();
        // Création du point gps sur adresse non validée manuellement
        if (!empty($geolocalisation)) {
            $gps_contructor = $geolocalisation["latitude"];
            $gps_contructor_deux = $geolocalisation["longitude"];
        }

        $pas_de_click_sur_adresse = 1;
    }

    // Récupération des données du formulaire
    $adresse = $adresse;
    $job = $job . "&gps=1&lat=" . $gps_contructor . "&lng=" . $gps_contructor_deux . "&distance_max=" . $max_distance;
}

$retourne = $bdd->prepare("SELECT COUNT(DISTINCT um.user_id) AS nb_users 
 FROM resho_usermeta um 
 JOIN resho_usermeta m ON m.user_id = um.user_id 
 AND m.meta_key LIKE ? 
 AND m.meta_value LIKE ? 
 WHERE um.meta_key LIKE '%zone%' AND (um.meta_value LIKE 'France' OR um.meta_value LIKE 'monde' OR um.meta_value LIKE '%" . $adresse . "%'  )
 ");
$retourne->execute(array("%metier%", "%" . $result . "%"));
$nombre_candidat = $retourne->fetch();


$num_candidate = 0;
if (!empty($nombre_candidat["nb_users"]) && $nombre_candidat["nb_users"] >= 1 && (empty($_GET["demenage"]) or empty($_POST["demenage"]))) {
    $num_candidate = $nombre_candidat["nb_users"];
}

if (empty($e) && !empty($gps_contructor) && !empty($gps_contructor_deux) && !empty($result) && !empty($max_distance)) {

    if (empty($_GET["demenage"]) && empty($_POST["demenage"])) {
        //retourne le resultat de la recherche

        $resultat_candidat_count = $bdd->query("SELECT  um.user_id,
        MAX(CASE WHEN um.meta_key = 'cv' THEN um.meta_value END) AS cv,
        MAX(CASE WHEN um.meta_key = 'photo' THEN um.meta_value END) AS photo,
        MAX(CASE WHEN um.meta_key = 'realisation_1' THEN um.meta_value END) AS realisation_1,
        MAX(CASE WHEN um.meta_key = 'realisation_2' THEN um.meta_value END) AS realisation_2,
        MAX(CASE WHEN um.meta_key = 'realisation_3' THEN um.meta_value END) AS realisation_3,
        MAX(CASE WHEN um.meta_key = 'latitude' THEN um.meta_value END) AS latitude,
        MAX(CASE WHEN um.meta_key = 'longitude' THEN um.meta_value END) AS longitude,
        MAX(CASE WHEN um.meta_key = 'youtube' THEN um.meta_value END) AS youtube,
        MAX(CASE WHEN um.meta_key = 'github' THEN um.meta_value END) AS github,
        MAX(CASE WHEN um.meta_key = 'linkedin' THEN um.meta_value END) AS linkedin,
        MAX(CASE WHEN um.meta_key = 'tiktok' THEN um.meta_value END) AS tiktok,
        MAX(CASE WHEN um.meta_key = 'instagram' THEN um.meta_value END) AS instagram,
        MAX(CASE WHEN um.meta_key = 'twitter' THEN um.meta_value END) AS twitter,
        MAX(CASE WHEN um.meta_key = 'facebook' THEN um.meta_value END) AS facebook,
        MAX(CASE WHEN um.meta_key = 'salaire_souhaite' THEN um.meta_value END) AS salaire_souhaite,
        MAX(CASE WHEN um.meta_key = 'dispo_jours_extra' THEN um.meta_value END) AS dispo_jours_extra,
        MAX(CASE WHEN um.meta_key = 'salaire_souhaite_extra' THEN um.meta_value END) AS salaire_souhaite_extra,
        MAX(CASE WHEN um.meta_key = 'type_contrat' THEN um.meta_value END) AS type_contrat,
        MAX(CASE WHEN um.meta_key = 'genre' THEN um.meta_value END) AS genre,
        MAX(CASE WHEN um.meta_key = 'metier_3' THEN um.meta_value END) AS metier_3,
        MAX(CASE WHEN um.meta_key = 'metier_2' THEN um.meta_value END) AS metier_2,
        MAX(CASE WHEN um.meta_key = 'metier_1' THEN um.meta_value END) AS metier_1,
        MAX(CASE WHEN um.meta_key = 'zone_travail3' THEN um.meta_value END) AS zone_travail3,
        MAX(CASE WHEN um.meta_key = 'zone_travail2' THEN um.meta_value END) AS zone_travail2,
        MAX(CASE WHEN um.meta_key = 'zone_travail1' THEN um.meta_value END) AS zone_travail1,
        MAX(CASE WHEN um.meta_key = 'adresse' THEN um.meta_value END) AS adresse,
        MAX(CASE WHEN um.meta_key = 'telephone' THEN um.meta_value END) AS telephone,
        MAX(CASE WHEN um.meta_key = 'user_email' THEN um.meta_value END) AS user_email,
        MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) AS first_name,
        MAX(CASE WHEN um.meta_key = 'last_name' THEN um.meta_value END) AS last_name,
        MAX(CASE WHEN um.meta_key = 'latitude_zone_travail1' THEN um.meta_value END) AS latitude_zone_travail1,
        MAX(CASE WHEN um.meta_key = 'longitude_zone_travail1' THEN um.meta_value END) AS longitude_zone_travail1,
        MAX(CASE WHEN um.meta_key = 'latitude_zone_travail2' THEN um.meta_value END) AS latitude_zone_travail2,
        MAX(CASE WHEN um.meta_key = 'longitude_zone_travail2' THEN um.meta_value END) AS longitude_zone_travail2,
        MAX(CASE WHEN um.meta_key = 'latitude_zone_travail3' THEN um.meta_value END) AS latitude_zone_travail3,
        MAX(CASE WHEN um.meta_key = 'longitude_zone_travail3' THEN um.meta_value END) AS longitude_zone_travail3,
        MAX(CASE WHEN um.meta_key = 'experience' THEN um.meta_value END) AS experience,
        MAX(CASE WHEN um.meta_key = 'date_inscription' THEN um.meta_value END) AS date_inscription, 
        COALESCE(MAX(CASE WHEN um.meta_key LIKE '%metier%' AND um.meta_value LIKE '%" . $result . "%' THEN um.meta_value END), '') AS metier,
        (6371 * acos(
        cos(radians($gps_contructor))
        * cos(radians(MAX(CASE WHEN um.meta_key = 'latitude' THEN um.meta_value END)))
        * cos(radians(MAX(CASE WHEN um.meta_key = 'longitude' THEN um.meta_value END)) - radians($gps_contructor_deux))
        + sin(radians($gps_contructor))
        * sin(radians(MAX(CASE WHEN um.meta_key = 'latitude' THEN um.meta_value END)))
        )) AS distance,
        (6371 * acos(
        cos(radians($gps_contructor))
        * cos(radians(MAX(CASE WHEN um.meta_key = 'latitude_zone_travail1' THEN um.meta_value END)))
        * cos(radians(MAX(CASE WHEN um.meta_key = 'longitude_zone_travail1' THEN um.meta_value END)) - radians($gps_contructor_deux))
        + sin(radians($gps_contructor))
        * sin(radians(MAX(CASE WHEN um.meta_key = 'latitude_zone_travail1' THEN um.meta_value END)))
        )) AS distance_zone_travail1,
        (6371 * acos(
        cos(radians($gps_contructor))
        * cos(radians(MAX(CASE WHEN um.meta_key = 'latitude_zone_travail2' THEN um.meta_value END)))
        * cos(radians(MAX(CASE WHEN um.meta_key = 'longitude_zone_travail2' THEN um.meta_value END)) - radians($gps_contructor_deux))
        + sin(radians($gps_contructor))
        * sin(radians(MAX(CASE WHEN um.meta_key = 'latitude_zone_travail2' THEN um.meta_value END)))
        )) AS distance_zone_travail2,
        (6371 * acos(
        cos(radians($gps_contructor))
        * cos(radians(MAX(CASE WHEN um.meta_key = 'latitude_zone_travail3' THEN um.meta_value END)))
        * cos(radians(MAX(CASE WHEN um.meta_key = 'longitude_zone_travail3' THEN um.meta_value END)) - radians($gps_contructor_deux))
        + sin(radians($gps_contructor))
        * sin(radians(MAX(CASE WHEN um.meta_key = 'latitude_zone_travail3' THEN um.meta_value END)))
        )) AS distance_zone_travail3  
        FROM resho_usermeta um
        WHERE um.meta_key IN ('latitude', 'longitude', 'latitude_zone_travail1', 'longitude_zone_travail1',
        'latitude_zone_travail2', 'longitude_zone_travail2', 'latitude_zone_travail3', 'longitude_zone_travail3',
        'cv','photo','realisation_1','realisation_2','realisation_3', 'youtube','github','linkedin','tiktok',
        'instagram','twitter','facebook','salaire_souhaite','dispo_jours_extra','salaire_souhaite_extra',
        'type_contrat','metier_3','metier_2','metier_1','zone_travail3','zone_travail2','zone_travail1','adresse',
        'telephone','user_email','first_name','last_name','experience', 'date_inscription','genre')
        OR (um.meta_key LIKE '%metier%' AND um.meta_value LIKE '%" . $result . "%' ) 
        GROUP BY um.user_id HAVING metier != '' " . $and . " AND ( distance <= " . $max_distance . " OR distance_zone_travail1 <= " . $max_distance . " OR distance_zone_travail2 <= " . $max_distance . " OR distance_zone_travail3 <= " . $max_distance . " )
         " . $filtration . " ");
    } else {
        //retourne le resultat des candidats prêts a déménager


        $resultat_candidat_count = $bdd->prepare("SELECT um.user_id,
          MAX(CASE WHEN um.meta_key = 'cv' THEN um.meta_value END) AS cv,
        MAX(CASE WHEN um.meta_key = 'photo' THEN um.meta_value END) AS photo,
        MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) AS first_name,
        MAX(CASE WHEN um.meta_key = 'last_name' THEN um.meta_value END) AS last_name,
        MAX(CASE WHEN um.meta_key = 'user_email' THEN um.meta_value END) AS user_email,
        MAX(CASE WHEN um.meta_key = 'adresse' THEN um.meta_value END) AS adresse,
        MAX(CASE WHEN um.meta_key = 'latitude' THEN um.meta_value END) AS latitude,
        MAX(CASE WHEN um.meta_key = 'longitude' THEN um.meta_value END) AS longitude,
        MAX(CASE WHEN um.meta_key = 'metier_1' THEN um.meta_value END) AS metier_1,
        MAX(CASE WHEN um.meta_key = 'metier_2' THEN um.meta_value END) AS metier_2,
        MAX(CASE WHEN um.meta_key = 'metier_3' THEN um.meta_value END) AS metier_3,
        MAX(CASE WHEN um.meta_key = 'realisation_1' THEN um.meta_value END) AS realisation_1,
        MAX(CASE WHEN um.meta_key = 'realisation_2' THEN um.meta_value END) AS realisation_2,
        MAX(CASE WHEN um.meta_key = 'realisation_3' THEN um.meta_value END) AS realisation_3,
        MAX(CASE WHEN um.meta_key = 'zone_travail3' THEN um.meta_value END) AS zone_travail3,
        MAX(CASE WHEN um.meta_key = 'zone_travail2' THEN um.meta_value END) AS zone_travail2,
        MAX(CASE WHEN um.meta_key = 'zone_travail1' THEN um.meta_value END) AS zone_travail1,
        MAX(CASE WHEN um.meta_key = 'experience' THEN um.meta_value END) AS experience,
        MAX(CASE WHEN um.meta_key = 'date_inscription' THEN um.meta_value END) AS date_inscription, 
        MAX(CASE WHEN um.meta_key = 'linkedin' THEN um.meta_value END) AS linkedin,
        MAX(CASE WHEN um.meta_key = 'github' THEN um.meta_value END) AS github,
        MAX(CASE WHEN um.meta_key = 'twitter' THEN um.meta_value END) AS twitter,
        MAX(CASE WHEN um.meta_key = 'instagram' THEN um.meta_value END) AS instagram,
        MAX(CASE WHEN um.meta_key = 'facebook' THEN um.meta_value END) AS facebook,
        MAX(CASE WHEN um.meta_key = 'salaire_souhaite' THEN um.meta_value END) AS salaire_souhaite,
        MAX(CASE WHEN um.meta_key = 'dispo_jours_extra' THEN um.meta_value END) AS dispo_jours_extra,
        MAX(CASE WHEN um.meta_key = 'salaire_souhaite_extra' THEN um.meta_value END) AS salaire_souhaite_extra,
        MAX(CASE WHEN um.meta_key = 'type_contrat' THEN um.meta_value END) AS type_contrat,
        MAX(CASE WHEN um.meta_key = 'adresse' THEN um.meta_value END) AS adresse,
        MAX(CASE WHEN um.meta_key = 'telephone' THEN um.meta_value END) AS telephone,
        MAX(CASE WHEN um.meta_key = 'genre' THEN um.meta_value END) AS genre
        FROM resho_usermeta um
        JOIN resho_usermeta m ON m.user_id = um.user_id
        AND m.meta_key LIKE ?
        AND m.meta_value LIKE ?
        WHERE um.meta_key IN ('cv','photo',
        'first_name', 'last_name', 'user_email', 'adresse', 'latitude', 'longitude',
        'metier_1', 'metier_2', 'metier_3', 'realisation_1', 'realisation_2', 'realisation_3','zone_travail1'
        ,'zone_travail2','zone_travail','experience', 'date_inscription','linkedin', 'github', 'twitter','instagram','facebook','salaire_souhaite','dispo_jours_extra',
        'salaire_souhaite_extra','type_contrat','adresse','telephone','genre')
        GROUP BY um.user_id HAVING metier_1 LIKE '%" . $result . "%' 
        OR metier_2 LIKE '%" . $result . "%' 
        OR metier_3 LIKE '%" . $result . "%' ");
        $resultat_candidat_count->execute(array("%metier%", "%" . $result . "%"));
    }

    $resultat_candidat_count_OK =   $resultat_candidat_count->fetchAll();

    if (!empty($resultat_candidat_count_OK)) {
        $count_position = count($resultat_candidat_count_OK);
    }
    if ($nbr >= $count_position) {
        $nbr = $count_position;
    }

    if (!empty($resultat_candidat_count_OK)) {

        foreach ($resultat_candidat_count_OK as $row) {

            if (!empty($row)) {
                $first_name = "";
                $last_name = "";
                $type_contrat = "";
                $metier = "";
                $salaire_souhaite = "";
                $zone = "";
                // var_me($row["user_id"], $ip_address);

                if (!empty($row["type_contrat"])) {
                    $type_contrat = $row["type_contrat"];
                }

                if (!empty($row["salaire_souhaite"])) {
                    $salaire_souhaite = $row["salaire_souhaite"];
                }
                if (!empty($row["metier_1"])) {
                    $metier = $row["metier_1"];
                }
                if (!empty($row["metier_2"])) {
                    $metier = $metier . ' | ' . $row["metier_2"];
                }
                if (!empty($row["metier_3"])) {
                    $metier = $metier . ' | ' . $row["metier_3"];
                }

                if (!empty($row["zone_travail1"])) {
                    $zone = $row["zone_travail1"];
                }
                if (!empty($row["experience"])) {
                    $experience = $row["experience"];
                }
                if (!empty($row["experience"]) && !empty($row["date_inscription"])) {

                    $date_inscription = substr($row["date_inscription"], -4);
                    $exp_plus = (int)$today - (int)$date_inscription;
                    $experience = (int)$row["experience"] + $exp_plus;
                }

                if (!empty($row["last_name"])) {
                    $last_name = $row["last_name"];
                }
                if (!empty($row["first_name"])) {
                    $first_name = $row["first_name"];
                }
                $cv = '';
                $contact_par_mail='';
                $contact_par_tel = '';
                $social = "";
                if(empty($cv_visible)){
                    $cv = 'employer-packages.php?c=manquant';

                    $contact_par_mail =  '<a href="employer-packages.php?c=manquant" class="butn butn-md" target="_blank">Email</a>';
    
                    $contact_par_tel =  '   <a href="employer-packages.php?c=manquant" class="butn butn-md" target="_blank">Appeler </a>';
           
                }
     
    
             if(!empty($row["cv"]) && !empty($cv_visible)) {         
                    // Vérifier si le fichier CV existe
                       
                        $cv = $row["cv"]; // Supposons que $row["cv"] contient le chemin du fichier
                        
                        if (file_exists($cv)) {
                            // Le fichier existe, vous pouvez continuer à l'utiliser
                            // Faites ce que vous devez faire avec le fichier ici
                        } else {
                            // Le fichier n'existe pas, vous pouvez gérer cette situation en conséquence
                            $cv = $row["cv"];
                        }            
                                  
                }

                if (!empty($row["user_email"]) && !empty($cv_visible)) {


                    $email = filter_var($row["user_email"], FILTER_VALIDATE_EMAIL);
                    if (!$email) {
                        // traitement d'erreur si l'email n'est pas valide

                    } else {


                        $subject = "Opportunité d'emploi pour " . $row["genre"] . " " . $row["last_name"] . " " . $row["first_name"] . " via CVdumonde.com chez [NOTRE ETABLISSEMENT]";
                        $subject = str_replace(" ", "%20", $subject);

                        $body = "Bonjour " . $row["genre"] . " " . $row["last_name"] . " " . $row["first_name"] . ",\n\nNous avons été attirés par votre profil et souhaitons vous proposer une opportunité d'emploi pour le poste de " . $_POST["search_Metiers"] . "\n\nVeuillez nous contacter rapidement pour plus de détails sur cette opportunité voir [URL OFFRE D EMPLOI]. Nous sommes impatients de vous rencontrer pour discuter de votre parcours.\n\nCordialement,\n\n[Nom de l'entreprise]";

                        $body = str_replace("\r\n", "<br>", $body);
                        $body = str_replace(" ", "%20", $body);

                        $contact_par_mail = '<a href="mailto:' . $email . '?subject=' . $subject . '&body=' . $body . '&importance=high" class="butn butn-md" target="_blank">Email </a>';
                        $contact_par_mail .=  '<p>'.$email.'</p>';
                    }
                    
                }

                if (!empty($row["telephone"]) && !empty($cv_visible)) {
                   
                    $contact_par_tel =  '   <a href="tel:' . $row["telephone"] . '" class="butn butn-md" target="_blank">Appeler </a>';
                    $contact_par_tel .= '<p>0'.$row["telephone"].'</p>';
                }
             

                if (!empty($_SESSION["auth"]["user_status"]) && $_SESSION["auth"]["user_status"] == 4) {
                    $cv = $cv;
                    $first_name =  $first_name;
                    $last_name = $last_name;
                } else {
                    $cv = 'login.php?w=2';
                    $first_name = substr($first_name, 0, 1) . '.'; // f
                    $last_name  = substr($last_name, 0, 1) . '.'; // f
                }

                $non_empty_values = array_filter($row);
                $count = count($non_empty_values);

                if ($count <= 14) {
                    $etoile_count = 1;
                    $etoile =  '<i class="fas fa-star"></i>';
                    $etoile_count_conseil = 'Boostez votre visibilité en remplissant davantage votre profil !';
                } elseif ($count >= 14 && $count <= 26) {
                    $etoile_count = 2;
                    $etoile =  '<i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>';
                    $etoile_count_conseil = 'Boostez votre visibilité en remplissant davantage votre profil !';
                } elseif ($count > 26 && $count <= 40) {
                    $etoile_count = 3;
                    $etoile =  '<i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>';
                    $etoile_count_conseil = 'Boostez votre visibilité en remplissant davantage votre profil !';
                } elseif ($count > 40 && $count <= 53) {
                    $etoile_count = 4;
                    $etoile =  '<i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>';
                } elseif ($count > 53) {
                    $etoile_count = 5;
                    $etoile =  '<i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>';
                }
                $photo = '';
                $photo_image = '';
                $realisations = '';
               
                if (!empty($row["realisation_1"]) or !empty($row["realisation_2"]) or !empty($row["realisation_3"])) {
                    if (!empty($row["realisation_1"])) {
                        $realisations = '  <div class="col-md-6 col-lg-4 mt-1-9">
                            <a href="' . $row["realisation_1"] . '" class="realisation-link" data-image="' . $row["realisation_1"] . '"><img src="' . $row["realisation_1"] . '" style="width: 200px; height: 150px;" class="border-radius-10 realisation-image" alt="..." loading="lazy"></a>
                        </div>';
                    }
                    if (!empty($row["realisation_2"])) {
                        $realisations = $realisations . '  <div class="col-md-6 col-lg-4 mt-1-9">
                            <a href="' . $row["realisation_2"] . '" class="realisation-link" data-image="' . $row["realisation_2"] . '"><img src="' . $row["realisation_2"] . '" style="width: 200px; height: 150px;" class="border-radius-10 realisation-image" alt="..." loading="lazy"></a>
                        </div>';
                    }
                    if (!empty($row["realisation_3"])) {
                        $realisations = $realisations . '  <div class="col-md-6 col-lg-4 mt-1-9">
                            <a href="' . $row["realisation_3"] . '" class="realisation-link" data-image="' . $row["realisation_3"] . '"><img src="' . $row["realisation_3"] . '" style="width: 200px; height: 150px;" class="border-radius-10 realisation-image" alt="..." loading="lazy"></a>
                        </div>';
                    }
                  

                    $social = "";

                    $socialNetworks = [
                        "linkedin" => '<i class="fab fa-linkedin-in"></i>',
                        "facebook" => '<i class="fab fa-facebook-f"></i>',
                        "twitter" => '<i class="fab fa-twitter"></i>',
                        "instagram" => '<i class="fab fa-instagram"></i>',
                        "tiktok" => '<i class="fab fa-tiktok"></i>',
                        "github" => '<i class="fab fa-github"></i>',
                        "youtube" => '<i class="fa-brands fa-youtube"></i>'
                    ];

                    foreach ($socialNetworks as $network => $icon) {
                        if (!empty($row[$network])) {
                            if (!empty($_SESSION["auth"]["user_status"]) && $_SESSION["auth"]["user_status"] == 4) {
                                $social .= '<a href="' . $row[$network] . '" target="_blank">' . $icon . '</a> ';
                            } else {
                                $social .= '<a href="login.php?w=2" target="_blank">' . $icon . '</a> ';
                            }
                        }
                    }

                    if (!empty($social)) {
                        $social = '<div class="social-links">' . $social . '</div>';
                    }

                }
               
             
                

                if (!empty($row["photo"])) {
                    $chemin_du_fichier = $row["photo"];
                
                    if (file_exists($chemin_du_fichier)) {
                        // Le fichier de la photo existe localement, vous pouvez procéder avec vos opérations.
                        // Assurez-vous que $chemin_du_fichier est un chemin absolu.
                    } else {
                        // Le fichier de la photo n'existe pas localement.
                        // Vérifiez si le fichier existe sur le serveur distant.
                        $chemin_distant = 'https://www.cvdumonde.com/' . $row["photo"];
                        
                        if (!empty($chemin_distant)) {
                            $headers = @get_headers($chemin_distant);
                            if ($headers && stripos($headers[0], "200 OK") !== false) {
                                // Le fichier existe à l'URL distante, vous pouvez procéder.
                                $chemin_du_fichier = $chemin_distant;
                            } else {
                                // Essayer avec une autre URL
                                $chemin_distant = 'https://' . $row["photo"];
                                $headers = @get_headers($chemin_distant);
                                if ($headers && stripos($headers[0], "200 OK") !== false) {
                                    // Le fichier existe à l'URL distante, vous pouvez procéder.
                                    $chemin_du_fichier = $chemin_distant;
                                } else {
                                    // Le fichier n'existe pas à l'URL distante, envoyez un email.
                                    $to = 'mikaduweb@gmail.com'; // Adresse e-mail du destinataire
                                    $subject = 'Le fichier de la photo est introuvable'; // Sujet de l'email
                                    $message = 'Cher utilisateur, le fichier de la photo [' . $chemin_distant . '] est introuvable.'; // Message de l'email
                                    $headers = "From: contact@cvdumonde.com\r\n"; // En-têtes pour l'expéditeur
                                    $headers .= "Reply-To: contact@cvdumonde.com\r\n"; // En-têtes pour la réponse
                                    $headers .= "MIME-Version: 1.0\r\n"; // Version MIME
                                    $headers .= "Content-Type: text/plain; charset=utf-8\r\n"; // Type de contenu
                
                                    if (mail($to, $subject, $message, $headers)) {
                                        // Le mail a été envoyé
                                    } else {
                                        // Il y a eu un problème lors de l'envoi du mail
                                    }
                                }
                            }
                        } else {
                            echo "L'URL du fichier distant est vide ou incorrecte.";
                        }
                    }
                    
                    // Affichage de la photo
                    $photo = '<img src="' . htmlspecialchars($chemin_du_fichier) . '" alt="Photo" class="rounded-img custom-size">';
                }
                
                
                if (!empty($categorie_contrat) && !empty($profil_OK["ID"])) {
                    $select_sms_envoye = $bdd->prepare('SELECT COUNT(ID) as count_id, ENVOI_PROGRAMME FROM `resho_sms_tracabilite` WHERE `ID_USER_RECRUTEUR` = ? AND `ID_USER_CANDIDAT` = ? ORDER BY `resho_sms_tracabilite`.`ENVOI_PROGRAMME` DESC LIMIT 1');
                    $select_sms_envoye->execute(array($profil_OK["ID"], $row["user_id"]));
                    $select_sms_envoye_OK = $select_sms_envoye->fetch();
                    if (!empty($select_sms_envoye_OK) && !empty($select_sms_envoye_OK["ENVOI_PROGRAMME"])) {
                        $sms_ok_datetime = $select_sms_envoye_OK["ENVOI_PROGRAMME"];
                        $sms_datetime_OK_formatted = date('d/m/Y à H:i:s', strtotime($sms_ok_datetime));
                        $contact = $select_sms_envoye_OK["count_id"] . ' SMS au ' . $sms_datetime_OK_formatted;
                    } else {
                        $contact = 'Dispo par SMS';
                    }

                    $bouton_ajouter .= '<button class="selectButton butn butn-md" id="selectButton" value="Ajouter" data-user-id="id:' . $row["user_id"] . '">' . $contact . '</button>';
                } else {

                    $bouton_ajouter = '';
                }
            
                if(!empty($cv)){
                    $contact_par_cv =' <a href="' .  $cv . '" class="butn butn-md" target="_blank">Voir le CV</a>';
                }else{
                    $contact_par_cv ='';
                }
                
          
                 // <span class="border-end border-color-extra-light-gray pe-2 me-2"><i class="fas fa-map-marker-alt pe-2 text-secondary"></i>' .  $zone . '</span>
                $candidat .=  '<div class="col-lg-12 mt-1-9">
                <div class="px-3 py-1-6 px-md-4 py-md-1-9 border border-color-extra-light-gray border-radius-10 bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-9 mb-4 mb-md-0"> 
                            <div class="d-md-flex text-center text-md-start">
                                <div class="flex-shrink-0 mx-auto mx-md-0 w-80px w-md-auto mb-3 mb-md-0">
                                  ' . $photo . ' 
                                </div>
                                <div class="flex-grow-1 ms-md-4">
                                    <h4 class="h5">' . $last_name . ' ' .  $first_name . ' </h4>
                                    <span class="text-muted d-block mb-2 font-weight-500">' .  $metier . '</span>
                                    <div class="display-31 text-warning mb-3">
                                    ' . $etoile . '
                                        <span class="bg-primary px-2 py-1 ms-2 display-31 text-white font-weight-600 border-radius-10">' .  $etoile_count . '/5</span>
                                    <p>Taux de renseignement du profil<p>
                                        </div>
                                    
                                    <span class="border-end border-color-extra-light-gray pe-2 me-2"><i class="ti-briefcase pe-2 text-secondary"></i> ' .  $experience . ' ans d\'expérience</span>
                                    <span class="border-end border-color-extra-light-gray pe-2 me-2"><i class="ti-briefcase pe-2 text-secondary"></i> ' .  $type_contrat . ' </span>
                                    <span><i class="far fa-money-bill-alt pe-2 text-secondary"></i>' .  $salaire_souhaite . '</span>
                                    <br> ' .  $social . '
                                
                                    
                                        </div>
                            </div>

                        </div>

                        <div class="col-md-3 text-center text-md-end">
                        ' . $bouton_ajouter . '  <br>
                        <br>
                        
                        ' . $contact_par_mail . '
                        <br>
                        <br>
                        
                        ' . $contact_par_tel . '
                        <br>
                        <br>
                            '.$contact_par_cv .'
                        
                          
                            </div>

                            </div>
                            </div>';
                     
                            
                if (!empty($realisations)) {
                    $candidat .= '<div class="p-1-6 border border-color-extra-light-gray border-radius-10">
                                                  <h5 class="mb-3">Quelques Photos de ' .  $last_name . ' ' .  $first_name . ':</h5>
                                                  <div class="row mt-n1-9">
                                                      ' .  $realisations . ' 
                                                  </div>
                                              </div>';
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <!-- metas -->
    <meta charset="utf-8">
    <meta name="author" content="Equipe de cvdumonde.com" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="keywords" content="Emploi" />
    <meta name="description" content="Trouvez Votre Prochaine Opportunitée sur www.cvdumonde.com !" />

    <!-- title  -->
    <title>Trouvez Votre Prochaine Opportunitée sur www.cvdumonde.com !</title>

    <!-- favicon -->
    <link rel="shortcut icon" href="img/logos/favicon.png" />
    <link rel="apple-touch-icon" href="img/logos/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="img/logos/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="img/logos/apple-touch-icon-114x114.png" />

    <!-- plugins -->
    <link rel="stylesheet" href="css/plugins.css" />

    <!-- search css -->
    <link rel="stylesheet" href="/search/search.css" />

    <!-- quform css -->
    <link rel="stylesheet" href="quform/css/base.css">
    <!-- core style css -->
    <link href="css/styles.css" rel="stylesheet" />
    <style>
    /* Style de la bulle */
    #btn {
        position: relative;
    }

    #btn:hover .bulle {
        display: block;
    }

    .bulle {
        position: absolute;
        background-color: #333;
        color: #fff;
        padding: 10px;
        border-radius: 5px;
        top: 100%;
        /* Position sous le bouton */
        left: 0;
        display: none;
    }



    .rounded-img {
        border-radius: 50%;
    }

    .custom-size {
        width: 150px;
        height: 150px;
        border-radius: 50%;
    }

    a img {
        animation: bounce 0.5s;
    }

    img {
        animation: bounce 0.5s;
    }

    @keyframes bounce {
        0% {
            transform: translateY(0);
        }

        25% {
            transform: translateY(-40px);
        }

        50% {
            transform: translateY(0);
        }

        75% {
            transform: translateY(-20px);
        }

        100% {
            transform: translateY(0);
        }
    }

    #countButton {
        position: fixed;
        bottom: 50%;
        left: 40px;
        transform: translateY(50%);
        background-color: blue;
        color: white;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        text-align: center;
        z-index: 999;
    }

    #FormSms {
        width: 350px;
        height: 650px;
        background-color: #333;
        border-radius: 20px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        margin: 0 auto;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;


    }


    #FormSms h3 {
        font-size: 22px;
        text-align: center;
        color: #333;
        margin: 0;
        width: 90%;
        padding: 10px;
        background-color: #f2f2f2;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        border-bottom-right-radius: 15px;
        border-bottom-left-radius: 15px;
        position: absolute;
        top: 0;
    }

    #FormSms label {
        font-size: 18px;
        color: #333;
        margin-bottom: 10px;
        text-align: center;
    }

    #FormSms select {
        height: 40px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 10px;
        font-size: 16px;
        margin: 10px 0;
        width: 80%;
        color: #333;
    }

    #FormSms input[type="submit"] {
        height: 40px;
        background-color: #4CAF50;
        color: #fff;
        border-radius: 5px;
        font-size: 18px;
        cursor: pointer;
        width: 80%;
        margin: 10px 0;
    }



    #FormSms .home-button {
        width: 50px;
        height: 50px;
        background-color: #fff;
        border-radius: 50%;
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
    }



    #FormSms .sms-section {
        width: 80%;
        background-color: #fff;
        border-radius: 20px;
        padding: 20px;
        position: absolute;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
    }

    #FormSms .sms-section-fini {
        width: 80%;
        background-color: #fff;
        border-radius: 20px;
        padding: 20px;
        position: absolute;
        top: 150px;
        left: 50%;
        transform: translateX(-50%);
    }
    </style>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Slider de photo -->
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css">


</head>

<body>

    <!-- PAGE LOADING
    ================================================== -->
    <div id="preloader"></div>

    <!-- MAIN WRAPPER
    ================================================== -->
    <div class="main-wrapper">

        <!-- HEADER
        ================================================== -->

        <?php
        include 'header.php';
        ?>



        <div class="container">
            <!-- <div class="row justify-content-center">
                    <div class="col-lg-6"> -->
            <div class="job-search-wrapper">
                <?= $e1; ?>

                <form id="myForm" action="" method="POST">
                    <div class="row mt-n3">
                        <div class="col-md-3 col-lg-3 mt-3">
                            <div class="job-search-text">
                                <?php

                                require './inc/search_metiers.php';
                                ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 mt-3">
                            <div class="job-search-text">
                                <!-- <input type="text" class="form-control" placeholder="Ville ou code postal"> -->
                                <?php

                                require './inc/search_adresse.php';
                                ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 mt-3">
                            <div class="job-search-text">

                                <input type="number" id="distance_max" name="distance_max" class="form-control"  min="1" placeholder="Distance max. en KM">
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-3 mt-3">
                            <div class="job-search-button">

                                <button type="submit" id="bouton2" name="btn_search_cv" class="butn theme">
                                    <i class="ti-search"></i><span class="text-end">Mon employé </span></button>

                            </div>
                        </div>

                    </div>
                </form>


            </div>
        </div>



        <!-- default Modal-->
        <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Stock SMS manquant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                      
                        if ($maxChecked >= 1) {
                        ?>
                        <p>Vous ne pouvez sélectionner que <?php echo $maxChecked; ?> utilisateurs. </p>
                        <p>Ce qui correspond à votre stock de crédit sms restant.</p>
                        <p>Merci de recharger.</p>
                        <?php
                        } else {
                        ?>
                        <p>Vous ne pouvez pas contacter les candidats par sms. </p>
                        <p>Vous ne disposez pas de crédit.</p>
                        <p>Merci de recharger.</p>
                        <?php
                        }
                        ?>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>

                        <button type="button" class="btn btn-primary"
                            onclick="window.location.href='https://checkout.stripe.com/c/pay/cs_test_a1uUhW0jelyWSzhRCpI40WtMoSOLRBBcvjmuV1aL0BqDSO0qOSCQrqktCA#fidkdWxOYHwnPyd1blpxYHZxWjA0SGFcdHJAdUJXQWMxNkxublJsQHRAQnxdSkAxTUxMak5PSX1/RzUyZHBQbn9vNVVpUkswPXFPUGFiSGBKXUNjczMxX1dpTTJXdHxSUH1AZzxWd0BubVF3NTU9Rz1jYEFVMScpJ3VpbGtuQH11anZgYUxhJz8ncWB2cVoxYnJnSDQ8X25jMGxmbEQzancnKSd3YGNgd3dgd0p3bGJsayc/J21xcXU/Kio0NzIrNSs1KzQqJ3gl'">Recharger</button>

                    </div>
                </div>
            </div>
        </div>





        <!-- PAGE TITLE
        ================================================== -->

        <?php
  var_dump($maxChecked); 
  echo '<br>';
  var_dump($sms_OK);
        if (!empty($_POST["search_Metiers"])) {


        ?>

        <!-- CANDIDATE LIST
                    ================================================== -->
        <section>
            <div class="container">
                <div class="row">
                    <?php
                        if (!empty($pas_de_click_sur_adresse) && !empty($_POST["btn_search_cv"])) {
                            echo  '<p style="color: red"><strong>L\'emplacement peut ne pas être exact puisque vous n\'avez pas validé l\'adresse proposée sur le formulaire.</strong></p>';
                        }
                        if (!empty($num_candidate) && $num_candidate  >= 1 && empty($_GET["demenage"]) && empty($_POST["demenage"]) && !empty($_POST["btn_search_cv"])) {


                            if (!empty($c) && $c == 'Contrat ?') {
                                $c = '';
                            }
                            if(!empty($adresse) && $adresse ='moi'){
                                $adresse ='';
                            }
                        ?>



                    <br>
                    <hr>
                    <br>
                    <?php
                        }

                        ?>
                    <?php
                        if (!empty($_POST["btn_search_cv"])) {
                        ?>
                    <!-- Afficher la carte en haut sur mobile -->
                    <div class="d-block d-md-none">
                        <div class="row text-center">
                            <div class="col-lg-6">
                                <div class="page-title-list">
                                    <div class="sidebar">
                                        <div class="sidebar-title">
                                            <div class="row mt-n1-9">
                                                <div class="col-sm-6 col-lg-4 mt-1-9 text-center text-sm-start">
                                                    <div class="d-sm-flex align-items-center">
                                                        <div class="flex-shrink-0 mb-3 mb-sm-0">

                                                            <span
                                                                class="font-weight-500 text-muted"><?= $string; ?><?= $s; ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-lg-4 mt-1-9 text-center text-sm-start">
                                                    <div
                                                        class="flex-grow-1 border-sm-start border-color-extra-light-gray ps-sm-3 ps-xl-4 ms-sm-3 ms-xl-4">
                                                        <h3 class="countup h1 text-secondary mb-1">
                                                            <?= $count_position; ?></h3>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-lg-4 mt-1-9 text-center text-sm-start">
                                                    <div class="d-sm-flex align-items-center">

                                                        <div
                                                            class="flex-grow-1 border-sm-start border-color-extra-light-gray ps-sm-3 ps-xl-4 ms-sm-3 ms-xl-4">
                                                            <form method="post" action="candidate-list.php">
                                                                <?= $autres; ?>
                                                                <button type="submit">
                                                                    <img class="animated bounce"
                                                                        src="img/icons/icon-10.png" alt="...">
                                                                </button>
                                                            </form>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?= $distance_max; ?> autour de <?= $adresse; ?> <?= $c; ?>
                                        </div>

                                        <div class="row text-center">
                                            <div class="col-lg-6">
                                                <div class="page-title-list">
                                                    <!-- <iframe class="map-h125" id="gmap_canvas"
                                                        src="./carte_candidat.php<?= $job; ?>"></iframe> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="widget search">


                                            <div class="quform-element form-group">
                                                <label for="category"><strong>Affinez par</strong></label>

                                            </div>
                                            <div class="quform-element">
                                                <?php
                                            if (empty($c)) {
                                                $c = 'Contrat ?';
                                            }
                                            ?>
                                                <form action="candidate-list.php" method="POST" id="forme">

                                                    <select id="category"
                                                        class="form-control form-select border-radius-10"
                                                        name="category" onchange="submitForm()">
                                                        <option value=""><?= $c; ?></option>
                                                        <option value="1">CDI</option>
                                                        <option value="2">CDD</option>
                                                        <option value="3">Interim</option>
                                                        <option value="4">Freelance</option>
                                                        <option value="5">Extra</option>
                                                        <option value="6">Apprentissage</option>
                                                        <option value="7">Stage</option>
                                                    </select>
                                                    <?php echo $autres; ?>
                                                </form>
                                            </div>
                                            <div class="quform-element">
                                                <label for="gender"> </label>
                                                <div class="quform-input">

                                                    <?php

                                                $bouton_genre = '
                                                                            <form action="candidate-list.php" method="post" id="genre">
                                                                            <select class="form-control form-select border-radius-10 " class="animated bounce" name="g" onchange="submitGenre()">
                                                                                <option value="">' . $g . '</option>
                                                                                <option value="1">Homme</option>
                                                                                <option value="2">Femme</option>
                                                                                <option value="3">Tous</option>  </select>
                                                                                ' . $autres . '
                                                                                ';



                                                $bouton_genre .= '
                                                                            </form>
                                                                        
                                                                            ';
                                                echo    $bouton_genre;
                                                ?>

                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- candidate-list left -->

                    <div class="col-lg-3 order-2 order-lg-1">
                        <div class="sidebar">
                            <div class="sidebar-title">
                                <div class="row mt-n1-9">
                                    <div class="col-sm-6 col-lg-4 mt-1-9 text-center text-sm-start">
                                        <div class="d-sm-flex align-items-center">
                                            <div class="flex-shrink-0 mb-3 mb-sm-0">

                                                <span
                                                    class="font-weight-500 text-muted"><?= $string; ?><?= $s; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mt-1-9 text-center text-sm-start">
                                        <div
                                            class="flex-grow-1 border-sm-start border-color-extra-light-gray ps-sm-3 ps-xl-4 ms-sm-3 ms-xl-4">
                                            <h3 class="countup h1 text-secondary mb-1"><?= $count_position; ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mt-1-9 text-center text-sm-start">
                                        <div class="d-sm-flex align-items-center">

                                            <div
                                                class="flex-grow-1 border-sm-start border-color-extra-light-gray ps-sm-3 ps-xl-4 ms-sm-3 ms-xl-4">
                                                <form method="post" action="candidate-list.php">
                                                    <?= $autres; ?>
                                                    <!-- <button type="submit" id="btn">
                                                        <img class="animated bounce" src="img/icons/icon-10.png"
                                                            alt="...">
                                                    </button> -->

                                                    <div id="btn">
                                                        <button type="submit">
                                                            <img class="animated bounce" src="img/icons/icon-10.png"
                                                                alt="...">
                                                        </button>
                                                        <div class="bulle">Élargir la recherche?</div>
                                                    </div>
                                                </form>


                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?= $distance_max; ?> autour de <?= $adresse; ?> <?= $c; ?>
                            </div>

                            <div class="row text-center">
                                <div class="col-lg-6">
                                    <div class="page-title-list">
                                        <!-- <iframe class="map-h125" id="gmap_canvas"
                                            src="./carte_candidat.php<?= $job; ?>"></iframe> -->
                                    </div>
                                </div>
                            </div>
                            <div class="widget search">


                                <div class="quform-element form-group">
                                    <label for="category"><strong>Affinez par</strong></label>

                                </div>
                                <div class="quform-element">
                                    <?php
                                            if (empty($c)) {
                                                $c = 'Contrat ?';
                                            }
                                            ?>
                                    <form action="candidate-list.php" method="POST" id="forme">

                                        <select id="category" class="form-control form-select border-radius-10"
                                            name="category" onchange="submitForm()">
                                            <option value=""><?= $c; ?></option>
                                            <option value="1">CDI</option>
                                            <option value="2">CDD</option>
                                            <option value="3">Interim</option>
                                            <option value="4">Freelance</option>
                                            <option value="5">Extra</option>
                                            <option value="6">Apprentissage</option>
                                            <option value="7">Stage</option>
                                        </select>
                                        <?php echo $autres; ?>
                                    </form>
                                </div>
                                <div class="quform-element">
                                    <label for="gender"> </label>
                                    <div class="quform-input">

                                        <?php

                                                $bouton_genre = '
                                                                            <form action="candidate-list.php" method="post" id="genre">
                                                                            <select class="form-control form-select border-radius-10 " class="animated bounce" name="g" onchange="submitGenre()">
                                                                                <option value="">' . $g . '</option>
                                                                                <option value="1">Homme</option>
                                                                                <option value="2">Femme</option>
                                                                                <option value="3">Tous</option>  </select>
                                                                                ' . $autres . '
                                                                                ';



                                                $bouton_genre .= '
                                                                            </form>
                                                                        
                                                                            ';
                                                echo    $bouton_genre;
                                                ?>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <!-- end candidate-list left -->

                    <!-- candidate-list right -->
                    <div class="col-lg-9 order-1 order-lg-2 mb-6 mb-lg-0">
                        <div class="ps-lg-1-6 ps-xl-1-9">
                            <div class="row mb-2-5">
                                <div class="col-lg-12">
                                    <div class="d-md-flex justify-content-between align-items-center">
                                        <div class="mb-4 mb-md-0">
                                            <h4 class="mb-0 h5">Affichage de 1 à <?= $nbr; ?> / <a
                                                    href="candidate-list.php?p=<?= $count_position; ?><?= $get; ?>"><span
                                                        class="text-primary"><?= $count_position; ?>
                                                        Candidats proche de moi affichés sur la carte</span></a></h4>
                                        </div>
                                        <?= $alerte; ?>

                                        <div class="quform-elements">
                                            <div class="row align-items-center">
                                                <!-- Begin Select element -->
                                                <div class="col-md-12 mb-8 mb-md-0">
                                                    <div class="quform-element">
                                                        <div class="quform-input">




                                                            <?php

                                                                    $bouton_filtre = '
                                                                            <form action="candidate-list.php" method="post" id="filtre">
                                                                            <select class="form-control form-select border-radius-10 " class="animated bounce" name="d" onchange="submitFiltre()">
                                                                                <option value="">' . $d . '</option>
                                                                                <option value="1">+ Proche</option>
                                                                                <option value="2">- Proche</option>
                                                                                <option value="4">+ Expérience</option>
                                                                                <option value="3">- Expérience</option>
                                                                                <option value="6">+ Salaire</option>
                                                                                <option value="5">- Salaire</option>
                                                                                  </select>
                                                                                  ' . $autres . '                          
                                                                            </form>
                                                                        
                                                                            ';
                                                                    echo    $bouton_filtre;
                                                                    ?>

                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Select element -->
                                            </div>
                                        </div>

                                        <br>

                                    </div>
                                </div>
                            </div>
                            <div class="row mt-n1-9">

                                <?= $candidat; ?>
                            </div>
                            <br>
                            <?php
                                    if (!empty($count_position) && $count_position > $nbr) {


                                    ?>
                            <div style="text-align: center;">


                                <form action="candidate-list.php" method="post">
                                    <input type="hidden" name="p" value="<?= $nbr; ?>">
                                    <?= $autres; ?>
                                    <button class="butn butn-md" type="submit" style="text-decoration: none;">
                                        <div style="display: flex; align-items: center;">
                                            <i class="fas fa-plus" style="color: #d5085a; font-size: 28px"></i>
                                            <span
                                                style="margin-left: 8px; color: #2f27ae; font-weight: bold; font-size: 24px">de
                                                résultats</span>
                                        </div>
                                    </button>
                                </form>
                            </div>

                            <?php
                                    }
                                    if (!empty($c) && $c = 'Contrat ?') {
                                        $c = '';
                                    }
                                    ?>

                            <br>
                            <h3 class="text-center">Bon à savoir, en plus de votre résultat, il y a<strong>
                                    <?= $num_candidate; ?> profils au poste de
                                    <?= $string; ?><?= $s; ?> </strong>prêts à déménager <?= $adresse; ?> <?= $c; ?>
                                <br>
                                <br>
                                <form method="post" action="candidate-list.php">
                                    <input type="hidden" name="demenage" value="1">
                                    <?= $autres; ?>
                                    <button type="submit" class="butn butn-md">Voir les candidats prêts à
                                        déménager</button>
                                </form>
                            </h3>

                        </div>

                    </div>

                    <?php
                        }
                        ?>
                </div>
                <input type="hidden" id="selectedValuesInput">
                <button id="countButton" style="display:none"></button>
                <!-- end candidate-list right -->
            </div>

        </section>
        <?php
        } else {
        ?>
        <section>
            <div class="container">
                <div class="row text-center">
                    <h1>Merci d'utiliser le champ de recherche</h1>
                </div>
            </div>
        </section>
        <?php
        }
        ?>


    </div>

    <!-- SCROLL TO TOP
    ================================================== -->
    <a href="#!" class="scroll-to-top"><i class="fas fa-angle-up" aria-hidden="true"></i></a>
    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <!-- all js include start -->

    <!-- //fonction de récupération des variable  $latitude & $longitude reçu en géolocalisation -->
    <div id="coordinates"></div>
    <script src="./inc/geolocation/geolocation.js"></script>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js">
    </script>


    <!-- Caroussel d'image -->
    <script>
    $(document).ready(function() {
        $('.realisation-link').click(function(event) {
            event.preventDefault();
            var images = [];
            $('.realisation-link').each(function() {
                var image = $(this).data('image');
                images.push({
                    'src': image,
                    'opts': {
                        'caption': $(this).attr('href')
                    }
                });
            });
            var index = $(this).index('.realisation-link');
            $.fancybox.open(images, {
                'loop': true,
                'index': index
            });
        });
    });
    </script>


    <!-- popper js -->
    <script src="js/popper.min.js"></script>

    <!-- bootstrap -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Serch -->
    <script src="/search/search.js"></script>

    <!-- navigation -->
    <script src="js/nav-menu.js"></script>

    <!-- animated.headline -->
    <script src="js/animated.headline.js"></script>

    <!-- tab -->
    <script src="js/easy.responsive.tabs.js"></script>

    <!-- owl carousel -->
    <script src="js/owl.carousel.js"></script>

    <!-- jquery.counterup.min -->
    <script src="js/jquery.counterup.min.js"></script>

    <!-- stellar js -->
    <script src="js/jquery.stellar.min.js"></script>

    <!-- waypoints js -->
    <script src="js/waypoints.min.js"></script>

    <!-- countdown js -->
    <script src="js/countdown.js"></script>

    <!-- magnific-popup js -->
    <script src="js/jquery.magnific-popup.min.js"></script>

    <!-- nice-select js -->
    <script src="js/jquery.nice-select.js"></script>

    <!-- range slider -->
    <script src="js/ion.rangeSlider.min.js"></script>

    <!-- clipboard js -->
    <script src="js/clipboard.min.js"></script>

    <!-- prism js -->
    <script src="js/prism.js"></script>

    <!-- custom scripts -->
    <script src="js/main.js"></script>

    <!-- form plugins js -->
    <script src="quform/js/plugins.js"></script>

    <!-- form scripts js -->
    <script src="quform/js/scripts.js"></script>




    <script>
    function afficherBouton() {
        var choix = document.querySelector('input[name="choix"]:checked').value;
        if (choix == "1") {
            document.getElementById("bouton1").style.display = "inline-block";
            document.getElementById("bouton2").style.display = "none";
            document.getElementById("myForm").action = "traitement_employe.php";
        } else if (choix == "2") {
            document.getElementById("bouton2").style.display = "inline-block";
            document.getElementById("bouton1").style.display = "none";
            document.getElementById("myForm").action = "candidate-list.php";
        } else {
            document.getElementById("bouton1").style.display = "none";
            document.getElementById("bouton2").style.display = "none";
        }
    }
    </script>

    <!-- CODE V5 Contacte par SMS + alerte Popup -->
    <script>
    var selectButtons = document.querySelectorAll('.butn.butn-md');
    var countButton = document.getElementById("countButton");
    var selectedValuesInput = document.getElementById("selectedValuesInput");
    var maxChecked = <?php echo $maxChecked; ?>;
    var checkedCount = 0;

    for (var i = 0; i < selectButtons.length; i++) {
        selectButtons[i].addEventListener("click", function() {
            if (this.value === "Ajouter") {
                if (checkedCount < maxChecked) {
                    this.value = "Sélectionné";
                    checkedCount++;
                    selectedValuesInput.value += "," + this.dataset.userId;
                } else {
                    $("#myModal").modal("show");
                }
            } else {
                this.value = "Ajouter";
                checkedCount--;
                selectedValuesInput.value = selectedValuesInput.value.replace("," + this.dataset.userId, "");
            }
            if (checkedCount > 0) {
                countButton.style.display = "inline-block";
                countButton.innerHTML = "Demande dispo. par sms <br>" + checkedCount + " / " + maxChecked;
                countButton.style.backgroundColor = "blue";
                countButton.style.color = "white";
                countButton.style.borderRadius = "50%";
                countButton.style.padding = "10px";
            } else {
                countButton.style.display = "none";
            }
        });
    }

    countButton.addEventListener("click", function() {
        var form = document.createElement("form");
        form.setAttribute("method", "post");
        form.setAttribute("action", "candidate-list.php?cont=1<?= $get; ?>&selected_values=" +
            encodeURIComponent(selectedValuesInput.value));
        form.style.display = "none";

        document.body.appendChild(form);
        form.submit();
    });
    </script>
    <script>
    $(document).ready(function() {
        var selectButtons = document.querySelectorAll('.selectButton');
        for (var i = 0; i < selectButtons.length; i++) {
            selectButtons[i].addEventListener("click", function() {
                if (this.innerHTML === "Dispo par SMS") {
                    this.innerHTML = "Retirer";
                } else {
                    this.innerHTML = "Dispo par SMS";
                }
            });
        }
    });
    </script>


    <script>
    function submitForm() {
        document.getElementById("form").submit();
    }
    </script>
    <script>
    function submitForm() {
        document.getElementById("forme").submit();
    }
    </script>
    <script>
    function submitFiltre() {
        document.getElementById("filtre").submit();
    }
    </script>
    <script>
    function submitGenre() {
        document.getElementById("genre").submit();
    }
    </script>
    <!-- Définition de la date et heure minimum des sms -->

    <script>
    document.getElementById("sms-datetime").value = new Date().toISOString().split(".")[0];
    document.getElementById("sms-datetime").min = new Date().toISOString().split(".")[0];
    </script>

    <!-- Ajoutez le code JavaScript pour la bulle temporaire -->
    <script>
    // Fonction pour afficher la bulle pendant 15 secondes
    function afficherBullePendant15Secondes() {
        var bulle = document.querySelector('.bulle');

        // Affichez la bulle
        bulle.style.display = 'block';

        // Supprimez la bulle après 15 secondes
        setTimeout(function() {
            bulle.style.display = 'none';
        }, 10000); // 10 secondes en millisecondes
    }

    // Appelez la fonction pour afficher la bulle dès que la page est chargée
    window.onload = afficherBullePendant15Secondes;
    </script>


</body>

<!-- FOOTER
        ================================================== -->
<?php
include 'footer.php';

?>



</html>