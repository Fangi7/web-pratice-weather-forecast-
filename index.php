<?php
    // check whether login or not
    if(!isset($_COOKIE["logged"]) || $_COOKIE["logged"] == false)
    {
        $logged_err = "PleaseLoginFirst";
    }
    $classCgange = 'inactive';
    session_start();
    $_SESSION["everpost"] = "";
    if($_SERVER["REQUEST_METHOD"] == "POST"){
    if($_POST["searchCity"]) $classCgange ="";
    $apiKey = "YOURAPI";
    $cityId = $_POST["searchCity"];
    $currentApiUrl = "http://api.openweathermap.org/data/2.5/weather?q=".$cityId."&appid=".$apiKey;
    $forecastApiUrl = "http://api.openweathermap.org/data/2.5/forecast?q=".$cityId."&appid=".$apiKey;
    $weatherJSON = file_get_contents($currentApiUrl);
    $weather = json_decode($weatherJSON,true);
    $weatherforecastJSON = file_get_contents($forecastApiUrl);
    $weatherforecast = json_decode($weatherforecastJSON,true);
    }
?>
<!DOCTYPE html>
<html lang="zh-tw">

<head>
    <meta charset="UTF-8">
    <title>HW3</title>

    <script src="sweetalert.js">
    <script src="main.js" defer></script>
    <link rel="stylesheet" href="main.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous"></script>

    <style> 
        table{
        margin:10px;
    }
    </style>
</head>

<body>
    
    <!-- main page -->
    <div id="main_page">
        <header>
            <div class="headerInfo">
                <input type="button" id="weatherPageBtn" value="Weather Page" name="weatherPageBtn" >
                <input type="button" id="logoutBtn" value="Logout" name="logoutBtn" onclick="window.location.href='logout.php'">
                <input type="button" id="historyBtn" value="History" name="historyBtn" onclick="window.location.href='history.php'">
                <span id="userName" style="color:white ">Hello,<?php  echo $_COOKIE["loginName"]; ?></span>
            </div>
        </header>

        <div class="container">
            <?php
                if(!empty($logged_err))
                {
                    echo '<div class="alert alert-danger">' . $logged_err . '</div>';
                    echo "<script>Swal.fire({
                    icon: 'warning',
                    title: 'Please login first',
                    text: 'You should login first'
                    }).then(function(){
                            window.location = 'login.php';
                        });
                    </script>";     
                }
                
            ?>
            <h1>The weather Now In your City</h1>
                <h2>Enter the Name of a City.</h2>
                <form <?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                    <input type="search" id="search" class="form-control me-2" style="margin:0" name="searchCity" placeholder="Chiayi,Minxiong" />
                    <input type="submit" id="searchBtn" value="Search" class="btn btn-outline-success" name="searchBtn" style="margin-top: 10pt;" onclick="">
                </form>
                <div class="<?php $classCgange ?>">
                    <div id="weatherResult">
                        <div>The weather in <?php echo $cityId ?> is currently <?php
                            echo $weather['weather'][0]['description'];
                            ?>.
                        </div>
                        <div>The temperature is <?php echo $weather['main']['temp']/10.0; ?>℃.</div>
                        <div>Humidith <?php echo $weather['main']['humidity'];?>.</div>
                        <div>Wind speed <?php echo $weather['wind']['speed']; ?>mph.</div>
                    </div>
                    <h2>The Weather Forecast</h2>
                    <?php

                        define('server', 'localhost');
                        define('username', 'root');
                        define('pwd', '');
                        define('name', 'hw3');
                        define('port','3306');
                        $link = mysqli_connect(server, username, pwd, name, port);
                        if($link == false)
                        {
                            die("ERROR: could not connect" . mysqli_connect_error());
                        }

                        
                        print "<table class='table'><tr><td></td><td>City</td><td>Date</td><td>Temperature</td><td>FeelsLike</td><td>Humidity</td><td>Description</td><td>Wind</td></tr>";
                        foreach($weatherforecast['list'] as $index => $value)
                        {  
                            if($index == 0 || $index == 8 || $index == 16 || $index == 24 || $index == 32)
                            {
                                print "<tr><td><img src=./weathericon/".$value['weather'][0]['icon'].".png style="."width=30px; height=30px;"."></img></td><td>".$cityId."</td><td>".substr($value['dt_txt'],0,-8)."</td><td>".$value['main']['temp']/10.0."℃</td>";
                                print "<td>".$value['main']['feels_like']/10.0."℃</td><td>".$value['main']['humidity']."</td><td>".$value['weather'][0]['description']."</td><td>".$value['wind']['speed']." m/s</td></tr>";
                                //insert database
                                $sql = "INSERT INTO weatherinfo (city, date,description,feel,humidity,temperature,username,wind,icon) VALUES (?,?,?,?,?,?,?,?,?)";
                                if($stmt = mysqli_prepare($link, $sql))
                                {
                                    mysqli_stmt_bind_param($stmt, "sssssssss", $param_city, $param_date, $param_des, $param_feel, $param_humi, $param_temper, $param_username, $param_wind, $param_icon);
                                    $param_city = $cityId;
                                    $param_date = substr($value['dt_txt'],0,-8);
                                    $param_des = $value['weather'][0]['description'];
                                    $param_feel = $value['main']['feels_like']/10.0;
                                    $param_humi = $value['main']['humidity'];
                                    $param_temper = $value['main']['temp']/10.0;
                                    $param_username = $_SESSION['username'];
                                    $param_wind = $value['wind']['speed'];
                                    $param_icon = $value['weather'][0]['icon'].".png";
                                }
                                mysqli_stmt_execute($stmt);
                            }
                        }
                        print "</table>";
                        
                        mysqli_close($link);
                    ?>
                </div>
            </div>
        </div>

    </body>

</html>