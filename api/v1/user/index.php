<?php
include_once "../../../utils/Common.php";
include_once "../../../configs/DBContext.php";

$common = new Common();
$conn = new DBContext();
$conn = $conn->Connection();

echo $common->createToken(["data" => 1, "exp" => time()]);
