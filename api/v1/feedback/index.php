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
    case "feedback":
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $fname = $_POST["firstname"];
            $lname = $_POST["lastname"];
            $email = $_POST["email"];
            $phone = $_POST["phone"];
            $note = $_POST["note"];
            $timestamp = time(); // Lấy giá trị thời gian hiện tại dưới dạng số giây
            $currentTime = date('Y-m-d H:i:s', $timestamp); // Chuyển đổi thành định dạng datetime


            if (!isset($_POST["firstname"]) || !isset($_POST["lastname"]) || !isset($_POST["email"]) || !isset($_POST["phone"]) || !isset($_POST["note"])) {
                http_response_code(400);
                echo json_encode(
                    [
                        "status" => false,
                        "statusCode" => 400,
                        "msg" => "Thiếu thông tin cần thiết"
                    ]
                );
                exit;
            }


            try {
                $pstm = $conn->prepare("INSERT INTO feedback VALUES(null, :firstname, :lastname, :email, :phone_number, 1, :note, 0, :createdAt, :updatedAt)");
                $pstm->execute(array(
                    "firstname" => $fname,
                    "lastname" => $lname,
                    "email" => $email,
                    "phone_number" => $phone,
                    "note" => $note,
                    "createdAt" => $currentTime,
                    "updatedAt" => $currentTime
                ));
                if ($pstm->rowCount() > 0) {
                    http_response_code(200);
                    echo json_encode(
                        [
                            "status" => true,
                            "statusCode" => 200,
                            "msg" => "Cảm ơn bạn đã đóng góp ý kiến"
                        ]
                    );
                } else {
                    http_response_code(400);
                    echo json_encode(
                        [
                            "status" => true,
                            "statusCode" => 400,
                            "msg" => "Vui Lòng Thử Lại Sau !!!"
                        ]
                    );
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
        }
        break;
    default:
        return;
}
