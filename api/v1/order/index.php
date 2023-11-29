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
    case "get-order":
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $token = $common->getBearerToken();
            if ($token && $token != -1) {
                $tokenPayload = $common->verifyToken($token);
                if ($tokenPayload) {
                    $decodeToken = json_decode($tokenPayload, true);
                    $uid = $decodeToken["uid"];
                    $orderId = $_GET['id'];
                    try {
                        $fetchOrderQuery = "SELECT * FROM orders WHERE id = :id and user_id = :user_id";
                        $fetchOrderParams = array(
                            "id" => $orderId,
                            "user_id" => $uid,
                        );
                        $fetchOrderStatement = $conn->prepare($fetchOrderQuery);
                        $fetchOrderStatement->execute($fetchOrderParams);
                        $orderDetails = $fetchOrderStatement->fetch(PDO::FETCH_ASSOC);
                        if ($orderDetails) {
                            $pstm10 = $conn->prepare("SELECT * FROM orderdetail WHERE order_id = :order_id");
                            $params = array(
                                "order_id" => $orderId,
                            );
                            $pstm10->execute($params);
                            $count = $pstm10->rowCount();
                            $orderDetails["total"] = $count;
                            http_response_code(200);
                            echo json_encode([
                                "status" => true,
                                "statusCode" => 200,
                                "order" => $orderDetails,
                            ]);
                        } else {
                            http_response_code(404);
                            echo json_encode([
                                "status" => false,
                                "statusCode" => 404,
                                "msg" => "Không tim thấy đơn hàng",
                            ]);
                        }
                    } catch (\Throwable $th) {
                        http_response_code(500);
                        echo json_encode([
                            "status" => false,
                            "statusCode" => 500,
                            "msg" => "Database Lỗi!!!!",
                            "error" => $e->getMessage(),
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
    case "create":
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $timestamp = time(); // Lấy giá trị thời gian hiện tại dưới dạng số giây
            $currentTime = date('Y-m-d H:i:s', $timestamp); // Chuyển đổi thành định dạng datetime
            $token = $common->getBearerToken();
            if ($token && $token != -1) {
                $tokenPayload = $common->verifyToken($token);
                if ($tokenPayload) {
                    $decodeToken = json_decode($tokenPayload, true);
                    $uid = $decodeToken["uid"];
                    $fullname = $_POST["fullname"];
                    $phone_number = $_POST["phone_number"];
                    $email = $_POST["email"];
                    $order_date = $currentTime;
                    $note = $_POST["note"];
                    $status = 1;
                    $total_money = $_POST["total_money"];
                    $payment = $_POST["payment_methods"];
                    $address = $_POST["address"];
                    $cart = $_POST["cart"];

                    $pstm = $conn->prepare("INSERT INTO orders VALUES(null, :uid, :fullname, :phone_number, :email, :order_date, :note, :status, :total_money, :payment_methods, :address)");
                    $params = array(
                        "uid" => $uid,
                        "fullname" => $fullname,
                        "phone_number" => $phone_number,
                        "email" => $email,
                        "order_date" => $order_date,
                        "note" => $note,
                        "status" => $status,
                        "total_money" => $total_money,
                        "payment_methods" => $payment,
                        "address" => $address
                    );
                    $pstm->execute($params);
                    if ($pstm->rowCount() > 0) {
                        $order_id = $conn->lastInsertId();

                        foreach ($cart as $item) {
                            $pstm2 = $conn->prepare("INSERT INTO orderdetail VALUES(null, :order_id, :pid, :price, :quantity, :total)");
                            $params2 = array(
                                "order_id" => $order_id,
                                "pid" => $item["pid"],
                                "price" => $item["price"],
                                "quantity" => $item["quantity"],
                                "total" => $item["price"] * $item["quantity"]
                            );
                            $pstm2->execute($params2);
                        }

                        http_response_code(201);
                        echo json_encode([
                            "status" => true,
                            "statusCode" => 201,
                            "order_id" => $order_id,
                            "msg" => "Tạo Đơn Hàng Thành Công",
                        ]);
                    } else {
                        http_response_code(500);
                        echo json_encode([
                            "status" => false,
                            "statusCode" => 500,
                            "msg" => "Tạo Đơn Hàng Thất Bại",
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
    default:
        http_response_code(404);
        echo json_encode([
            "status" => false,
            "statusCode" => 404,
            "msg" => "Hành Động Không Hợp Lệ",
        ]);
        break;
}
