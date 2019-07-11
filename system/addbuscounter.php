<?php 
/**
 * @author MD. RASHEDUL ISLAM
 * @package Bus Ticket Reservation System
 * @version v2.0
 * @see https://github.com/rashed370/webtech-final
 */

require_once 'lib/function.php';

// Check For Authorization Positive

if(!($sessionCookie = getSessionCookie()) || !verifyLogin($sessionCookie, true))
{
	header('location: login.php');
	die();
}

$errors = array();

ob_start();
?>

<table width="100%" border="0" cellspacing="0" cellpadding="10">
	<tr bgcolor="#C7CBD1">
		<td>
			<font face="Verdana"><b>Add New Bus Counter</b></font>
		</td>
		<td width="150px" align="right">
			<a href="buscounter.php"><img src="assets/img/cancel_button.png" alt="[+]" title="Cancel"></a>
		</td>
	</tr>
	<tr>
		<td bgcolor="#FFFFFF" colspan="2">
			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<td valign="top" align="right">
						<label for="name"><font face="Arial" size="2"><b>Counter Name</b></font></label>
					</td>
					<td valign="top">
						<input type="text" name="name" id="name" placeholder="Enter Counter Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" size="24"><br/>
						<?php __errors($errors, 'name') ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<label for="bus_manager"><font face="Arial" size="2"><b>Bus Manager</b></font></label>
					</td>
					<td valign="top">
						<select name="bus_manager" id="bus_manager">
							<option value="">Select</option>
							<option value="1"<?php __selected("1", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Thane Hooper</option>
							<option value="2"<?php __selected("2", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Alvin Vang</option>
							<option value="3"<?php __selected("3", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Erich Stevens</option>
							<option value="4"<?php __selected("4", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Brent Mathis</option>
							<option value="5"<?php __selected("4", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Ferdinand Woods</option>
							<option value="6"<?php __selected("4", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Gavin Ferrell</option>
							<option value="7"<?php __selected("4", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Joshua Raymond</option>
							<option value="8"<?php __selected("4", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Zachary Crosby</option>
							<option value="9"<?php __selected("4", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Samuel Alston</option>
							<option value="10"<?php __selected("4", (isset($_POST['bus_manager']) ? $_POST['bus_manager'] : '')) ?>>Basil Noble</option>
						</select><br>
						<?php __errors($errors, 'bus_manager') ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<label for="counter_location"><font face="Arial" size="2"><b>Counter Location</b></font></label>
					</td>
					<td valign="top">
						<input type="text" name="counter_location" id="counter_location" placeholder="Enter full location" value="<?php echo isset($_POST['counter_location']) ? htmlspecialchars($_POST['counter_location']) : '' ?>" size="24"><br/>
						<?php __errors($errors, 'counter_location') ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<label for="counter_type"><font face="Arial" size="2"><b>Counter Type</b></font></label>
					</td>
					<td valign="top">
						<input type="text" name="counter_type" id="counter_type" placeholder="Enter Cunter Type" value="<?php echo isset($_POST['counter_type']) ? htmlspecialchars($_POST['counter_type']) : '' ?>" size="24"><br/>
						<?php __errors($errors, 'counter_type') ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<label for="description"><font face="Arial" size="2"><b>Description</b></font></label>
					</td>
					<td valign="top">
						<textarea name="description" rows="4" cols="25"></textarea>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="submit" name="submit" value="Add Bus Counter">
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<?php
$content = ob_get_clean();
$usermail = getEmailBySession($sessionCookie);
$user = getUser($usermail);
__visualize(array(
	'title' => 'BTRS - Add Bus Counter',
	'area' => 'buscounter',
	'data' => $content,
	'user' => $user ? $user['name'] : '',
	'usermail' => $usermail
));