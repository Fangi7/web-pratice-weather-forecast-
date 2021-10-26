<?php
session_start();

define('server', 'localhost');
define('username', 'root');
define('pwd', '');
define('name', 'hw3');
define('port','3306');
$link = mysqli_connect(server, username, pwd, name, port);
if($link==false)
{
    die("ERROR: could not connect" . mysqli_connect_error());
}
if($_SERVER["REQUEST_METHOD"] == "POST" || $_SESSION["everpost"]){ 
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $searchCity = $_POST["historySearch"];
        $_SESSION["everpost"] = $searchCity;
    } 
    $searchCity = $_SESSION["everpost"] ;
    $sql = 'SELECT * from weatherinfo where weatherinfo.username = ? AND city = "'.$searchCity.'"'; 
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        // Set parameters
        $param_username = $_SESSION['username'];
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            $per_total = mysqli_stmt_num_rows($stmt);  //計算總筆數
            $per = 7;  //每頁筆數
            $pages = ceil($per_total/$per);  //計算總頁數;ceil(x)取>=x的整數,也就是小數無條件進1
            if(!isset($_GET['page'])){  //!isset 判斷有沒有$_GET['page']這個變數
                $page = 1;	  
            }
            else{
                $page = $_GET['page'];
            }
            $start = ($page-1)*$per;//每一頁開始的資料序號(資料庫序號是從0開始)
           
            $page_start = $start +1;  //選取頁的起始筆數
            $page_end = $start + $per;  //選取頁的最後筆數
            if($page_end>$per_total){  //最後頁的最後筆數=總筆數
                $page_end = $per_total;
            }
        }
    }

}
else {
    $sql = "SELECT * from weatherinfo where weatherinfo.username = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        // Set parameters
        $param_username = $_SESSION['username'];
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            $per_total = mysqli_stmt_num_rows($stmt);  //計算總筆數
            $per = 7;  //每頁筆數
            $pages = ceil($per_total/$per);  //計算總頁數;ceil(x)取>=x的整數,也就是小數無條件進1
            if(!isset($_GET['page'])){  //!isset 判斷有沒有$_GET['page']這個變數
                $page = 1;	  
            }
            else{
                $page = $_GET['page'];
            }
            $start = ($page-1)*$per;//每一頁開始的資料序號(資料庫序號是從0開始)
           
            $page_start = $start +1;  //選取頁的起始筆數
            $page_end = $start + $per;  //選取頁的最後筆數
            if($page_end>$per_total){  //最後頁的最後筆數=總筆數
                $page_end = $per_total;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="zh-tw">

<head>
    <meta charset="UTF-8">
    <title>HW3</title>
    <link rel="stylesheet" href="main.css" />
    <script src="sweetalert.js">
    <script src="main.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous"></script>
    <style> 
    
    table{
        margin:20px;
        
    }
</style>
</head>

<body>
    
    <!-- main page -->
    <div id="main_page">
        <header>
            <div class="headerInfo">
                <input type="button" id="weatherPageBtn" value="Weather Page" name="weatherPageBtn" onclick="window.location.href='index.php'">
                <input type="button" id="logoutBtn" value="Logout" name="logoutBtn" onclick="window.location.href='logout.php'">
                <input type="button" id="historyBtn" value="History" name="logohistoryBtnutBtn">
                <span id="userName" style="color:white ">Hello,<?php echo $_SESSION['username']; ?></span>
            </div>
        </header>

        <div class="container">
            <h1>The Weather History</h1>
            <form <?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                    <input type="search" id="search" class="form-control me-2"  name='historySearch' placeholder="Chiayi,Minxiong" />
                    <input type="submit" id="searchBtn" class="btn btn-outline-success" value="Search" name="searchBtn" style="margin-top: 10pt;" >

            </form>
                    <?php
                        print "<table class='table table-striped'><tr><td>#</td><td>Date</td><td>City</td><td>Temperature</td><td>FeelsLike</td><td>Humidity</td><td>Wind</td></tr>";
                        
                        $result = $sql.' ORDER BY date DESC LIMIT '.$start.', '.$per; 
                        if($stmt = mysqli_prepare($link,$result)){
                            mysqli_stmt_bind_param($stmt, "s", $param_username);
                            // Set parameters
                            $param_username = $_SESSION['username'];
                            if(mysqli_stmt_execute($stmt)){
                                if(mysqli_stmt_store_result($stmt) > 0){
                                    mysqli_stmt_bind_result($stmt, $city, $date, $description, $feel, $temp, $username, $wind, $icon, $humidity);
                                    $i=$start;
                                    while(mysqli_stmt_fetch($stmt)){
                                        print "<tr><td>".$i."</td><td>".$date."</td><td>".$city."</td><td>".$temp."℃</td><td>".$feel."</td><td>".$humidity."</td><td>".$wind."</td></tr>";
                                        $i = $i + 1;
                                    }
                                                            
                                }
                        
                            }
                        }
                        print "</table>";
                        //
                    ?>
                    <div align="center">
                    <?php
                       
                        if($page=='1'){
                            echo "<div class='btn btn-light'>首頁 </div>";
                            echo "<div class='btn btn-light'>上一頁 </div>";		
                        }else{
                            echo "<a class='btn btn-primary' href=?page=1>首頁 </a> ";
                            echo "<a class='btn btn-primary' href=?page=".($page-1).">上一頁 </a> ";		
                        }
                       
                        for($i=1 ; $i<=$pages ;$i++){ 
                            $lnum = 2;  //顯示左分頁數，直接修改就可增減顯示左頁數
                            $rnum = 2;  //顯示右分頁數，直接修改就可增減顯示右頁數
                        
                    
                            //判斷左(右)頁籤數是否足夠設定的分頁數，不夠就增加右(左)頁數，以保持總顯示分頁數目。
                            if($page <= $lnum){
                                $rnum = $rnum + ($lnum-$page+1);
                            }
                    
                            if($page+$rnum > $pages){
                                 $lnum = $lnum + ($rnum - ($pages-$page));
                            }
                    
                            //分頁部份處於該頁就不超連結,不是就連結送出$_GET['page']
                            if($page-$lnum <= $i && $i <= $page+$rnum){
                                  if($i==$page){echo " <div class='btn btn-light'> ".$i.' </div>';}else{echo '<a class="btn btn-primary" href=?page='.$i.'>'.$i.'</a> ';}
                            }
                        }
                    
                    
                        //在最後頁時,該頁就不超連結,可連結就送出$_GET['page']	
                        if($page==$pages){
                            echo " <div class='btn btn-light'> 下一頁 </div>";
                            echo " <div class='btn btn-light'> 末頁 </div>";	
                        }else{
                            echo "<a class='btn btn-primary' href=?page=".($page+1)."> 下一頁</a>";
                            echo "<a class='btn btn-primary' href=?page=".$pages."> 末頁</a>";		
                        }
                        echo "<div style="."margin:15px;".">".$per_total.' resultaten page '.$page.' of '.$pages."</div>"; 
                    ?>
                    </div>
                   
        </div>
    </div>

</body>

</html>