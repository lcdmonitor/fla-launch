<?php
    $username=null;
    $password=null;

    if(isset($_POST['username']))
    {
        $username=$_POST['username'];
    }

    if(isset($_POST['password']))
    {
        $password=$_POST['password'];
    }

    if($username == "dave" and $password == "xyz123"){
            exit();
    }
    else{
        echo "invalid username/password";
        http_response_code(403);
    }
?>