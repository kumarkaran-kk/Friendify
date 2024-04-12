<?php
require_once 'functions.php';
require_once 'send_code.php';

// for managaing signup
if (isset($_GET['signup'])) {
    $response = validateSignupForm($_POST);
    if ($response['status']) {
        if (createUser($_POST)) {
            header('location:../../?login&newuser=1');
        } else {
            echo "<script>alert('Something went wrong')</script>";
        }
    } else {
        $_SESSION['error'] = $response;
        $_SESSION['formdata'] = $_POST;
        header("location:../../?signup");
    }
}


// for managing login
if (isset($_GET['login'])) {
    $response = validateLoginForm($_POST);

    if ($response['status']) {
        $_SESSION['Auth'] = true;
        $_SESSION['userdata'] = $response['user'];

        if ($response['user']['ac_status'] == 0) {
            $_SESSION['code'] = $code = rand(111111, 999999);
            sendCode($response['user']['email'], 'Verify Your Email', $code);
        }

        header("location:../../");
    } else {
        $_SESSION['error'] = $response;
        $_SESSION['formdata'] = $_POST;
        header("location:../../?login");
    }
}

if (isset($_GET['resend_code'])) {
    $_SESSION['code'] = $code = rand(111111, 999999);
    sendCode($_SESSION['userdata']['email'], 'Verify Your Email', $code);
    header('location:../../?resended');
}

if (isset($_GET['verify_email'])) {
    $user_code = $_POST['code'];
    $code = $_SESSION['code'];
    if ($code == $user_code) {
        if (verifyEmail($_SESSION['userdata']['email'])) {
            header('location:../../');
        } else {
            echo "Something went wrong while verifying email.";
        }
    } else {
        $response['msg'] = 'Incorrect Verification Code!';
        if (!$_POST['code']) {
            $response['msg'] = 'Enter a 6-digit code!';
        }
        $response['field'] = 'email_verify';
        $_SESSION['error'] = $response;
        header('location:../../');
    }
}

// for forgot password 
if (isset($_GET['forgotpassword'])) {
    if (!$_POST['email']) {
        $response['msg'] = "Enter your email id !";
        $response['field'] = 'email';
        $_SESSION['error'] = $response;
        header('location:../../?forgotpassword');
    } elseif (!isEmailRegistered($_POST['email'])) {
        $response['msg'] = "Email id is not registered";
        $response['field'] = 'email';
        $_SESSION['error'] = $response;
        header('location:../../?forgotpassword');
    } else {
        $_SESSION['forgot_email'] = $_POST['email'];
        $_SESSION['forgot_code'] = $code = rand(111111, 999999);
        sendCode($_POST['email'], 'Forgot Your Password ?', $code);
        header('location:../../?forgotpassword&resended');
    }
}

// for logout the user
if (isset($_GET['logout'])) {
    session_destroy();
    header('location:http://localhost/PICTOGRAM/');
}

// for verify forgot code
if (isset($_GET['verifycode'])) {
    $user_code = $_POST['code'];
    $code = $_SESSION['forgot_code'];
    if ($code == $user_code) {
        $_SESSION['auth_temp'] = true;
        header('location:../../?forgotpassword');
    } else {
        $response['msg'] = 'Incorrect verifictaion code !';
        if (!$_POST['code']) {
            $response['msg'] = 'Enter 6 digit code !';
        }
        $response['field'] = 'email_verify';
        $_SESSION['error'] = $response;
        header('location:../../?forgotpassword');
    }
}

if (isset($_GET['changepassword'])) {
    if (!$_POST['password']) {
        $response['msg'] = "Enter your new passsword";
        $response['field'] = 'password';
        $_SESSION['error'] = $response;
        header('location:../../?forgotpassword');
    } else {
        resetpassword($_SESSION['forgot_email'], $_POST['password']);
        header('location:../../?reseted');
    }
}

// for updating the user info
if (isset($_GET['updateprofile'])) {
    $response = validateUpdateForm($_POST, $_FILES['profile_pic']);

    if ($response['status']) {
        if (updateProfile($_POST, $_FILES['profile_pic'])) {
            header("location:../../?editprofile&success");
            exit; // Ensure that no further code is executed after the redirect
        } else {
            echo "Something went wrong during profile update.";
        }
    } else {
        $_SESSION['error'] = $response;
        header("location:../../?editprofile");
        exit;
    }
}

// for managing add post
if (isset($_GET['addpost'])) {
    $response = validatePostImage($_FILES['post_img']);

    if ($response['status']) {
        $result = createPost($_POST, $_FILES['post_img']);
        if ($result === true) {
            header("location:../../?new_post_added");
        } else {
            $_SESSION['error'] = $result;
            header("location:../../");
        }
    } else {
        $_SESSION['error'] = $response;
        header("location:../../");
    }
}


// for blocking the user
if (isset($_GET['block'])) {
    $user_id = $_GET['block'];
    $user = $_GET['username'];
    if (blockUser($user_id)) {
        header("location:../../?u=$user");
    } else {
        echo "something went wrong";
    }
}

// for deleting the post
if (isset($_GET['deletepost'])) {
    $post_id = $_GET['deletepost'];
    if (deletePost($post_id)) {
        header("location:{$_SERVER['HTTP_REFERER']}");
    } else {
        echo "something went wrong";
    }
}
