<?php

/**
 * @author girish ramnani
 * @link https://github.com/girishramnani/php-login-minimal/
 * @license http://opensource.org/licenses/MIT MIT License
 */


// include the configs / constants for the database connection
require_once("config/db.php");

// load the registration class
require_once("classes/Registration.php");

// create the registration object. when this object is created, it will do all registration stuff automatically
// so this single line handles the entire registration process.
$registration = new Registration();

// show the register view (with the registration form, and messages/errors)
include("views/register.php");
