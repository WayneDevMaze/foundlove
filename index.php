<?php 
session_start();
if (!isset($_SESSION['user'])) {
  if (isset($_COOKIE['user'])) {
    $_SESSION['user'] = $_COOKIE['user'];
  }else{
    header('location:welcome.php');
    exit();
  }
}
if (isset($_SESSION['rem'])) {
  setcookie('user',$_SESSION['user'],time()+3600);
  unset($_SESSION['rem']);
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
  <!-- header部分 -->
  <?php require_once 'public/layouts/header.php' ?>

  <body>
  <!-- 导航栏 -->
  <?php require_once 'public/layouts/nav.php' ?>

  <!-- 页面主体内容 -->
    <div class="container">
      <div class="content">
          <div class="starter-template">
            <hr>
            <h1>🍦欢迎来到 FoundLove —— 方的爱🍦</h1>
            <br>
            <p class="lead">好看的皮囊千篇一律,</p>
            <p class="lead">有趣的灵魂看不上你.</p>
            <hr>
          </div>  
      </div>
    </div><!-- /.container -->
    
    <!-- 网页底部 -->
    <?php require_once 'public/layouts/footer.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="public/js/check.js"></script>
  </body>
</html>