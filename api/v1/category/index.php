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
    case "create":
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $_POST["name"];
            $token = $common->getBearerToken();
            if ($token && $token != -1) {
                $tokenPayload = $common->verifyToken($token);
                if ($tokenPayload != null) {
                    $decode = json_decode($tokenPayload, true);
                    $role = $decode["role"];
                    if ($role == "admin") {
                        $sql = "INSERT INTO category VALUES(null, :name)";
                        $params = array(
                            "name" => $name,
                        );
                        try {
                            $pstm = $conn->prepare($sql);
                            $pstm->execute($params);
                            if ($pstm->rowCount() > 0) {
                                http_response_code(201);
                                echo json_encode([
                                    "status" => true,
                                    "statusCode" => 201,
                                    "msg" => "Tạo Danh Mục $name Thành Công",
                                ]);
                            }
                        } catch (\Throwable $th) {
                            http_response_code(400);
                            echo json_encode([
                                "status" => true,
                                "statusCode" => 201,
                                "msg" => "Tạo Danh Mục $name Thất Bại.",
                            ]);
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode([
                            "status" => false,
                            "statusCode" => 401,
                            "msg" => "Chỉ Có Admin Mới Được Thực Hiện Chức Năng Này",
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
                http_response_code(401);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 401,
                    "msg" => "Bạn Không Có Quyền Truy Cập Vào Chức Năng Này",
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "statusCode" => 404,
                "msg" => "Không Tìm Thấy API Tương Ứng",
            ]);
        }
        break;
    case "get":
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $pstm = $conn->prepare("SELECT * FROM category");
                $pstm->execute();
                $results = $pstm->fetchAll(PDO::FETCH_ASSOC);
                $arr = [];
                foreach ($results as $data) {
                    $cate = [
                        "cid" => $data["cid"],
                        "name" => $data["name"]
                    ];
                    array_push($arr, $cate);
                }
                http_response_code(200);
                echo json_encode([
                    "status" => true,
                    "statusCode" => 200,
                    "data" => $arr
                ]);
            } catch (\Throwable $th) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 400,
                    "msg" => "Lấy Danh Sách Danh Mục Thất Bại.",
                ]);
            }
        }
        break;
    default:
        return;
}
