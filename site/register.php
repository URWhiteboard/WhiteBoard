<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// include the PHPMailer library
require_once($_SERVER['DOCUMENT_ROOT'] .'/included/libraries/PHPMailer.php');

// load the registration class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Registration.php');

// create the registration object. when this object is created, it will do all registration stuff automatically
// so this single line handles the entire registration process.
$registration = new Registration();
// showing the register view (with the registration form, and messages/errors)
include($_SERVER['DOCUMENT_ROOT'] ."/included/headerout.php");

// show potential errors / feedback (from registration object)
if (isset($registration)) {
	if ($registration->errors) {
		foreach ($registration->errors as $error) {
			echo $error;
		}
	}
	if ($registration->messages) {
		foreach ($registration->messages as $message) {
			echo $message;
		}
	}
}
?>
<!-- show registration form, but only if we didn't submit already -->
<?php if (!$registration->registration_successful && !$registration->verification_successful) { ?>
<form method="post" action="/register/" name="registerform">
	<label for="user_name"><?php echo "Username (only letters and numbers, 2 to 64 characters)"; ?></label>
	<input id="user_name" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required />
	<br />
	<label for="user_name_first"><?php echo "First name"; ?></label>
	<input id="user_name_first" type="text" pattern="[a-zA-Z]{2,30}" name="user_name_first" required />
	<br />
	<label for="user_name_last"><?php echo "Last name"; ?></label>
	<input id="user_name_last" type="text" pattern="[a-zA-Z]{2,30}" name="user_name_last" required />
	<br />
	<label for="user_email"><?php echo "User's email (please provide a real email address, you'll get a verification mail with an activation link)"; ?></label>
	<input id="user_email" type="email" name="user_email" required />
	<br />
	<label for="user_birthday_month"><?php echo "Month"; ?></label>
	<select id="user_birthday_month" name="user_birthday_month">
	<option disabled="" selected=""></option>
	<option value="01">January</option>
	<option value="02">Febuary</option>
	<option value="03">March</option>
	<option value="04">April</option>
	<option value="05">May</option>
	<option value="06">June</option>
	<option value="07">July</option>
	<option value="08">August</option>
	<option value="09">September</option>
	<option value="10">October</option>
	<option value="11">November</option>
	<option value="12">December</option>
	</select>
	<br />
	<label for="user_birthday_day"><?php echo "Day"; ?></label>
	<select id="user_birthday_day" name="user_birthday_day">
	<option disabled="" selected=""></option>
	<?php
	for($i = 1; $i < 32; $i++) {
		echo "<option value=\"". $i ."\">". $i ."</option>";
	}
	?>
	</select>
	<br />
	<label for="user_birthday_year"><?php echo "Year"; ?></label>
	<select id="user_birthday_year" name="user_birthday_year">
	<option disabled="" selected=""></option>
	<?php
	for($i = date("Y"); $i > 1899 ; $i--) {
		echo "<option value=\"". $i ."\">". $i ."</option>";
	}
	?>
	</select>
	<br />
	<label for="user_class_of"><?php echo "Class of"; ?></label>
	<select id="user_class_of" name="user_class_of">
	<option disabled="" selected=""></option>
	<?php
	for($i = date("Y")-4; $i < date("Y")+5; $i++) {
		echo "<option value=\"". $i ."\">". $i ."</option>";
	}
	?>
	</select>
	<br />

	<label for="user_password_new"><?php echo "Password (min. 6 characters!)"; ?></label>
	<input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />
	<br />
	<label for="user_password_repeat"><?php echo "Password repeat"; ?></label>
	<input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />
	<br />
	<img src="/tools/showCaptcha.php" alt="captcha" />
	<br />
	<label><?php echo "Please enter these characters"; ?></label>
	<input type="text" name="captcha" required />
	<br />
	<input type="submit" name="register" value="<?php echo "Register"; ?>" />
</form>
<?php } ?>

	<a href="../../../"><?php echo "Back to Login Page"; ?></a>
</body>
</html>
