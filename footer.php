<?php
if (!$mobileLayout) echo '<a href="#" onclick="enableMobileLayout(1); return false;" rel="nofollow">Mobile view</a>';
else echo '<a href="#" onclick="enableMobileLayout(-1); return false;" rel="nofollow">Disable mobile view</a>';
?>
<br>
<br>
<?php $bucket = "footer"; include(LIBDIR . '/pluginloader.php');?>
Powered by <a href="http://abxd.dirbaio.net/">AcmlmBoard XD</a> 3.0<br />
By Dirbaio, xfix, Kawa, StapleButter, Nina, et al<br />
AcmlmBoard &copy; Jean-Fran&ccedil;ois Lapointe<br />
<?php print __("<!-- English translation by The ABXD Team -->")?>

<?php print (isset($footerButtons) ? $footerButtons : "")?>
<?php print (isset($footerExtensionsB) ? $footerExtensionsB : "")?>


