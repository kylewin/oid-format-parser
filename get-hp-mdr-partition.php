<?php
	$conn = new MongoClient("mongodb://localhost", array("username" => "tos", "password" => "123asd","db"=>"tosdb"));
    	$db=$conn->tosdb;

	$folder=date("Ymd",mktime(0,0,0,date("m"), date("d")-1,date("Y")));
        $db->createCollection("hp-servers-mdr-".$folder);
        $collection="hp-servers-mdr-".$folder;
        $sv=$db->$collection;
        $progress=0;
        $sumfile=0;

	if ($handle = opendir('/tosdb/mdr_partition/hp/'.$folder.'/')) {
		while (false !== ($entry = readdir($handle))){
			if ($entry != "." && $entry != ".."){
					$sumfile++;
			}
		}
	}

	if ($handle = opendir('/tosdb/mdr_partition/hp/'.$folder)) {
        while (false !== ($entry = readdir($handle))){
            if ($entry != "." && $entry != ".."){
				$xml = @simplexml_load_file('/tosdb/mdr_partition/hp/'.$folder.'/'.$entry);
                                $arr = json_decode( json_encode($xml) , 1);
				if ($arr["SERIAL_SERVER"] != "" && $arr["CONTROLLERS"] != ""){
					$arr["SERIAL_SERVER"]=trim($arr["SERIAL_SERVER"]);
                                	$sv->update(array("serial" => $arr["SERIAL_SERVER"]),array('$set' => array("PAR" => $arr["CONTROLLERS"])),array("upsert" => true));
                                	$progress++;
                                	echo $progress."/".$sumfile."\n";
				}
			}
		}
		closedir($handle);
	}
	
	//Close MongoDB
        $conn->close();
?>
