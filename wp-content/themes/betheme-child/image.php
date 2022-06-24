<?php
//cookie.txt to connect to trello
$cookie =  dirname(__FILE__) .'/js/cookie.txt';

// File to download
$url = $_GET['imgurl'];

// curl the file
$ch = curl_init ();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
$output = curl_exec ($ch);
$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
$errors = curl_error($ch);
curl_close($ch);

header("Content-Type: image/jpeg");
echo $output;