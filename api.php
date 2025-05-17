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
        elseif($type === 'ResetOrdersStorage'){
            $this -> handleResetOrdersToStorage($data);
        }
        elseif($type === 'MarkDroneCrashed'){
            $this -> handleMarkDroneCrashed($data);
        }
        elseif($type === 'GetCurrentlyDelivering'){
            $this -> handleGetCurrentlyDelivering($data);
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
            $this->returnError('Email or Password is invalid.', 401);
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => [
                'apikey' => $user['api_key'],
                'username' => $user['username']
            ]
        ]);
        exit;
    }

    private function handleRegister($data) {
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $userType = $data['user_type'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($userType)) {
            $this->returnError('No empty fields are allowed.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->returnError('Invalid email address.');
        }

        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_])[A-Za-z\d@$!%*?&#_]{8,}$/';
        if (!preg_match($passwordRegex, $password)) {
            $this->returnError('Password should be longer than 8 characters, contain upper and lower case letters, at least one digit and one symbol (Symbols you can use: @ $ ! % * ? & # _).');
        }

        $pstmt = $this -> mysqli->prepare('SELECT * FROM Users WHERE email = ?');
        if (!$pstmt) {
            $this->returnError('Database error: ' . $this -> mysqli->error, 500);
        }
        $pstmt->bind_param('s', $email);
        $pstmt->execute();
        $pstmt->store_result();
        if ($pstmt->num_rows > 0) {
            $this->returnError('Email is already in use.', 409);
        }

        $salt = bin2hex(random_bytes(16));
        $hashedPassword = hash('sha256', $salt . $password);

        $pstmt = $this -> mysqli->prepare('INSERT INTO Users (username, password, email, type) VALUES (?, ?, ?, ?)');
        if (!$pstmt) {
            $this->returnError('Database error: ' . $this -> mysqli->error, 500);
        }
        $pstmt->bind_param('ssss', $username, $hashedPassword, $email, $userType);
        if (!$pstmt->execute()) {
            $this->returnError('Failed to register user.', 500);
        }

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
        $stmt = $this->mysqli->prepare("SELECT * FROM Orders WHERE state='Storage'");
        if (!$stmt) {
            $this->returnError('Database error: ' . $this->mysqli->error, 500);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();
        if (empty($orders)) {
            $this->returnError('No orders found.', 404);
        }
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => $orders
        ]);
        exit;
    }

    private function handleGetAllDrones($data) {
        $stmt = $this->mysqli->prepare("SELECT * FROM Drones");
        if (!$stmt) {
            $this->returnError('Database error: ' . $this->mysqli->error, 500);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $drones = [];
        while ($row = $result->fetch_assoc()) {
            $drones[] = $row;
        }
        $stmt->close();
        if (empty($drones)) {
            $this->returnError('No drones found.', 404);
        }
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => $drones
        ]);
        exit;
    }

    public function resetOrdersToStorage($courierUsername) {
        $stmt = $this->mysqli->prepare("UPDATE Orders SET state='Storage' AND state='Out for delivery'");
        // $stmt->bind_param("s", $trackingNum);
        // if ($stmt->execute()) {
        //     echo json_encode([
        //         'status' => 'success',
        //         'timestamp' => time(),
        //         'data' => 'Orders reset to Storage'
        //     ]);
        // } else {
        //     $this->returnError('Failed to reset orders');
        // }
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => 'Orders reset to Storage'
        ]);
        $stmt->close();
        exit;
    }

    public function markDroneCrashed($courierID) {
        // Example: Mark the drone as crashed for this courier
        $stmt = $this->mysqli->prepare("UPDATE Drones SET is_available='false' WHERE current_operator_id=?");
        $stmt->bind_param("s", $courierID);
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'timestamp' => time(),
                'data' => 'Drone has crashed'
            ]);
        } else {
            $this->returnError('Failed to mark drone as crashed');
        }
        $stmt->close();
        exit;
    }

    public function getCurrentlyDelivering() {
        $sql = "SELECT o.order_id, p.title, o.destination_latitude, o.destination_longitude, u.username AS recipient_username, u.email AS recipient_email
                FROM Users u
                JOIN Orders o ON u.id = o.customer_id
                JOIN Orders_Products op ON o.order_id = op.order_id
                JOIN Products p ON op.product_id = p.product_id
                WHERE o.status = 'Out for delivery'";
        $result = $this->mysqli->query($sql);
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = [
                'orderId' => $row['order_id'],
                'product' => $row['title'],
                'destination' => [
                    'latitude' => $row['destination_latitude'],
                    'longitude' => $row['destination_longitude']
                ],
                'recipient' => [
                    'username' => $row['recipient_username'],
                    'email' => $row['recipient_email']
                ]
            ];
        }
        echo json_encode([
            'status' => 'success',
            'timestamp' => time(),
            'data' => $orders
        ]);
    }
}

$api = new API();
$api -> handleRequest($data);
?>