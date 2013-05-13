<?php 
	
	//first one makes the system run for unlimited time
	//second one is to detect automatic line eding. Sometimes csv created with Mac creates problem
	
	@ set_time_limit(0);
	@ ini_set("auto_detect_line_endings", "1");
	
	$lists = self::get_interspire_lists();
	$options = self::get_site_default_options();
	
	$message = array();
	if($_POST['default-csv-uploader'] == 'Y'){
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
        	
		        	foreach ($csv->getRawArray() as $num => $csv_data) {
		        		    		
		        		if($num == 0) continue;

		        		if(!is_email($csv_data[0])){
		        			$aborted ++;
		        			continue;
		        		}
		        		
		        		if(self::create_default_user($csv_data, $options)){
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

	<h2> CSV Import (default group) </h2>
	
	<p style="color: green; font-stlye: italic;">CSV Import for default group only. To upload users into specific groups, please use the Group "edit screen" </p>
	
	
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
	
	<p>Group Information: Default Group</p>
	
	
	
	<form acton="" method="post" enctype="multipart/form-data">
		
		<input type="hidden" name="default-csv-uploader" value="Y" />
		
		<p> 
			Interspire List: 
			<select name="interspire-list" style="min-width: 150px;">
				<option value="">Choose</option>
				
				<?php 
					if(count($lists) > 0){
						foreach($lists as $list){
							?>
							<option <?php selected($list['listid'], $options['default-interspire-list']); ?> value="<?php echo $list['listid']; ?>"><?php echo $list['name']; ?></option>
							<?php 
						}
					}							
				?>	
				
			</select>
		
		 </p>
			
		<p>Upload a csv (.csv) file</p>
		
		<p> <input type="file" name="group_csv"  /> <input class="button button-primary" type="submit" value="Import"> </p>
				
		
	</form>

</div>