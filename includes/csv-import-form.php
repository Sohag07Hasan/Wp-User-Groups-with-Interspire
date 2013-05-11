<?php

	set_time_limit(0);

	if($_REQUEST['group_id'] > 0){
		$group = $Ugdb->get_group($_REQUEST['group_id']);				
	}
	
	$message = array();
	
	//form is submitted
	if($_POST['csv-uploader'] == 'Y'){
		
		$time_start = microtime(true);
		
		if(empty($_FILES['group_csv']['tmp_name'])){
			$message['error'][] = "No file is uploaded";
		}
		else{
			$file = $_FILES['group_csv']['tmp_name'];
			
			//var_dump(file($file)); exit;
			
			$info = pathinfo($_FILES['group_csv']['name']);
			
			if($info['extension'] == 'csv'){
				self::stripBOM($file);
				
				$csv = self::_csv_uploader();
				
				if (!$csv->load($file)) {
		            $message['error'][][] = 'Failed to load file, aborting.';
		                      
		        }
		        else{
		        	$aborted = 0;
		        	$skipped = 0;
	        		$imported = 0;
		        	$csv->symmetrize();

		        	//var_dump($csv->getHeaders());
		        	
		        	foreach ($csv->getRawArray() as $num => $csv_data) {
		        		
		        		//$csv_data = $csv->connect($csv_data);
		        		
		        	//	var_dump($csv_data);
		        		
		        		
		        		if($num == 0) continue;

		        		if(!is_email($csv_data[0])){
		        			$aborted ++;
		        			continue;
		        		}
		        		
		        		if(self::create_user($csv_data)){
		        			$imported ++;
		        		}
		        		else{
		        			$skipped ++;
		        		}
		        		
		        		
		        	}
		        			        	
		        	 $exec_time = microtime(true) - $time_start;
		        	 
		        	 $message['updated'][] = 'Total user added: ' . $imported;
		        	 $message['updated'][] = 'Total user skipped due to domain unmatched error: ' . $skipped;
		        	 $message['updated'][] = 'Total user aborted due to wrong email: ' . $aborted;
		        	 $message['updated'][] = sprintf('Total time required: %.2f seconds' , $exec_time);
		        	 
		        }
			}

			else{
				$message['error'][] = "The uploaded file is not a csv file";
			}
			
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