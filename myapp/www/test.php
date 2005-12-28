<?php

error_reporting(E_ALL);


?>

<html>

<head>
  <title>coucou</title>
  <style type="text/css">
  </style>
</head>
<body>

<?php

$conn = mysql_connect('localhost', 'copix', 'copix');
if (!$conn) {
   die('Could not connect: ' . mysql_error());
}
mysql_select_db('copixng');

$sql='SELECT usr.login_cusr as login, usr.email_cusr as email, grp.name_cgrp as groupname';
$sql.=' FROM  copixusergroup AS usrgrp, copixgroup AS grp, copixuser AS usr';
$sql.=' WHERE  usrgrp.id_cgrp=grp.id_cgrp AND usr.login_cusr=usrgrp.login_cusr';

$result = mysql_query($sql);
if (!$result) {
   die('Query failed: ' . mysql_error());
}
/* get column metadata */
$i = 0;
while ($i < mysql_num_fields($result)) {
   echo "Information for column $i:<br />\n";
   $meta = mysql_fetch_field($result, $i);
   if (!$meta) {
       echo "No information available<br />\n";
   }
   echo "<pre>
blob:        $meta->blob
max_length:  $meta->max_length
multiple_key: $meta->multiple_key
name:        $meta->name
not_null:    $meta->not_null
numeric:      $meta->numeric
primary_key:  $meta->primary_key
table:        $meta->table
type:        $meta->type
default:      $meta->def
unique_key:  $meta->unique_key
unsigned:    $meta->unsigned
zerofill:    $meta->zerofill
</pre>";
   $i++;
}
mysql_free_result($result);
?>






</body>