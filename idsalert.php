<?php
$time1=date("Y-m-d H:i:s");
$time2=date("Y-m-d H:i:s",strtotime("-5 minute"));
//$time1='2013-10-16 19:00:00';
//$time2='2013-10-14 11:00:00';

function hc()                                          //换行，偷懒用的
{
echo "\n";
}

function display($sid,$attack_count,$attacktype)
{
global $time1;
global $time2;
$con = mysql_connect("1.1.1.1","user","password");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("ids", $con);
$sql = "select inet_ntoa(i.ip_src) as ipsrc,inet_ntoa(i.ip_dst) as ipdst,count(*) as sum FROM event e,signature s,iphdr i  where
 e.sid=i.sid and e.cid=i.cid and s.sig_sid=$sid and e.signature=s.sig_id and e.timestamp between '$time2' and '$time1' group by 
i.ip_src,i.ip_dst order by sum desc";
$result=mysql_query($sql);
$outstr="src\t\t\t\tdst\t\t\ttotal\n";
$bodystr='';
while ($temp = mysql_fetch_array($result))
      {
       $bodystr.=$temp['ipsrc']."\t\t".$temp['ipdst']."\t\t".$temp['sum']."\n";
       hc();
       
       intodbinfo($time1,$attacktype,$temp['sum'],$temp['ipsrc'],$temp['ipdst'],'IDCX');
      }
$outstr=$outstr.$bodystr;
print "$outstr\n";
mysql_close($con);
sendmail("IDCX ".$attacktype." 5分钟(".$time2."---".$time1.")攻击次数为 ".$attack_count."\n".$outstr,$attacktype);
}

function sendmail($body,$subject)                      //发送邮件
{
        $mail_to = "sec@name.com";
        $mail_now = date("Y-m-d h:i:s");
        $from_name = "ids_report@name.com";
        $from_email = "sendmail";
        $headers = "From:$from_name <$from_email>";
//      $body = "这是一封重要的邮件，请您查收";
//      $subject = "[$mail_now] 测试邮件";
        if (mail($mail_to,$subject,$body,$headers))
        {
                echo "success \n";
        }
        else
        {
                echo "failed \n";
        }
}

function alert($sid,$count)                               //报警，参数sid对应ids的sid,count为阀值
{
 switch ($sid)
  { case 90000001:
       $attacktype="SQLMAP";
        break;
    case 90000002:
        $attacktype="AWVS";
        break;
    case 90000003:
        $attacktype="China Chopper";
        break;
    case 90000007:
        $attacktype="MYSQL INJECTION";
        break;
    case 90000008:
        $attacktype="Havij Found";
        break;
    case 90000011:
        $attacktype="APPSCAN FOUND";
        break;
    case 90000012:
        $attacktype="STRUTS2 FOUND";
        break;
    default:
        $attacktype="OTHER";
  }

$con = mysql_connect("1.1.1.1","user","password");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

//    $time1='2013-10-14 19:00:00';
//    $time2='2013-10-14 11:00:00';

  global $time1;
  global $time2;

  mysql_select_db("ids", $con);
  $sql = "select count(*) as total FROM event e,signature s  where s.sig_sid=$sid and e.signature=s.sig_id and e.timestamp betwe
en '$time2' and '$time1'";
  $result=mysql_query($sql);
  $temp=mysql_fetch_array($result);
//  echo "\n";
//  echo $time2;
//  echo "\n";
//  echo $time1;
//  echo "\n";
//  echo $temp['total'];
//  echo "\n";
//  sendmail($attacktype." 5分钟攻击次数为".$temp['total'],$attacktype); 
  $attack_count=$temp['total'];
  mysql_close($con);
if ((int)$attack_count > (int)$count)
  {
  echo "fuck you";
  echo "\n";
  display($sid,$attack_count,$attacktype);
// sendmail($attacktype." 5分钟攻击次数为".$attack_count,$attacktype);
  }
else
  {
  echo "ok!!!";
  echo "\n";
  }


intodb($time1,$sid,$temp['total']);

}

function intodb($time,$type,$count)                   //入库
{
echo $time;
hc();
echo $type;
hc();
echo $count;
hc();
$con = mysql_connect("1.1.1.1","user","password");

if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("attack", $con);
mysql_query("INSERT INTO attack (date,type,count) VALUES ('$time', '$type', '$count')");
mysql_close($con);
}


function intodbinfo($time,$type,$count,$src_ip,$dst_ip,$location)
{
echo $time;
hc();
echo $type;
hc();
echo $count;
hc();
$con = mysql_connect("1.1.1.1","user","password");

if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("attack", $con);
mysql_query("INSERT INTO attackinfo (date,type,count,src_ip,dst_ip,location) VALUES ('$time','$type','$count','$src_ip','$dst_ip
','$location')");
echo "ffffffffffffffffffffffffffffffffffffffffffuuuuuuuuuuuuuuuuuuuuuuucccccccccccccccccccckkkkkkkkkkkkkkkkkkkk";
echo $time;
hc();
echo $src_ip;
hc();
mysql_close($con);
}





alert(90000001,1);
alert(90000002,1);
alert(90000003,1);
alert(90000007,1);
alert(90000008,1);
alert(90000011,1);
alert(90000012,1);
?>
