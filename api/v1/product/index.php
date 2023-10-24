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

if (!isset($action)) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "statusCode" => 404,
        "msg" => "Thiếu Tham Số action",
    ]);
    exit;
}


switch ($action) {
    case "create":
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $token = $common->getBearerToken();
            if ($token && $token != -1) {
                $tokenPayload = $common->verifyToken($token);
                if ($tokenPayload != null) {
                    $decode = json_decode($tokenPayload, true);
                    $role = $decode["rid"];
                    if ($role == "admin") {
                        $cid = $_POST["category"];
                        $title = $_POST["title"];
                        $price = $_POST["price"];
                        $discount = $_POST["sale"];
                        $description = $_POST["des"];
                        $images = $_POST["images"];

                        $timestamp = time(); // Lấy giá trị thời gian hiện tại dưới dạng số giây
                        $currentTime = date('Y-m-d H:i:s', $timestamp); // Chuyển đổi thành định dạng datetime

                        $sql = "INSERT INTO product VALUES(null, :cid, :title, :price, :discount, :description_product, :slug, :createdAt, :updatedAt, 0)";
                        $params = array(
                            "cid" => $cid,
                            "title" => $title,
                            "price" => $price,
                            "discount" => $discount,
                            "description_product" => $description,
                            "slug" => $common->createSlug($title),
                            "createdAt" => $currentTime,
                            "updatedAt" => $currentTime,
                        );

                        try {
                            $pstm = $conn->prepare($sql);
                            $pstm->execute($params);
                            if ($pstm->rowCount() > 0) {
                                $pid = $conn->lastInsertId();
                                foreach ($images as $image) {
                                    $pstmImages = $conn->prepare("INSERT INTO imagesproduct VALUES(null, :pid, :description)");
                                    $pstmImages->execute(array(
                                        "pid" => $pid,
                                        "description" => $image
                                    ));
                                    if ($pstm->rowCount() <= 0) {
                                        http_response_code(400);
                                        echo json_encode([
                                            "status" => false,
                                            "statusCode" => 400,
                                            "msg" => "Thêm Sản Phẩm Thất Bại",
                                        ]);
                                        exit;
                                    }
                                }
                                http_response_code(201);
                                echo json_encode([
                                    "status" => true,
                                    "statusCode" => 201,
                                    "msg" => "Thêm Sản Phẩm Thành Công",
                                ]);
                            } else {
                                http_response_code(400);
                                echo json_encode([
                                    "status" => false,
                                    "statusCode" => 400,
                                    "msg" => "Thêm Sản Phẩm Thất Bại",
                                ]);
                            }
                        } catch (\Throwable $th) {
                            http_response_code(500);
                            echo json_encode([
                                "status" => false,
                                "statusCode" => 500,
                                "msg" => "Lỗi Truy Cập Vào SQL",
                                "error" => $th->getMessage(),
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
    case "get-all":
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $pstm = $conn->prepare("SELECT * FROM product JOIN imagesproduct ON product.pid = imagesproduct.pid");
                $pstm->execute();
                $results = $pstm->fetchAll(PDO::FETCH_ASSOC);

                $images = [];
                $production = [];
                $status = false;
                foreach ($results as $data) {
                    if (!$status) {
                        $production = [
                            "title" => $data["title"],
                            "price" => $data["price"],
                            "discount" => $data["discount"],
                            "description" => $data["description_product"],
                            "slug" => $data["slug"],
                            "pid" => $data["pid"],
                            "cid" => $data["cid"],
                            "isDeleted" => $data["isDeleted"]
                        ];
                    }
                    $image = $data["description"];
                    array_push($images, $image);
                    $status = true;
                }
                $production["images"] = $images;
                $dataResult = [
                    "status" => true,
                    "statusCode" => 200,
                    "product" => $production,
                ];
                http_response_code(200);
                echo json_encode($dataResult);
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
