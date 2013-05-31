<?php
require_once('config.php');
require_once('admin/engine.php'); 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Savnet NTRKH.no - Om Savnet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="/admin/css/savnet.css" rel="stylesheet/less">
    <script>
    	var BASEURL = '<?= BASEURL ?>';
    </script>
    <script src="//maps.googleapis.com/maps/api/js?key=AIzaSyANgZz6JPzBjSS5KoVyQ7I9a4RAwrS015Y&sensor=false"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="/admin/js/savnet.js"></script>
  </head>
  
  <body>
   <div class="container-narrow">
      <div class="masthead">
        <ul class="nav nav-pills pull-right">
          <li id="nav_start"><a href="<?= BASEURL ?>">Logg inn</a></li>
        </ul>
      </div>

	  <?php require_once('admin/gui/om.gui.php'); ?>
   </div>
  </body>
<script src="/admin/js/less.min.js"></script>
</html>