<!-- Bootstrap core JavaScript
    ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script>
  window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')
</script>
<script src="../../assets/js/vendor/popper.min.js"></script>
<script src="../../dist/js/bootstrap.min.js"></script>

<!--  Notifications Plugin, full documentation here: http://bootstrap-notify.remabledesigns.com/    -->
<script src="../../assets/js/plugins/bootstrap-notify.js"></script>

<!-- Icons -->
<script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
<script>
  feather.replace()
</script>

<script>
  window.onload = function() {
    <?php
    if ($notification) {
      echo "showNotification(\"top\",\"right\",\"$notification\");";
    }
    ?>
  }

  function showNotification(from, align, text) {
    color = 'warning';
    $.notify({
      icon: "",
      message: text
    }, {
      type: color,
      delay: 9000,
      timer: 1000,
      placement: {
        from: from,
        align: align
      }
    });
  }
</script>

<!-- replaceState method of JQuery to prevent duplication of data submission due to post back -->
<script>
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>
</body>

</html>