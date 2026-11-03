<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = isset($pdo) ? $pdo : (isset($conn) ? $conn : null);

if (!$db) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database tidak tersedia.'
    ]);
    exit();
}

switch ($method) {
    case 'GET':
        handleGet($db);
        break;
    case 'POST':
        handlePost($db);
        break;
    case 'DELETE':
        handleDelete($db);
        break;
    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Metode HTTP tidak diizinkan.'
        ]);
        break;
}

function handleGet($db) {
    try {
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'fail', 'message' => 'Parameter ID tidak valid.']);
                return;
            }

            if ($db instanceof PDO) {
                $stmt = $db->prepare("SELECT * FROM buku_tamu WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $db->prepare("SELECT * FROM buku_tamu WHERE id = ? LIMIT 1");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
            }

            if ($data) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'data' => $data]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'fail', 'message' => 'Data tamu tidak ditemukan.']);
            }
        } else {
            $query = "SELECT * FROM buku_tamu ORDER BY id DESC";
            
            if ($db instanceof PDO) {
                $stmt = $db->query($query);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $result = $db->query($query);
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }

            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $data]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function handlePost($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $input = $_POST;
        }

        $nama = isset($input['nama']) ? trim($input['nama']) : null;
        $instansi = isset($input['instansi']) ? trim($input['instansi']) : null;
        $no_hp = isset($input['no_hp']) ? trim($input['no_hp']) : null;
        $tujuan = isset($input['tujuan']) ? trim($input['tujuan']) : null;
        $keperluan = isset($input['keperluan']) ? trim($input['keperluan']) : null;

        if (empty($nama) || empty($instansi) || empty($tujuan) || empty($keperluan)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'fail',
                'message' => 'Kolom nama, instansi, tujuan, dan keperluan wajib diisi.'
            ]);
            return;
        }

        if ($db instanceof PDO) {
            $sql = "INSERT INTO buku_tamu (nama, instansi, no_hp, tujuan, keperluan, created_at) 
                    VALUES (:nama, :instansi, :no_hp, :tujuan, :keperluan, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nama' => $nama,
                ':instansi' => $instansi,
                ':no_hp' => $no_hp,
                ':tujuan' => $tujuan,
                ':keperluan' => $keperluan
            ]);
            $insertedId = $db->lastInsertId();
        } else {
            $sql = "INSERT INTO buku_tamu (nama, instansi, no_hp, tujuan, keperluan, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sssss", $nama, $instansi, $no_hp, $tujuan, $keperluan);
            $stmt->execute();
            $insertedId = $stmt->insert_id;
        }

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Data tamu inspektora berhasil disimpan.',
            'id' => $insertedId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function handleDelete($db) {
    try {
        $id = null;

        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['id'])) {
                $id = filter_var($input['id'], FILTER_VALIDATE_INT);
            }
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'fail',
                'message' => 'Parameter ID tidak valid atau tidak disediakan.'
            ]);
            return;
        }

        if ($db instanceof PDO) {
            $stmt = $db->prepare("DELETE FROM buku_tamu WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $affected = $stmt->rowCount();
        } else {
            $stmt = $db->prepare("DELETE FROM buku_tamu WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
        }

        if ($affected > 0) {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Data tamu berhasil dihapus.'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'fail',
                'message' => 'Data tamu dengan ID tersebut tidak ditemukan.'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}