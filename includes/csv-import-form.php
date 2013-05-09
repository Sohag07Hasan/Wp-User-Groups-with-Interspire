<?php
	if($_REQUEST['group_id'] > 0){
		$group = $Ugdb->get_group($_REQUEST['group_id']);
				
	}
?>

<div class="wrap">

	<h2> Import Users </h2>
	
	<p>Group Name: <?php echo $group['name']; ?></p>
	<p>Group Domain: <?php echo $group['domain']; ?> </p>
	
	<form acton="" method="post" enctype="multipart/form-data">
		
		<input type="hidden" name="csv-uploader" value="Y" />
		<input type="hidden" name="group_id" value="<?php echo $_REQUEST['group_id']; ?>">
		
		<p>Upload a csv (.csv) file</p>
		
		<p> <input type="file" name="group_csv"  /> <input class="button button-primary" type="submit" value="Import"> </p>
				
		
	</form>

</div>