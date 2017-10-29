<?php 
$I = new AcceptanceTester($scenario);
$I ->amOnPage('/login.php');
$I ->see('UNSW Christians Sign-up');
