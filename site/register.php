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
<div id="mainContentContainerContent" class="mainContentContainerContent">
<form method="post" action="/register/" name="registerform">
	<input id="username" type="text" pattern="[a-zA-Z0-9]{2,64}" name="username" placeholder="Username" required />
	<br />
	<input id="name_first" type="text" pattern="[a-zA-Z]{2,30}" name="name_first" placeholder="First Name" required />
	<br />
	<input id="name_last" type="text" pattern="[a-zA-Z]{2,30}" name="name_last" placeholder="Last Name" required />
	<br />
	<input id="name_suffix" type="text" pattern="[a-zA-Z]{2,30}" placeholder="Suffix" name="name_suffix" />
	<br />
	<input id="email" type="email" name="email" placeholder="Email" required />
	<br />
	<label>&nbsp;Birthday</label><br />
	<select id="birth_month" name="birth_month" required>
	<option disabled="" selected="">Month</option>
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
	<select id="birth_day" name="birth_day" required>
	<option disabled="" selected="">Day</option>
	<?php
	for($i = 1; $i < 32; $i++) {
		echo "<option value=\"". $i ."\">". $i ."</option>";
	}
	?>
	</select>
	<select id="birth_year" name="birth_year" required>
	<option disabled="" selected="">Year</option>
	<?php
	for($i = date("Y"); $i > 1899 ; $i--) {
		echo "<option value=\"". $i ."\">". $i ."</option>";
	}
	?>
	</select>
	<br />
	<select id="expected_graduation" name="expected_graduation" required>
	<option disabled="" selected="">Class Of</option>
	<?php
	for($i = date("Y")-4; $i < date("Y")+5; $i++) {
		echo "<option value=\"". $i ."\">". $i ."</option>";
	}
	?>
	</select>
	<br />

	<input id="password_new" type="password" name="password_new" pattern=".{6,}" placeholder="Password (Min 6 characters)" required autocomplete="off" />
	<br />
	<input id="password_repeat" type="password" name="password_repeat" pattern=".{6,}" placeholder="Repeat Password" required autocomplete="off" />
	<br />
	<img src="../tools/showCaptcha.php" alt="captcha" />
	<br />
	<label>Enter these charaters below</label><br />
	<input type="text" name="captcha" required />
	<br />
	<input type="submit" name="register" value="<?php echo "Register"; ?>" />
</form>
</div>
<?php } ?>
</body>
</html>
