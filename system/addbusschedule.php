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
			<font face="Verdana"><b>Add Bus Schedule</b></font>
		</td>
		<td width="150px" align="right">
			<a href="busschedule.php"><img src="assets/img/cancel_button.png" alt="[+]" title="Cancel"></a>
		</td>
	</tr>
	<tr>
		<td bgcolor="#FFFFFF" colspan="2">
			<table border="0" cellpadding="5" cellspacing="0">
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
						<label for="bus"><font face="Arial" size="2"><b>Bus</b></font></label>
					</td>
					<td valign="top">
						<select name="bus" id="bus">
							<option value="">Select</option>
							<option value="DHA-58109">Hyundai Universe [DHA-58109]</option>
							<option value="DHA-57203">Hyundai Universe [DHA-57203]</option>
							<option value="DHA-12501">Scania [DHA-12501]</option>
							<option value="DHA-64808">AK1J Super Plus [DHA-64808]</option>
						</select><br>
						<?php __errors($errors, 'bus_manager') ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<label for="route"><font face="Arial" size="2"><b>Route</b></font></label>
					</td>
					<td valign="top">
						<input type="route" name="route" id="route" placeholder="Enter Route" value="<?php echo isset($_POST['route']) ? htmlspecialchars($_POST['route']) : '' ?>" size="24"><br/>
						<?php __errors($errors, 'route') ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<label for="seat_price"><font face="Arial" size="2"><b>Price Per Seart</b></font></label>
					</td>
					<td valign="top">
						<input type="text" name="seat_price" id="seat_price" placeholder="Enter Per Seat Price" value="<?php echo isset($_POST['seat_price']) ? htmlspecialchars($_POST['seat_price']) : '' ?>" size="24"><br/>
						<?php __errors($errors, 'seat_price') ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<label for="departure"><font face="Arial" size="2"><b>Departure</b></font></label>
					</td>
					<td valign="top">
						<input type="text" name="departure" id="departure" placeholder="Enter Departure Time/Date" value="<?php echo isset($_POST['departure']) ? htmlspecialchars($_POST['departure']) : '' ?>" size="24"><br/>
						<?php __errors($errors, 'departure') ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<label for="arrival"><font face="Arial" size="2"><b>Arrival</b></font></label>
					</td>
					<td valign="top">
						<input type="text" name="arrival" id="arrival" placeholder="Enter Arrival Time/Date" value="<?php echo isset($_POST['arrival']) ? htmlspecialchars($_POST['arrival']) : '' ?>" size="24"><br/>
						<?php __errors($errors, 'arrival') ?>
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
						<input type="submit" name="submit" value="Add Schedule">
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
	'title' => 'BTRS - Add Bus Schedule',
	'area' => 'busschedule',
	'data' => $content,
	'user' => $user ? $user['name'] : '',
	'usermail' => $usermail
));
