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
    case "create-v2":
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
            // Lấy data sort
            $sort = isset($_GET["sort"]) ? $_GET["sort"] : -1;
            $dateCreate = isset($_GET["new"]) ? $_GET["new"] : -1;

            // Thuật Toán Phân Trang
            $page = isset($_GET["page"]) ? $_GET["page"] : 1;
            $limit = isset($_GET["limit"]) ? $_GET["limit"] : 20;
            $skip = (int) ($page - 1) * $limit;
            try {
                // Lấy Ra Tổng Số Phần Tử Trong Table
                $total = 0;
                $pstm = $conn->prepare("SELECT * FROM product");
                $pstm->execute();
                $allData = $pstm->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allData as $count) {
                    $total = $total + 1;
                }
                // SQL PHÂN TRANG
                $sqlSelect = "SELECT * FROM product";
                $paginate = "LIMIT :skip, :limit;";
                $where = "WHERE isDeleted = false";

                // SQL Filter Category
                if (isset($_GET["category"])) {
                    $where .= " AND cid = :cid";
                }

                // SQL sort price
                $sortFilter = "";
                if ($sort !== -1) {
                    if ($dateCreate !== -1) {
                        $sortFilter = "ORDER BY price $sort, createdAt $dateCreate";
                    } else {
                        $sortFilter = "ORDER BY price $sort";
                    }
                } else {
                    if ($dateCreate !== -1) {
                        $sortFilter = "ORDER BY createdAt $dateCreate";
                    }
                }
                $query = "$sqlSelect $where $sortFilter $paginate";
                $pstm = $conn->prepare($query);
                $pstm->bindValue(":skip", (int) trim($skip), PDO::PARAM_INT);
                $pstm->bindValue(":limit", (int) trim($limit), PDO::PARAM_INT);

                // Bind Value Sort Category
                if (isset($_GET["category"])) {
                    $cid = $_GET["category"];
                    $pstm->bindValue(":cid", (int) trim($cid), PDO::PARAM_INT);
                }
                $pstm->execute();
                $results = $pstm->fetchAll(PDO::FETCH_ASSOC);
                $products = []; // Mảng Chứa Các Sản Phẩm
                foreach ($results as $data) {
                    // Object Sản Phẩm
                    $dataProduct = [
                        "title" => $data["title"],
                        "price" => $data["price"],
                        "discount" => $data["discount"],
                        "description" => $data["description"],
                        "slug" => $data["slug"],
                        "pid" => $data["pid"],
                        "cid" => $data["cid"],
                        "isDeleted" => $data["isDeleted"],
                    ];
                    // Select Hình Ảnh Tử Table imagesproduct từ pid vừa lấy dc
                    $pstm = $conn->prepare("SELECT * FROM imagesproduct WHERE pid = :pid");
                    $pstm->execute(array(
                        "pid" => $data["pid"]
                    ));
                    // Duyệt Qua Từng Hình Mảng
                    $images = $pstm->fetchAll(PDO::FETCH_ASSOC);
                    $listImages = []; // Mảng Chứa Các Hình Ảnh
                    foreach ($images as $image) {
                        $dataImg = $image["description"]; // Lấy URL hình ảnh
                        array_push($listImages, $dataImg); // Thêm Vào mảng chứa hình ảnh
                    }
                    $dataProduct["images"] = $listImages; // Thêm Hình Ảnh Vào Mảng dataProduct có key là images => $dataProduct["images"]
                    array_push($products, $dataProduct); // Đẩy $dataProduct vào mảng các sản phẩm
                }
                $totalPage = ceil($total / $limit); // Tính Tổng Số Lượng Page Để Chứa Hết Các Bản Ghi Trong Table Product
                http_response_code(200);
                echo json_encode([
                    "status" => true,
                    "statusCode" => 200,
                    "page" => (int) $page,
                    "total" => $total,
                    "totalPage" => $totalPage,
                    "products" => $products
                ]);
            } catch (\Throwable $th) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 400,
                    "msg" => "Lấy Danh Sách Sản Phẩm Thất Bại.",
                    "Error" => $th->getMessage(),
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
    case "search":
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Thuật Toán Phân Trang
            $page = isset($_GET["page"]) ? $_GET["page"] : 1;
            $limit = isset($_GET["limit"]) ? $_GET["limit"] : 20;
            $skip = (int) ($page - 1) * $limit;
            try {
                // Lấy Ra Tổng Số Phần Tử Trong Table
                $total = 0;
                $pstm = $conn->prepare("SELECT * FROM product");
                $pstm->execute();
                $allData = $pstm->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allData as $count) {
                    $total = $total + 1;
                }
                // SQL PHÂN TRANG
                $sqlSelect = "SELECT * FROM product";
                $paginate = "LIMIT :skip, :limit;";
                $where = "WHERE 1";
                if (isset($_GET["category"])) {
                    $where .= " AND cid = :cid";
                }
                if (isset($_POST["title"])) {
                    $where .= " AND title LIKE :title";
                }
                $query = "$sqlSelect $where $paginate";
                $pstm = $conn->prepare($query);
                $pstm->bindValue(":skip", (int) trim($skip), PDO::PARAM_INT);
                $pstm->bindValue(":limit", (int) trim($limit), PDO::PARAM_INT);
                if (isset($_GET["category"])) {
                    $cid = $_GET["category"];
                    $pstm->bindValue(":cid", (int) trim($cid), PDO::PARAM_INT);
                }
                if (isset($_POST["title"])) {
                    $title = $_POST["title"];
                    $dataTitle = "%$title%";
                    $pstm->bindParam(":title", $dataTitle);
                }
                $pstm->execute();
                $results = $pstm->fetchAll(PDO::FETCH_ASSOC);
                $products = []; // Mảng Chứa Các Sản Phẩm
                foreach ($results as $data) {
                    // Object Sản Phẩm
                    $dataProduct = [
                        "title" => $data["title"],
                        "price" => $data["price"],
                        "discount" => $data["discount"],
                        "description" => $data["description_product"],
                        "slug" => $data["slug"],
                        "pid" => $data["pid"],
                        "cid" => $data["cid"],
                        "isDeleted" => $data["isDeleted"],
                    ];
                    // Select Hình Ảnh Tử Table imagesproduct từ pid vừa lấy dc
                    $pstm = $conn->prepare("SELECT * FROM imagesproduct WHERE pid = :pid");
                    $pstm->execute(array(
                        "pid" => $data["pid"]
                    ));
                    // Duyệt Qua Từng Hình Mảng
                    $images = $pstm->fetchAll(PDO::FETCH_ASSOC);
                    $listImages = []; // Mảng Chứa Các Hình Ảnh
                    foreach ($images as $image) {
                        $dataImg = $image["description"]; // Lấy URL hình ảnh
                        array_push($listImages, $dataImg); // Thêm Vào mảng chứa hình ảnh
                    }
                    $dataProduct["images"] = $listImages; // Thêm Hình Ảnh Vào Mảng dataProduct có key là images => $dataProduct["images"]
                    array_push($products, $dataProduct); // Đẩy $dataProduct vào mảng các sản phẩm
                }
                $totalPage = ceil($total / $limit); // Tính Tổng Số Lượng Page Để Chứa Hết Các Bản Ghi Trong Table Product
                http_response_code(200);
                echo json_encode([
                    "status" => true,
                    "statusCode" => 200,
                    "page" => (int) $page,
                    "total" => $total,
                    "totalPage" => $totalPage,
                    "products" => $products
                ]);
            } catch (\Throwable $th) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 400,
                    "msg" => "Lấy Danh Sách Sản Phẩm Thất Bại.",
                    "Error" => $th->getMessage(),
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
    case "get-product":
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $pid = $_GET["pid"];
            try {
                $sql = "SELECT * FROM product WHERE pid = :pid";
                $params = array(
                    "pid" => $pid
                );
                $pstm = $conn->prepare($sql);
                $pstm->execute($params);
                if ($pstm->rowCount() > 0) {
                    $imgs = [];
                    $data = $pstm->fetch(PDO::FETCH_ASSOC);
                    $pstmImg = $conn->prepare("SELECT * FROM imagesproduct WHERE pid = :pid");
                    $pstmImg->execute(array(
                        "pid" => $data["pid"]
                    ));
                    if ($pstmImg->rowCount() > 0) {
                        $listImage = $pstmImg->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($listImage as $img) {
                            array_push($imgs, $img["description"]);
                        }
                        $data["images"] = $imgs;
                    }
                    http_response_code(200);
                    echo json_encode([
                        "status" => true,
                        "statusCode" => 200,
                        "product" => $data,
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "status" => false,
                        "statusCode" => 404,
                        "msg" => "Không Tìm Thấy Sản Phẩm",
                    ]);
                }
            } catch (\Throwable $th) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "statusCode" => 400,
                    "msg" => "Lấy Danh Sách Sản Phẩm Thất Bại.",
                    "Error" => $th->getMessage(),
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
    default:
        return;
}
