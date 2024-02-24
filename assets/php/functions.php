<?php

use function PHPSTORM_META\type;

require_once 'config.php';
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die("database is not connected");


// function for showing pages
function showPage($page, $data = "")
{
    include("assets/pages/$page.php");
}

//function for show errors
function showError($field)
{
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        if (isset($error['field']) && $field == $error['field']) {
?>
            <div class="alert alert-danger my-2" role="alert">
                <?= $error['msg'] ?>
            </div>
<?php
        }
    }
}

// function for showing previous form data
function showFormData($field)
{
    if (isset($_SESSION['formdata'])) {
        $formdata = $_SESSION['formdata'];
        return $formdata["$field"];
    }
}

// for checking duplicate email
function isEmailRegistered($email)
{
    global $db;

    $query = "SELECT count(*) as row FROM users WHERE email='$email'";
    $run = mysqli_query($db, $query);
    $return_data = mysqli_fetch_assoc($run);
    return $return_data['row'];
}

//for checking duplicate username
function isUsernameRegistered($username)
{
    global $db;
    $query = "SELECT count(*) as row FROM users WHERE username='$username'";
    $run = mysqli_query($db, $query);
    $return_data = mysqli_fetch_assoc($run);
    return $return_data['row'];
}

//for checking duplicate username by other
function isUsernameRegisteredByOther($username)
{
    global $db;
    $user_id = $_SESSION['userdata']['id'];
    $query = "SELECT count(*) as row FROM users WHERE username='$username' && id!=$user_id";
    $run = mysqli_query($db, $query);
    $return_data = mysqli_fetch_assoc($run);
    return $return_data['row'];
}

// for validating the signup form
function validateSignupForm($form_data)
{
    $response = array();
    $response['status'] = true;
    if (!$form_data['password']) {
        $response['msg'] = "password is not given";
        $response['status'] = false;
        $response['field'] = 'password';
    }
    if (!$form_data['username']) {
        $response['msg'] = "username is not given";
        $response['status'] = false;
        $response['field'] = 'username';
    }
    if (!$form_data['email']) {
        $response['msg'] = "email is not given";
        $response['status'] = false;
        $response['field'] = 'email';
    }
    if (!$form_data['gender']) {
        $response['msg'] = "gender is not given";
        $response['status'] = false;
        $response['field'] = 'gender';
    }
    if (!$form_data['last_name']) {
        $response['msg'] = "last name is not given";
        $response['status'] = false;
        $response['field'] = 'last_name';
    }
    if (!$form_data['first_name']) {
        $response['msg'] = "First name is not given";
        $response['status'] = false;
        $response['field'] = 'first_name';
    }
    if (isEmailRegistered($form_data['email'])) {
        $response['msg'] = "email id is already registered";
        $response['status'] = false;
        $response['field'] = 'email';
    }
    if (isUsernameRegistered($form_data['username'])) {
        $response['msg'] = "username is already registered";
        $response['status'] = false;
        $response['field'] = 'username';
    }

    return $response;
}

//for validate the login form
function validateLoginForm($form_data)
{
    $response = array();
    $response['status'] = true;
    $blank = false;

    if (!$form_data['password']) {
        $response['msg'] = "password is not given";
        $response['status'] = false;
        $response['field'] = 'password';
        $blank = true;
    }

    if (!$form_data['username_email']) {
        $response['msg'] = "username/email is not given";
        $response['status'] = false;
        $response['field'] = 'username_email';
        $blank = true;
    }

    if (!$blank && !checkUser($form_data)['status']) {
        $response['msg'] = "something is incorrect, we can't find you";
        $response['status'] = false;
        $response['field'] = 'checkuser';
    } else {
        $response['user'] = checkUser($form_data)['user'];
    }

    return $response;
}

// for checking  the user
function checkUser($login_data)
{
    global $db;
    $username_email = $login_data['username_email'];
    $password = md5($login_data['password']);

    $query = "SELECT * FROM users WHERE (email='$username_email' || username='$username_email') && password='$password'";
    $run = mysqli_query($db, $query);
    $data['user'] = mysqli_fetch_assoc($run) ?? array();
    if (count($data['user']) > 0) {
        $data['status'] = true;
    } else {
        $data['status'] = false;
    }

    return $data;
}

// for getting userdata by id
function getUser($user_id)
{
    global $db;

    $query = "SELECT * FROM users WHERE id=$user_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run);
}

//for creating new user
function createUser($data)
{
    global $db;
    $first_name = mysqli_real_escape_string($db, $data['first_name']);
    $last_name = mysqli_real_escape_string($db, $data['last_name']);
    $gender = $data['gender'];
    $email = mysqli_real_escape_string($db, $data['email']);
    $username = mysqli_real_escape_string($db, $data['username']);
    $password = mysqli_real_escape_string($db, $data['password']);
    $password = md5($password);

    $query = "INSERT INTO users(first_name,last_name,gender,email,username,password) ";
    $query .= "VALUES ('$first_name','$last_name',$gender,'$email','$username','$password')";
    return mysqli_query($db, $query);
}

// function for verify email
function verifyEmail($email)
{
    global $db;
    $query = "UPDATE users SET ac_status=1 WHERE email='$email'";
    return mysqli_query($db, $query);
}

// function for verify email
function resetPassword($email, $password)
{
    global $db;
    $password = md5($password);
    $query = "UPDATE users SET password='$password'  WHERE email='$email'";
    return mysqli_query($db, $query);
}

// for validating update form
function validateUpdateForm($form_data, $image_data)
{
    $response = array();
    $response['status'] = true;
    if (!$form_data['username']) {
        $response['msg'] = "username is not given";
        $response['status'] = false;
        $response['field'] = 'username';
    }
    if (!$form_data['last_name']) {
        $response['msg'] = "Last name is not given";
        $response['status'] = false;
        $response['field'] = 'last_name';
    }
    if (!$form_data['first_name']) {
        $response['msg'] = "First name is not given";
        $response['status'] = false;
        $response['field'] = 'first_name';
    }
    if (isUsernameRegisteredByOther($form_data['username'])) {
        $response['msg'] = $form_data['username'] . " is already registered";
        $response['status'] = false;
        $response['field'] = 'username';
    }

    if ($image_data['name']) {
        $image = basename($image_data['name']);
        $type = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $size = $image_data['size'] / 1000;

        if ($type !== 'jpg' && $type != 'jpeg' && $type != 'png') {
            $response['msg'] = "Only jpg, jpeg, png images file type is allowed ";
            $response['status'] = false;
            $response['field'] = 'profile_pic';
        }
        if ($size > 2000) {
            $response['msg'] = "Upload image less than 2 mb";
            $response['status'] = false;
            $response['field'] = 'profile_pic';
        }
    }

    return $response;
}

// for updating user profile
function updateProfile($data, $imagedata)
{
    global $db;

    $first_name = mysqli_real_escape_string($db, $data['first_name']);
    $last_name = mysqli_real_escape_string($db, $data['last_name']);
    $username = mysqli_real_escape_string($db, $data['username']);

    // Check if the password is provided and not blank
    if (isset($data['password']) && $data['password'] !== "") {
        $password = md5(mysqli_real_escape_string($db, $data['password']));
        $_SESSION['userdata']['password'] = $password;
        $password_update = ", password='$password'";
    } else {
        // If password is not provided or is blank, do not update the password
        $password_update = "";
    }

    $profile_pic = "";
    if ($imagedata['name']) {
        $image_name = time() . basename($imagedata['name']);
        $image_dir = "../images/profile/$image_name";
        move_uploaded_file($imagedata['tmp_name'], $image_dir);
        $profile_pic = ", profile_pic='$image_name'";
    }

    $query = "UPDATE users SET first_name='$first_name', last_name='$last_name', username='$username'$password_update$profile_pic WHERE id={$_SESSION['userdata']['id']}";

    return mysqli_query($db, $query);
}

// for validating add post form
function validatePostImage($image_data)
{
    $response = array();
    $response['status'] = true;

    if (empty($image_data['name'])) {
        // No image is selected, but it's not an error
        return $response;
    }

    // if (!$image_data['name']) {
    //     $response['msg'] = "No image is selected";
    //     $response['status'] = false;
    //     $response['field'] = 'post_img';
    // }

    if ($image_data['name']) {
        $image = basename($image_data['name']);
        $type = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $size = $image_data['size'] / 1000;

        if ($type !== 'jpg' && $type != 'jpeg' && $type != 'png') {
            $response['msg'] = "Only jpg, jpeg, png images file type is allowed ";
            $response['status'] = false;
            $response['field'] = 'post_img';
        }
        if ($size > 2000) {
            $response['msg'] = "Upload image less than 2 mb";
            $response['status'] = false;
            $response['field'] = 'post_img';
        }
    }

    return $response;
}

// for creating a new post 
function createPost($text, $image)
{
    global $db;

    $post_text = mysqli_real_escape_string($db, $text['post_text']);
    $user_id = $_SESSION['userdata']['id'];

    // Check if both post text and image are empty
    if (empty($post_text) && empty($image['name'])) {
        return [
            'msg' => 'Please enter either text or upload an image.',
            'status' => false,
            'field' => 'post_img'
        ];
    }

    $image_name = "";
    if (!empty($image['name'])) {
        $image_name = time() . basename($image['name']);
        $image_dir = "../images/posts/$image_name";
        if (!move_uploaded_file($image['tmp_name'], $image_dir)) {
            return [
                'msg' => 'Error uploading image.',
                'status' => false,
                'field' => 'post_img'
            ];
        }
    }

    $query = "INSERT INTO posts (user_id, post_text, post_img) VALUES ($user_id, '$post_text', '$image_name')";
    if (mysqli_query($db, $query)) {
        return true;
    } else {
        return [
            'msg' => 'Error adding new post to the database.',
            'status' => false,
            'field' => 'post_img'
        ];
    }
}


// for getting posts
function getPost()
{
    global $db;

    $query = "SELECT users.id as uid,posts.id,posts.user_id,posts.post_img,posts.post_text,posts.created_at,users.first_name,users.last_name,users.username,users.profile_pic FROM posts JOIN users ON users.id=posts.user_id ORDER BY id DESC";

    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

// for getting user by username (showing of user profile)
function getUserByUsername($username)
{
    global $db;

    $query = "SELECT * FROM users WHERE username='$username'";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run);
}

// for getting posts by id(for user profile)
function getPostbyId($user_id)
{
    global $db;
    $query = "SELECT * FROM posts WHERE user_id=$user_id ORDER BY id DESC";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

// for filtering follower suggestion(on home screen)
function filterFollowerSuggestion()
{
    $list = getFollowSuggestions();
    $filter_list = array();
    foreach ($list as $user) {
        if (!checkFollowStatus($user['id']) && count($filter_list) < 5) {
            $filter_list[] = $user;
        }
    }

    return $filter_list;
}

// for checking the user is followed by current user or not
function checkFollowStatus($user_id)
{
    global $db;
    $current_user = $_SESSION['userdata']['id'];
    $query = "SELECT count(*) as row FROM follow_list WHERE follower_id=$current_user && user_id=$user_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}

//for getting users for follow suggestion (on home scrren)
function getFollowSuggestions()
{
    global $db;

    $current_user = $_SESSION['userdata']['id'];
    $query = "SELECT * FROM users WHERE id!=$current_user";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

// function for follow the user
function followUser($user_id)
{
    global $db;
    $cu = getUser($_SESSION['userdata']['id']);
    $current_user = $_SESSION['userdata']['id'];
    $query = "INSERT INTO follow_list(follower_id,user_id) VALUES($current_user,$user_id)";

    createNotification($cu['id'], $user_id, "started following you !");
    return mysqli_query($db, $query);
}

// for getting posts of mine followers(dynamically)
function filterPosts()
{
    $list = getPost();
    $filter_list = array();
    foreach ($list as $post) {
        if (checkFollowStatus($post['user_id']) || $post['user_id'] == $_SESSION['userdata']['id']) {
            $filter_list[] = $post;
        }
    }

    return $filter_list;
}

// get followers count(for profile page)
function getFollowers($user_id)
{
    global $db;
    $query = "SELECT * FROM follow_list WHERE user_id=$user_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

// get followers count(for profile page)
function getFollowing($user_id)
{
    global $db;
    $query = "SELECT * FROM follow_list WHERE follower_id=$user_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

// for unfollow the user
function unfollowUser($user_id)
{
    global $db;
    $current_user = $_SESSION['userdata']['id'];
    $query = "DELETE FROM follow_list WHERE follower_id=$current_user && user_id=$user_id";

    createNotification($current_user, $user_id, "Unfollowed you !");
    return mysqli_query($db, $query);
}

// function for checking like status of post
function checkLikeStatus($post_id)
{
    global $db;
    $current_user = $_SESSION['userdata']['id'];
    $query = "SELECT count(*) as row FROM likes WHERE user_id=$current_user && post_id=$post_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}

// for like the post
function like($post_id)
{
    global $db;
    $current_user = $_SESSION['userdata']['id'];
    $query = "INSERT INTO likes(post_id,user_id) VALUES($post_id,$current_user)";
    $poster_id = getPosterId($post_id);

    if ($poster_id != $current_user) {
        createNotification($current_user, $poster_id, "liked your post !", $post_id);
    }


    return mysqli_query($db, $query);
}

// for unlike the post
function unlike($post_id)
{
    global $db;
    $current_user = $_SESSION['userdata']['id'];
    $query = "DELETE FROM likes WHERE user_id=$current_user && post_id=$post_id";

    $poster_id = getPosterId($post_id);
    if ($poster_id != $current_user) {
        createNotification($current_user, $poster_id, "unliked your post !", $post_id);
    }

    return mysqli_query($db, $query);
}

// for count the likes on a post
function getLikes($post_id)
{
    global $db;
    $query = "SELECT * FROM likes WHERE post_id=$post_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

// for add comments on posts
function addComment($post_id, $comment)
{
    global $db;
    $comment = mysqli_real_escape_string($db, $comment);
    $current_user = $_SESSION['userdata']['id'];
    $query = "INSERT INTO comments(user_id, post_id,comment) VALUES($current_user, $post_id, '$comment')";
    $poster_id = getPosterId($post_id);

    if ($poster_id != $current_user) {
        createNotification($current_user, $poster_id, "commented on your post", $post_id);
    }


    return mysqli_query($db, $query);
}

// for getting the comments 
function getComment($post_id)
{
    global $db;
    $query = "SELECT * FROM comments WHERE post_id=$post_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

//function for blocking the user
function blockUser($blocked_user_id)
{
    global $db;
    $cu = getUser($_SESSION['userdata']['id']);
    $current_user = $_SESSION['userdata']['id'];
    $query = "INSERT INTO block_list(user_id,blocked_user_id) VALUES($current_user,$blocked_user_id)";

    createNotification($cu['id'], $blocked_user_id, "blocked you");
    $query2 = "DELETE FROM follow_list WHERE follower_id=$current_user && user_id=$blocked_user_id";
    mysqli_query($db, $query2);
    $query3 = "DELETE FROM follow_list WHERE follower_id=$blocked_user_id && user_id=$current_user";
    mysqli_query($db, $query3);

    return mysqli_query($db, $query);
}

//for checking the user is followed by current user or not
function checkBlockStatus($current_user, $user_id)
{
    global $db;

    $query = "SELECT count(*) as row FROM block_list WHERE user_id=$current_user && blocked_user_id=$user_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}

function checkBS($user_id)
{
    global $db;
    $current_user = $_SESSION['userdata']['id'];
    $query = "SELECT count(*) as row FROM block_list WHERE (user_id=$current_user && blocked_user_id=$user_id) || (user_id=$user_id && blocked_user_id=$current_user)";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}

// for unblocking the user

function unblockUser($user_id)
{
    global $db;
    $current_user = $_SESSION['userdata']['id'];
    $query = "DELETE FROM block_list WHERE user_id=$current_user && blocked_user_id=$user_id";
    createNotification($current_user, $user_id, "Unblocked you !");
    return mysqli_query($db, $query);
}

//for searching the users
function searchUser($keyword)
{
    global $db;
    $query = "SELECT * FROM users WHERE username LIKE '%" . $keyword . "%' || (first_name LIKE '%" . $keyword . "%' || last_name LIKE '%" . $keyword . "%') LIMIT 5";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

//get notifications
function getNotifications()
{
    $cu_user_id = $_SESSION['userdata']['id'];

    global $db;
    $query = "SELECT * FROM notifications WHERE to_user_id=$cu_user_id ORDER BY id DESC";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, true);
}

function getUnreadNotificationsCount()
{
    $cu_user_id = $_SESSION['userdata']['id'];

    global $db;
    $query = "SELECT count(*) as row FROM notifications WHERE to_user_id=$cu_user_id && read_status=0 ORDER BY id DESC";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}

function show_time($time)
{
    return '<time style="font-size:small" class="timeago text-muted text-small" datetime="' . $time . '"></time>';
}

function setNotificationStatusAsRead()
{
    $cu_user_id = $_SESSION['userdata']['id'];
    global $db;
    $query = "UPDATE notifications SET read_status=1 WHERE to_user_id=$cu_user_id";
    return mysqli_query($db, $query);
}

//for getting post
function getPosterId($post_id)
{
    global $db;
    $query = "SELECT user_id FROM posts WHERE id=$post_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['user_id'];
}

//function for creating notifications
function createNotification($from_user_id, $to_user_id, $msg, $post_id = 0)
{
    global $db;
    $query = "INSERT INTO notifications(from_user_id,to_user_id,message,post_id) VALUES($from_user_id,$to_user_id,'$msg',$post_id)";
    mysqli_query($db, $query);
}

// for getting time
function gettime($date)
{
    return date('H:i - (F jS, Y )', strtotime($date));
}

// for deleting the post
function deletePost($post_id)
{
    global $db;
    $user_id = $_SESSION['userdata']['id'];
    $dellike = "DELETE FROM likes WHERE post_id=$post_id && user_id=$user_id";
    mysqli_query($db, $dellike);
    $delcom = "DELETE FROM comments WHERE post_id=$post_id && user_id=$user_id";
    mysqli_query($db, $delcom);
    $not = "UPDATE notifications SET read_status=2 WHERE post_id=$post_id && to_user_id=$user_id";
    mysqli_query($db, $not);


    $query = "DELETE FROM posts WHERE id=$post_id";
    return mysqli_query($db, $query);
}

// for getting ids of chats users
function getActivateChatUserIds()
{
    global $db;
    $current_user_id = $_SESSION['userdata']['id'];

    $query = "SELECT from_user_id,to_user_id FROM messages WHERE to_user_id=$current_user_id || from_user_id=$current_user_id ORDER BY id DESC";
    $run = mysqli_query($db, $query);
    $data = mysqli_fetch_all($run, true);
    $ids = array();
    foreach ($data as $ch) {
        if ($ch['from_user_id'] != $current_user_id && !in_array($ch['from_user_id'], $ids)) {
            $ids[] = $ch['from_user_id'];
        }

        if ($ch['to_user_id'] != $current_user_id && !in_array($ch['to_user_id'], $ids)) {
            $ids[] = $ch['to_user_id'];
        }
    }

    return $ids;
}

// for getting meassges
function getMessages($user_id)
{
    global $db;
    $current_user_id = $_SESSION['userdata']['id'];
    $query = "SELECT * FROM messages WHERE (to_user_id=$current_user_id && from_user_id=$user_id) || (from_user_id=$current_user_id && to_user_id=$user_id) ORDER BY id DESC";
    $run = mysqli_query($db, $query);
    return  mysqli_fetch_all($run, true);
}

function getAllMessages()
{
    $active_chat_ids = getActivateChatUserIds();
    $conversation = array();
    foreach ($active_chat_ids as $index => $id) {
        $conversation[$index]['user_id'] = $id;
        $conversation[$index]['messages'] = getmessages($id);
    }
    return $conversation;
}

// for sending the messages
function sendMessage($user_id, $msg)
{
    global $db;
    $current_user_id = $_SESSION['userdata']['id'];
    $query = "INSERT INTO messages (from_user_id,to_user_id,msg)VALUES($current_user_id,$user_id,'$msg') ";
    updateMessageReadStatus($user_id);
    $return = mysqli_query($db, $query);
    return $return ? true : false;
}

// 
function updateMessageReadStatus($user_id)
{
    $cu_user_id = $_SESSION['userdata']['id'];
    global $db;
    $query = "UPDATE messages SET read_status=1 WHERE to_user_id=$cu_user_id && from_user_id=$user_id";
    return mysqli_query($db, $query);
}


// function to count all new msg and show in notfication
function newMsgCount()
{
    global $db;
    $current_user_id = $_SESSION['userdata']['id'];
    $query = "SELECT COUNT(*) as row FROM messages WHERE to_user_id=$current_user_id && read_status=0";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}


?>