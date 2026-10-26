<H2 class="notext"><?=$label_loader_title?></h2>
<div id="loading-image">
	<p id="loading-loader"><img src="/images/loader.gif" alt="Loading..." width="100px;" /><br><br><b style="color:#000000;"><?=$label_loader_loading?></b>
</div>
<?
if (substr($mid,0,1) == "A") {
	?>
	<script type="text/javascript" src="/script/maintainscroll.jquery.js"></script>
	<?
}