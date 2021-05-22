<?PHP

  set_time_limit(60);
  parse_str($_SERVER['QUERY_STRING'],$settings);

  $pdo = new PDO ("mysql:host=fdb21.awardspace.net;port=3306;dbname=3850374_talespire", '3850374_talespire', 'DatabaseAccess1');

  function dump($msg)
  {
    file_put_contents("Log.txt",file_get_contents("Log.txt").$msg."\r\n");
  }

  if((isset($settings['session'])===false) || ($settings['session']==""))
  { 
    // Clean database
    $cutOffDate = date_sub(date_create(date('Y-m-d h:i:s')), date_interval_create_from_date_string("30 days"));
    $cutoff = date_format($cutOffDate,"Y-m-d h:i:s");

    $query = "SELECT * FROM transactions";
    foreach($pdo->query($query) as $row)
    {
      if($row['timestamp']<$cutoff)
      {
        $exec = "DELETE FROM transactions WHERE trans=".$row['trans'];
        $pdo->query($exec);
      }
    }
    // Return no response (this is needed to distinguish Get and Post responses on the client side)
  }
  else if(isset($settings['content']))
  {
    // Post transaction
    if(isset($settings['user'])===false){ $settings['user']='Anonymous'; }
    $settings['content']=urlencode($settings['content']);
    $pdo->exec("INSERT INTO transactions (timestamp,session,sender,message) VALUES ('".date("Y.m.d h:i:s")."','".$settings['session']."','".$settings['user']."','".$settings['content']."');");
    // Return no response (this is needed to distinguish Get and Post responses on the client side)
  }
  else if(isset($settings['trans']))
  {
    $trans = $settings['trans'];
    // Get transactions since index
    if(isset($settings['exclude']))
    {
      $query = "SELECT * FROM transactions WHERE session='".$settings['session']."' AND sender!='".$settings['exclude']."' AND trans>".$settings['trans'];
    }
    else
    {
      $query = "SELECT * FROM transactions WHERE session='".$settings['session']."' AND trans>".$settings['trans'];
    }
    $result = "";
    foreach($pdo->query($query) as $row)
    {
      $result = $result . "{\"timestamp\": \"".$row['timestamp']."\", \"author\": \"".$row['sender']."\", \"content\": \"".$row['message']."\"},";
      $trans = $row['trans']; 
    }
    // At a minimum, return the trans id (this is needed to distinguish Get and Post responses on the client side)
    echo "{\"trans\":".$trans.",\"messages\":[".$result."]}";
  }

				
?>