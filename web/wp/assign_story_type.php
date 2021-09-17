<?php

$host = "database.internal";
$username = "";
$password = "";
$database = "main";
$prefix = "wp";



// Create connection!
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// FA
$idsFA = $conn->query("select p.ID from wp_posts p inner join wp_term_relationships tr on tr.object_id = p.ID where p.post_type = 'external_post' and tr.term_taxonomy_id = 20");

die(var_dump($idsFA));

// ITN
$idsITN = $conn->query("select p.ID from wp_posts p inner join wp_term_relationships tr on tr.object_id = p.ID where p.post_type = 'external_post' and tr.term_taxonomy_id = 195");
