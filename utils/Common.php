<?php
include_once "../../../vendor/autoload.php";
include_once "../../../configs/config.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Common
{
    function __construct()
    {
    }

    // Tạo Field Slug trong Database
    public function createSlug($string)
    {
        // Lọc ra các kỳ tự tiếng việt
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            "/[^a-zA-Z0-9\-\_]/",
        );
        // Tham chiếu nó với bản mã tiếng anh
        $replace = array(
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
            'd',
            'A',
            'E',
            'I',
            'O',
            'U',
            'Y',
            'D',
            '-',
        );
        // Dùng Regex biểu thức chính quy để tìm và thay đổi chuổi tạo slug
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        $string = strtolower($string);
        return $string;
    }

    // Lấy Token
    public function getBearerToken()
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];

            // Kiểm tra xem header có chứa "Bearer " không
            if (strpos($authHeader, 'Bearer ') === 0) {
                $token = substr($authHeader, 7); // Loại bỏ 7 ký tự "Bearer " để lấy token
                return $token; // Trả về token
            } else {
                return 0; // Không có token trong Authorization
            }
        } else {
            return -1; // Không Có Authorization
        }
    }

    // Tạo Token
    public function createToken($data, $key = secretKey)
    {
        $encode = JWT::encode($data, $key, "HS256");
        return $encode;
    }

    // Xác Thực Token
    public function verifyToken($token, $key = secretKey)
    {
        try {
            $decode = JWT::decode($token, new Key($key, 'HS256'));
            $data = json_encode($decode);
            return $data;
        } catch (Exception $th) {
            return null;
        }
    }

    // Tạo Cookies
    public function setCookies($key, $value, $expire, $env = env)
    {
        if ($env == "dev") {
            setcookie($key, $value, $expire, "/", "", false, true);
        } else {
            setcookie($key, $value, $expire, "/", "", true, true);
        }
    }

    // Lấy Cookies
    public function getCookies($key)
    {
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return null;
        }
    }

    // Xoá Cookies
    public function deleteCookies($key)
    {
        setcookie($key, "", time() - 3600);
    }

    // Kiểm Tra Tính Hợp Lệ Của Password ở server và client
    public function verifyPassword($server, $client)
    {
        return $server === md5($client);
    }
}
