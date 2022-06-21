<?php 
/**
 * Template Name: Trello Board Reader
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package MDLWP
 */
$trelloID = get_query_var( 'trelloID');

//cookie.txt to connect to trello
$cookie = get_stylesheet_directory() . '/js/cookie.txt';

$ch = curl_init ('https://trello.com/b/'.$trelloID.'.json');
curl_setopt ($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec ($ch);
 
header('Content-Type: application/json');
echo $output;
?>