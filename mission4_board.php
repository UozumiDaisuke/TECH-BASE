<?php
	header('Content-Type: text/html; charset=UTF-8');

	//============       MySQL接続       ============
	$dsn = 'データベース名';
	$user = 'ユーザ名';
	$password = 'パスワード';
	//$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	$pdo = new PDO($dsn, $user, $password);

	//============       変数の指定       ============
	$post_name = $_POST["name"];													//フォームから送信された名前
	$post_comment = $_POST["comment"];										//フォームから送信されたコメント
	$post_time = date( "Y/m/d H:i:s" );										//フォームから送信された時間
	$delete_number = $_POST["delete_number"];							//フォームから送信された削除番号
	$edit_number = $_POST["edit_number"];									//フォームから送信された編集番号
	$hidden_number = $_POST["hidden_number"];							//編集確認番号
	$comment_password = $_POST["comment_password"];				//フォームから投稿されたパスワード
	$delete_password = $_POST["delete_password"];					//削除用パスワード
	$edit_password = $_POST["edit_password"];							//編集用パスワード
	$next_id = 0;																					//次の投稿番号
	$next_secret_id = 0;																	//削除番号整理用番号
	$space = ' 　';												  							//ブラウザ表示時のスペース
	$count = 1;																						//投稿削除後の番号整理
	$table_name = 'mission4_v5';													//テーブル名
	$delete_message = '削除されました！';											//削除時のメッセージ
	$miss_password = 'パスワードが間違っています';							//パスワード間違い時のメッセージ
	$edit_message = '投稿フォームから編集をおこなってください';		//編集場所を促すメッセージ
	$miss_writing = '記入漏れがあります．';										//記入漏れ時のメッセージ
	$delete_new_password = 'deleted';											//削除時変更パスワード
	$flag_edit = 0;																				//編集モードの確認

	//============       テーブル作成       ============
	$sql = "CREATE TABLE $table_name"
	."("
	."id									INT,"
	."name								char(32),"
	."comment							TEXT,"
	."post_time  					char(32),"
	."password						char(32)"
	.");";

	$stmt = $pdo->query($sql);

/*
	//============       テーブルの一覧表示       ============
	$sql = 'SHOW TABLES';
	$results = $pdo -> query($sql);
	foreach($results as $row){
		echo $row[0];
	}

	//============       テーブルの中身確認       ============
	$sql = "SHOW CREATE TABLE $table_name";
	$results = $pdo -> query($sql);
	foreach($results as $row){
		print_r($row);
	}
*/

	//============       投稿モードの確認      ============
	if(!empty($hidden_number)){
		//hidden_numberが存在する -> 編集モード
		$flag_edit = 1;
	}
	//============       投稿      ============
	if($flag_edit == 0){
		//最後の投稿番号の確認，次回の投稿番号の確認
		if(!empty($post_name) and !empty($post_comment) and !empty($comment_password)){
			$sql = "SELECT*FROM $table_name";
			$results = $pdo -> query($sql);
			foreach($results as $row){
				//ループの最後の投稿番号を保存
				$next_id = $row['id'];
			}
			//+1をし，次の投稿番号に変更
			$next_id += 1;
			//echo $next_id. '<br>';

			//投稿準備
			$sql = $pdo -> prepare("INSERT INTO $table_name (id, name, comment, post_time, password) VALUES(:id, :name, :comment, :post_time, :password)");
			$sql -> bindParam(':id', $id, PDO::PARAM_INT);									//投稿番号
			$sql -> bindParam(':name', $name, PDO::PARAM_STR);							//投稿者名
			$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);				//投稿コメント
			$sql -> bindParam(':post_time', $time, PDO::PARAM_STR);					//投稿時間
			$sql -> bindParam(':password', $password, PDO::PARAM_STR);			//パスワード

			//投稿データの挿入
			$id = $next_id;
			$name = $post_name;
			$comment = $post_comment;
			$time = $post_time;
			$password = $comment_password;

			//実行し，テーブルに内容を保存
			$sql -> execute();

		}else{
			//投稿に空欄が存在していることを知らせるメッセージの表示
			//echo $miss_writing. '<br>';
		}

		//============       投稿削除      ============
		if(!empty($delete_number) and !empty($delete_password)){
			$sql = "SELECT*FROM $table_name";
			$results = $pdo -> query($sql);
			foreach($results as $row){
				//データベースの投稿番号と削除指定された番号との一致を確認
				if($row['id'] == $delete_number){
					//パスワードの一致を確認
					if($row['password'] == $delete_password){
						//全てが一致した場合，名前とパスワードを変更
						$sql = "UPDATE $table_name SET name = :name, password = :password WHERE id = :id";
						$stmt = $pdo -> prepare($sql);
						$params = array(':name' => $delete_message, ':password' => $delete_new_password, ':id' => $delete_number);
						//値の変更を実行
						$stmt->execute($params);
					}else{
						//パスワードが異なっているというエラーメッセージの表示
						echo $miss_password. '<br>';
					}
				}
			}
		}else{
			//空欄があるというエラーメッセージの表示
			//echo $miss_writing;
		}

	//============       投稿編集     ============
		if(!empty($edit_number) and !empty($edit_password)){
			$sql = "SELECT*FROM $table_name";
			$results = $pdo -> query($sql);
			foreach($results as $row){
				//データベースの投稿番号と編集指定された番号との一致を確認
				if($row['id'] == $edit_number){
					//パスワードの一致を確認
					if($row['password'] == $edit_password){
						//編集場所を指示するメッセージを表示
						echo $edit_message. '<br>';

						//投稿フォームに編集データを戻す
						$edit_number = $row['id'];
						$edit_name = $row['name'];
						$edit_comment = $row['comment'];
						$post_password = $row['password'];

					}else{
						//パスワードが異なっているというエラーメッセージの表示
						echo $miss_password. '<br>';
					}
				}
			}
		}else{
			//空欄があるというエラーメッセージの表示
			//echo $miss_writing;
		}
	}

	//============       投稿内容の変更     ============
	if($flag_edit == 1){
		if(!empty($post_name) and !empty($post_comment) and !empty($comment_password)){
			$sql = "UPDATE $table_name SET name = :name, comment = :comment, post_time = :post_time, password = :password WHERE id = :id";
			$stmt = $pdo -> prepare($sql);
			//変更内容の指定
			$params = array(':name' => $post_name, ':comment' => $post_comment, ':post_time' => $post_time, ':password' => $comment_password, ':id' => $hidden_number);
			$stmt->execute($params);
		}else{
			//空欄があるというエラーメッセージの表示
			//echo $miss_writing. '<br>';
		}
	}
?>

<!DOCTYPE html>
<html>
<!-- ============       投稿フォームの見た目      ============ -->
	<head>
		<meta charset = "utf-8">
		<title>掲示板</title>
	</head>
	<body>

		<h3>投稿欄</h3>
		<form action = "mission4.php" method = "post">
			<p>お名前：<br>
			<input type = "text" name = "name" placeholder="名前" value = "<?php echo $edit_name; ?>"></p>
			<p>コメント：<br>
			<input type = "text" name = "comment" placeholder="コメント" value = "<?php echo $edit_comment; ?>"></p>
			<p>パスワード：<br>
			<input type = "text" name = "comment_password" placeholder="パスワード" value = "<?php echo $post_password; ?>"></p>
			<input type = "hidden" name = "hidden_number" value = "<?php echo $edit_number; ?>">
			<input type = "submit" value = "送信">
		</form>

		<h3>投稿の編集はコチラ！</h3>
		<form action = "mission4.php" method = "post">
			<p>編集を行う投稿番号：<br>
			<input type = "text" name = "edit_number" placeholder="投稿番号"></p>
			<p>パスワード：<br>
			<input type = "text" name = "edit_password" placeholder="パスワード"></p>
			<input type = "submit" value = "送信">
		</form>

		<h3>投稿の削除はコチラ！</h3>
		<form action = "mission4.php" method = "post">
			<p>削除を行う投稿番号：<br>
			<input type = "text" name = "delete_number" placeholder="投稿番号"></p>
			<p>パスワード：<br>
			<input type = "text" name = "delete_password" placeholder="パスワード"></p>
			<input type = "submit" value = "送信">
		</form>

		<?php
			echo '<br><hr>';	//線の作成
		?>

		<h2>みんなの投稿！</h2>

		<?php
//============       ブラウザの表示      ============
			$sql = "SELECT*FROM $table_name";
			$results = $pdo -> query($sql);
			foreach($results as $row){
				if($row['name'] == $delete_message){
					//名前に削除メッセージが入っている時投稿が削除されていることを知らせるメッセージの表示
					echo '投稿番号：'. $row['id']. $space;
					echo 'この投稿は削除されました．<br>';
				}else{
					echo '投稿番号：'. $row['id']. $space;
					echo '投稿者：'. $row['name']. $space;
					echo $row['comment']. $space;
					echo '('. $row['post_time']. ')<br>';
				}
			}
		?>
	</body>
</html>
