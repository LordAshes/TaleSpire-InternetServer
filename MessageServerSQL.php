<?PHP

    set_time_limit(60);
    parse_str($_SERVER['QUERY_STRING']);

	$pdo = new PDO ("mysql:host=sql304.byethost18.com;dbname=b18_28670148_transactions", 'b18_28670148', 'Dummy1');
	
    if((isset($session)===true) && ($session!=""))
    {
	    if(isset($content))
	    {
    		// Post transaction
    	    if(isset($user)===false){ $user='Anonymous'; }
            $content=urlencode($content);
		    $pdo->exec("INSERT INTO transactions (timestamp,session,sender,message) VALUES ('".date("Y.m.d h:i:s")."','".$session."','".$user."','".$content."');");
            // Return no response (this is needed to distinguish Get and Post responses on the client side)
	    }
	    else if(isset($trans))
	    {
    		// Get transactions since index
	    	if(isset($session)===false){ return; }
    		if(isset($trans)===false){ clean(); return; }
		    if(isset($exclude))
		    {
                $query = "SELECT * FROM transactions WHERE session='".$session."' AND sender!='".$exclude."' AND trans>".$trans;			
		    }
		    else
		    {
    			$query = "SELECT * FROM transactions WHERE session='".$session."' AND trans>".$trans;
		    }
            $result = "";
            foreach($pdo->query($query) as $row)
            {
                $result = $result . urldecode($row['message'])."|";
                $trans = $row['trans']; 
            }
            // At a minimu, return the trans id (this is needed to distinguish Get and Post responses on the client side)
            echo $trans."|".$result;
	    }
    }
    else 
    {
		// Clean database

        $cutOffDate = date_sub(date_create(date('Y-m-d h:i:s')), date_interval_create_from_date_string("30 days"));
        $cutoff = date_format($cutOffDate,"Y-m-d h:i:s");

        $query = "SELECT * FROM transactions";
        foreach($pdo->query($query) as $row)
        {
            // echo($row['trans'].": ".$row['timestamp']." vs ".$cutoff."<BR>\r\n");
            if($row['timestamp']<$cutoff)
            {
                $exec = "DELETE FROM transactions WHERE trans=".$row['trans'];
                // echo $exec."\r\n";
                $pdo->query($exec);
            }
        }
        // Return no response (this is needed to distinguish Get and Post responses on the client side)
	}

	function dump($msg)
	{
		file_put_contents("Log.txt",file_get_contents("Log.txt").$msg."\r\n");
	}
				
?>