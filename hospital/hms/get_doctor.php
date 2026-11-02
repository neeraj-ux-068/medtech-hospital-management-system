<?php
include('include/config.php');
if(!empty($_POST["specilizationid"]))
{
 $specialization = hms_post_string('specilizationid', 255);
 $sql = hms_db_query($con, 'SELECT doctorName, id FROM doctors WHERE specilization = ?', [$specialization]); ?>
 <option selected="selected">Select Doctor </option>
 <?php
 while($row=mysqli_fetch_array($sql))
 	{?>
  <option value="<?php echo htmlentities($row['id']); ?>"><?php echo htmlentities($row['doctorName']); ?></option>
  <?php
}
}


if(!empty($_POST["doctor"]))
{

 $doctorId = filter_var($_POST['doctor'], FILTER_VALIDATE_INT);
 $sql = $doctorId ? hms_db_query($con, 'SELECT docFees FROM doctors WHERE id = ?', [$doctorId]) : false;
 while($sql && $row=mysqli_fetch_array($sql))
 	{?>
 <option value="<?php echo htmlentities($row['docFees']); ?>"><?php echo htmlentities($row['docFees']); ?></option>
  <?php
}
}

?>

