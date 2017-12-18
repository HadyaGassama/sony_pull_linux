<?php
chdir("/home/contentops/sony_pull_system");
require_once("include/conf.php");
require_once("include/functions.php");
require_once("include/db_connect.php");

//***********************************************************************************************
//Information à renseigner dans l'en-tête de l'XML
	$MessageSender_PartyId 		= "PADPIDA2007040502I";
	$MessageSender_PartyName 	= "7 Digital 3.8.1 Test Feed";
	$MessageRecipient_PartyId 	= "PADPIDA2011021601H";
	$MessageRecipient_PartyName	= "Sony Music Entertainment";

//***********************************************************************************************
//Traitement pour identifier le status de l'acquitement à envoyer
	$status = "";	
	$req1 = "SELECT distinct account_name, message_id, file_status_ack, album_grid FROM sony_recuperation WHERE status_ack='ready_to_send'";
	$resultat1 = $db_sony->query($req1);	
	while($ligne1=$resultat1->fetch(PDO::FETCH_ASSOC))
	{
		$account_name		= $ligne1['account_name'];	
		$message_id 		= $ligne1['message_id'];	
		$file_status_ack 	= $ligne1['file_status_ack'];
		$album_grid 		= $ligne1['album_grid'];
		
	//***********************************************************************************************
	//Création de l'acquitement	
		$date = new DateTime();
		$MessageCreatedDateTime = $date->format(DateTime::ISO8601);	

		$ack_file_source = $conf['main_directory']."/".$conf['dir_ack']."/".$account_name."/ACK_".$album_grid.".xml";
		echo "ack_file_source : ".$ack_file_source."\r\n";
		$fp = fopen($ack_file_source,"w");

		$xml_file = "";
		$xml_file .= "<ns3:FtpAcknowledgementMessage MessageVersionId=\"1.0\" xmlns:ns2=\"http://www.w3org/2000/09/xmldsig#\" xmlns:ns3=\"http://ddex.net/xml/ern-c/14\">\r\n";
		$xml_file .= "\t<MessageHeader>\r\n";
		$xml_file .= "\t\t<MessageSender>\r\n";
		$xml_file .= "\t\t\t<PartyId>".$MessageSender_PartyId."</PartyId>\r\n";
		$xml_file .= "\t\t\t<PartyName>\r\n";
		$xml_file .= "\t\t\t\t<FullName>".$MessageSender_PartyName."</FullName>\r\n";
		$xml_file .= "\t\t\t</PartyName>\r\n";
		$xml_file .= "\t\t</MessageSender>\r\n";
		$xml_file .= "\t\t<MessageRecipient>\r\n";
		$xml_file .= "\t\t\t<PartyId>".$MessageRecipient_PartyId."</PartyId>\r\n";
		$xml_file .= "\t\t\t<PartyName>\r\n";
		$xml_file .= "\t\t\t\t<FullName>".$MessageRecipient_PartyName."</FullName>\r\n";
		$xml_file .= "\t\t\t</PartyName>\r\n";
		$xml_file .= "\t\t</MessageRecipient>\r\n";
		$xml_file .= "\t\t<MessageCreatedDateTime>".$MessageCreatedDateTime."</MessageCreatedDateTime>\r\n";	
		$xml_file .= "\t</MessageHeader>\r\n";
		$xml_file .= "\t<AcknowledgedFile>\r\n";
		$xml_file .= "\t\t<ReleaseId>".$message_id."</ReleaseId>\r\n";
		$xml_file .= "\t\t<Date>".$MessageCreatedDateTime."</Date>\r\n";
		$xml_file .= "\t</AcknowledgedFile>\r\n";
		$xml_file .= "\t<FileStatus>".$file_status_ack."</FileStatus>\r\n";
		$xml_file .= "</ns3:FtpAcknowledgementMessage>\r\n";

		fwrite($fp,$xml_file);
		fclose($fp);
		
		//***********************************************************************************************		
		//Mise à jour de la table "sony_recuperation"
			$req = "UPDATE sony_recuperation set status_ack='sent' WHERE message_id=".$message_id;
			echo "req : ".$req."\r\n";
			$resultat = $db_sony->query($req);	
	}

?>