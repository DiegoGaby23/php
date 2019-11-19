<?php
// Initialise la session
session_start();

if(isset($_SESSION['id_compte']))
{
$titre ="<span>Bienvenu"." ". utf8_encode($_SESSION['prenom_compte']. " ".$_SESSION['nom_compte'])."</sapn>";

require_once("../outils/fonctions.php");

// Etablie la connection avec la BDD
$connexion = connexion();



if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "deconnecter":
            session_destroy();
            header("Location:../index.php");
            break;

        case "compte":
            $titre = "Administration du compte";
            break;

            case "contact":
                $titre ="Listes de contacts";
                $liste = afficher_contacts();

            break;

        case "produit":

            // Contenu à afficher
            $contenu = "form_produit.html";
            $titre = "Administration du produit";


            if (isset($_GET['cas'])) {
                switch ($_GET['cas']) {
                    case "ajouter";
                    $action_form = "ajouter";
                        if (isset($_POST["submit"])) {

                            if (empty($_POST['nom_produit'])) {
                                $message = '<label id="warning">Le nom du produit est obligatoire</label>';
                            } elseif (empty($_POST['description_produit'])) {
                                $message = '<label id="warning">La description du produit est obligatoire</label>';
                            } elseif (empty($_POST['prix_produit'])) {
                                $message = '<label id="warning">Le prix du produit est obligatoire</label>';
                            }elseif ($_FILES['photo_produit']['name'] == "") {
                                $message = '<label id="warning"> La photo du produit est obligatoire</label>';
                            } else {
                                $requete = "INSERT INTO produits SET nom_produit='" . addslashes($_POST['nom_produit']) . "',
                                                    description_produit='" . addslashes($_POST['description_produit']) . "',
                                                    prix_produit='" .str_replace(",",".", $_POST['prix_produit']) . "'";

                                // execution de la requete dans la BDD
                                $resultat = mysqli_query($connexion, $requete);

                                // on récupère le id_produit créé
                                $dernier_id_creer = mysqli_insert_id($connexion);

                                if (
                                    fichier_type($_FILES['photo_produit']['name']) == "jpg" || fichier_type($_FILES['photo_produit']['name']) == "png"
                                    || fichier_type($_FILES['photo_produit']['name']) == "gif"
                                ) {
                                    $extension = fichier_type($_FILES['photo_produit']['name']);
                                    $chemin_media = "../medias/media" . $dernier_id_creer . "_g." . $extension;
                                    $chemin_media2 = "../medias/media" . $dernier_id_creer . "_p." . $extension;

                                    if (is_uploaded_file($_FILES['photo_produit']['tmp_name'])) {
                                        if (copy($_FILES['photo_produit']['tmp_name'], $chemin_media)) {
                                            $size = getimagesize($chemin_media);
                                            $width = $size[0];
                                            $height = $size[1];
                                            $rapport = $width / $height;

                                            if ($rapport > 1) {
                                                $new_width = 200;
                                                $new_height = 200 / $rapport;
                                            } elseif ($rapport < 1) {
                                                $new_width = 200 * $rapport;
                                                $new_height = 200 / $rapport;
                                            } else {
                                                $new_width = 200;
                                                $new_height = 200;
                                            }

                                            redimage($chemin_media, $chemin_media2, "200", "200", "70");

                                            $requete2 = "UPDATE produits SET photo_produit='" . $chemin_media2 . "'
                                           WHERE id_produit='" . $dernier_id_creer . "'";
                                            $resultat2 = mysqli_query($connexion, $requete2);
                                        }
                                    }
                                }


                                 $message = '<label\"bravo\">Le produit a bien été ajouté</label>';

                                foreach ($_POST as $nom_champ => $valeur) {
                                    $_POST[$nom_champ] = "";
                                }
                            }
                        }
                        break;

                    case "modifier";
                    

                    // Je suis ici
                    if(isset($_GET['id_produit']))    
                    {
                    $action_form="modifier&id_produit=" .$_GET['id_produit']. "";
                        if(isset($_POST['submit']))                           
                        {
                            // On met à jour la ligne de la BDD de la table produit 
                            $requete="UPDATE produits SET nom_produit='".addslashes($_POST['nom_produit'])."',
                            description_produit='".addslashes($_POST['description_produit'])."',
                            prix_produit='".$_POST['prix_produit']."' WHERE id_produit='".$_GET['id_produit']."'";
                            $resultat=mysqli_query($connexion, $requete);

                            // uploader image

                            if (
                                fichier_type($_FILES['photo_produit']['name']) == "jpg" || fichier_type($_FILES['photo_produit']['name']) == "png"
                                || fichier_type($_FILES['photo_produit']['name']) == "gif"
                            ) {
                                $extension = fichier_type($_FILES['photo_produit']['name']);
                                $chemin_media = "../medias/media" . $_GET['id_produit'] . "_g." . $extension;
                                $chemin_media2 = "../medias/media" .$_GET['id_produit'] . "_p." . $extension;

                                if (is_uploaded_file($_FILES['photo_produit']['tmp_name'])) {
                                    if (copy($_FILES['photo_produit']['tmp_name'], $chemin_media)) {
                                        $size = getimagesize($chemin_media);
                                        $width = $size[0];
                                        $height = $size[1];
                                        $rapport = $width / $height;

                                        if ($rapport > 1) {
                                            $new_width = 200;
                                            $new_height = 200 / $rapport;
                                        } elseif ($rapport < 1) {
                                            $new_width = 200 * $rapport;
                                            $new_height = 200 / $rapport;
                                        } else {
                                            $new_width = 200;
                                            $new_height = 200;
                                        }

                                        redimage($chemin_media, $chemin_media2, "200", "200", "70");

                                        $requete2 = "UPDATE produits SET photo_produit='" . $chemin_media2 . "'
                                       WHERE id_produit='" . $_GET['id_produit']. "'";
                                        $resultat2 = mysqli_query($connexion, $requete2);
                                    }
                                }
                            }


                            $message = '<label\"bravo\">Le produit a bien été modifié</label>';
                            foreach($_POST as $cle=>$valeur)
                           {
                               $_POST[$cle]="";
                           }
                           $action_form="ajouter";
                        }
                    else{
                        // on recharge le formulaire avec les données
                        $requete="SELECT * FROM produits WHERE id_produit='".$_GET['id_produit']."'";
                        $resultat=mysqli_query($connexion, $requete);

                        $ligne=mysqli_fetch_object($resultat);
                        $_POST['nom_produit']=stripslashes($ligne->nom_produit);
                        $_POST['description_produit']=stripslashes($ligne->description_produit);
                        $_POST['prix_produit']=$ligne->prix_produit;
                        }

                    }    

                    break;

                    case "supprimer";

                    $action_form = "supprimer";
                    // Suppression des produits    
                    if(isset($_GET['id_produit']))
                    {
                        $message = "<label id=\"confirme\">Voulez-vous supprimer le produit ?</label>
                        <a href=\"admin.php?action=produit&cas=supprimer&id_produit=".$_GET['id_produit']."&confirme=oui\">Oui</a>&nbsp;&nbsp;&nbsp;<a href=\"admin.php?action=produit\">Non</a></label>";
                        // Confirmation de la suppresion du produit
                        if(isset($_GET['confirme']) && $_GET['confirme']=="oui")
                        {
                            $requete="SELECT * FROM produits WHERE id_produit='".$_GET['id_produit']."'";
                            $resultat=mysqli_query($connexion, $requete);
                            $ligne=mysqli_fetch_object($resultat);
                            $miniature=$ligne->photo_produit;
                            $lien_media_g=str_replace("_p","_g", $miniature);
    
                            @unlink($lien_media_g);
                            @unlink($miniature);
                            // si oui alors supprime le produit en prenant compte son id
                            $requete2="DELETE FROM produits WHERE id_produit='".$_GET['id_produit']."'";
                            $resultat2=mysqli_query($connexion, $requete2);
                            $message="<label id=\"Bravo\">Le produit a bien été supprimer</label>";
                        }

                     
                    }

                        break;
                }
            }
            $liste = afficher_produits();// Function pour afficher la liste des produits

            break;
    }
}

// referme la connection a la BDD
mysqli_close($connexion);
}

else
{
    
    header("Location:login.php");
}



include("admin.html");// Inclue le contenue de admin.html

?>