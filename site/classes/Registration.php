<?php

/**
 * Handles the user registration
 * @author Panique
 * @link http://www.php-login.net
 * @link https://github.com/panique/php-login-advanced/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Registration
{
	/**
	 * @var object $db_connection The database connection
	 */
	private $db_connection            = null;
	/**
	 * @var bool success state of registration
	 */
	public  $registration_successful  = false;
	/**
	 * @var bool success state of verification
	 */
	public  $verification_successful  = false;
	/**
	 * @var array collection of error messages
	 */
	public  $errors                   = array();
	/**
	 * @var array collection of success / neutral messages
	 */
	public  $messages                 = array();

	/**
	 * the function "__construct()" automatically starts whenever an object of this class is created,
	 * you know, when you do "$login = new Login();"
	 */
	public function __construct()
	{
		session_start();

		// if we have such a POST request, call the registerNewUser() method
		if (isset($_POST["register"])) {
			$this->registerNewUser($_POST['username'], $_POST['email'], $_POST['name_first'], $_POST['name_last'], $_POST['name_suffix'],  $_POST['expected_graduation'], $_POST['birth_year'], $_POST['birth_month'], $_POST['birth_day'], $_POST['password_new'], $_POST['password_repeat'], $_POST["captcha"]);
		// if we have such a GET request, call the verifyNewUser() method
		} else if (isset($_GET["id"]) && isset($_GET["verification_code"])) {
			$this->verifyNewUser($_GET["id"], $_GET["verification_code"]);
		}
	}

	/**
	 * Checks if database connection is opened and open it if not
	 */
	private function databaseConnection()
	{
		// connection already opened
		if ($this->db_connection != null) {
			return true;
		} else {
			// create a database connection, using the constants from config/config.php
			try {
				// Generate a database connection, using the PDO connector
				// @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
				// Also important: We include the charset, as leaving it out seems to be a security issue:
				// @see http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers#Connecting_to_MySQL says:
				// "Adding the charset to the DSN is very important for security reasons,
				// most examples you'll see around leave it out. MAKE SURE TO INCLUDE THE CHARSET!"
				$this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
				return true;
			// If an error is catched, database connection failed
			} catch (PDOException $e) {
				$this->errors[] = "Database connection problem.";
				return false;
			}
		}
	}

	/**
	 * handles the entire registration process. checks all error possibilities, and creates a new user in the database if
	 * everything is fine
	 */
	private function registerNewUser($username, $email, $name_first, $name_last, $name_suffix, $expected_graduation, $birth_year, $birth_month, $birth_day, $password, $password_repeat, $captcha)
	{
		// we just remove extra space on username and email
		$username  = trim($username);
		$email = trim($email);

		// check provided data validity
		// TODO: check for "return true" case early, so put this first
		if (strtolower($captcha) != strtolower($_SESSION['captcha'])) {
			$this->errors[] = "Captcha was wrong!";
		} elseif (empty($username)) {
			$this->errors[] = "Username field was empty";
		} elseif (empty($password) || empty($password_repeat)) {
			$this->errors[] = "Password field was empty";
		} elseif ($password !== $password_repeat) {
			$this->errors[] = "Password and password repeat are not the same";
		} elseif (strlen($password) < 6) {
			$this->errors[] = "Password has a minimum length of 6 characters";
		} elseif (strlen($username) > 64 || strlen($username) < 2) {
			$this->errors[] = "Username cannot be shorter than 2 or longer than 64 characters";
		} elseif (!preg_match('/^[a-z\d]{2,64}$/i', $username)) {
			$this->errors[] = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";
		} elseif (empty($email)) {
			$this->errors[] = "Email cannot be empty";
		} elseif (empty($name_first)) {
			$this->errors[] = "First name cannot be empty";
		} elseif (empty($name_last)) {
			$this->errors[] = "Last name cannot be empty";
		} elseif (empty($expected_graduation)) {
			$this->errors[] = "Class cannot be empty";
		} elseif (empty($birth_year)) {
			$this->errors[] = "Birth year cannot be empty";
		} elseif (empty($birth_month)) {
			$this->errors[] = "Birth month cannot be empty";
		} elseif (empty($birth_day)) {
			$this->errors[] = "Birth day cannot be empty";
		} elseif (strlen($email) > 64) {
			$this->errors[] = "Email cannot be longer than 64 characters";
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->errors[] = "Your email address is not in a valid email format";

		// finally if all the above checks are ok
		} else if ($this->databaseConnection()) {
			// check if username or email already exists
			$query_check_username = $this->db_connection->prepare('SELECT username, email FROM users WHERE username=:username OR email=:email');
			$query_check_username->bindValue(':username', $username, PDO::PARAM_STR);
			$query_check_username->bindValue(':email', $email, PDO::PARAM_STR);
			$query_check_username->execute();
			$result = $query_check_username->fetchAll();

			// if username or/and email find in the database
			// TODO: this is really awful!
			if (count($result) > 0) {
				for ($i = 0; $i < count($result); $i++) {
					$this->errors[] = ($result[$i]['username'] == $username) ? "Sorry, that username is already taken. Please choose another one." : "This email address is already registered. Please use the \"I forgot my password\" page if you don't remember it.";
				}
			} else {
				// check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
				// if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
				$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

				// crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
				// the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
				// compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
				// want the parameter: as an array with, currently only used with 'cost' => XX.
				$password_hash = password_hash($password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
				// generate random hash for email verification (40 char string)
				$activation_hash = sha1(uniqid(mt_rand(), true));
				
				// write new users data into database
				$query_new_user_insert = $this->db_connection->prepare('INSERT INTO users (username, password_hash, email, activation_hash, is_active, registration_ip, registration_time, name_first, name_last, name_suffix, expected_graduation, birth_year, birth_day, birth_month) VALUES(:username, :password_hash, :email, :activation_hash, :is_active, :registration_ip, :registration_time, :name_first, :name_last, :name_suffix, :expected_graduation, :birth_year, :birth_day, :birth_month)');
				$query_new_user_insert->bindValue(':username', $username, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':email', $email, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':activation_hash', $activation_hash, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':is_active', "0", PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':registration_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':registration_time', time(), PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':name_first', $name_first, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':name_last', $name_last, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':name_suffix', $name_suffix, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':expected_graduation', $expected_graduation, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':birth_year', $birth_year, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':birth_day', $birth_day, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':birth_month', $birth_month, PDO::PARAM_STR);
				$query_new_user_insert->execute();

				// id of new user
				$userID = $this->db_connection->lastInsertId();

				if ($query_new_user_insert) {
					// send a verification email
					if ($this->sendVerificationEmail($userID, $email, $activation_hash)) {
						// when mail has been send successfully
						$this->messages[] = "Your account has been created successfully and we have sent you an email. Please click the verification link within that mail.";
						$this->registration_successful = true;
					} else {
						// delete this users account immediately, as we could not send a verification email
						$query_delete_user = $this->db_connection->prepare('DELETE FROM users WHERE userID=:userID');
						$query_delete_user->bindValue(':userID', $userID, PDO::PARAM_INT);
						$query_delete_user->execute();

						$this->errors[] = "Sorry, we could not send you an verification mail. Your account has NOT been created.";
					}
				} else {
					$this->errors[] = "Sorry, your registration failed. Please go back and try again.";
				}
			}
		}
	}

	/*
	 * sends an email to the provided email address
	 * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
	 */
	public function sendVerificationEmail($userID, $email, $activation_hash)
	{
		$mail = new PHPMailer;

		// please look into the config/config.php for much more info on how to use this!
		// use SMTP or use mail()
		if (EMAIL_USE_SMTP) {
			// Set mailer to use SMTP
			$mail->IsSMTP();
			//useful for debugging, shows full SMTP errors
			//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
			// Enable SMTP authentication
			$mail->SMTPAuth = EMAIL_SMTP_AUTH;
			// Enable encryption, usually SSL/TLS
			if (defined(EMAIL_SMTP_ENCRYPTION)) {
				$mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
			}
			// Specify host server
			$mail->Host = EMAIL_SMTP_HOST;
			$mail->Username = EMAIL_SMTP_USERNAME;
			$mail->Password = EMAIL_SMTP_PASSWORD;
			$mail->Port = EMAIL_SMTP_PORT;
		} else {
			$mail->IsMail();
		}

		$mail->From = EMAIL_VERIFICATION_FROM;
		$mail->FromName = EMAIL_VERIFICATION_FROM_NAME;
		$mail->AddAddress($email);
		$mail->Subject = EMAIL_VERIFICATION_SUBJECT;

		$link = EMAIL_VERIFICATION_URL.'?id='.urlencode($userID).'&verification_code='.urlencode($activation_hash);

		// the link to your register.php, please set this value in config/email_verification.php
		$mail->Body = EMAIL_VERIFICATION_CONTENT.' '.$link;

		if(!$mail->Send()) {
			$this->errors[] = "Verification Mail NOT successfully sent! Error: " . $mail->ErrorInfo;
			return false;
		} else {
			return true;
		}
	}

	/**
	 * checks the id/verification code combination and set the user's activation status to true (=1) in the database
	 */
	public function verifyNewUser($userID, $activation_hash)
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// try to update user with specified information
			$query_update_user = $this->db_connection->prepare('UPDATE users SET is_active = 1, activation_hash = NULL WHERE userID = :userID AND activation_hash = :activation_hash');
			$query_update_user->bindValue(':userID', intval(trim($userID)), PDO::PARAM_INT);
			$query_update_user->bindValue(':activation_hash', $activation_hash, PDO::PARAM_STR);
			$query_update_user->execute();

			if ($query_update_user->rowCount() > 0) {
				$this->verification_successful = true;
				$this->messages[] = "Activation was successful! You can now log in!";
			} else {
				$this->errors[] = "Sorry, no such id/verification code combination here...";
			}
		}
	}
}
