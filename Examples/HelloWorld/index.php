<?php 
    include_once "../../xantase.php";
    (new Xantase())->xantase_build_output_to_file(__DIR__,__DIR__ . DIRECTORY_SEPARATOR . "js.js");
?>