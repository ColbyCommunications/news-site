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
$idsFA = $conn->query("select p.ID from wp_posts p inner join wp_term_relationships tr on p.ID = tr.object_id where tr.term_taxonomy_id = 20 and p.post_type = 'external_post' and post_status='publish'");

die(var_dump($idsFA));

// ITN
$idsITN = $conn->query("select p.ID from wp_posts p inner join wp_term_relationships tr on p.ID = tr.object_id where tr.term_taxonomy_id = 195 and p.post_type = 'external_post' and post_status='publish'");
