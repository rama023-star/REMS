<?php
require_once 'rems/includes/config.php';
$stmt = db()->query("SELECT user_id, email, role FROM users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
