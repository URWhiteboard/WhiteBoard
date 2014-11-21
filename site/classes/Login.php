<?php

/**
 * handles the user login/logout/session
 * @author Panique
 * @link http://www.php-login.net
 * @link https://github.com/panique/php-login-advanced/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Login
{
	/**
	 * @var object $db_connection The database connection
	 */
	public $db_connection = null;
	/**
	 * @var int $userID The user's id
	 */
	private $userID = null;
	/**
	 * @var string $username The user's name
	 */
	private $username = "";
	/**
	 * @var string $type The user's type
	 */
	private $type = "";
	/**
	 * @var string $email The user's mail
	 */
	private $email = "";
	/**
	 * @var boolean $user_is_logged_in The user's login status
	 */
	private $user_is_logged_in = false;
	/**
	 * @var string $user_gravatar_image_url The user's gravatar profile pic url (or a default one)
	 */
	public $user_gravatar_image_url = "";
	/**
	 * @var string $user_gravatar_image_tag The user's gravatar profile pic url with <img ... /> around
	 */
	public $user_gravatar_image_tag = "";
	/**
	 * @var boolean $password_reset_link_is_valid Marker for view handling
	 */
	private $password_reset_link_is_valid  = false;
	/**
	 * @var boolean $password_reset_was_successful Marker for view handling
	 */
	private $password_reset_was_successful = false;
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
	 * you know, when you do "$login = new Login();"
	 */
	public function __construct()
	{
		// create/read session
		session_start();

		// TODO: organize this stuff better and make the constructor very small
		// TODO: unite Login and Registration classes ?

		// check the possible login actions:
		// 1. logout (happen when user clicks logout button)
		// 2. login via session data (happens each time user opens a page on your php project AFTER he has successfully logged in via the login form)
		// 3. login via cookie
		// 4. login via post data, which means simply logging in via the login form. after the user has submit his login/password successfully, his
		//    logged-in-status is written into his session data on the server. this is the typical behaviour of common login scripts.

		// if user tried to log out
		if (isset($_GET["logout"])) {
			$this->doLogout();

		// if user has an active session on the server
		} elseif (!empty($_SESSION['username']) && ($_SESSION['user_logged_in'] == 1)) {
			$this->loginWithSessionData();

			// checking for form submit from editing screen
			// user try to change his username
			if (isset($_POST["edit_submit_name"])) {
				// function below uses use $_SESSION['userID'] et $_SESSION['email']
				$this->editUserName($_POST['username']);
			// user try to change his email
			} elseif (isset($_POST["edit_submit_email"])) {
				// function below uses use $_SESSION['userID'] et $_SESSION['email']
				$this->editUserEmail($_POST['email']);
			// user try to change his password
			} elseif (isset($_POST["edit_submit_password"])) {
				// function below uses $_SESSION['username'] and $_SESSION['userID']
				$this->editUserPassword($_POST['password_old'], $_POST['password_new'], $_POST['password_repeat']);
			}

		// login with cookie
		} elseif (isset($_COOKIE['rememberme'])) {
			$this->loginWithCookieData();

		// if user just submitted a login form
		} elseif (isset($_POST["login"])) {
			if (!isset($_POST['user_rememberme'])) {
				$_POST['user_rememberme'] = null;
			}
			$this->loginWithPostData($_POST['username'], $_POST['password'], $_POST['user_rememberme']);
		}

		// checking if user requested a password reset mail
		if (isset($_POST["request_password_reset"]) && isset($_POST['username'])) {
			$this->setPasswordResetDatabaseTokenAndSendMail($_POST['username']);
		} elseif (isset($_GET["username"]) && isset($_GET["verification_code"])) {
			$this->checkIfEmailVerificationCodeIsValid($_GET["username"], $_GET["verification_code"]);
		} elseif (isset($_POST["submit_new_password"])) {
			$this->editNewPassword($_POST['username'], $_POST['password_reset_hash'], $_POST['password_new'], $_POST['password_repeat']);
		}

		// get gravatar profile picture if user is logged in
		if ($this->isUserLoggedIn() == true) {
			$this->getGravatarImageUrl($this->email);
		}
	}

	/**
	 * Checks if database connection is opened. If not, then this method tries to open it.
	 * @return bool Success status of the database connecting process
	 */
	public function databaseConnection()
	{
		// if connection already exists
		if ($this->db_connection != null) {
			return true;
		} else {
			try {
				// Generate a database connection, using the PDO connector
				// @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
				// Also important: We include the charset, as leaving it out seems to be a security issue:
				// @see http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers#Connecting_to_MySQL says:
				// "Adding the charset to the DSN is very important for security reasons,
				// most examples you'll see around leave it out. MAKE SURE TO INCLUDE THE CHARSET!"
				$this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
				return true;
			} catch (PDOException $e) {
				$this->errors[] = "Database connection problem." . $e->getMessage();
			}
		}
		// default return
		return false;
	}

	/**
	 * Search into database for the user data of username specified as parameter
	 * @return user data as an object if existing user
	 * @return false if username is not found in the database
	 * TODO: @devplanete This returns two different types. Maybe this is valid, but it feels bad. We should rework this.
	 * TODO: @devplanete After some resarch I'm VERY sure that this is not good coding style! Please fix this.
	 */
	private function getUserData($username)
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// database query, getting all the info of the selected user
			$query_user = $this->db_connection->prepare('SELECT * FROM users WHERE username = :username');
			$query_user->bindValue(':username', $username, PDO::PARAM_STR);
			$query_user->execute();
			// get result row (as an object)
			return $query_user->fetchObject();
		} else {
			return false;
		}
	}

	/**
	 * Logs in with S_SESSION data.
	 * Technically we are already logged in at that point of time, as the $_SESSION values already exist.
	 */
	private function loginWithSessionData()
	{
		$this->username = $_SESSION['username'];
		$this->email = $_SESSION['email'];
		$this->type = $_SESSION['type'];
		// set logged in status to true, because we just checked for this:
		// !empty($_SESSION['username']) && ($_SESSION['user_logged_in'] == 1)
		// when we called this method (in the constructor)
		$this->user_is_logged_in = true;
	}

	/**
	 * Logs in via the Cookie
	 * @return bool success state of cookie login
	 */
	private function loginWithCookieData()
	{
		if (isset($_COOKIE['rememberme'])) {
			// extract data from the cookie
			list ($userID, $token, $hash) = explode(':', $_COOKIE['rememberme']);
			// check cookie hash validity
			if ($hash == hash('sha256', $userID . ':' . $token . COOKIE_SECRET_KEY) && !empty($token)) {
				// cookie looks good, try to select corresponding user
				if ($this->databaseConnection()) {
					// get real token from database (and all other data)
					$sth = $this->db_connection->prepare("SELECT userID, username, email, type FROM users WHERE userID = :userID
													  AND user_rememberme_token = :user_rememberme_token AND user_rememberme_token IS NOT NULL");
					$sth->bindValue(':userID', $userID, PDO::PARAM_INT);
					$sth->bindValue(':user_rememberme_token', $token, PDO::PARAM_STR);
					$sth->execute();
					// get result row (as an object)
					$result_row = $sth->fetchObject();

					if (isset($result_row->userID)) {
						// write user data into PHP SESSION [a file on your server]
						$_SESSION['userID'] = $result_row->userID;
						$_SESSION['username'] = $result_row->username;
						$_SESSION['type'] = $result_row->type;
						$_SESSION['email'] = $result_row->email;
						$_SESSION['name_first'] = $result_row->name_first;
						$_SESSION['name_last'] = $result_row->name_last;
						$_SESSION['user_logged_in'] = 1;

						// declare user id, set the login status to true
						$this->userID = $result_row->userID;
						$this->username = $result_row->username;
						$this->type = $result_row->type;
						$this->email = $result_row->email;
						$this->user_is_logged_in = true;

						// Cookie token usable only once
						$this->newRememberMeCookie();
						return true;
					}
				}
			}
			// A cookie has been used but is not valid... we delete it
			$this->deleteRememberMeCookie();
			$this->errors[] = "Invalid cookie";
		}
		return false;
	}

	/**
	 * Logs in with the data provided in $_POST, coming from the login form
	 * @param $username
	 * @param $password
	 * @param $user_rememberme
	 */
	private function loginWithPostData($username, $password, $user_rememberme)
	{
		if (empty($username)) {
			$this->errors[] = "Username field was empty";
		} else if (empty($password)) {
			$this->errors[] = "Password field was empty";

		// if POST data (from login form) contains non-empty username and non-empty password
		} else {
			// user can login with his username or his email address.
			// if user has not typed a valid email address, we try to identify him with his username
			if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
				// database query, getting all the info of the selected user
				$result_row = $this->getUserData(trim($username));

			// if user has typed a valid email address, we try to identify him with his email
			} else if ($this->databaseConnection()) {
				// database query, getting all the info of the selected user
				$query_user = $this->db_connection->prepare('SELECT * FROM users WHERE email = :email');
				$query_user->bindValue(':email', trim($username), PDO::PARAM_STR);
				$query_user->execute();
				// get result row (as an object)
				$result_row = $query_user->fetchObject();
			}

			// if this user not exists
			if (! isset($result_row->userID)) {
				// was MESSAGE_USER_DOES_NOT_EXIST before, but has changed to MESSAGE_LOGIN_FAILED
				// to prevent potential attackers showing if the user exists
				$this->errors[] = "Login failed.";
			} else if (($result_row->failed_logins >= 4) && ($result_row->last_failed_login_time > (time() - (($result_row->failed_logins*$result_row->failed_logins*10)/60)*60))) {
				$this->errors[] = "You have entered an incorrect password ". $result_row->failed_logins ." or more times already. Please wait ". ($result_row->failed_logins*$result_row->failed_logins*10)/60 ." minutes to try again.";
			} else if (($result_row->failed_logins >= 3) && ($result_row->last_failed_login_time > (time() - 30))) {
				$this->errors[] = "You have entered an incorrect password 3 or more times already. Please wait 30 seconds to try again.";
			} else if (! password_verify($password, $result_row->password_hash)) {
				// using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
				// increment the failed login counter for that user
				$sth = $this->db_connection->prepare('UPDATE users '
						. 'SET failed_logins = failed_logins+1, last_failed_login_time = :last_failed_login_time '
						. 'WHERE username = :username OR email = :username');
				$sth->execute(array(':username' => $username, ':last_failed_login_time' => time()));

				$this->errors[] = "Wrong password. Try again.";
			// has the user activated their account with the verification email
			} else if ($result_row->is_active != 1) {
				$this->errors[] = "Your account is not activated yet. Please click on the confirm link in the mail.";
			} else if ($result_row->is_active == null) {
				$this->errors[] = "Your account has been blocked due to too many failed login attempts. Please reset your password.";
			} else {
				// write user data into PHP SESSION [a file on your server]
				$_SESSION['userID'] = $result_row->userID;
				$_SESSION['username'] = $result_row->username;
				$_SESSION['type'] = $result_row->type;
				$_SESSION['email'] = $result_row->email;
				$_SESSION['name_first'] = $result_row->name_first;
				$_SESSION['name_last'] = $result_row->name_last;
				$_SESSION['user_logged_in'] = 1;

				// declare user id, set the login status to true
				$this->userID = $result_row->userID;
				$this->username = $result_row->username;
				$this->type = $result_row->type;
				$this->email = $result_row->email;
				$this->user_is_logged_in = true;

				// reset the failed login counter for that user
				$sth = $this->db_connection->prepare('UPDATE users '
						. 'SET failed_logins = 0, last_failed_login_time = NULL '
						. 'WHERE userID = :userID AND failed_logins != 0');
				$sth->execute(array(':userID' => $result_row->userID));

				// if user has check the "remember me" checkbox, then generate token and write cookie
				if (isset($user_rememberme)) {
					$this->newRememberMeCookie();
				} else {
					// Reset remember-me token
					$this->deleteRememberMeCookie();
				}

				// OPTIONAL: recalculate the user's password hash
				// DELETE this if-block if you like, it only exists to recalculate users's hashes when you provide a cost factor,
				// by default the script will use a cost factor of 10 and never change it.
				// check if the have defined a cost factor in config/hashing.php
				if (defined('HASH_COST_FACTOR')) {
					// check if the hash needs to be rehashed
					if (password_needs_rehash($result_row->password_hash, PASSWORD_DEFAULT, array('cost' => HASH_COST_FACTOR))) {

						// calculate new hash with new cost factor
						$password_hash = password_hash($password, PASSWORD_DEFAULT, array('cost' => HASH_COST_FACTOR));

						// TODO: this should be put into another method !?
						$query_update = $this->db_connection->prepare('UPDATE users SET password_hash = :password_hash WHERE userID = :userID');
						$query_update->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
						$query_update->bindValue(':userID', $result_row->userID, PDO::PARAM_INT);
						$query_update->execute();

						if ($query_update->rowCount() == 0) {
							// writing new hash was successful. you should now output this to the user ;)
						} else {
							// writing new hash was NOT successful. you should now output this to the user ;)
						}
					}
				}
			}
		}
	}

	/**
	 * Create all data needed for remember me cookie connection on client and server side
	 */
	private function newRememberMeCookie()
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// generate 64 char random string and store it in current user data
			$random_token_string = hash('sha256', mt_rand());
			$sth = $this->db_connection->prepare("UPDATE users SET user_rememberme_token = :user_rememberme_token WHERE userID = :userID");
			$sth->execute(array(':user_rememberme_token' => $random_token_string, ':userID' => $_SESSION['userID']));

			// generate cookie string that consists of userid, randomstring and combined hash of both
			$cookie_string_first_part = $_SESSION['userID'] . ':' . $random_token_string;
			$cookie_string_hash = hash('sha256', $cookie_string_first_part . COOKIE_SECRET_KEY);
			$cookie_string = $cookie_string_first_part . ':' . $cookie_string_hash;

			// set cookie
			setcookie('rememberme', $cookie_string, time() + COOKIE_RUNTIME, "/", COOKIE_DOMAIN);
		}
	}

	/**
	 * Delete all data needed for remember me cookie connection on client and server side
	 */
	private function deleteRememberMeCookie()
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// Reset rememberme token
			$sth = $this->db_connection->prepare("UPDATE users SET user_rememberme_token = NULL WHERE userID = :userID");
			$sth->execute(array(':userID' => $_SESSION['userID']));
		}

		// set the rememberme-cookie to ten years ago (3600sec * 365 days * 10).
		// that's obivously the best practice to kill a cookie via php
		// @see http://stackoverflow.com/a/686166/1114320
		setcookie('rememberme', false, time() - (3600 * 3650), '/', COOKIE_DOMAIN);
	}

	/**
	 * Perform the logout, resetting the session
	 */
	public function doLogout()
	{
		$this->deleteRememberMeCookie();

		$_SESSION = array();
		session_destroy();

		$this->user_is_logged_in = false;
		$this->messages[] = "You have been logged out.";
	}

	/**
	 * Simply return the current state of the user's login
	 * @return bool user's login status
	 */
	public function isUserLoggedIn()
	{
		return $this->user_is_logged_in;
	}

	/**
	 * Edit the user's name, provided in the editing form
	 */
	public function editUserName($username)
	{
		// prevent database flooding
		$username = substr(trim($username), 0, 64);

		if (!empty($username) && $username == $_SESSION['username']) {
			$this->errors[] = "Sorry, that username is the same as your current one. Please choose another one.";

		// username cannot be empty and must be azAZ09 and 2-64 characters
		// TODO: maybe this pattern should also be implemented in Registration.php (or other way round)
		} elseif (empty($username) || !preg_match("/^(?=.{2,64}$)[a-zA-Z][a-zA-Z0-9]*(?: [a-zA-Z0-9]+)*$/", $username)) {
			$this->errors[] = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";

		} else {
			// check if new username already exists
			$result_row = $this->getUserData($username);

			if (isset($result_row->userID)) {
				$this->errors[] = "Sorry, that username is already taken. Please choose another one.";
			} else {
				// write user's new data into database
				$query_edit_username = $this->db_connection->prepare('UPDATE users SET username = :username WHERE userID = :userID');
				$query_edit_username->bindValue(':username', $username, PDO::PARAM_STR);
				$query_edit_username->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_INT);
				$query_edit_username->execute();

				if ($query_edit_username->rowCount()) {
					$_SESSION['username'] = $username;
					$this->messages[] = "Your username has been changed successfully. New username is " . $username;
				} else {
					$this->errors[] = "Sorry, your chosen username renaming failed";
				}
			}
		}
	}

	/**
	 * Edit the user's email, provided in the editing form
	 */
	public function editUserEmail($email)
	{
		// prevent database flooding
		$email = substr(trim($email), 0, 64);

		if (!empty($email) && $email == $_SESSION["email"]) {
			$this->errors[] = "Sorry, that email address is the same as your current one. Please choose another one.";
		// user mail cannot be empty and must be in email format
		} elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->errors[] = "Your email address is not in a valid email format";

		} else if ($this->databaseConnection()) {
			// check if new email already exists
			$query_user = $this->db_connection->prepare('SELECT * FROM users WHERE email = :email');
			$query_user->bindValue(':email', $email, PDO::PARAM_STR);
			$query_user->execute();
			// get result row (as an object)
			$result_row = $query_user->fetchObject();

			// if this email exists
			if (isset($result_row->userID)) {
				$this->errors[] = "This email address is already registered. Please use the \"I forgot my password\" page if you don't remember it.";
			} else {
				// write users new data into database
				$query_edit_email = $this->db_connection->prepare('UPDATE users SET email = :email WHERE userID = :userID');
				$query_edit_email->bindValue(':email', $email, PDO::PARAM_STR);
				$query_edit_email->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_INT);
				$query_edit_email->execute();

				if ($query_edit_email->rowCount()) {
					$_SESSION['email'] = $email;
					$this->messages[] = "Your email address has been changed successfully. New email address is " . $email;
				} else {
					$this->errors[] = "Sorry, your email changing failed.";
				}
			}
		}
	}

	/**
	 * Edit the user's password, provided in the editing form
	 */
	public function editUserPassword($password_old, $password_new, $password_repeat)
	{
		if (empty($password_new) || empty($password_repeat) || empty($password_old)) {
			$this->errors[] = "Password field was empty";
		// is the repeat password identical to password
		} elseif ($password_new !== $password_repeat) {
			$this->errors[] = "Password and password repeat are not the same";
		// password need to have a minimum length of 6 characters
		} elseif (strlen($password_new) < 6) {
			$this->errors[] = "Password has a minimum length of 6 characters";

		// all the above tests are ok
		} else {
			// database query, getting hash of currently logged in user (to check with just provided password)
			$result_row = $this->getUserData($_SESSION['username']);

			// if this user exists
			if (isset($result_row->password_hash)) {

				// using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
				if (password_verify($password_old, $result_row->password_hash)) {

					// now it gets a little bit crazy: check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
					// if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
					$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

					// crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
					// the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
					// compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
					// want the parameter: as an array with, currently only used with 'cost' => XX.
					$password_hash = password_hash($password_new, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

					// write users new hash into database
					$query_update = $this->db_connection->prepare('UPDATE users SET password_hash = :password_hash WHERE userID = :userID');
					$query_update->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
					$query_update->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_INT);
					$query_update->execute();

					// check if exactly one row was successfully changed:
					if ($query_update->rowCount()) {
						$this->messages[] = "Password successfully changed!";
					} else {
						$this->errors[] = "Sorry, your password changing failed.";
					}
				} else {
					$this->errors[] = "Your OLD password was wrong.";
				}
			} else {
				$this->errors[] = "This user does not exist";
			}
		}
	}

	/**
	 * Sets a random token into the database (that will verify the user when he/she comes back via the link
	 * in the email) and sends the according email.
	 */
	public function setPasswordResetDatabaseTokenAndSendMail($username)
	{
		$username = trim($username);

		if (empty($username)) {
			$this->errors[] = "Username field was empty";

		} else {
			// generate timestamp (to see when exactly the user (or an attacker) requested the password reset mail)
			// btw this is an integer ;)
			$temporary_timestamp = time();
			// generate random hash for email password reset verification (40 char string)
			$password_reset_hash = sha1(uniqid(mt_rand(), true));
			// database query, getting all the info of the selected user
			$result_row = $this->getUserData($username);

			// if this user exists
			if (isset($result_row->userID)) {

				// database query:
				$query_update = $this->db_connection->prepare('UPDATE users SET password_reset_hash = :password_reset_hash,
															   password_reset_timestamp = :password_reset_timestamp
															   WHERE username = :username');
				$query_update->bindValue(':password_reset_hash', $password_reset_hash, PDO::PARAM_STR);
				$query_update->bindValue(':password_reset_timestamp', $temporary_timestamp, PDO::PARAM_INT);
				$query_update->bindValue(':username', $username, PDO::PARAM_STR);
				$query_update->execute();

				// check if exactly one row was successfully changed:
				if ($query_update->rowCount() == 1) {
					// send a mail to the user, containing a link with that token hash string
					$this->sendPasswordResetMail($username, $result_row->email, $password_reset_hash);
					return true;
				} else {
					$this->errors[] = "Database connection problem.";
				}
			} else {
				$this->errors[] = "This user does not exist";
			}
		}
		// return false (this method only returns true when the database entry has been set successfully)
		return false;
	}

	/**
	 * Sends the password-reset-email.
	 */
	public function sendPasswordResetMail($username, $email, $password_reset_hash)
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

		$mail->From = EMAIL_PASSWORDRESET_FROM;
		$mail->FromName = EMAIL_PASSWORDRESET_FROM_NAME;
		$mail->AddAddress($email);
		$mail->Subject = EMAIL_PASSWORDRESET_SUBJECT;

		$link    = EMAIL_PASSWORDRESET_URL.'?username='.urlencode($username).'&verification_code='.urlencode($password_reset_hash);
		$mail->Body = EMAIL_PASSWORDRESET_CONTENT . ' ' . $link;

		if(!$mail->Send()) {
			$this->errors[] = "Password reset mail NOT successfully sent! Error: " . $mail->ErrorInfo;
			return false;
		} else {
			$this->messages[] = "Password reset mail successfully sent!";
			return true;
		}
	}

	/**
	 * Checks if the verification string in the account verification mail is valid and matches to the user.
	 */
	public function checkIfEmailVerificationCodeIsValid($username, $verification_code)
	{
		$username = trim($username);

		if (empty($username) || empty($verification_code)) {
			$this->errors[] = "Empty link parameter data.";
		} else {
			// database query, getting all the info of the selected user
			$result_row = $this->getUserData($username);

			// if this user exists and have the same hash in database
			if (isset($result_row->userID) && $result_row->password_reset_hash == $verification_code) {

				$timestamp_one_hour_ago = time() - 3600; // 3600 seconds are 1 hour

				if ($result_row->password_reset_timestamp > $timestamp_one_hour_ago) {
					// set the marker to true, making it possible to show the password reset edit form view
					$this->password_reset_link_is_valid = true;
				} else {
					$this->errors[] = "Your reset link has expired. Please use the reset link within one hour.";
				}
			} else {
				$this->errors[] = "This user does not exist";
			}
		}
	}

	/**
	 * Checks and writes the new password.
	 */
	public function editNewPassword($username, $password_reset_hash, $password_new, $password_repeat)
	{
		// TODO: timestamp!
		$username = trim($username);

		if (empty($username) || empty($password_reset_hash) || empty($password_new) || empty($password_repeat)) {
			$this->errors[] = "Password field was empty";
		// is the repeat password identical to password
		} else if ($password_new !== $password_repeat) {
			$this->errors[] = "Password and password repeat are not the same";
		// password need to have a minimum length of 6 characters
		} else if (strlen($password_new) < 6) {
			$this->errors[] = "Password has a minimum length of 6 characters";
		// if database connection opened
		} else if ($this->databaseConnection()) {
			// now it gets a little bit crazy: check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
			// if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
			$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

			// crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
			// the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
			// compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
			// want the parameter: as an array with, currently only used with 'cost' => XX.
			$password_hash = password_hash($password_new, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

			// write users new hash into database
			$query_update = $this->db_connection->prepare('UPDATE users SET password_hash = :password_hash,
														   password_reset_hash = NULL, password_reset_timestamp = NULL
														   WHERE username = :username AND password_reset_hash = :password_reset_hash');
			$query_update->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
			$query_update->bindValue(':password_reset_hash', $password_reset_hash, PDO::PARAM_STR);
			$query_update->bindValue(':username', $username, PDO::PARAM_STR);
			$query_update->execute();

			// check if exactly one row was successfully changed:
			if ($query_update->rowCount() == 1) {
				$this->password_reset_was_successful = true;
				$this->messages[] = "Password successfully changed!";
			} else {
				$this->errors[] = "Sorry, your password changing failed.";
			}
		}
	}

	/**
	 * Gets the success state of the password-reset-link-validation.
	 * TODO: should be more like getPasswordResetLinkValidationStatus
	 * @return boolean
	 */
	public function passwordResetLinkIsValid()
	{
		return $this->password_reset_link_is_valid;
	}

	/**
	 * Gets the success state of the password-reset action.
	 * TODO: should be more like getPasswordResetSuccessStatus
	 * @return boolean
	 */
	public function passwordResetWasSuccessful()
	{
		return $this->password_reset_was_successful;
	}

	/**
	 * Gets the username
	 * @return string username
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Gets the type of the user
	 * @return string username
	 */
	public function getType()
	{
		return $this->type;
	}

	public function getSections($CRN)
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// database query, getting all the info of the selected user
			$query_user = $this->db_connection->prepare('SELECT * FROM sections WHERE CRN = :CRN');
			$query_user->bindValue(':CRN', $CRN, PDO::PARAM_STR);
			$query_user->execute();
			// get result row (as an object)
			return $query_user->fetchObject();
		} else {
			return false;
		}
	}

	public function getSections($CRN)
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// database query, getting all the info of the selected user
			$query_user = $this->db_connection->prepare('SELECT * FROM sections WHERE CRN = :CRN');
			$query_user->bindValue(':CRN', $CRN, PDO::PARAM_STR);
			$query_user->execute();
			// get result row (as an object)
			return $query_user->fetchObject();
			var_dump($this);
		} else {
			return false;
		}
	}

	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 * Gravatar is the #1 (free) provider for email address based global avatar hosting.
	 * The URL (or image) returns always a .jpg file !
	 * For deeper info on the different parameter possibilities:
	 * @see http://de.gravatar.com/site/implement/images/
	 *
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 50px [ 1 - 2048 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @source http://gravatar.com/site/implement/images/php/
	 */
	public function getGravatarImageUrl($email, $s = 50, $d = 'mm', $r = 'g', $atts = array() )
	{
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5(strtolower(trim($email)));
		$url .= "?s=$s&d=$d&r=$r&f=y";

		// the image url (on gravatarr servers), will return in something like
		// http://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50?s=80&d=mm&r=g
		// note: the url does NOT have something like .jpg
		$this->user_gravatar_image_url = $url;

		// build img tag around
		$url = '<img src="' . $url . '"';
		foreach ($atts as $key => $val)
			$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';

		// the image url like above but with an additional <img src .. /> around
		$this->user_gravatar_image_tag = $url;
	}
}
