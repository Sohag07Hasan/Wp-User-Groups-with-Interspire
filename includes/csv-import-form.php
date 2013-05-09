<?php
	if($_REQUEST['group_id'] > 0){
		$group = $Ugdb->get_group($_REQUEST['group_id']);				
	}
	
	$message = array();
	
	//form is submitted
	if($_POST['csv-uploader'] == 'Y'){
		if(empty($_FILES['group_csv']['tmp_name'])){
			$message['error'][] = "No file is uploaded";
		}
		else{
			
		}			
	}
	
	
	
?>

<div class="wrap">

	<h2> Import Users </h2>
	
	<?php 
		if($message){
			foreach($message as $class => $msg){
				?>
				<div class="<?php echo $class; ?>">
					<?php 
						foreach($msg as $m){
							echo '<p>' . $m . '</p>';
						}
					?>
				</div>
				<?php 
			}
		}
	?>
	
	<p>Group Name: <?php echo $group['name']; ?></p>
	<p>Group Domain: <?php echo $group['domain']; ?> </p>
	
	<form acton="" method="post" enctype="multipart/form-data">
		
		<input type="hidden" name="csv-uploader" value="Y" />
		<input type="hidden" name="group_id" value="<?php echo $_REQUEST['group_id']; ?>">
		
		<p>Upload a csv (.csv) file</p>
		
		<p> <input type="file" name="group_csv"  /> <input class="button button-primary" type="submit" value="Import"> </p>
				
		
	</form>

</div>