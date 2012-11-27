
  </div>
  
  <?php
    $loadt=microtime(true)-$time;
  ?>

  <div id='contentfooter'>
    <div id='cfooterbox'>
      <?php
        echo '<span id="nameversion">'.P_NAME.' '.P_VERSION.'</span> ';
        echo '<a href="',URL,'home">',$plk_la['home'],'</a> ';
        echo '<a href="',URL,'developer">',$plk_la['developer'],'</a> ';
        echo '<a href="',URL,'about">',$plk_la['about'],'</a> ';
        echo '<a href="',URL,'help">',$plk_la['help'],'</a> ';
        echo '<a href="',URL,'privacy">',$plk_la['privacy'],'</a> ';
        if(DEBUG) { echo '<span id="loadtime">'.number_format($loadt,5),'s</span>'; }
      ?>
    </div>
  </div>

</div>

<?=plk_scripts(); ?>  <!--//Don't Remove-->
<?php load_gui_scripts(); ?>
<?php if( RELEASE == 0 ) { load_local_scripts($js); } //js is passed via load_foot() ?>
</body>
</html>
