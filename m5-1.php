

<?php
// データベース接続設定
$dbname = 'データベース名';
$host = 'localhost';
$username = 'ユーザー名';
$password = 'パスワード';

// データベースへの接続
try {
    $pdo = new PDO("mysql:dbname=$dbname;host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('データベースの接続に失敗しました。' . $e->getMessage());
}

// 投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 新規投稿
    if (isset($_POST['name']) && isset($_POST['comment']) && isset($_POST['password'])) {
        $name = $_POST['name'];
        $comment = $_POST['comment'];
        $password = $_POST['password'];

        // パスワードが空でない場合のみ投稿を保存
        if (!empty($password)) {
            // 現在の日時を取得
            $timestamp = date('Y-m-d H:i:s');

            // 投稿データの保存
            $sql = "INSERT INTO comments (name, comment, password, timestamp) VALUES (:name, :comment, :password, :timestamp)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    // 削除
    if (isset($_POST['delete_id']) && isset($_POST['delete_password'])) {
        $deleteId = $_POST['delete_id'];
        $deletePassword = $_POST['delete_password'];

        // 削除対象の投稿を取得
        $sql = "SELECT * FROM comments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        $stmt->execute();
        $deleteData = $stmt->fetch(PDO::FETCH_ASSOC);

        // パスワードが一致した場合のみ削除処理を実行
        if ($deleteData && $deleteData['password'] === $deletePassword) {
            // 投稿の削除
            $sql = "DELETE FROM comments WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    // 編集
    if (isset($_POST['edit_id']) && isset($_POST['edit_name']) && isset($_POST['edit_comment']) && isset($_POST['edit_password'])) {
        $editId = $_POST['edit_id'];
        $editName = $_POST['edit_name'];
        $editComment = $_POST['edit_comment'];
        $editPassword = $_POST['edit_password'];

        // 編集対象の投稿を取得
        $sql = "SELECT * FROM comments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
        $stmt->execute();
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);

        // パスワードが一致した場合のみ編集処理を実行
        if ($editData && $editData['password'] === $editPassword) {
            // 投稿の更新
            $sql = "UPDATE comments SET name = :name, comment = :comment WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $editName, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $editComment, PDO::PARAM_STR);
            $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}

// 投稿一覧の取得
$sql = "SELECT * FROM comments ORDER BY id ASC";
$stmt = $pdo->query($sql);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
</head>
<body>
    <h1>掲示板</h1>

    <!-- 新規投稿フォーム -->
    <h2>新規投稿フォーム</h2>
    <form method="POST" action="">
        <label for="name">名前:</label>
        <input type="text" name="name" id="name" required><br>

        <label for="comment">コメント:</label>
        <input type="text" name="comment" id="comment" required><br>

        <label for="password">パスワード:</label>
        <input type="password" name="password" id="password" required><br>

        <input type="submit" value="送信">
    </form>

    <!-- 投稿一覧 -->
    <h2>投稿一覧</h2>
    <table>
        <tr>
            <th>投稿番号</th>
            <th>名前</th>
            <th>コメント</th>
            <th>投稿日時</th>
            <th>削除</th>
            <th>編集</th>
        </tr>
        <?php foreach ($comments as $comment): ?>
        <tr>
            <td><?php echo $comment['id']; ?></td>
            <td><?php echo $comment['name']; ?></td>
            <td><?php echo $comment['comment']; ?></td>
            <td><?php echo $comment['timestamp']; ?></td>
            <td>
                <!-- 削除フォーム -->
                <form method="POST" action="">
                    <input type="hidden" name="delete_id" value="<?php echo $comment['id']; ?>">
                    <input type="password" name="delete_password" placeholder="パスワード" required>
                    <input type="submit" value="削除">
                </form>
            </td>
            <td>
                <!-- 編集フォーム -->
                <form method="POST" action="">
                    <input type="hidden" name="edit_id" value="<?php echo $comment['id']; ?>">
                    <input type="text" name="edit_name" value="<?php echo $comment['name']; ?>" required>
                    <input type="text" name="edit_comment" value="<?php echo $comment['comment']; ?>" required>
                    <input type="password" name="edit_password" placeholder="パスワード" required>
                    <input type="submit" value="編集">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
