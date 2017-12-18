<?php
error_reporting(E_ALL & ~E_NOTICE);
chdir("/home/contentops/sony_pull_system");
require_once("include/conf.php");
require_once("include/functions.php");
require_once("include/db_connect.php");

//***********************************************************************************************	
//log file
	$fp_0_octet = fopen($conf['log_file_recuperation_0_octet'],"a");
	
//***********************************************************************************************
//TMP (to remove)
	$req = "TRUNCATE TABLE sony_recuperation";
	//$resultat = $db_sony->query($req);	
	
//***********************************************************************************************	
//Download batch files on Sony FTP
	//download_deliveries_on_ftp();

//***********************************************************************************************
//Boucle pour tous les comptes
	for($c=0;$c<count($conf['account_name']);$c++)
	{
		echo "======================================================================================\r\n";
		//Media recovery and XML modifications
			$compteur = "";
			$repertoire_livraison = $conf['main_directory']."/".$conf['dir_todo']."/".$conf['account_name'][$c];
			echo "repertoire_livraison : ".$repertoire_livraison."\r\n";
			$livraison_a_traiter = scandir($repertoire_livraison);
			for($a=0;$a<count($livraison_a_traiter);$a++)
			{
				if($livraison_a_traiter[$a]!="." AND $livraison_a_traiter[$a]!="..")
				{
					$tab = explode('_',$livraison_a_traiter[$a]);
					$xml_a_traiter = $repertoire_livraison."/".$livraison_a_traiter[$a]."/".$tab[0].".xml";
					echo "xml_a_traiter : ".$xml_a_traiter."\r\n";

				//Creation of the directory /resources
					@mkdir($repertoire_livraison."/".$livraison_a_traiter[$a]."/resources",0777,true);

				//Retrieving information in XML
					$liste_info = get_info_from_xml($xml_a_traiter);		
					
				//Media Recovery
					/************TO DELETE******************/		
					//$status = "ko";		
					/************TO DELETE******************/	
					
					unset($contenu_fichier);
					unset($compteur);
					
					for($b=0;$b<count($liste_info['url']);$b++)
					{
						echo "url : ".$liste_info['url'][$b]."\r\n";
						echo "destination : ".$liste_info['fichier_destination'][$b]."\r\n";
						$status = download_url($liste_info['url'][$b],$repertoire_livraison."/".$livraison_a_traiter[$a]."/resources/".$liste_info['fichier_destination'][$b]);
						echo "status : ".$status."\r\n";

					//Checking the weight of the file
						if(file_exists($repertoire_livraison."/".$livraison_a_traiter[$a]."/resources/".$liste_info['fichier_destination'][$b])==true AND filesize($repertoire_livraison."/".$livraison_a_traiter[$a]."/resources/".$liste_info['fichier_destination'][$b])==0)
						{
						//On réessaie de télécharger
							$status = download_url($liste_info['url'][$b],$repertoire_livraison."/".$livraison_a_traiter[$a]."/resources/".$liste_info['fichier_destination'][$b]);	
							if($status=="ok" AND file_exists($repertoire_livraison."/".$livraison_a_traiter[$a]."/resources/".$liste_info['fichier_destination'][$b])==true AND filesize($repertoire_livraison."/".$livraison_a_traiter[$a]."/resources/".$liste_info['fichier_destination'][$b])==0)
								fwrite($fp_0_octet,date("Ymd H:i:s")." ===> COPIE KO (file 0 octet downloaded) ===> ".$liste_info['url'][$b]." ===> ".$liste_info['fichier_destination'][$b]."\r\n");
						}
						
					//Updating the table sony_recuperation 
						$req = "INSERT INTO sony_recuperation (account_name,url_to_copy,fichier_destination,status_recuperation,message_id,album_grid) VALUES ('".$conf['account_name'][$c]."','".$liste_info['url'][$b]."','".$liste_info['fichier_destination'][$b]."','".$status."','".$liste_info['message_id']."','".$liste_info['album_grid']."')";
					echo "req : ".$req."\r\n";	
						$resultat = $db_sony->query($req);	

					//Modifying the XML (changing the URL by the path to the file)
						$compteur++;
						if($compteur==1)
							$contenu_fichier = file_get_contents($xml_a_traiter);
						$ancienne_chaine = "<URL>".str_replace("&","&amp;",$liste_info['url'][$b])."</URL>";
						$nouvelle_chaine = "<FileName>".$liste_info['fichier_destination'][$b]."</FileName>\r\n\t\t\t\t\t\t<FilePath>resources/</FilePath>";
						$contenu_fichier = str_replace($ancienne_chaine,$nouvelle_chaine,$contenu_fichier);		
					}
				//***********************************************************************************************
				//S'il s'agit d'une livraison d'UPDATE sans média 
					if($contenu_fichier=="")
					{
						$contenu_fichier = file_get_contents($xml_a_traiter);
						
					//Updating the table sony_recuperation 
						$req = "INSERT INTO sony_recuperation (account_name,status_ack,message_id,album_grid) VALUES ('".$conf['account_name'][$c]."','ready_to_send','".$liste_info['message_id']."','".$liste_info['album_grid']."')";
						echo "req : ".$req."\r\n";
						$resultat = $db_sony->query($req);							
					}
			
				//***********************************************************************************************
				//Modifications supplémentaires à ajouter pour être conforme avec DDEX 3.8.1
				
					//1. replace <LabelName LabelNameType="DisplayLabelName"> par <LabelName>
						$contenu_fichier = str_replace("<LabelName LabelNameType=\"DisplayLabelName\">","<LabelName>",$contenu_fichier);
						
					//2. replace GlobalOriginalReleaseDate par OriginalReleaseDate
						$contenu_fichier = str_replace("GlobalOriginalReleaseDate","OriginalReleaseDate",$contenu_fichier);
						
					//3. ajouter OriginalReleaseDate	
						$contenu_fichier = str_replace("</ReleaseDetailsByTerritory>","\t\t\t<OriginalReleaseDate>".$liste_info['globaloriginalreleasedate']."</OriginalReleaseDate>\r\n\t\t</ReleaseDetailsByTerritory>",$contenu_fichier);
				

				
				//***********************************************************************************************	
				//Overwriting the old XML by the new
					$fp = fopen($xml_a_traiter,"w");				
					/*POUR FAIRE DES TESTS*///$fp = fopen(str_replace(".xml","_new.xml",$xml_a_traiter),"w");
					fwrite($fp,$contenu_fichier);
					fClose($fp);
				
				//4. Suppression de la 2ème partie <TechnicalSoundRecordingDetails>
					unset($contenu_fichier);
					$tableau_xml = file($xml_a_traiter);
					
					for($d=0;$d<count($tableau_xml);$d++)
					{
						//if(trim($tableau_xml[$d])=="</TechnicalSoundRecordingDetails>" AND trim($tableau_xml[$d+1])=="<TechnicalSoundRecordingDetails>")
						if(stristr($tableau_xml[$d+1],"<TechnicalSoundRecordingDetails>")==true AND stristr($tableau_xml[$d+3],"IsPreview")==true)
						{
							/*
							$tableau_xml[$d+1]	= "";
							$tableau_xml[$d+2] 	= "";
							$tableau_xml[$d+3] 	= "";
							$tableau_xml[$d+4] 	= "";
							$tableau_xml[$d+5] 	= "";
							$tableau_xml[$d+6] 	= "";
							$tableau_xml[$d+7] 	= "";
							$tableau_xml[$d+8] 	= "";
							$tableau_xml[$d+9] 	= "";
							$tableau_xml[$d+10] = "";		
							*/

							if(stristr($tableau_xml[$d+1],"TechnicalSoundRecordingDetails")==true)
								$tableau_xml[$d+1] = "";
							
							if(stristr($tableau_xml[$d+2],"TechnicalResourceDetailsReference")==true)
								$tableau_xml[$d+2] = "";
							
							if(stristr($tableau_xml[$d+3],"IsPreview")==true)
								$tableau_xml[$d+3] = "";	

							if(stristr($tableau_xml[$d+4],"PreviewDetails")==true)
								$tableau_xml[$d+4] = "";	

							if(stristr($tableau_xml[$d+5],"StartPoint")==true)
								$tableau_xml[$d+5] = "";	

							if(stristr($tableau_xml[$d+6],"EndPoint")==true)
								$tableau_xml[$d+6] = "";	

							if(stristr($tableau_xml[$d+7],"Duration")==true)
								$tableau_xml[$d+7] = "";	

							if(stristr($tableau_xml[$d+8],"ExpressionType")==true)
								$tableau_xml[$d+8] = "";	

							if(stristr($tableau_xml[$d+9],"PreviewDetails")==true)
								$tableau_xml[$d+9] = "";	

							if(stristr($tableau_xml[$d+10],"TechnicalSoundRecordingDetails")==true)
								$tableau_xml[$d+10] = "";	
							
						}
						$contenu_fichier .= $tableau_xml[$d];
					}						
					$fp = fopen($xml_a_traiter,"w");				
					/*POUR FAIRE DES TESTS*///$fp = fopen(str_replace(".xml","_new2.xml",$xml_a_traiter),"w");
					fwrite($fp,$contenu_fichier);
					fClose($fp);
					
				//***********************************************************************************************
				//Déplacement de la livraison vers ready_to_ingest
					$repertoire_ready_to_ingest = $conf['main_directory']."/".$conf['ready_to_ingest']."/".$conf['account_name'][$c];
					$cmd = "mv ".$repertoire_livraison."/".$livraison_a_traiter[$a]." ".$repertoire_ready_to_ingest;
					//echo "cmd : ".$cmd."\r\n";
					exec($cmd);				
				
				}
			}		
	}
//***********************************************************************************************	
//Processing to determine the status of notifications to be sent
//***********************************************************************************************
//TMP (to remove)
//$req = "UPDATE sony_recuperation set status_recuperation='ok'";
//$resultat = $db_sony->query($req);	
	//======================================
	//For the acks FileOK
	//======================================	
		$status_recuperation = "ok";
		$file_status_ack = "FileOK";		
		$req1 = "SELECT message_id, count(*) as nb_message_id from sony_recuperation WHERE status_recuperation='".$status_recuperation."' AND status_ack in ('waiting') GROUP BY message_id";
		$resultat1 = $db_sony->query($req1);	
		while($ligne1=$resultat1->fetch(PDO::FETCH_ASSOC))
		{
			$message_id			= $ligne1['message_id'];	
			$nb_message_id_ok	= $ligne1['nb_message_id'];	
			
		//Recovery of the number of messages_id listed in the table (to compare)
			$req2 = "SELECT count(*) as nb_message_id from sony_recuperation WHERE message_id='".$message_id."'";
			$resultat2 = $db_sony->query($req2);	
			while($ligne2=$resultat2->fetch(PDO::FETCH_ASSOC))
			$nb_message_id	= $ligne2['nb_message_id'];	

			if($nb_message_id_ok==$nb_message_id)
			{			
				//UPDATE table sony_recuperation
					$req = "UPDATE sony_recuperation set status_ack='ready_to_send', file_status_ack='".$file_status_ack."' WHERE message_id='".$message_id."'";
					$resultat = $db_sony->query($req);		
			}
		}
	//======================================
	//For the acks ResourceCorrupt
	//======================================	
		$status_recuperation = "ko";
		$file_status_ack = "ResourceCorrupt";
		$req1 = "SELECT distinct message_id from sony_recuperation WHERE status_recuperation='".$status_recuperation."' AND status_ack in ('waiting') GROUP BY message_id";
		echo "req1 : ".$req1."\r\n";
		$resultat1 = $db_sony->query($req1);	
		while($ligne1=$resultat1->fetch(PDO::FETCH_ASSOC))
		{
			$message_id			= $ligne1['message_id'];	
		
		//UPDATE table sony_recuperation			
			$req = "UPDATE sony_recuperation set status_ack='ready_to_send', file_status_ack='".$file_status_ack."' WHERE message_id='".$message_id."'";
			echo "req : ".$req."\r\n";
			$resultat = $db_sony->query($req);		
		}
	
//***********************************************************************************************	
//Fermeture des fichiers de log
	fclose($fp_0_octet);

?>