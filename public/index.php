<?php
session_start();
require_once(__DIR__ . '/../src/db_connect.php');

if (isset($_POST['action_type']) && $_POST['action_type']) {
  if ($_POST['action_type'] === 'insert') {
    require(__DIR__ . '/../src/insert_message.php');
  } else if ($_POST['action_type'] === 'delete') {
    require(__DIR__ . '/../src/delete_message.php');
  }
}

require(__DIR__ . '/../src/session_values.php');
//投稿内容の情報をデータベースから取得する処理を実装しています。 PDO クラスで用意されているqueryメソッドを使うために、~/src/db_connect.phpで生成したPDOインスタンス($dbh)を利用しています 
// データベースから投稿内容を取得するだけであれば、ユーザからの入力情報を SQL 文に含める必要がないため、ここではqueryメソッドを利用しています。
//<aside>
// query と prepare の違い 
//SELECT 文のように、単純なデータ取得を行う SQL 文を実行する場合は query メソッドを利用します。
//query()：ユーザからの入力を利用しない。SELECTのようにデータの取得のみ。
//prepare()：ユーザからの入力を利用する。INSERTのようにデータを渡すとき。

$stmt = $dbh->query('SELECT * FROM posts ORDER BY created_at DESC;');
$message_length = $stmt->rowCount();

function convertTz($datetime_text)
{
  $datetime = new DateTime($datetime_text);
  $datetime->setTimezone(new DateTimeZone('Asia/Tokyo'));
  return $datetime->format('Y/m/d H:i:s');
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UFT-8">
  <meta http-equiv="X-UA-Compatibale" content="ie=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="robots" content="noindex" />
  <title>ひとこと掲示板</title>
  <link rel="stylesheet" href="./assets/main.css">
</head>

<body>
  <div class="page-cover">

    <p class="page-title">ひとこと掲示板</p>
    <hr class="page-divider" />
  
    <!-- 投稿用フォーム -->
    
    <?php if ($messages['action_success_text'] !== '') { ?>
      <div class="action-success-area"><?php echo $messages['action_success_text']; ?></div>
    <?php } ?>
    <?php if ($messages['action_error_text'] !== '') { ?>
      <div class="action-failed-area"><?php echo $messages['action_error_text']; ?></div>
    <?php } ?>

    <div class="form-cover">
      <!--meyod属性の「POST」による送信を行うことで、リソースの送信を行います。  -->
      <!-- URL ではなく、メッセージボディに設定されるため、パスワードやユーザ情報などの機密性の高いデータを送信するのに用いられます -->
      <!-- action には送信先の URL を指定することができる -->
      <!-- 今回は `index.php` 内でデータを扱い、DB への保存処理を展開していくため、`action="/"` としてフォームが置かれた
          自身のページ `~~/public/index.php` に送信しています。
        　値を空、もしくは属性自体がない場合でも、フォームが置かれた自身のページに送信されます。 -->
      <form action="/" method="post">

        <!-- 投稿者ニックネーム入力欄 -->
        <div class="form-input-title">投稿者ニックネーム</div>
        <!-- `htmlspecialchars`によってエスケープ処理がされています。 エスケープ処理は、フォームから送られてきた値や、データベースから取り出した値をブラウザ上に表示する際に、
         特殊文字や記号の変換を行っています。こうすることで、悪意のあるコードの埋め込みを防いだり、HTML 上で特殊文字を適切に表示することができます。 -->
        <input type="text" name="author_name" maxlength="40" value="<?php echo htmlspecialchars($messages['input_pre_author_name'], ENT_QUOTES); ?>" class="input-author-name" />
        <?php if ($messages['input_error_author_name'] !== '') { ?>
          <div class="form-input-error">
            <?php echo $messages['input_error_author_name']; ?>
          </div>
        <?php } ?>
        <!--投稿内容入力欄について  -->
        <div class="form-input-title">投稿内容<small>(必須)</small></div>
        <!-- <textarea>は複数行の入力フィールドを作成するタグです -->
        <!-- 【rows,cols】<textarea>`が占める実際の大きさを指定。サイズを指定することによる欠点として、文字数による指定になるため、文字のフォントによってはデザインが汚くなってしまいます。それを避けるため、今回は CSS で指定-->
        <textarea name="message" class="input-message"><?php echo htmlspecialchars($messages['input_pre_message'], ENT_QUOTES); ?></textarea>
        <?php if ($messages['input_error_message'] !== '') { ?>
          <div class="form-input-error">
            <?php echo $messages['input_error_message']; ?>
          </div>
        <?php } ?>
        <!-- 発火するアクションを指定するための要素 -->
        <!--<input>要素はブラウザに表示する必要は無いため、type="hidden"を指定し、value 属性で指定した値をがサーバーへ送信。これにより、ボタンがクリックされた時に、
          $_POSTという変数に値を格納することができます。name="action_type"、value="insert"としているので、 $_POST['action_type']という変数に“insert”という文字列を格納する -->
        <input type="hidden" name="action_type" value="insert" />

        <!-- 投稿するボタンについて -->
        <!-- <button>タグは、ボタンを作成する際に使用され、<input>と同様に name や value 等の属性が指定できます。type 属性には 以下の  ３種類のいずれかを指定することができます
          <button>タグは、ボタンを作成する際に使用され、<input>と同様に name や value 等の属性が指定できます。type 属性には 以下の３種類のいずれかを指定することができます -->
        <!-- submit、reset、button  -->
        <!-- nput 要素のボタンとは異なり、button 要素では子要素を持つことができます。 -->
        <!-- input タグと違い、button タグには疑似要素が使うことができ､CSS でのデザインに自由度が高くなります -->
        <button type="submit" class="input-submit-button">投稿する</button>
      </form>
    </div>
    <!-- 投稿内容の表示 -->
    <hr class="page-divider" />
    <div class="message-list-cover">
      <small>
        <!-- DB から取得した件数を表示しています。 -->
        <?php echo $message_length; ?> 件の投稿
      </small>
      <!-- データベースから取得したデータを `$row` へ格納し、`while` による繰り返し処理で展開し、メッセージの一覧を表示しています。
          while 文は「指定の条件を満たすまでのループ処理」が可能であるため、取得したデータ `$stmt->fetch(PDO::FETCH_ASSOC` の数だけ繰り返し処理を行います。
          `explode()`によって`$row['message']`を改行文字`"\n"`ごとに分割して、`$lines`へ格納しています。 -->
      <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
        <?php $lines = explode("\n", $row['message']); ?>
        <div class="message-item">
          <div class="message-title">
            <div><?php echo htmlspecialchars($row['author_name'], ENT_QUOTES); ?></div>
            <small><?php echo convertTz($row['created_at']); ?></small>
            <div class="spacer"></div>
            <form action="/" method="post" style="text-align:right">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
              <input type="hidden" name="action_type" value="delete" />
              <button type="submit" class="message-delete-button">削除</button>
            </form>
          </div>
          <!-- `foreach()`による繰り返し処理で`$lines`の中身を展開・表示しています。
          こうすることで、ユーザが入力したメッセージに含まれる改行を反映して表示しています。 -->
          <?php foreach ($lines as $line) { ?>
            <p class="message-line"><?php echo htmlspecialchars($line, ENT_QUOTES); ?></p>
          <?php } ?>
        </div>
      <?php } ?>

      <!-- モックアップ -->
      <!--<small>
      1 件の投稿
        </small>
      <div class="message-item">
        <div class="message-title">
          <div>イチロー</div>
          <small>2022-01-01 00:00:00</small>
          <div class="spacer"></div>
          <form action="/" method="post" style="text-align:right">
            <input type="hidden" name="id" value="" />
            <input type="hidden" name="action_type" value="delete" />
            <button type="submit" class="message-delete-button">削除</button>
          </form>
        </div>
          <p class="message-line">明けましておめでとうございます</p>
      </div>-->

    </div>

  </div>
</body>

</html>


