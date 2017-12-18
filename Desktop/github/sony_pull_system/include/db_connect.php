<?php
/**
 * @file db_connect.php
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

 // Connection to the database sony
	try
	{
		$db_sony = new PDO("mysql:host=".$conf['db_host']."; dbname=".$conf['db_name'],
			$conf['db_user'], $conf['db_pass'], array (PDO::ATTR_PERSISTENT => false));
		$db_sony->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e)
	{		
		echo "DB 7digital_sony Connection Error : ".$e->getMessage()." [".$sql."]".$conf['retour_ligne'];
		exit();
	}

?>