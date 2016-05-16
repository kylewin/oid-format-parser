<?php

	//Connect MongoDB
	$conn = new MongoClient("mongodb://localhost", array("username" => "tos", "password" => "123asd","db"=>"tosdb"));
    	$db=$conn->tosdb;

	$folder=date("Ymd",mktime(0,0,0,date("m"), date("d")-1,date("Y")));
        $db->createCollection("hp-servers-mdr-".$folder);
        $collection="hp-servers-mdr-".$folder;
        $sv=$db->$collection;
        $progress=0;
        $sumfile=0;

	if ($handle = opendir('/tosdb/mdr/hp/'.$folder.'/')) {
                while (false !== ($entry = readdir($handle))){
                        if ($entry != "." && $entry != ".."){
                                $sumfile++;
                        }
                }
        }

	if ($handle = opendir('/tosdb/mdr/hp/'.$folder)) {
        while (false !== ($entry = readdir($handle))){
            if ($entry != "." && $entry != ".."){
				$myhandle = fopen('/tosdb/mdr/hp/'.$folder.'/'.$entry, "r");
				$data=array();
				while (($line = fgets($myhandle)) !== false) {
					if (trim($line) != "") {	
						//get keys and values
						$partsofline=explode("=",$line);
						$keys=trim($partsofline[0]);
						$value=trim($partsofline[1]);
						
						//parse key
						$keyarray=explode(".",$keys);
						$sumkeys=count($keyarray);	
						switch ($sumkeys) {
							case 1:
								$obj=$keyarray[0];
								$data[$obj]=$value;
								break;
							case 2:
								$obj=$keyarray[0];
								$property=$keyarray[1];
								$data[$obj][$property]=$value;
								break;
							case 3:
								$obj=$keyarray[0];
								$property=$keyarray[1];
								$index=$keyarray[2];
								$data[$obj][$property][$index]=$value;
								break;
							default:
								$obj=$keyarray[0];
								$property=$keyarray[1];
								$index=$keyarray[2];
								$id=$keyarray[3];
								for ($i=4;$i<$sumkeys;$i++) {
									$id=$id.".".$keyarray[$i];
								}	
								$data[$obj][$property][$index][$id]=$value;
								break;
						}
					}
				}
				fclose($myhandle);
				$entry=preg_replace('/\\.[^.\\s]{3,4}$/', '', $entry);
				$data['serial']=$entry;
				echo $entry."\n";
				try{
					$sv->insert($data);
					$progress++;
                                	echo "Inserted ".$progress."/".$sumfile."\n";
				}catch(Exception $e){
					echo "error ".$entry;
					continue;
				}
			}
		}
	}else{
		echo 'Failed to open folder';
	}
	
	//Close MongoDB
	$conn->close();

?>
