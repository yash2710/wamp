<?php

/**
 * @author girish ramnani
 * @link https://github.com/girishramnani/php-login-minimal/
 * @license http://opensource.org/licenses/MIT MIT License
 */


define("DB_HOST", "localhost");
define("DB_NAME", "dlc_test");
define("DB_USER", "root");
define("DB_PASS", "");

foreach ( $_POST as $key => $value){
echo $key ." :" .$value ."\n";	
echo "\n";
}

// load the registration class

/**
 * Class registration
 * handles the user registration
 */
class Registration
{
    /**
     * @var object $db_connection The database connection
     */
    private $db_connection = null;
    /**
     * @var array $errors Collection of error messages
     */
    public $errors = array();
    /**
     * @var array $messages Collection of success / neutral messages
     */
    public $messages = array();

    /**
     * the function "__construct()" automatically starts whenever an object of this class is created,
     * you know, when you do "$registration = new Registration();"
     */
    public function __construct()
    {
        if (isset($_POST["submit"])) {
            $this->registerNewUser();
        }
    }

    /**
     * handles the entire registration process. checks all error possibilities
     * and creates a new user in the database if everything is fine
     */
    private function registerNewUser()
    {
        if (empty($_POST['user_name'])) {
            $this->errors[] = "Empty Username";
        } elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
            $this->errors[] = "Username cannot be shorter than 2 or longer than 64 characters";
        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
            $this->errors[] = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";
        } elseif (!empty($_POST['user_name'])
            && strlen($_POST['user_name']) <= 64
            && strlen($_POST['user_name']) >= 2
            && preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])

        ) {
            // create a database connection
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // change character set to utf8 and check it
            if (!$this->db_connection->set_charset("utf8")) {
                $this->errors[] = $this->db_connection->error;
            }

            // if no connection errors (= working database connection)
            if (!$this->db_connection->connect_errno) {

                // escaping, additionally removing everything that could be (html/javascript-) code
                $user_name = $this->db_connection->real_escape_string(strip_tags($_POST['user_name'], ENT_QUOTES));
             
             
                // crypt the user's password with PHP 5.5's password_hash() function, results in a 60 character
                // hash string. the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using
                // PHP 5.3/5.4, by the password hashing compatibility library
              

                // check if user or email address already exists
            
                    // write new user's data into database
                    $sql = "INSERT INTO `results` (user_name,marks,school)
                            VALUES('" . $user_name . "', '". $_POST['marks']. "','". $_POST['school'] . "');";
                    $query_new_user_insert = $this->db_connection->query($sql);

                    echo "\n".$sql;
                    // if user has been added successfully
                    if ($query_new_user_insert) {
                        $this->messages[] = "Your marks has been send successfully.";
                    } else {
                        $this->errors[] = "Sorry, your registration failed. Please go back and try again.";
                    }
                
            } else {
                $this->errors[] = "Sorry, no database connection.";
            }
        } else {
            $this->errors[] = "An unknown error occurred.";
        }
    }
}

// create the registration object. when this object is created, it will do all registration stuff automatically
// so this single line handles the entire registration process.
$registration = new Registration();

 if ($registration->errors) {
        foreach ($registration->errors as $error) {
            echo "<font color=\"#FF0000\">" .$error."</font>";
        }
    }
    if ($registration->messages) {
        foreach ($registration->messages as $message) {
            echo "<font color=\"green\">" .$message."</font>";
        }
		
    }
	
	header("Location: /app/index.html");
?>