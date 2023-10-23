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
                $pstm = $conn->prepare("SELECT * FROM USER JOIN ROLE WHERE email = :email AND password = :password");
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
                        "exp" => time() + (15 * 60),
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
                    $common->setCookies("token", $token, time() + 30 * 60 * 1000);
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
    default:
        http_response_code(404);
        echo json_encode([
            "status" => false,
            "statusCode" => 404,
            "msg" => "Hành Động Không Hợp Lệ",
        ]);
        break;
}
