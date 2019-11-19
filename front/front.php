<?php
session_start();

require_once("../outils/fonctions.php");

// Etablie la connection avec la BDD
$connexion = connexion();

//calcul du menu
$items=array("team","events","company","contact");
// \n permet de passer à la ligne dans le code source
$menu_haut="<nav id=\"menu_haut\">\n<ul>\n";
for($i=0;$i<count($items);$i++)
	{
	$menu_haut.="<li><a href=\"front.php?page=" . $items[$i] . "#" . $items[$i] . "\">" . $items[$i] . "</a></li>";	
	}
$menu_haut.="</ul>\n</nav>\n";

if(isset($_GET['page']))
	{
	$fermer="close";
	$lien_fermer="../index.php";
	$contenu=$_GET['page'] . ".html";	
	
	switch($_GET['page'])
	{
		case "contact":
			if(isset($_POST['submit']))
		  {
		  if(empty($_POST['nom_contact']))
			{
			$message="<label id=\"warning\">Veuillez entrer votre nom</label>\n";
			} 
		  elseif(empty($_POST['prenom_contact']))
			{
			$message="<label id=\"warning\">Veuillez entrer votre prénom</label>\n";
			}
		  elseif(empty($_POST['mel_contact']))
			{
			$message="<label id=\"warning\">Veuillez entrer votre email</label>\n";
			}        
		  elseif(empty($_POST['message_contact']))
			{
			$message="<label id=\"warning\">Veuillez entrer votre message</label>\n";
			}
			elseif(isset($_SESSION['captcha'])&& $_SESSION['captcha']!=$_POST['captcha'])
			{
				$message="<label id=\"warning\">captcha pas bien</label>\n";
			}
			

		  else
			{
			$requete="INSERT INTO contacts SET nom_contact='" . addslashes($_POST['nom_contact']) . "',
											   prenom_contact='" . addslashes($_POST['prenom_contact']) . "',
											   mel_contact='" . addslashes($_POST['mel_contact']) . "',
											   message_contact='" . addslashes($_POST['message_contact']) . "', 
											   date_contact='" . date("Y-m-d H:i:s") . "'";                                             
			$resultat=mysqli_query($connexion,$requete);
			
			// on envoie les confirmations par mails
			$message_recu=str_replace("javascript:","",$_POST['message_contact']); //pour lutter contre la faille xss 
			$type="html";
			
			// mail recu par la boite
			@envoi_mel($_SESSION['mail_retour'], "Contact depuis le site","CONTACT : " . $_POST['prenom_contact'] . " " . $_POST['nom_contact'] . "\nMESSAGE : " . $message_recu,$_POST['mel_contact'] . "\r\n", $type);
			
			// mail automatique envoyé à l'internaute
			@envoi_mel($_POST['mel_contact'],"Snowboard Corporation","Merci de votre confiance...","contact@snowboard.com\r\n", $type);                  			 
			  
			header("Location:front.php?page=merci#contact");                                
			}         
		  }		
		
		break;
		
		case "produits":
		$requete = "SELECT * FROM produits ORDER BY nom_produit";
		$resultat=mysqli_query($connexion, $requete);
		$galerie_produit = "<figure>";
		while($ligne=mysqli_fetch_object($resultat))
		{
			$galerie_produit.="<div class =\"galerie\"><h2>".$ligne->nom_produit."</h2><a href=\"front.php?page=fiche&id_produit=".$ligne->id_produit."#produits\"><img src=\"".$ligne->photo_produit."\" alt=\"".$ligne->nom_produit."\"></a></div>";
			
		}

		$galerie_produit.="</figure>";
		break;
		
		case "fiche":
		$lien_fermer="front.php?page=produits#produits";

		if(isset($_GET['id_produit']))
		{
			$requete = "SELECT * FROM produits WHERE id_produit='".$_GET['id_produit']."'";
			$resultat=mysqli_query($connexion, $requete);
			$ligne=mysqli_fetch_object($resultat);
			$nom_produit=$ligne->nom_produit;
			$photo_produit="<img src=\"".str_replace("_p ", "_g",$ligne->photo_produit) .
			"\" alt=\"".$ligne->nom_produit."\">";
			$description_produit=$ligne->description_produit;
			$prix_produit="<p class=\"prix\">" . $ligne->prix_produit . "</p>";
		}

		break;
	}

	}
else{
	$contenu="intro.html";	
	}
mysqli_close($connexion);
include("front.html");
?>