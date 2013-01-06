<!--session timeout prompt -->
<div id="idle_timer">
	<h1>You've been inactive for <?php echo $inactivity_timeout/1000;?> seconds.</h1>
	<h1>Click OK to continue.</h1>
	<h1>Otherwise your session will end in <span style="color:#b60606">20</span> seconds.</h1>
	<div class="ok_button button" title="selfcheck_button" onclick="tb_remove();">
		<h1>OK</h1>
	</div>
</div>
<!--end session timeout prompt -->

</body>
</html>