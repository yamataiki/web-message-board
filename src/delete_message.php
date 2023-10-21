<?php

if (isset($_POST['id']) && $_POST['id']) {
  $stmt = $dbh->prepare('DELETE FROM posts WHERE id = :id');
  $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
  $stmt->execute();
}

header('Location: /');
exit();