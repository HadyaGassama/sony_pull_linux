<?php
/**
 * @file functions.php
 *
 * @brief 
 * The purpose: That script is used to defined utils functions used in the script
 * Sony pull system (7digital plateform)
 * 
 * @author Hadya GASSAMA  
 *
 * @version 1
 * @since 25/08/2017
 */

/* Function List
* 
* - xml2array
* - get_info_from_xml
*/

/**
* function used to parse an XML File to aa PHP Array
* @param char $contents 
* @param boolean $get_attributes 
* @return array
*/
function xml2array($contents, $get_attributes=1) { 
    if(!$contents) return array(); 

    if(!function_exists('xml_parser_create')) { 
        //print "'xml_parser_create()' function not found!"; 
        return array(); 
    }
    //Get the XML parser of PHP - PHP must have this module for the parser to work 

    $parser = xml_parser_create(); 
    xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 ); 
    xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
    $valid = xml_parse_into_struct( $parser, $contents, $xml_values ); 
    if ($valid == 0)
       {
       return "";
       }
    xml_parser_free( $parser ); 

    if(!$xml_values) return "";//Hmm... 

    //Initializations 
    $xml_array = array(); 
    $parents = array(); 
    $opened_tags = array(); 
    $arr = array(); 

    $current = &$xml_array; 

    //Go through the tags. 
    foreach($xml_values as $data) { 
        unset($attributes,$value);//Remove existing values, or there will be trouble 
        extract($data);//We could use the array by itself, but this cooler. 

        $result = ''; 
        if($get_attributes) {//The second argument of the function decides this. 
            $result = array(); 
            if(isset($value)) {
					$result['value'] = $value;//$value;//utf8_decode($value); //utf8dec($value)
					}

            //Set the attributes too. 
            if(isset($attributes)) { 
                foreach($attributes as $attr => $val) { 
                    if($get_attributes == 1) $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr' 
                    /**  :TODO: should we change the key name to '_attr'? Someone may use the tagname 'attr'. Same goes for 'value' too */ 
                } 
            } 
        } elseif(isset($value)) { 
            $result = $value; 
        } 

        //See tag status and do the needed. 
        if($type == "open") {//The starting of the tag '<tag>' 
            $parent[$level-1] = &$current; 

            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag 
                $current[$tag] = $result; 
                $current = &$current[$tag]; 

            } else { //There was another element with the same tag name 
                if(isset($current[$tag][0])) { 
                    array_push($current[$tag], $result); 
                } else { 
                    $current[$tag] = array($current[$tag],$result); 
                } 
                $last = count($current[$tag]) - 1; 
                $current = &$current[$tag][$last]; 
            } 

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />' 
            //See if the key is already taken. 
            if(!isset($current[$tag])) { //New Key 
                $current[$tag] = $result; 

            } else { //If taken, put all things inside a list(array) 
                if((is_array($current[$tag]) and $get_attributes == 0)//If it is already an array... 
                        or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) { 
                    array_push($current[$tag],$result); // ...push the new element into that array. 
                } else { //If it is not an array... 
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value 
                } 
            } 

        } elseif($type == 'close') { //End of tag '</tag>' 
            $current = &$parent[$level-1]; 
        } 
    } 

    return($xml_array); 
} 
////////////////////////////  END FUNCTION /////////////////////////////////////

function get_info_from_xml($xml_a_traiter)
{
	//Création des tableaux
		$liste_info['message_id'] = array();
		$liste_info['isrc'] = array();
		$liste_info['technicalresourcedetailsreference'] = array();
		$liste_info['audiocodectype'] = array();
		$liste_info['bitrate'] = array();
		$liste_info['url'] = array();		
		$liste_info['fichier_destination'] = array();	
		$liste_info['album_grid'] = array();	
			
	//Reading xml file		
		$xmlContent = file_get_contents($xml_a_traiter);	
		if ($xmlContent == "") 
		{
			echo "Fichier XML Vide!" . "\n";
		}
		else 
		{
			$tabXML = array();
			$tabXML = xml2array($xmlContent);	
		}
	//************************************************************************************************************	
	//Récuperation du MessageId
		if(isset($tabXML['ernm:PurgeReleaseMessage']))
			$liste_info['message_id'] = $tabXML['ernm:PurgeReleaseMessage']['MessageHeader']['MessageId']['value'];
		else
			$liste_info['message_id'] = $tabXML['ernm:NewReleaseMessage']['MessageHeader']['MessageId']['value'];
	//************************************************************************************************************
	//For processing the array in the same manner even if there is one or more sub-block
		if (isset($tabXML['ernm:NewReleaseMessage']['ResourceList']['SoundRecording'][0])) 
		{
			//There are several sub-block
			$ProductInfoSoundRecording = null;
			$ProductInfoSoundRecording = $tabXML['ernm:NewReleaseMessage']['ResourceList']['SoundRecording'];
		}
		else 
		{
			//There is one sub-block
			$ProductInfoSoundRecording = null;
			$ProductInfoSoundRecording[0] = $tabXML['ernm:NewReleaseMessage']['ResourceList']['SoundRecording'];
		}
		
	//************************************************************************************************************	
	$nb_SoundRecording = count($ProductInfoSoundRecording);
	for($element=0;$element<$nb_SoundRecording;$element++)
	{	
		$info_track_tmp1['SoundRecording_SoundRecordingId_ISRC'] 							= $ProductInfoSoundRecording[$element]['SoundRecordingId']['ISRC']['value'];
		$info_track_tmp1['SoundRecording_ResourceReference'] 								= $ProductInfoSoundRecording[$element]['ResourceReference']['value'];

		//************************************************************************************************************
		//For processing the array in the same manner even if there is one or more sub-block			
			if (isset($ProductInfoSoundRecording[$element]['SoundRecordingDetailsByTerritory']['TechnicalSoundRecordingDetails'][0])) 
			{
				//There are several sub-block
				$ProductInfoTechnicalSoundRecordingDetails = null;
				$ProductInfoTechnicalSoundRecordingDetails = $ProductInfoSoundRecording[$element]['SoundRecordingDetailsByTerritory']['TechnicalSoundRecordingDetails'];
			}
			else 
			{
				//There is one sub-block
				$ProductInfoTechnicalSoundRecordingDetails = null;
				$ProductInfoTechnicalSoundRecordingDetails[0] = $ProductInfoSoundRecording[$element]['SoundRecordingDetailsByTerritory']['TechnicalSoundRecordingDetails'];
			}		
		//************************************************************************************************************
		$nb_TechnicalSoundRecordingDetails = count($ProductInfoTechnicalSoundRecordingDetails);
		
		for($y=0;$y<$nb_TechnicalSoundRecordingDetails;$y++)
		{
			if(isset($ProductInfoTechnicalSoundRecordingDetails[$y]['BitsPerSample']['value']))
			{
				array_push($liste_info['isrc'],$ProductInfoSoundRecording[$element]['SoundRecordingId']['ISRC']['value']);
				array_push($liste_info['technicalresourcedetailsreference'],$ProductInfoTechnicalSoundRecordingDetails[$y]['TechnicalResourceDetailsReference']['value']);
				array_push($liste_info['audiocodectype'],$ProductInfoTechnicalSoundRecordingDetails[$y]['AudioCodecType']['value']);
				array_push($liste_info['bitrate'],$ProductInfoTechnicalSoundRecordingDetails[$y]['BitsPerSample']['value']);			
				array_push($liste_info['url'],$ProductInfoTechnicalSoundRecordingDetails[$y]['File']['URL']['value']);			
				array_push($liste_info['fichier_destination'],$ProductInfoTechnicalSoundRecordingDetails[$y]['TechnicalResourceDetailsReference']['value']."_".$ProductInfoTechnicalSoundRecordingDetails[$y]['AudioCodecType']['value']."_".$ProductInfoTechnicalSoundRecordingDetails[$y]['BitsPerSample']['value']."_".$ProductInfoSoundRecording[$element]['SoundRecordingId']['ISRC']['value'].".".strtolower($ProductInfoTechnicalSoundRecordingDetails[$y]['AudioCodecType']['value']));						
			}
		}
	}
	//************************************************************************************************************	
		//For processing the array in the same manner even if there is one or more sub-block
			if (isset($tabXML['ernm:NewReleaseMessage']['ReleaseList']['Release'][0])) 
			{
				//There are several sub-block
				$ProductInfoRelease = null;
				$ProductInfoRelease = $tabXML['ernm:NewReleaseMessage']['ReleaseList']['Release'];
			}
			else 
			{
				//There is one sub-block
				$ProductInfoRelease = null;
				$ProductInfoRelease[0] = $tabXML['ernm:NewReleaseMessage']['ReleaseList']['Release'];
			}
		//************************************************************************************************************	
		$nb_Release = count($ProductInfoRelease);
		for($element=0;$element<$nb_Release;$element++)
		{
			if($ProductInfoRelease[$element]['ReleaseType']['value']=="Album" or $ProductInfoRelease[$element]['ReleaseType']['value']=="Single" or $ProductInfoRelease[$element]['ReleaseType']['value']=="Bundle" or $ProductInfoRelease[$element]['ReleaseType']['value']=="ClassicalAlbum")
			{
				$liste_info['album_grid'] = $ProductInfoRelease[$element]['ReleaseId']['GRid']['value'];
				$liste_info['globaloriginalreleasedate'] = $ProductInfoRelease[$element]['GlobalOriginalReleaseDate']['value'];
			}
		}
	//************************************************************************************************************

	return $liste_info;
}

////////////////////////////  END FUNCTION /////////////////////////////////////

function download_deliveries_on_ftp()
{
	global $conf;
	
	$fp = fopen($conf['log_file'],"a");
	
	$conn_id = ftp_connect($conf['ftp_server'],$conf['ftp_port']); 
	$login_result = ftp_login($conn_id,$conf['ftp_login'],$conf['ftp_password']); 
	if ((!$conn_id) || (!$login_result)) 
	{
		fwrite($fp,date("Ymd H:i:s")." ===> The ftp connection to the Sony server failed.\r\n");		
	}		
	else
	{
		ftp_pasv($conn_id, true); 
	}		
	ftp_chdir($conn_id, $conf['repertoire_ftp_general']);

	$liste_repertoire_generale = ftp_nlist($conn_id, ".");
	$max = count($liste_repertoire_generale);
	
	//Vérification de la présence du répertoire complete
		$liste_batch = array();
		for($element=0;$element<$max;$element++)
		{
			ftp_chdir($conn_id,$conf['repertoire_ftp_general']."/".$liste_repertoire_generale[$element]);
			$contenu_repertoire = ftp_nlist($conn_id, ".");
			$key = array_search("complete", $contenu_repertoire);
			if($key=="0")
			{
				array_push($liste_batch,$liste_repertoire_generale[$element]);			
				$destination = $conf['main_directory']."\\todo\\".$liste_repertoire_generale[$element];
				@mkdir($destination,0777,true);
				$contenu_batch = ftp_nlist($conn_id,".");
				echo "contenu_batch : \r\n";
				echo "<pre>";
				print_r($contenu_batch);
				echo "</pre>";
			
				for($i=0;$i<count($contenu_batch);$i++)
				{	
					ftp_get($conn_id,$destination."\\".$contenu_batch[$i],$contenu_batch[$i],FTP_BINARY);				
				}
			}
		}	
		$nb_fichiers = count($liste_batch);
		ftp_close($conn_id);
		
		fwrite($fp,date("Ymd H:i:s")." ===> ".$nb_fichiers." batchs have been downloaded.\r\n");
		fclose($fp);
}

////////////////////////////  END FUNCTION /////////////////////////////////////

function download_url($url,$fichier_destination)
{
	global $conf;
	
//Pause
	$pause = 5;//En seconde
	
//log file
	$fp_ok = fopen($conf['log_file_recuperation_ok'],"a");
	$fp_ko = fopen($conf['log_file_recuperation_ko'],"a");
	$fp_0_octet = fopen($conf['log_file_recuperation_0_octet'],"a");					
	
//Media Recovery
	$status = "";
	if(!copy($url,$fichier_destination))
	{
		fwrite($fp_ko,date("Ymd H:i:s")." ===> COPIE KO (tentative 1/3) ===> ".$url." ===> ".$fichier_destination."\r\n");
		sleep($pause);
		if(!copy($url,$fichier_destination))
		{
			fwrite($fp_ko,date("Ymd H:i:s")." ===> COPIE KO (tentative 2/3) ===> ".$url." ===> ".$fichier_destination."\r\n");
			sleep($pause);
			if(!copy($url,$fichier_destination))
			{
				fwrite($fp_ko,date("Ymd H:i:s")." ===> COPIE KO (tentative 3/3) ===> ".$url." ===> ".$fichier_destination."\r\n");
				sleep($pause);
			}
			else
			{
				fwrite($fp_ok,date("Ymd H:i:s")." ===> COPIE OK ===> ".$url." ===> ".$fichier_destination."\r\n");			
				$status = "ok";		
			}							
		}
		else
		{
			fwrite($fp_ok,date("Ymd H:i:s")." ===> COPIE OK ===> ".$url." ===> ".$fichier_destination."\r\n");			
			$status = "ok";		
		}						
	}
	else
	{
		fwrite($fp_ok,date("Ymd H:i:s")." ===> COPIE OK ===> ".$url." ===> ".$fichier_destination."\r\n");			
		$status = "ok";		
	}	

//Closing Log Files
	fclose($fp_ok);
	fclose($fp_ko);
	fclose($fp_0_octet);
	
	if($status=="")
		$status="ko";
				
	return $status;				
}
?>