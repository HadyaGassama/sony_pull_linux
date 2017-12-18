<?php
/**
 * @file conf.php
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
// ******************************************************************* //
// ***						 Variables    					       *** //
// ******************************************************************* //
	$conf['main_directory']	= "/home/contentops/sony_pull_system";
	$conf['dir_ack'] 		= "acknowledgement";
	
// ******************************************************************* //
// ***						 Database Variables    				   *** //
// ******************************************************************* //
	$conf['db_host'] = "localhost"; 
	$conf['db_name'] = "7digital_sony";
	$conf['db_user'] = "contentops";
	$conf['db_pass'] = "affonsolopez";
	
// ******************************************************************* //
// ***   	                    Dossier à traiter                  *** //
// ******************************************************************* //	
	$conf['account_name'][0] = "NowFeed";
	$conf['account_name'][1] = "SonyHd";
	$conf['account_name'][2] = "SonyRed";
	$conf['account_name'][3] = "SonySet";
	$conf['account_name'][4] = "SonyShelf";
	
	$conf['dir_todo'] = "todo";
	$conf['ready_to_ingest'] = "ready_to_ingest";

// ******************************************************************* //
// ***						 FTP Variables    					   *** //
// ******************************************************************* //
	/*
	$conf['ftp_server'] 			= "";
	$conf['ftp_login'] 				= "";
	$conf['ftp_password'] 			= "";
	$conf['ftp_port'] 				= "";
	$conf['repertoire_ftp_general'] = "";
	*/

// ******************************************************************* //
// ***   	                    Log files                          *** //
// ******************************************************************* //
	$conf['log_file'] 						= $conf['main_directory']."/log/log.txt"; 
	$conf['log_file_recuperation_ok'] 		= $conf['main_directory']."/log/".date("Ym")."_log_recuperation_ok.txt"; 
	$conf['log_file_recuperation_ko'] 		= $conf['main_directory']."/log/".date("Ym")."_log_recuperation_ko.txt"; 
	$conf['log_file_recuperation_0_octet'] 	= $conf['main_directory']."/log/".date("Ym")."_log_recuperation_0_octet.txt"; 
	

	
?>