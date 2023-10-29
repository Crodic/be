<?php
include_once "../../../utils/Common.php";
include_once "../../../configs/DBContext.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header('HTTP/1.1 204 No Content');
    exit;
}

$common = new Common();
$conn = new DBContext();
$conn = $conn->Connection();
if (!isset($_GET["action"])) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "statusCode" => 404,
        "msg" => "Thiếu Tham Số action",
    ]);
    exit;
}

$action = $_GET["action"];


switch ($action) {
    case "register":
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $firstname = $_POST["firstname"];
            $lastname = $_POST["lastname"];
            $password = $_POST["password"];
            $email = $_POST["email"];

            if (!isset($firstname) || !isset($lastname) || !isset($email) || !isset($password)) {

                http_response_code(400);
                echo json_encode(["status" => false, "statusCode" => 400, "msg" => "Thiếu Các Thông Tin Cần Thiết."]);
                exit;
            }

            $fullname = "$firstname $lastname";
            $timestamp = time(); // Lấy giá trị thời gian hiện tại dưới dạng số giây
            $currentTime = date('Y-m-d H:i:s', $timestamp); // Chuyển đổi thành định dạng datetime
            $query = "INSERT INTO User VALUES(null, :fullname, :email, :password, :phone_number, :address, 1, :createdAt, :updatedAt, 0)";
            $param = array(
                "fullname" => $fullname,
                "email" => $email,
                "password" => md5($password),
                "phone_number" => null,
                "address" => null,
                "createdAt" => $currentTime,
                "updatedAt" => $currentTime,
            );
            try {
                $pstm = $conn->prepare($query);
                $pstm->execute($param);
                $count = $pstm->rowCount();
                if ($count > 0) {
                    http_response_code(201);
                    echo json_encode([
                        "status" => true,
                        "statusCode" => 201,
                        "msg" => "Đăng Ký Tài Khoảng $email thành công !!",
                    ]);
                }
            } catch (\Throwable $th) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 400,
                    "msg" => "Thông Tin Không Hợp Lệ Vui Lòng Thử Lại Hoặc Đã Tồn Tại.",
                    "error" => $th->getMessage(),
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không Thể Tìm Thấy API tương ứng",
            ]);
        }
        break;
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $password = $_POST["password"];
            if (!isset($email) || !isset($password)) {
                http_response_code(400);
                echo json_encode(["status" => false, "statusCode" => 400, "msg" => "Thiếu Các Thông Tin Cần Thiết."]);
                exit;
            }
            try {

                $pstm = $conn->prepare("SELECT * FROM User JOIN ROLE ON User.rid = Role.rid WHERE email = :email AND password = :password");
                $params = array(
                    "email" => $email,
                    "password" => md5($password),
                );
                $pstm->execute($params);
                $count = $pstm->rowCount();
                if ($count > 0) {
                    $result = $pstm->fetch(PDO::FETCH_ASSOC);
                    $data = [
                        "uid" => $result["uid"],
                        "rid" => $result["name"],
                        "exp" => time() + (1 * 30),
                        "iat" => time(),
                    ];
                    $data2 = [
                        "uid" => $result["uid"],
                        "rid" => $result["name"],
                        "exp" => time() + (24 * 60 * 60),
                        "iat" => time(),
                    ];
                    $token = $common->createToken($data);
                    $token2 = $common->createToken($data2);
                    http_response_code(200);
                    echo json_encode([
                        "status" => true,
                        "statusCode" => 200,
                        "msg" => "Đăng Nhập Thành Công",
                        "user" => [
                            "uid" => $result["uid"],
                            "fullname" => $result["fullname"],
                            "role" => $result["name"],
                            "email" => $result["email"],
                        ],
                        "accessToken" => $token,
                        "refreshToken" => $token2
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 404,
                        "msg" => "Đăng Nhập Thất Bại",
                    ]);
                }
            } catch (\Throwable $th) {
                http_response_code(500);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 500,
                    "msg" => "Hệ Thống Gặp Sự Cố !!!",
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không Thể Tìm Thấy API tương ứng",
            ]);
        }
        break;

    case "logout":
        $common->setCookies("token", "", time() - 3600);
        http_response_code(200);
        echo json_encode([
            "status" => true,
            "statusCode" => 200,
            "msg" => "Đăng Xuất Thành Công",
        ]);
        break;
    case "token":
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $token = $common->getBearerToken();
            try {
                if ($token) {
                    $decodeToken = $common->verifyToken($token);
                    if ($decodeToken) {
                        $decodeToken = json_decode($decodeToken, true);
                        $data = [
                            "uid" => $decodeToken['uid'],
                            "rid" => $decodeToken['rid'],
                            "exp" => time() + (15 * 60),
                            "iat" => time(),
                        ];
                        $data2 = [
                            "uid" => $decodeToken["uid"],
                            "rid" => $decodeToken["rid"],
                            "exp" => time() + (24 * 60 * 60),
                            "iat" => time(),
                        ];
                        $token = $common->createToken($data);
                        $token2 = $common->createToken($data2);
                        $common->setCookies("token", $token, time() + 30 * 60 * 1000);
                        http_response_code(200);
                        echo json_encode([
                            "status" => true,
                            "statusCode" => 200,
                            "msg" => "Tạo Token Thành Công!!!",
                            "accessToken" => $token,
                            "refreshToken" => $token2,
                        ]);
                    } else {
                        http_response_code(401);
                        echo json_encode(["status" => false, "statusCode" => 401, "msg" => "Token Đã Hết Hiệu Lực. Vui Lòng Đăng Nhập Lại Phiên"]);
                    }
                } else if ($token === -1) {
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 401,
                        "msg" => "Không có token trong yêu cầu.",
                    ]);
                } else {
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 403,
                        "msg" => "Không tìm thấy token trong Authorization header.",
                    ]);
                }
            } catch (\Throwable $th) {
                echo json_encode([
                    "status" => false,
                    "statusCode" => 500,
                    "msg" => "Lỗi Hệ Thống !!!",
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không Thể Tìm Thấy API tương ứng",
            ]);
        }
        break;
    case "getuser":
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $token = $common->getBearerToken();
            if ($token && $token != -1) {
                try {
                    $tokenPayload = $common->verifyToken($token);
                    if ($tokenPayload != null) {
                        $decodeToken = json_decode($tokenPayload, true);
                        $uid = $decodeToken["uid"];
                        $params = array("uid" => $uid);
                        $sql = "SELECT * FROM User JOIN ROLE ON User.rid = Role.rid WHERE User.uid = :uid;";
                        $pstm = $conn->prepare($sql);
                        $pstm->execute($params);
                        if ($pstm->rowCount() > 0) {
                            $result = $pstm->fetch(PDO::FETCH_ASSOC);
                            http_response_code(200);
                            echo json_encode([
                                "status" => true,
                                "statusCode" => 200,
                                "msg" => "Lấy Thông Tin Người Dùng Thành Công",
                                "user" => ["uid" => $result["uid"], "email" => $result["email"], "fullname" => $result["fullname"], "role" => $result["name"], "phone" => $result["phone_number"], "address" => $result["address"], "isDeleted" => $result["isDeleted"]]
                            ]);
                        } else {
                            http_response_code(404);
                            echo json_encode([
                                "status" => false,
                                "statusCode" => 404,
                                "msg" => "Không tìm thấy user"
                            ]);
                        }
                    } else {
                        http_response_code(403);
                        echo json_encode([
                            "status" => false,
                            "statusCode" => 403,
                            "msg" => "Token Hết Hạn",
                        ]);
                    }
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 500,
                        "msg" => "Sever lỗi!!!",
                    ]);
                }
            } else {
                http_response_code(403);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 403,
                    "msg" => "Bạn Không Đủ Quyền Để Truy Cập Vào Chức Năng Này.",
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không Thể Tìm Thấy API tương ứng",
            ]);
        }
        break;

    case "all":
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $token = $common->getBearerToken();

            if ($token && $token != -1) {
                $tokenPayload = $common->verifyToken($token);

                if ($tokenPayload) {
                    $decodeToken = json_decode($tokenPayload, true);
                    $userRole = $decodeToken["rid"];

                    if ($userRole === "admin") {

                        $page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
                        $limit = isset($_GET["limit"]) ? intval($_GET["limit"]) : 10;
                        if ($page <= 0) {
                            $page = 1;
                        }
                        if ($limit <= 0) {
                            $limit = 10;
                        }
                        $skip = ($page - 1) * $limit;
                        $sql = "SELECT u.uid, u.fullname, u.email, u.phone_number,u.address,u.createdAt,
                                        u.updatedAt,u.isDeleted,r.name
                                         AS role
                                        FROM user u
                                        JOIN role r ON u.rid = r.rid
                                        LIMIT :skip, :limit;";
                        try {
                            $pstm = $conn->prepare($sql);
                            $pstm->bindValue(':skip', $skip, PDO::PARAM_INT);
                            $pstm->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $pstm->execute();
                            $results = $pstm->fetchAll(PDO::FETCH_ASSOC);
                            $total = 0;
                            if ($pstm->rowCount() > 0) {
                                $pstm = $conn->prepare("SELECT * FROM user");
                                $pstm->execute();
                                $users = $pstm->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($users as $user) {
                                    $total++;
                                }
                                $totalPages = ceil($total / $limit);
                                http_response_code(200);
                                echo json_encode([
                                    "status" => true,
                                    "statusCode" => 200,
                                    "msg" => "Danh sách người dùng",
                                    "users" => $results,
                                    "page" => $page,
                                    "total" => $total,
                                    "totalPage" => $totalPages,
                                ]);
                            } else {
                                http_response_code(404);
                                echo json_encode([
                                    "status" => false,
                                    "statusCode" => 404,
                                    "msg" => "Không tìm thấy người dùng",
                                    "users" => [],
                                ]);
                            }
                        } catch (PDOException $e) {
                            error_log("Database Lỗi: " . $e->getMessage());
                            http_response_code(500);
                            echo json_encode([
                                "status" => false,
                                "statusCode" => 500,
                                "msg" => "Lỗi hệ thống",
                                "error" => "Lỗi hệ thống. Vui lòng liên hệ quản trị viên."
                            ]);
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode([
                            "status" => false,
                            "statusCode" => 401,
                            "msg" => "Bạn không đủ quyền để truy cập chức năng này",
                        ]);
                    }
                } else {
                    http_response_code(403);
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 403,
                        "msg" => "Token Hết Hạn",
                    ]);
                }
            } else {
                http_response_code(403);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 403,
                    "msg" => "Token không hợp lệ",
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không tìm thấy API tương ứng",
            ]);
        }
        break;
    case "update":
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get the user's token
            $token = $common->getBearerToken();
            if ($token && $token != -1) {
                $tokenPayload = $common->verifyToken($token);

                if ($tokenPayload) {
                    $decodeToken = json_decode($tokenPayload, true);
                    $uid = $decodeToken["uid"];
                    if (isset($_GET["uid"]) && $_GET["uid"] == $uid) {
                        $address = isset($_POST["address"]) ? filter_var($_POST["address"]) : null;
                        $phone_number = isset($_POST["phone_number"]) ? filter_var($_POST["phone_number"]) : null;
                        // SQL Update user 
                        $updateQuery = "UPDATE User SET address = :address, phone_number = :phone_number, updatedAt = :updatedAt WHERE uid = :uid";
                        $updateParams = array(
                            "address" => $address,
                            "phone_number" => $phone_number,
                            "updatedAt" => date('Y-m-d H:i:s', time()),
                            "uid" => $uid
                        );

                        try {
                            $updateStatement = $conn->prepare($updateQuery);
                            $updateStatement->execute($updateParams);

                            if ($updateStatement->rowCount() > 0) {
                                $selectQuery = "SELECT * FROM User WHERE uid = :uid";
                                $selectParams = array("uid" => $uid);

                                $selectStatement = $conn->prepare($selectQuery);
                                $selectStatement->execute($selectParams);

                                if ($selectStatement->rowCount() > 0) {
                                    $result = $selectStatement->fetch(PDO::FETCH_ASSOC);
                                    // Exclude sensitive information like password
                                    unset($result["password"]);

                                    http_response_code(200);
                                    echo json_encode([
                                        "status" => true,
                                        "statusCode" => 200,
                                        "msg" => "Cập nhật thông tin thành công",
                                        "user" => $result
                                    ]);
                                } else {
                                    http_response_code(404);
                                    echo json_encode([
                                        "status" => false,
                                        "statusCode" => 404,
                                        "msg" => "Không tìm thấy người dùng",
                                    ]);
                                }
                            } else {
                                http_response_code(400);
                                echo json_encode([
                                    "status" => false,
                                    "statusCode" => 400,
                                    "msg" => "Cập nhật thông tin thất bại",
                                ]);
                            }
                        } catch (PDOException $e) {
                            http_response_code(500);
                            echo json_encode([
                                "status" => false,
                                "statusCode" => 500,
                                "msg" => "Lỗi hệ thống",
                                "error" => "Lỗi hệ thống. Vui lòng liên hệ quản trị viên."
                            ]);
                        }
                    } else {
                        http_response_code(403);
                        echo json_encode([
                            "status" => false,
                            "statusCode" => 403,
                            "msg" => "Không có quyền cập nhật thông tin của người dùng khác",
                        ]);
                    }
                } else {
                    http_response_code(403);
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 403,
                        "msg" => "Token Hết Hạn",
                    ]);
                }
            } else {
                http_response_code(403);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 403,
                    "msg" => "Token không hợp lệ",
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không Thể Tìm Thấy API tương ứng",
            ]);
        }
        break;

    case "change-password":
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $token = $common->getBearerToken();
            if ($token && $token != -1) {
                $tokenPayload = $common->verifyToken($token);

                if ($tokenPayload) {
                    $decodeToken = json_decode($tokenPayload, true);
                    $uid = $decodeToken["uid"];
                    if (isset($_GET["uid"]) && $_GET["uid"] == $uid) {
                        $newPassword = $_POST["new_password"];
                        $oldPassword = $_POST["old_password"];
                        $selectUser = "SELECT * FROM user where uid=:uid and password = :password";
                        $pstm = $conn->prepare($selectUser);
                        $pstm->execute(array(
                            "uid" => $uid,
                            "password" => md5($oldPassword),
                        ));
                        if ($pstm->rowCount() > 0) {
                            $hashedNewPassword = md5($newPassword);
                            $updateQuery = "UPDATE User SET password = :password, updatedAt = :updatedAt WHERE uid = :uid";
                            $updateParams = array(
                                "password" => $hashedNewPassword,
                                "updatedAt" => date('Y-m-d H:i:s', time()),
                                "uid" => $uid
                            );

                            try {
                                $updateStatement = $conn->prepare($updateQuery);
                                $updateStatement->execute($updateParams);

                                if ($updateStatement->rowCount() > 0) {
                                    http_response_code(200);
                                    echo json_encode([
                                        "status" => true,
                                        "statusCode" => 200,
                                        "msg" => "Thay đổi mật khẩu thành công",
                                    ]);
                                } else {
                                    http_response_code(400);
                                    echo json_encode([
                                        "status" => false,
                                        "statusCode" => 400,
                                        "msg" => "Thay đổi mật khẩu không thành công",
                                    ]);
                                }
                            } catch (PDOException $e) {
                                http_response_code(500);
                                echo json_encode([
                                    "status" => false,
                                    "statusCode" => 500,
                                    "msg" => "Lỗi hệ thống",
                                    "error" => "Lỗi hệ thống. Vui lòng liên hệ quản trị viên."
                                ]);
                            }
                        } else {

                            http_response_code(400);
                            echo json_encode([
                                "status" => false,
                                "statusCode" => 400,
                                "msg" => "Mật khẩu bạn nhập không trùng khớp với mật khẩu hệ thống",
                            ]);
                        }
                    } else {
                        http_response_code(403);
                        echo json_encode([
                            "status" => false,
                            "statusCode" => 403,
                            "msg" => "Không có quyền cập nhật thông tin của người dùng khác",
                        ]);
                    }
                } else {
                    http_response_code(403);
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 403,
                        "msg" => "Token hết hạn",
                    ]);
                }
            } else {
                http_response_code(403);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 403,
                    "msg" => "Token không hợp lệ",
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không tìm thấy API tương ứng",
            ]);
        }
        break;
    case "block-account":
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Check the user's token
            $token = $common->getBearerToken();

            if ($token && $token != -1) {
                $tokenPayload = $common->verifyToken($token);

                if ($tokenPayload) {
                    $decodedToken = json_decode($tokenPayload, true);
                    $currentUserRole = $decodedToken["rid"];
                    $uid = $decodedToken["uid"];
                    if ($currentUserRole === "admin") {
                        $targetUserID = $_POST["uid"];
                        $checkUserQuery = "SELECT * FROM user join role on user.rid=role.rid WHERE uid = :uid ";
                        $checkUserParams = array("uid" => $targetUserID);

                        $checkUserStatement = $conn->prepare($checkUserQuery);
                        $checkUserStatement->execute($checkUserParams);

                        if ($checkUserStatement->rowCount() > 0 && $checkUserStatement->fetch(PDO::FETCH_ASSOC)["name"] !== "admin") {
                            $blockUserQuery = "UPDATE User SET isDeleted = 1 WHERE uid = :uid";
                            $blockUserParams = array("uid" => $targetUserID);

                            $blockUserStatement = $conn->prepare($blockUserQuery);
                            $blockUserStatement->execute($blockUserParams);

                            if ($blockUserStatement->rowCount() > 0) {
                                http_response_code(200);
                                echo json_encode([
                                    "status" => true,
                                    "statusCode" => 200,
                                    "msg" => "Chặn người dùng thành công",
                                ]);
                            } else {
                                http_response_code(400);
                                echo json_encode([
                                    "status" => false,
                                    "statusCode" => 400,
                                    "msg" => "Chặn người dùng thất bại",
                                ]);
                            }
                        } else {
                            http_response_code(403);
                            echo json_encode([
                                "status" => false,
                                "statusCode" => 403,
                                "msg" => "Bạn không thể chặn tài khoản admin khác",
                            ]);
                        }
                    } else {
                        http_response_code(403);
                        echo json_encode([
                            "status" => false,
                            "statusCode" => 403,
                            "msg" => "Bạn không có quyền chặn vai trò của người dùng",
                        ]);
                    }
                } else {
                    http_response_code(403);
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 403,
                        "msg" => "Token hết hạn",
                    ]);
                }
            } else {
                http_response_code(403);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 403,
                    "msg" => "Token không hợp lệ",
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không tìm thấy API tương ứng",
            ]);
        }
        break;
    default:
        http_response_code(404);
        echo json_encode([
            "status" => false,
            "statusCode" => 404,
            "msg" => "Hành Động Không Hợp Lệ",
        ]);
        break;
}
