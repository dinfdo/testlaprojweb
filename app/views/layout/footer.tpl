<script src="<?= $base ?>/js/api.js"></script>
<?php foreach ($scripts ?? [] as $script): ?>
  <script src="<?= $base ?>/js/<?= $script ?>.js"></script>
<?php endforeach; ?>
</body>
</html>
