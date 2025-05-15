<?php
include 'config.php';

header('Content-Type: application/json');
header("HTTP/1.1 200 OK");

$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

class API {
    private $mysqli;

    public function __construct(){
        $this -> mysqli = Database::getInstance() -> connect();
    }

    private function returnError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'timestamp' => time(),
            'data' => $message
        ]);
        exit;
    }

    public function handleRequest($data) {
        if (!isset($data['type'])) {
            $this ->returnError('No Request type specified.');
        }

        $type = $data['type'];

        if($type === 'Login'){
            $this -> handleLogin($data);
        }
        elseif($type === 'Register'){
            $this -> handleRegister($data);
        }
        elseif($type === 'CreateOrder'){
            $this -> handleCreateOrder($data);
        }
        elseif($type === 'CreateDrone'){
            $this -> handleCreateDrone($data);
        }
        elseif($type === 'UpdateOrder'){
            $this -> handleGetUpdateOrder($data);
        }
        elseif($type === 'UpdateDrone'){
            $this -> handleUpdateDrone($data);
        }
        elseif($type === 'GetAllOrders'){
            $this -> handleGetAllOrders($data);
        }
        elseif($type === 'GetAllDrones'){
            $this -> handleGetAllDrones($data);
        }
        else{
            $this->returnError('Invalid request type.');
        }
    }

    private function handleLogin($data) {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->returnError('No empty fields are allowed.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->returnError('Invalid email address.');
        }

        $pstmt = $this -> mysqli->prepare('SELECT * FROM Users WHERE email = ?');
        if (!$pstmt) {
            $this->returnError('Database error: ' . $this -> mysqli->error, 500);
        }
        $pstmt->bind_param('s', $email);
        $pstmt->execute();
        $result = $pstmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            $this->returnError('Email or Password is invalid.', 401);
        }

        $hashedPassword = hash('sha256', $user['salt'] . $password);
        if ($hashedPassword !== $user['password']) {
            $this->returnError('Your Email or Password is INVALID.', 401);
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => [
                'apikey' => $user['api_key'],
                'name' => $user['name']
            ]
        ]);
        exit;
    }

    private function handleRegister($data) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => [
                'message' => 'Registration successful.'
            ]
        ]);
        exit;
    }

    private function handleCreateOrder($data) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => $order
        ]);
        exit;
    }

    private function handleCreateDrone($data) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => $drone
        ]);
        exit;
    }

    private function handleUpdateOrder($data) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => [
                'message' => 'Order successfully updated.'
            ]
        ]);
        exit;
    }

    private function handleUpdateDrone($data) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => [
                'message' => 'Drone successfully updated.'
            ]
        ]);
        exit;
    }

    private function handleGetAllOrders($data) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => $orders
        ]);
        exit;
    }

    private function handleGetAllDrones($data) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => $drones
        ]);
        exit;
    }
}

$api = new API();
$api -> handleRequest($data);
?>