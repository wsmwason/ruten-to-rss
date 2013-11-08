<?php
if(isset($_GET['result_url'])){
  header("Location: example.php?result_url=".base64_encode($_GET['result_url'])); exit;
}
?>
<form method="GET" action="">
  <input type="text" name="result_url" value="" size="100" /><br />
  <input type="submit" />
</form>