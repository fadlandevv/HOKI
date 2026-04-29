<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input  = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

// ── AUTO DETECT: DEV atau PRODUCTION ──────────────────
$host  = $_SERVER['HTTP_HOST'] ?? '';
// Cek environment - dev jika tidak ada dot di host (localhost, 127.0.0.1, dll)
// atau jika file dev.flag ada di folder
$isDev = (
    $host === 'localhost' || 
    $host === '127.0.0.1' || 
    substr($host, 0, 8) === '192.168.' ||
    strpos($host, 'localhost') !== false ||
    strpos($host, ':') !== false ||
    file_exists(__DIR__ . '/dev.flag')
);

if ($isDev) {
    // ── MODE DEV: gunakan dummy data ──────────────────
    $dummyUsers = [
        ['id'=>1,'user'=>'admin',  'pass'=>'admin',  'role'=>'VIP',          'cabang'=>'Semua',                       'fullName'=>'Administrator'],
        ['id'=>2,'user'=>'owner',  'pass'=>'owner',  'role'=>'Owner',        'cabang'=>'Semua',                       'fullName'=>'Owner Hoki'],
        ['id'=>3,'user'=>'spv1',   'pass'=>'spv1',   'role'=>'SPV',          'cabang'=>'Pusat',                       'fullName'=>'Supervisor Pusat'],
        ['id'=>4,'user'=>'senior', 'pass'=>'senior', 'role'=>'Senior Staff', 'cabang'=>'Pusat,Cabang A',              'fullName'=>'Senior Staff'],
        ['id'=>5,'user'=>'staff1', 'pass'=>'staff1', 'role'=>'Staff',        'cabang'=>'Pusat,Cabang A,Cabang B',     'fullName'=>'Staff Kasir'],
    ];

    switch ($action) {
        case 'login':
            $u = $input['user'] ?? '';
            $p = $input['pass'] ?? '';
            $found = null;
            foreach ($dummyUsers as $user) {
                if (strtolower($user['user']) === strtolower($u) && $user['pass'] === $p) { $found = $user; break; }
            }
            if ($found) {
                echo json_encode(['status'=>'success','user'=>['user'=>$found['user'],'fullName'=>$found['fullName'],'role'=>$found['role'],'cabang'=>$found['cabang']],'token'=>bin2hex(random_bytes(32))]);
            } else {
                echo json_encode(['status'=>'error','message'=>'Username atau Password salah!']);
            }
            break;
        case 'check_session':
            echo json_encode(['valid'=>true]);
            break;
        case 'get_cabang': case 'get_branches':
            echo json_encode([['id'=>1,'nama_cabang'=>'Pusat'],['id'=>2,'nama_cabang'=>'Cabang A'],['id'=>3,'nama_cabang'=>'Cabang B']]);
            break;
        case 'get_produk':
            echo json_encode([
                ['id'=>1,'sku'=>'DMS','nama'=>'Dimsum Ayam',  'harga'=>15000,'hpp'=>8000,'dimsumPcs'=>4,'aluTrayPcs'=>2,'urutan'=>1],
                ['id'=>2,'sku'=>'SIW','nama'=>'Siomay',       'harga'=>15000,'hpp'=>7000,'dimsumPcs'=>4,'aluTrayPcs'=>2,'urutan'=>2],
                ['id'=>3,'sku'=>'HAK','nama'=>'Hakau',        'harga'=>18000,'hpp'=>9000,'dimsumPcs'=>3,'aluTrayPcs'=>2,'urutan'=>3],
                ['id'=>4,'sku'=>'CSP','nama'=>'Ceker Spesial','harga'=>20000,'hpp'=>0,   'dimsumPcs'=>2,'aluTrayPcs'=>1,'urutan'=>4],
                ['id'=>5,'sku'=>'NAS','nama'=>'Nasi Putih',   'harga'=>5000, 'hpp'=>0,   'dimsumPcs'=>0,'aluTrayPcs'=>0,'urutan'=>5],
            ]);
            break;
        case 'get_history':
            echo json_encode([
                ['id'=>1,'waktu'=>date('Y-m-d H:i:s',strtotime('-1 hour')),'petugas'=>'Staff Kasir','cabang'=>'Pusat','items_json'=>json_encode([['nama'=>'Dimsum Ayam','qty'=>1]]),'total'=>15000,'metode'=>'CASH'],
                ['id'=>2,'waktu'=>date('Y-m-d H:i:s',strtotime('-2 hours')),'petugas'=>'Staff Kasir','cabang'=>'Pusat','items_json'=>json_encode([['nama'=>'Hakau','qty'=>1]]),'total'=>18000,'metode'=>'QRIS'],
            ]);
            break;
        case 'get_users':
            echo json_encode(array_map(fn($u) => ['id'=>$u['id'],'username'=>$u['user'],'password'=>$u['pass'],'role'=>$u['role'],'cabang'=>$u['cabang'],'fullName'=>$u['fullName'],'docs_json'=>'{}'], $dummyUsers));
            break;
        case 'get_master_stok':
            echo json_encode([['id'=>1,'nama_item'=>'Tepung'],['id'=>2,'nama_item'=>'Ayam'],['id'=>3,'nama_item'=>'Udang'],['id'=>4,'nama_item'=>'Minyak'],['id'=>5,'nama_item'=>'Bumbu']]);
            break;
        case 'get_roles':
            echo json_encode([['id'=>1,'nama_role'=>'VIP'],['id'=>2,'nama_role'=>'Owner'],['id'=>3,'nama_role'=>'SPV'],['id'=>4,'nama_role'=>'Senior Staff'],['id'=>5,'nama_role'=>'Staff']]);
            break;
        case 'get_omset_harian':
            echo json_encode(['total'=>33000,'jumlah'=>2]);
            break;
        case 'get_bahan_baku':
            echo json_encode([
                ['id'=>1,'nama'=>'Mentai','harga'=>50000,'banyak'=>1000,'satuan'=>'gr','harga_satuan'=>50],
                ['id'=>2,'nama'=>'Ayam','harga'=>30000,'banyak'=>500,'satuan'=>'gr','harga_satuan'=>60],
                ['id'=>3,'nama'=>'Tepung','harga'=>10000,'banyak'=>1000,'satuan'=>'gr','harga_satuan'=>10],
                ['id'=>4,'nama'=>'Tray Aluminium','harga'=>25000,'banyak'=>100,'satuan'=>'pcs','harga_satuan'=>250],
            ]);
            break;
        case 'get_hpp_produk':
            echo json_encode([
                ['id'=>1,'nama_produk'=>'Dimsum Ayam','sku'=>'DMS','harga_pokok'=>8000,'detail_json'=>'[{"bahan_id":2,"nama":"Ayam","qty":100,"satuan":"gr","harga_satuan":60,"subtotal":6000},{"bahan_id":3,"nama":"Tepung","qty":50,"satuan":"gr","harga_satuan":10,"subtotal":500}]'],
                ['id'=>2,'nama_produk'=>'Hakau','sku'=>'HAK','harga_pokok'=>9000,'detail_json'=>'[{"bahan_id":1,"nama":"Mentai","qty":100,"satuan":"gr","harga_satuan":50,"subtotal":5000}]'],
            ]);
            break;
        case 'get_warehouse_stok':
            echo json_encode([]);
            break;
        case 'save_warehouse_stok':
            echo json_encode(['status'=>'success']);
            break;
        case 'save_bahan_baku': case 'del_bahan_baku':
        case 'save_hpp_produk': case 'del_hpp_produk':
            echo json_encode(['status'=>'success']);
            break;
        case 'save_produk': case 'del_produk': case 'update_urutan':
        case 'save_transaksi': case 'del_transaksi': case 'clear_history':
        case 'save_user': case 'del_user':
        case 'save_cabang': case 'del_cabang':
        case 'save_role': case 'del_role':
        case 'save_master_stok': case 'del_master_stok':
        case 'get_laporan_stok': case 'save_laporan_stok': case 'del_laporan_stok':
        case 'get_laporan_restock': case 'save_laporan_restock': case 'del_laporan_restock':
        case 'get_kas_jenis': case 'save_kas_jenis': case 'del_kas_jenis':
        case 'get_kas_data': case 'save_kas_data': case 'del_kas_data':
        case 'get_laporan_history': case 'save_laporan': case 'del_laporan':
            echo json_encode(['status'=>'success']);
            break;
        default:
            echo json_encode(['status'=>'error','message'=>"Action '$action' tidak dikenali (dev mode)."]);
    }
    exit();
}

// ── MODE PRODUCTION: konek MySQL ──────────────────────
$conn = new mysqli("localhost", "u173485424_kurniarp", "Alpukat19#", "u173485424_hoki");
if ($conn->connect_error) {
    die(json_encode(["status"=>"error","message"=>"Koneksi gagal: ".$conn->connect_error]));
}
$conn->set_charset("utf8mb4");

// ── AUTO-CREATE ESSENTIAL TABLES ──────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255) DEFAULT '',
    role VARCHAR(50) DEFAULT 'Staff',
    fullName VARCHAR(200) DEFAULT '',
    cabang VARCHAR(255) DEFAULT '',
    docs_json TEXT,
    session_token VARCHAR(255) DEFAULT ''
)");
$conn->query("CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(20) DEFAULT '',
    nama VARCHAR(150) DEFAULT '',
    harga INT DEFAULT 0,
    hpp INT DEFAULT 0,
    dimsumPcs INT DEFAULT 0,
    aluTrayPcs INT DEFAULT 0,
    urutan INT DEFAULT 0
)");
$conn->query("CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cabang VARCHAR(100) DEFAULT '',
    petugas VARCHAR(100) DEFAULT '',
    total BIGINT DEFAULT 0,
    metode VARCHAR(50) DEFAULT 'CASH',
    items_json TEXT
)");
$conn->query("CREATE TABLE IF NOT EXISTS logs_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    username VARCHAR(100) DEFAULT '',
    role VARCHAR(50) DEFAULT '',
    cabang VARCHAR(100) DEFAULT ''
)");
$conn->query("CREATE TABLE IF NOT EXISTS hoki_cabang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_cabang VARCHAR(100) UNIQUE
)");
$conn->query("CREATE TABLE IF NOT EXISTS bahan_baku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) DEFAULT '',
    harga FLOAT DEFAULT 0,
    banyak FLOAT DEFAULT 0,
    satuan VARCHAR(20) DEFAULT 'gr',
    harga_satuan FLOAT DEFAULT 0
)");
$conn->query("CREATE TABLE IF NOT EXISTS hpp_produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(150) DEFAULT '',
    sku VARCHAR(20) DEFAULT '',
    harga_pokok FLOAT DEFAULT 0
)");
$conn->query("CREATE TABLE IF NOT EXISTS hpp_produk_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hpp_id INT DEFAULT 0,
    bahan_id INT DEFAULT 0,
    qty FLOAT DEFAULT 0,
    subtotal FLOAT DEFAULT 0
)");
$conn->query("CREATE TABLE IF NOT EXISTS hoki_kas_jenis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_jenis VARCHAR(100) UNIQUE
)");
$conn->query("CREATE TABLE IF NOT EXISTS hoki_kas_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user VARCHAR(100) DEFAULT '',
    jenis VARCHAR(100) DEFAULT '',
    nama VARCHAR(200) DEFAULT '',
    qty INT DEFAULT 1,
    mode VARCHAR(20) DEFAULT '',
    nominal BIGINT DEFAULT 0,
    ket TEXT,
    cabang VARCHAR(100) DEFAULT ''
)");
$conn->query("CREATE TABLE IF NOT EXISTS laporan_settlement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    report_id VARCHAR(100) UNIQUE,
    petugas VARCHAR(100) DEFAULT '',
    cabang VARCHAR(100) DEFAULT '',
    metode_json TEXT,
    audit_json TEXT,
    pengeluaran_json TEXT,
    grand_total BIGINT DEFAULT 0
)");
$conn->query("CREATE TABLE IF NOT EXISTS stok_master (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_item VARCHAR(150) UNIQUE
)");
$conn->query("CREATE TABLE IF NOT EXISTS stok_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    report_id VARCHAR(100) DEFAULT '',
    petugas VARCHAR(100) DEFAULT '',
    cabang VARCHAR(100) DEFAULT '',
    items_json TEXT
)");
$conn->query("CREATE TABLE IF NOT EXISTS restock_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    report_id VARCHAR(100) DEFAULT '',
    waktu_teks VARCHAR(100) DEFAULT '',
    petugas VARCHAR(100) DEFAULT '',
    cabang VARCHAR(100) DEFAULT '',
    items_json TEXT
)");
$conn->query("CREATE TABLE IF NOT EXISTS warehouse_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tgl DATE NOT NULL,
    bahan_id INT NOT NULL,
    stok_awal FLOAT DEFAULT 0,
    stok_masuk FLOAT DEFAULT 0,
    UNIQUE KEY uq_wh (tgl, bahan_id)
)");


switch ($action) {

    // ── AUTH ──────────────────────────────────────────
    case 'login':
        $u = $conn->real_escape_string($input['user'] ?? '');
        $p = $conn->real_escape_string($input['pass'] ?? '');
        $res = $conn->query("SELECT * FROM users WHERE LOWER(username)=LOWER('$u') AND password='$p'");
        if ($res && $res->num_rows > 0) {
            $user  = $res->fetch_assoc();
            $token = md5(uniqid($user['username'], true) . time());
            $uid   = (int)$user['id'];
            $conn->query("UPDATE users SET session_token='$token' WHERE id=$uid");
            $fullName = $user['fullName'] ?? $user['fullname'] ?? $user['full_name'] ?? $user['username'];
            echo json_encode([
                "status" => "success",
                "user"   => [
                    "user"     => $user['username'],
                    "fullName" => $fullName,
                    "role"     => $user['role'],
                    "cabang"   => $user['cabang']
                ],
                "token" => $token
            ]);
        } else {
            echo json_encode(["status"=>"error","message"=>"Username atau Password salah!"]);
        }
        break;

    // ── SESSION CHECK ──────────────────────────────────
    case 'check_session':
        $u     = $conn->real_escape_string($_GET['user']  ?? '');
        $token = $conn->real_escape_string($_GET['token'] ?? '');
        if (empty($u) || empty($token)) {
            echo json_encode(["valid"=>false]);
            break;
        }
        $res = $conn->query("SELECT session_token FROM users WHERE LOWER(username)=LOWER('$u')");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            echo json_encode(["valid" => ($row['session_token'] === $token)]);
        } else {
            echo json_encode(["valid"=>false]);
        }
        break;

    // ── CABANG ────────────────────────────────────────
    case 'get_cabang':
    case 'get_branches':
        $res = $conn->query("SELECT id, nama_cabang FROM hoki_cabang ORDER BY nama_cabang ASC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_cabang':
        $nama = $conn->real_escape_string($input['nama'] ?? '');
        if (!empty($nama)) {
            $conn->query("INSERT IGNORE INTO hoki_cabang (nama_cabang) VALUES ('$nama')");
            echo json_encode(["status"=>"success"]);
        } else {
            echo json_encode(["status"=>"error","message"=>"Nama cabang kosong"]);
        }
        break;

    case 'del_cabang':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM hoki_cabang WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    // ── ROLES ─────────────────────────────────────────
    case 'get_roles':
        $res = $conn->query("SELECT * FROM hoki_roles ORDER BY nama_role ASC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_role':
        $nama = $conn->real_escape_string($input['nama'] ?? '');
        if (!empty($nama)) {
            $conn->query("INSERT IGNORE INTO hoki_roles (nama_role) VALUES ('$nama')");
            echo json_encode(["status"=>"success"]);
        } else {
            echo json_encode(["status"=>"error","message"=>"Nama role kosong"]);
        }
        break;

    case 'del_role':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM hoki_roles WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    // ── USERS ─────────────────────────────────────────
    case 'get_users':
        $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_user':
        $u    = $conn->real_escape_string($input['username'] ?? '');
        $p    = $conn->real_escape_string($input['pass'] ?? '');
        $r    = $conn->real_escape_string($input['role'] ?? '');
        $fn   = $conn->real_escape_string($input['fullName'] ?? '');
        $cb   = $conn->real_escape_string($input['cabang'] ?? '');
        $docs = $conn->real_escape_string(json_encode($input['docs'] ?? []));
        $sql  = "REPLACE INTO users (username, password, role, fullName, cabang, docs_json) VALUES ('$u','$p','$r','$fn','$cb','$docs')";
        echo $conn->query($sql)
            ? json_encode(["status"=>"success"])
            : json_encode(["status"=>"error","message"=>$conn->error]);
        break;

    case 'del_user':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM users WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    case 'update_user_cabang':
        $id     = (int)($input['id'] ?? 0);
        $cabang = $conn->real_escape_string($input['cabang'] ?? '');
        echo $conn->query("UPDATE users SET cabang='$cabang' WHERE id=$id")
            ? json_encode(["status"=>"success"])
            : json_encode(["status"=>"error","message"=>$conn->error]);
        break;

    // ── PRODUK ────────────────────────────────────────
    case 'get_produk':
        $res = $conn->query("SELECT * FROM produk ORDER BY urutan ASC, nama ASC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_produk':
        $id     = (int)($input['id'] ?? 0);
        $sku    = $conn->real_escape_string($input['sku']    ?? '');
        $nama   = $conn->real_escape_string($input['nama']   ?? '');
        $harga  = (int)($input['harga']     ?? 0);
        $hpp    = (int)($input['hpp']       ?? 0);
        $dimsum = (int)($input['dimsumPcs'] ?? 0);
        $alu    = (int)($input['aluTrayPcs'] ?? 0);
        if ($id > 0) {
            $sql = "UPDATE produk SET sku='$sku', nama='$nama', harga=$harga, hpp=$hpp, dimsumPcs=$dimsum, aluTrayPcs=$alu WHERE id=$id";
        } else {
            $sql = "INSERT INTO produk (sku, nama, harga, hpp, dimsumPcs, aluTrayPcs) VALUES ('$sku','$nama',$harga,$hpp,$dimsum,$alu)";
        }
        $conn->query($sql);
        echo json_encode(["status"=>"success"]);
        break;

    case 'del_produk':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM produk WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    case 'update_urutan':
        $ids = $input['ids'] ?? [];
        foreach ($ids as $urutan => $id) {
            $id = (int)$id;
            $ur = (int)$urutan + 1;
            $conn->query("UPDATE produk SET urutan=$ur WHERE id=$id");
        }
        echo json_encode(["status"=>"success"]);
        break;

    // ── TRANSAKSI ─────────────────────────────────────
    case 'save_transaksi':
        $cb = $conn->real_escape_string($input['cabang'] ?? '');
        $pt = $conn->real_escape_string($input['petugas'] ?? '');
        $tt = (int)($input['total'] ?? 0);
        $mt = $conn->real_escape_string($input['metode'] ?? '');
        $it = $conn->real_escape_string(json_encode($input['items'] ?? []));
        $sql = "INSERT INTO transaksi (cabang, petugas, total, metode, items_json) VALUES ('$cb','$pt',$tt,'$mt','$it')";
        echo $conn->query($sql)
            ? json_encode(["status"=>"success","id"=>$conn->insert_id])
            : json_encode(["status"=>"error","message"=>$conn->error]);
        break;

    case 'get_history':
        $res = $conn->query("SELECT * FROM transaksi ORDER BY waktu DESC");
        echo json_encode($res ? $res->fetch_all(MYSQLI_ASSOC) : []);
        break;

    case 'del_transaksi':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM transaksi WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    case 'clear_history':
        $conn->query("TRUNCATE TABLE transaksi");
        echo json_encode(["status"=>"success"]);
        break;

    // ── OMSET HARIAN (dashboard) ──────────────────────
    case 'get_omset_harian':
        $cabang = $conn->real_escape_string($_GET['cabang'] ?? '');
        $today  = date('Y-m-d');
        if (empty($cabang) || $cabang === 'Semua') {
            $sql = "SELECT SUM(total) as total, COUNT(*) as jumlah FROM transaksi WHERE DATE(waktu)='$today'";
        } else {
            $sql = "SELECT SUM(total) as total, COUNT(*) as jumlah FROM transaksi WHERE DATE(waktu)='$today' AND cabang='$cabang'";
        }
        $res  = $conn->query($sql);
        $data = $res->fetch_assoc();
        echo json_encode(["total"=>(int)($data['total']??0),"jumlah"=>(int)($data['jumlah']??0)]);
        break;

    // ── LOGIN LOG ─────────────────────────────────────
    case 'add_log':
        $u = $conn->real_escape_string($input['user'] ?? '');
        $r = $conn->real_escape_string($input['role'] ?? '');
        $c = $conn->real_escape_string($input['cabang'] ?? '');
        $conn->query("INSERT INTO logs_login (username, role, cabang) VALUES ('$u','$r','$c')");
        echo json_encode(["status"=>"success"]);
        break;

    case 'get_logs':
        $res = $conn->query("SELECT * FROM logs_login ORDER BY waktu DESC LIMIT 200");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'clear_logs':
        echo $conn->query("TRUNCATE TABLE logs_login")
            ? json_encode(["status"=>"success"])
            : json_encode(["status"=>"error","message"=>$conn->error]);
        break;

    // ── MASTER STOK ───────────────────────────────────
    case 'get_master_stok':
        $res = $conn->query("SELECT * FROM stok_master ORDER BY nama_item ASC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_master_stok':
        $nama = $conn->real_escape_string($input['nama'] ?? '');
        $conn->query("INSERT IGNORE INTO stok_master (nama_item) VALUES ('$nama')");
        echo json_encode(["status"=>"success"]);
        break;

    case 'del_master_stok':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM stok_master WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    // ── LAPORAN STOK ──────────────────────────────────
    case 'get_laporan_stok':
        $role  = $_GET['role'] ?? 'Staff';
        $akses = $_GET['cabang'] ?? '';
        if ($role === 'Owner' || $role === 'VIP' || $akses === 'Semua') {
            $sql = "SELECT * FROM stok_history ORDER BY waktu DESC LIMIT 100";
        } elseif (empty($akses)) {
            echo json_encode([]); break;
        } else {
            $cabangArr  = explode(',', $akses);
            $cleanCabang = array_map(fn($i) => "'".$conn->real_escape_string(trim($i))."'", $cabangArr);
            $cabangList  = implode(',', $cleanCabang);
            $sql = "SELECT * FROM stok_history WHERE cabang IN ($cabangList) ORDER BY waktu DESC LIMIT 50";
        }
        $res = $conn->query($sql);
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_laporan_stok':
        $rid = $conn->real_escape_string($input['report_id'] ?? '');
        $pt  = $conn->real_escape_string($input['petugas'] ?? '');
        $cb  = $conn->real_escape_string($input['cabang'] ?? '');
        $it  = $conn->real_escape_string(json_encode($input['items'] ?? []));
        $sql = "INSERT INTO stok_history (report_id, petugas, cabang, items_json) VALUES ('$rid','$pt','$cb','$it')";
        echo $conn->query($sql)
            ? json_encode(["status"=>"success"])
            : json_encode(["status"=>"error","message"=>$conn->error]);
        break;

    case 'del_laporan_stok':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM stok_history WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    // ── LAPORAN RESTOCK ───────────────────────────────
    case 'get_laporan_restock':
        $role  = $_GET['role'] ?? 'Staff';
        $akses = $_GET['cabang'] ?? '';
        if ($role === 'Owner' || $role === 'VIP' || $akses === 'Semua') {
            $sql = "SELECT * FROM restock_history ORDER BY id DESC LIMIT 100";
        } else {
            $cabangArr  = explode(',', $akses);
            $cleanCabang = array_map(fn($i) => "'".$conn->real_escape_string(trim($i))."'", $cabangArr);
            $cabangList  = implode(',', $cleanCabang);
            $sql = "SELECT * FROM restock_history WHERE cabang IN ($cabangList) ORDER BY id DESC LIMIT 50";
        }
        $res = $conn->query($sql);
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_laporan_restock':
        $rid = $conn->real_escape_string($input['id'] ?? '');
        $wkt = $conn->real_escape_string($input['waktu'] ?? '');
        $pt  = $conn->real_escape_string($input['petugas'] ?? '');
        $cb  = $conn->real_escape_string($input['cabang'] ?? '');
        $it  = $conn->real_escape_string(json_encode($input['items'] ?? []));
        $sql = "INSERT INTO restock_history (report_id, waktu_teks, petugas, cabang, items_json) VALUES ('$rid','$wkt','$pt','$cb','$it')";
        $conn->query($sql);
        echo json_encode(["status"=>"success"]);
        break;

    case 'del_laporan_restock':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM restock_history WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    // ── BUKU KAS / BELANJA ────────────────────────────
    case 'get_kas_jenis':
        $res = $conn->query("SELECT nama_jenis FROM hoki_kas_jenis ORDER BY nama_jenis ASC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_kas_jenis':
        $nama = $conn->real_escape_string($input['nama'] ?? '');
        $conn->query("INSERT IGNORE INTO hoki_kas_jenis (nama_jenis) VALUES ('$nama')");
        echo json_encode(["status"=>"success"]);
        break;

    case 'del_kas_jenis':
        $nama = $conn->real_escape_string($_GET['nama'] ?? '');
        $conn->query("DELETE FROM hoki_kas_jenis WHERE nama_jenis='$nama'");
        echo json_encode(["status"=>"success"]);
        break;

    case 'get_kas_data':
        $role  = $_GET['role'] ?? '';
        $akses = $_GET['cabang'] ?? '';
        if ($role === 'Owner' || $role === 'VIP' || $akses === 'Semua') {
            $sql = "SELECT * FROM hoki_kas_data ORDER BY waktu DESC LIMIT 200";
        } else {
            $cabangArr  = explode(',', $akses);
            $cleanCabang = array_map(fn($i) => "'".$conn->real_escape_string(trim($i))."'", $cabangArr);
            $cabangList  = implode(',', $cleanCabang);
            $sql = "SELECT * FROM hoki_kas_data WHERE cabang IN ($cabangList) ORDER BY waktu DESC LIMIT 100";
        }
        $res = $conn->query($sql);
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_kas_data':
        $wkt = $conn->real_escape_string($input['waktu'] ?? '');
        $usr = $conn->real_escape_string($input['user'] ?? '');
        $jns = $conn->real_escape_string($input['jenis'] ?? '');
        $nam = $conn->real_escape_string($input['nama'] ?? '');
        $qty = (int)($input['qty'] ?? 1);
        $mod = $conn->real_escape_string($input['mode'] ?? '');
        $nom = (int)($input['nominal'] ?? 0);
        $ket = $conn->real_escape_string($input['ket'] ?? '');
        $cab = $conn->real_escape_string($input['cabang'] ?? '');
        $conn->query("INSERT INTO hoki_kas_data (waktu, user, jenis, nama, qty, mode, nominal, ket, cabang) VALUES ('$wkt','$usr','$jns','$nam',$qty,'$mod',$nom,'$ket','$cab')");
        echo json_encode(["status"=>"success"]);
        break;

    case 'del_kas_data':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM hoki_kas_data WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    // ── LAPORAN SETTLEMENT / KEUANGAN ─────────────────
    case 'get_laporan_history':
        $role  = $_GET['role'] ?? 'Staff';
        $akses = $_GET['cabang'] ?? '';
        if ($role === 'Owner' || $role === 'VIP' || $akses === 'Semua') {
            $sql = "SELECT * FROM laporan_settlement ORDER BY waktu DESC";
        } else {
            $cabangArr  = explode(',', $akses);
            $cleanCabang = array_map(fn($i) => "'".$conn->real_escape_string(trim($i))."'", $cabangArr);
            $cabangList  = implode(',', $cleanCabang);
            $sql = "SELECT * FROM laporan_settlement WHERE cabang IN ($cabangList) ORDER BY waktu DESC";
        }
        $res = $conn->query($sql);
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_laporan':
        // FIX: pakai $input bukan $json yang tidak terdefinisi
        $rid     = $conn->real_escape_string($input['report_id'] ?? '');
        $petugas = $conn->real_escape_string($input['petugas'] ?? '');
        $cb      = $conn->real_escape_string($input['cabang'] ?? '');
        $mt      = $conn->real_escape_string(json_encode($input['metode'] ?? []));
        $au      = $conn->real_escape_string(json_encode($input['audit'] ?? []));
        $ex      = $conn->real_escape_string(json_encode($input['expens'] ?? []));
        $tt      = (int)($input['total'] ?? 0);
        $sql = "REPLACE INTO laporan_settlement (report_id, petugas, cabang, metode_json, audit_json, pengeluaran_json, grand_total) VALUES ('$rid','$petugas','$cb','$mt','$au','$ex',$tt)";
        echo $conn->query($sql)
            ? json_encode(["status"=>"success"])
            : json_encode(["status"=>"error","message"=>$conn->error]);
        break;

    case 'del_laporan':
        $rid = $conn->real_escape_string($_GET['id'] ?? '');
        $conn->query("DELETE FROM laporan_settlement WHERE id='$rid'");
        echo json_encode(["status"=>"success"]);
        break;

    // ── BAHAN BAKU ────────────────────────────────────
    case 'get_bahan_baku':
        $res = $conn->query("SELECT * FROM bahan_baku ORDER BY nama ASC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_bahan_baku':
        $id   = (int)($input['id'] ?? 0);
        $nama = $conn->real_escape_string($input['nama'] ?? '');
        $hrg  = (float)($input['harga'] ?? 0);
        $byk  = (float)($input['banyak'] ?? 0);
        $sat  = $conn->real_escape_string($input['satuan'] ?? '');
        $hs   = $byk > 0 ? $hrg / $byk : 0;
        if ($id > 0) {
            $sql = "UPDATE bahan_baku SET nama='$nama', harga=$hrg, banyak=$byk, satuan='$sat', harga_satuan=$hs WHERE id=$id";
        } else {
            $sql = "INSERT INTO bahan_baku (nama, harga, banyak, satuan, harga_satuan) VALUES ('$nama',$hrg,$byk,'$sat',$hs)";
        }
        echo $conn->query($sql)
            ? json_encode(["status"=>"success"])
            : json_encode(["status"=>"error","message"=>$conn->error]);
        break;

    case 'del_bahan_baku':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM bahan_baku WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    // ── HPP PRODUK ────────────────────────────────────
    case 'get_hpp_produk':
        $res  = $conn->query("SELECT h.*, 
            (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                'bahan_id', d.bahan_id,
                'nama', b.nama,
                'qty', d.qty,
                'satuan', b.satuan,
                'harga_satuan', b.harga_satuan,
                'subtotal', d.subtotal
            )) FROM hpp_produk_detail d 
            LEFT JOIN bahan_baku b ON b.id=d.bahan_id 
            WHERE d.hpp_id=h.id) as detail_json
            FROM hpp_produk h ORDER BY h.nama_produk ASC");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'save_hpp_produk':
        $id    = (int)($input['id'] ?? 0);
        $nama  = $conn->real_escape_string($input['nama_produk'] ?? '');
        $sku   = $conn->real_escape_string($input['sku'] ?? '');
        $hpp   = (float)($input['harga_pokok'] ?? 0);
        $bahan = $input['bahan'] ?? [];

        if ($id > 0) {
            $conn->query("UPDATE hpp_produk SET nama_produk='$nama', sku='$sku', harga_pokok=$hpp WHERE id=$id");
            $conn->query("DELETE FROM hpp_produk_detail WHERE hpp_id=$id");
        } else {
            $conn->query("INSERT INTO hpp_produk (nama_produk, sku, harga_pokok) VALUES ('$nama','$sku',$hpp)");
            $id = $conn->insert_id;
        }

        foreach ($bahan as $b) {
            $bid  = (int)($b['bahan_id'] ?? 0);
            $qty  = (float)($b['qty'] ?? 0);
            $sub  = (float)($b['subtotal'] ?? 0);
            $conn->query("INSERT INTO hpp_produk_detail (hpp_id, bahan_id, qty, subtotal) VALUES ($id,$bid,$qty,$sub)");
        }

        // Sync HPP ke tabel produk berdasarkan SKU
        $conn->query("UPDATE produk SET hpp=$hpp WHERE sku='$sku'");

        echo json_encode(["status"=>"success"]);
        break;

    case 'del_hpp_produk':
        $id = (int)($_GET['id'] ?? 0);
        $conn->query("DELETE FROM hpp_produk_detail WHERE hpp_id=$id");
        $conn->query("DELETE FROM hpp_produk WHERE id=$id");
        echo json_encode(["status"=>"success"]);
        break;

    // ── WAREHOUSE STOK ────────────────────────────────
    case 'get_warehouse_stok':
        $tgl = $conn->real_escape_string($_GET['tgl'] ?? date('Y-m-d'));
        $res = $conn->query("SELECT bahan_id, stok_awal, stok_masuk FROM warehouse_stok WHERE tgl='$tgl'");
        echo json_encode($res ? $res->fetch_all(MYSQLI_ASSOC) : []);
        break;

    case 'save_warehouse_stok':
        $tgl   = $conn->real_escape_string($input['tgl'] ?? date('Y-m-d'));
        $items = $input['items'] ?? [];
        $ok    = true;
        foreach ($items as $item) {
            $bid  = (int)($item['bahan_id'] ?? 0);
            $awal = (float)($item['stok_awal'] ?? 0);
            if ($bid <= 0) continue;
            $r = $conn->query("INSERT INTO warehouse_stok (tgl, bahan_id, stok_awal)
                VALUES ('$tgl', $bid, $awal)
                ON DUPLICATE KEY UPDATE stok_awal=$awal");
            if (!$r) $ok = false;
        }
        echo json_encode($ok ? ["status"=>"success"] : ["status"=>"error","message"=>$conn->error]);
        break;

    // ─────────────────────────────────────────────────
    default:
        echo json_encode(["status"=>"error","message"=>"Action '$action' tidak dikenali."]);
        break;
}

$conn->close();
?>