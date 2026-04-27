<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart HMS Auth</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{

height:100vh;
display:flex;
justify-content:center;
align-items:center;
overflow:hidden;

background:linear-gradient(-45deg,#00c6ff,#0072ff,#8e2de2,#4facfe);
background-size:400% 400%;

animation:gradientBG 10s ease infinite;

}

/* animated background */

@keyframes gradientBG{

0%{background-position:0% 50%;}
50%{background-position:100% 50%;}
100%{background-position:0% 50%;}

}

/* floating shapes */

.shape{

position:absolute;
background:rgba(255,255,255,0.15);
border-radius:50%;
animation:float 6s infinite ease-in-out;

}

.shape1{
width:200px;
height:200px;
top:10%;
left:10%;
}

.shape2{
width:150px;
height:150px;
bottom:15%;
right:10%;
animation-delay:2s;
}

.shape3{
width:100px;
height:100px;
top:70%;
left:40%;
animation-delay:4s;
}

@keyframes float{

0%{transform:translateY(0px);}
50%{transform:translateY(-30px);}
100%{transform:translateY(0px);}

}

/* glass card */

.auth-box{

width:420px;
padding:50px;
border-radius:20px;

background:rgba(255,255,255,0.15);
backdrop-filter:blur(15px);

box-shadow:0 15px 40px rgba(0,0,0,0.3);

text-align:center;
color:white;

animation:fade 1s ease;

}

@keyframes fade{

from{
opacity:0;
transform:translateY(40px);
}

to{
opacity:1;
transform:translateY(0);
}

}

.auth-box h2{

font-weight:700;
margin-bottom:10px;

}

.tag{

font-size:14px;
opacity:0.9;
margin-bottom:30px;

}

/* buttons */

.big-btn{

width:100%;
margin-bottom:15px;
padding:12px;

font-size:16px;
font-weight:600;

border-radius:10px;

transition:0.4s;

}

.big-btn i{
margin-right:8px;
}

/* hover effect */

.big-btn:hover{

transform:scale(1.07);
box-shadow:0 10px 25px rgba(0,0,0,0.3);

}

/* button colors */

.btn-primary{

background:linear-gradient(45deg,#007bff,#00c6ff);
border:none;

}

.btn-success{

background:linear-gradient(45deg,#00b09b,#96c93d);
border:none;

}

.btn-warning{

background:linear-gradient(45deg,#f7971e,#ffd200);
border:none;
color:black;

}

</style>

</head>

<body>

<div class="shape shape1"></div>
<div class="shape shape2"></div>
<div class="shape shape3"></div>

<div class="auth-box">

<h2><i class="fa-solid fa-hospital"></i> Smart HMS</h2>

<p class="tag">Powered Hospital Management System</p>

<div class="btn-area">

<a href="login.php" class="btn btn-primary big-btn">
<i class="fa-solid fa-right-to-bracket"></i>
Login
</a>

<a href="patient/register.php" class="btn btn-success big-btn">
<i class="fa-solid fa-user"></i>
Patient Register
</a>

<a href="doctor/register.php" class="btn btn-warning big-btn">
<i class="fa-solid fa-user-doctor"></i>
Doctor Register
</a>

</div>

</div>

</body>
</html>