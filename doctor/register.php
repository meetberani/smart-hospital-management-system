<?php
session_start();
include("../config/db.php");

$msg="";

if(isset($_POST['register'])){

$name=mysqli_real_escape_string($conn,$_POST['name']);
$email=mysqli_real_escape_string($conn,$_POST['email']);
$password=password_hash($_POST['password'],PASSWORD_DEFAULT);
$specialization=mysqli_real_escape_string($conn,$_POST['specialization']);
$experience=mysqli_real_escape_string($conn,$_POST['experience']);

$check=mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($check)>0){
$msg="❌ Email already registered!";
}else{

mysqli_query($conn,"INSERT INTO users(name,email,password,role,status)
VALUES('$name','$email','$password','doctor','pending')");

$uid=mysqli_insert_id($conn);

mysqli_query($conn,"INSERT INTO doctors(user_id,specialization,experience)
VALUES('$uid','$specialization','$experience')");

$msg="✅ Registration successful! Wait for admin approval.";

}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Doctor Registration</title>

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

background:linear-gradient(-45deg,#fc466b,#3f5efb,#00c6ff,#38ef7d);
background-size:400% 400%;

animation:gradientBG 10s ease infinite;

}

@keyframes gradientBG{

0%{background-position:0% 50%;}
50%{background-position:100% 50%;}
100%{background-position:0% 50%;}

}

/* floating circles */

.circle{

position:absolute;
background:rgba(255,255,255,0.15);
border-radius:50%;
animation:float 7s infinite ease-in-out;

}

.c1{width:200px;height:200px;top:10%;left:10%;}
.c2{width:150px;height:150px;bottom:15%;right:10%;animation-delay:2s;}
.c3{width:100px;height:100px;top:70%;left:40%;animation-delay:4s;}

@keyframes float{
0%{transform:translateY(0);}
50%{transform:translateY(-30px);}
100%{transform:translateY(0);}
}

/* glass card */

.register-box{

width:420px;
padding:40px;
border-radius:20px;

background:rgba(255,255,255,0.15);
backdrop-filter:blur(15px);

box-shadow:0 15px 40px rgba(0,0,0,0.3);

text-align:center;
color:white;

animation:fade 1s ease;

}

@keyframes fade{

from{opacity:0;transform:translateY(40px);}
to{opacity:1;transform:translateY(0);}

}

.register-box h2{

margin-bottom:25px;
font-weight:600;

}

/* input group */

.input-group{

margin-bottom:15px;

}

.input-group-text{

background:#ff6a00;
color:white;
border:none;

}

.form-control{

border-radius:0 10px 10px 0;
padding:10px;
transition:0.3s;

}

.form-control:focus{

box-shadow:0 0 12px #ffd200;
transform:scale(1.02);

}

/* button */

.register-btn{

width:100%;
padding:12px;

border:none;
border-radius:10px;

background:linear-gradient(45deg,#ff6a00,#ee0979);

color:white;
font-weight:bold;

transition:0.4s;

}

.register-btn:hover{

transform:scale(1.05);
box-shadow:0 0 15px #ff9a00;

}

/* back */

.back{

display:block;
margin-top:15px;
color:white;
text-decoration:none;

}

.back:hover{
text-decoration:underline;
}

</style>

</head>

<body>

<div class="circle c1"></div>
<div class="circle c2"></div>
<div class="circle c3"></div>

<div class="register-box">

<h2><i class="fa-solid fa-user-doctor"></i> Doctor Registration</h2>

<?php if($msg!=""){ ?>
<div class="alert alert-light text-dark"><?php echo $msg; ?></div>
<?php } ?>

<form method="post">

<div class="input-group">
<span class="input-group-text"><i class="fa fa-user"></i></span>
<input type="text" class="form-control" name="name" placeholder="Full Name" required>
</div>

<div class="input-group">
<span class="input-group-text"><i class="fa fa-envelope"></i></span>
<input type="email" class="form-control" name="email" placeholder="Email" required>
</div>

<div class="input-group">
<span class="input-group-text"><i class="fa fa-lock"></i></span>
<input type="password" class="form-control" name="password" placeholder="Password" required>
</div>

<div class="input-group">
<span class="input-group-text"><i class="fa fa-stethoscope"></i></span>
<input type="text" class="form-control" name="specialization" placeholder="Specialization" required>
</div>

<div class="input-group">
<span class="input-group-text"><i class="fa fa-briefcase"></i></span>
<input type="number" class="form-control" name="experience" placeholder="Experience (Years)" required>
</div>

<button class="register-btn" name="register">
<i class="fa fa-user-md"></i> Register
</button>

</form>

<a class="back" href="../auth.php">⬅ Back</a>

</div>

</body>
</html>