<!--session timeout prompt -->
<div id="idle_timer" style="display:none">
	<div class="corners prompt selfcheck_button">
		<h1 style="white-space:nowrap" class="selfcheck_button">You've been inactive for <?php echo $inactivity_timeout/1000;?> seconds.<br />Click OK to continue.<br />Otherwise your session will end in <span style="color:#b60606">20</span> seconds.</h1>
		<div class="prompt_box_border corners" style="padding:5px;margin:10px auto 10px auto;cursor:pointer;width:150px" id="ok" onclick="tb_remove();" title="selfcheck_button">
			<div class="ok_button corners" title="selfcheck_button">
				<h1 style="color:#333;padding:25px;white-space:nowrap" title="selfcheck_button">OK</h1>
			</div>
		</div>
	</div>
</div>
<!--end session timeout prompt -->
</body>
</html>