<?php
session_start();

/******************************************************************************************************************
 * Author: Ryan P Sloan (RPS)
 *
 *
 *
 *
 *****************************************************************************************************************/
if(isset($_SESSION['data'])){
    $output = $_SESSION['data'];
    $clear = "<a href='clear.php' class='link nav-link'>Clear File</a>";
 }else{
    $output = null;
    $clear = "";
}

if(isset($_SESSION['fileName'])){
     $download = '<a href="download.php">Download File</a>';
     $message = $_SESSION['message'];
}
else{
     $download = "";
     $message = "";
}

if(isset($_SESSION['totalSum'])){
    $totalSum = $_SESSION['totalSum'];
}
else{
    $totalSum = null;
}

if(isset($_SESSION['finalSum'])){
    $finalSum = $_SESSION['finalSum'];
}
else{
    $finalSum = null;
}

if(isset($_SESSION['error'])){
    $error = $_SESSION['error'];
}else{
    $error = '';
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kewa GL Balancer</title>

    <!-- Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!--Custom CSS -->
    <link rel="stylesheet" href="css/standard.css"
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <a class="navbar-brand" href="#"> <img src="img/SantoDomingo_LogoSeal_200x200.png"/></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbar">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <?php echo $clear ?>
                <?php echo $download ?>
            </li>
        </ul>
        <form class="form-inline my-2 my-lg-0" enctype="multipart/form-data" action="php/processor.php" method="post">
            <input class="form-control mr-sm-2" type="file" name="file" placeholder="Search" aria-label="File Upload">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Process</button>

        </form>
    </div>
</nav>
<main role="main" class="container">
    <section class="row">
          <article class="col-md-12 p-lg-12 mx-auto my-12">
              <h1 class="display-4 font-weight-normal">Kewa Pueblo GL Balancer</h1>
              <p class="lead font-weight-normal">Upload the Kewa GL file as a .csv or .txt</p>
              <div id="messages" class="my-2 my-lg-0">
                  <?php
                      if(isset($message)) {
                          echo "<p class='green'>$message</p>";
                      }
                  ?>
              </div>
              <div id="errors" class="my-2 my-lg-0">
                <?php
                    if(isset($error)){
                        echo "<p class='red'>$error</p>";
                    }
                ?>
              </div>
              <div id="output" class="my-2 my-lg-0">
                  <?php

                  if(isset($totalSum)) {
                      $d = number_format($totalSum['debitTotalSum'], 2);
                      $c = number_format($totalSum['creditTotalSum'], 2);
                      echo "<p class='heading'>Totals before balance</p><p>Debit Total = $$d | Credit Total = $$c </p>";
                  }
                  echo '<hr>';
                  if(isset($output)) {
                      echo '<p>Number of Lines: '.$_SESSION['lineCount'].'</p>';
                      foreach ($output as $ee => $array) {

                          echo "<p class='heading'>$ee</p><br>";
                          foreach ($array as $arr) {
                              foreach ($arr as $a) {
                                  foreach($a as $line) {
                                      echo "<p>$line</p>";
                                  }
                              }
                          }
                          echo "<br><hr>";
                      }
                  }
                  if(isset($finalSum)) {
                      $dbt = number_format($finalSum['finalDebitSum'], 2);
                      $crt = number_format($finalSum['finalCreditSum'], 2);
                      echo "<p class='heading'>Totals after balance</p><p>Debit Total = $$dbt | Credit Total = $$crt </p><hr>";
                  }

                  ?>

              </div>
        </article>
    </section>
</main>
<footer class="container">
    <div class="row">
        <div class="col-md-12 p-lg-12 mx-auto my-12">
            <p class="lead font-weight-normal">Copyright &copy; PayDay HCM 2018</p>
        </div>
    </div>
</footer>
<!-- Custom JavaScript -->
<script src="js/balancer.js"></script>
</body>
</html>