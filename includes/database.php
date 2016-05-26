<?php
/**
 * taoResultsPage Database connection
 *
 * @author Franc Romain
 * @package taoResultsPage
 * @license GPL-2.0
 *
 */
 
try{
	// Récupération de la configuration BD
	$config = json_encode((array)(include 'config/generis/persistences.conf.php'));
	$config = str_replace("\u0000", "", $config);
	$config = json_decode($config);
	$config = (array) $config->{'oat\oatbox\Configurableoptions'}->{'persistences'}->{'default'};

	$db = new PDO("mysql:host=". $config["host"] .";dbname=". $config["dbname"] .";port=3306",$config["user"],$config["password"]);
	$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	$db->exec("SET NAMES 'utf8'");
	
} catch (Exception $e){	
	die("Impossible de se connecter à la base de données");
}