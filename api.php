<?php
/**
 * =====================================================
 *  HOKI POS — api_local.php
 *  Mode   : LOCAL DEVELOPMENT (tanpa database)
 *  Gunakan: Ganti nama file ini jadi api.php saat
 *           development di XAMPP/Laragon lokal.
 *  JANGAN upload file ini ke server production!
 * =====================================================
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

// ─── DUMMY DATA ───────────────────────────────────────
$dummyUsers = [
    ['user' => 'admin',  'pass' => 'admin',  'role' => 'VIP',          'cabang' => 'Semua'],
    ['user' => 'owner',  'pass' => 'owner',  'role' => 'Owner',        'cabang' => 'Semua'],
    ['user' => 'spv1',   'pass' => 'spv1',   'role' => 'SPV',          'cabang' => 'Pusat'],
    ['user' => 'senior', 'pass' => 'senior', 'role' => 'Senior Staff', 'cabang' => 'Pusat,Cabang A'],
    ['user' => 'staff1', 'pass' => 'staff1', 'role' => 'Staff',        'cabang' => 'Pusat,Cabang A,Cabang B'],
];

$dummyCabang = [
    ['id' => 1, 'nama_cabang' => 'Pusat'],
    ['id' => 2, 'nama_cabang' => 'Cabang A'],
    ['id' => 3, 'nama_cabang' => 'Cabang B'],
];

$dummyProduk = [
    ['id'=>1, 'sku'=>'DMS', 'nama'=>'Dimsum Ayam',    'harga'=>15000, 'dimsumPcs'=>4, 'aluTrayPcs'=>2, 'urutan'=>1],
    ['id'=>2, 'sku'=>'SIW', 'nama'=>'Siomay',         'harga'=>15000, 'dimsumPcs'=>4, 'aluTrayPcs'=>2, 'urutan'=>2],
    ['id'=>3, 'sku'=>'HAK', 'nama'=>'Hakau',          'harga'=>18000, 'dimsumPcs'=>3, 'aluTrayPcs'=>2, 'urutan'=>3],
    ['id'=>4, 'sku'=>'CSP', 'nama'=>'Ceker Spesial',  'harga'=>20000, 'dimsumPcs'=>2, 'aluTrayPcs'=>1, 'urutan'=>4],
    ['id'=>5, 'sku'=>'BKO', 'nama'=>'Bakso Goreng',   'harga'=>12000, 'dimsumPcs'=>5, 'aluTrayPcs'=>2, 'urutan'=>5],
    ['id'=>6, 'sku'=>'LWT', 'nama'=>'Lumpia Wartel',  'harga'=>13000, 'dimsumPcs'=>3, 'aluTrayPcs'=>2, 'urutan'=>6],
    ['id'=>7, 'sku'=>'NAS', 'nama'=>'Nasi Putih',     'harga'=>5000,  'dimsumPcs'=>0, 'aluTrayPcs'=>0, 'urutan'=>7],
    ['id'=>8, 'sku'=>'TCH', 'nama'=>'Teh Manis',      'harga'=>5000,  'dimsumPcs'=>0, 'aluTrayPcs'=>0, 'urutan'=>8],
];

$dummyHistory = [
    ['id'=>1, 'waktu'=>date('Y-m-d H:i:s', strtotime('-1 hour')),  'petugas'=>'staff1', 'cabang'=>'Pusat',    'items_json'=>json_encode([['nama'=>'Dimsum Ayam'],['nama'=>'Teh Manis']]), 'total'=>20000, 'metode'=>'CASH'],
    ['id'=>2, 'waktu'=>date('Y-m-d H:i:s', strtotime('-2 hours')), 'petugas'=>'staff1', 'cabang'=>'Pusat',    'items_json'=>json_encode([['nama'=>'Hakau'],['nama'=>'Siomay']]),          'total'=>33000, 'metode'=>'QRIS'],
    ['id'=>3, 'waktu'=>date('Y-m-d H:i:s', strtotime('-3 hours')), 'petugas'=>'senior', 'cabang'=>'Cabang A', 'items_json'=>json_encode([['nama'=>'Ceker Spesial']]),                      'total'=>20000, 'metode'=>'GF'],
];

$dummyLogs = [
    ['id'=>1, 'waktu'=>date('Y-m-d H:i:s', strtotime('-10 minutes')), 'username'=>'admin',  'role'=>'VIP',   'cabang'=>'Semua'],
    ['id'=>2, 'waktu'=>date('Y-m-d H:i:s', strtotime('-1 hour')),     'username'=>'staff1', 'role'=>'Staff', 'cabang'=>'Pusat'],
];

// ─── SWITCH ───────────────────────────────────────────
switch ($action) {

    case 'login':
        $u = $input['user'] ?? '';
        $p = $input['pass'] ?? '';
        $found = null;
        foreach ($dummyUsers as $user) {
            if ($user['user'] === $u && $user['pass'] === $p) {
                $found = $user;
                break;
            }
        }
        if ($found) {
            echo json_encode([
                'status' => 'success',
                'user'   => ['user' => $found['user'], 'role' => $found['role'], 'cabang' => $found['cabang']]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah! (Mode lokal)']);
        }
        break;

    case 'get_cabang':
    case 'get_branches':
        echo json_encode($dummyCabang);
        break;

    case 'get_produk':
        echo json_encode($dummyProduk);
        break;

    case 'save_produk':
        echo json_encode(['status' => 'success']);
        break;

    case 'del_produk':
        echo json_encode(['status' => 'success']);
        break;

    case 'update_urutan':
        echo json_encode(['status' => 'success']);
        break;

    case 'save_transaksi':
        echo json_encode(['status' => 'success', 'id' => rand(100, 999)]);
        break;

    case 'get_history':
        echo json_encode($dummyHistory);
        break;

    case 'del_transaksi':
        echo json_encode(['status' => 'success']);
        break;

    case 'clear_history':
        echo json_encode(['status' => 'success']);
        break;

    case 'add_log':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_logs':
        echo json_encode($dummyLogs);
        break;

    case 'clear_logs':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_users':
        $out = array_map(fn($u) => [
            'id' => array_search($u, $dummyUsers) + 1,
            'username' => $u['user'], 'role' => $u['role'],
            'cabang' => $u['cabang'], 'fullName' => ucfirst($u['user']),
            'docs_json' => '[]'
        ], $dummyUsers);
        echo json_encode($out);
        break;

    case 'save_user':
    case 'del_user':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_master_stok':
        echo json_encode([
            ['id'=>1,'nama_item'=>'Tepung'],
            ['id'=>2,'nama_item'=>'Ayam'],
            ['id'=>3,'nama_item'=>'Udang'],
        ]);
        break;

    case 'save_master_stok':
    case 'del_master_stok':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_laporan_stok':
        echo json_encode([]);
        break;

    case 'save_laporan_stok':
    case 'del_laporan_stok':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_laporan_restock':
        echo json_encode([]);
        break;

    case 'save_laporan_restock':
    case 'del_laporan_restock':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_kas_jenis':
        echo json_encode([
            ['nama_jenis' => 'Penjualan'],
            ['nama_jenis' => 'Belanja Bahan'],
            ['nama_jenis' => 'Operasional'],
        ]);
        break;

    case 'save_kas_jenis':
    case 'del_kas_jenis':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_kas_data':
        echo json_encode([]);
        break;

    case 'save_kas_data':
    case 'del_kas_data':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_laporan_history':
        echo json_encode([]);
        break;

    case 'save_laporan':
        echo json_encode(['status' => 'success']);
        break;

    case 'get_roles':
        echo json_encode([
            ['id'=>1,'nama_role'=>'VIP'],
            ['id'=>2,'nama_role'=>'Owner'],
            ['id'=>3,'nama_role'=>'SPV'],
            ['id'=>4,'nama_role'=>'Senior Staff'],
            ['id'=>5,'nama_role'=>'Staff'],
        ]);
        break;

    case 'save_role':
    case 'del_role':
        echo json_encode(['status' => 'success']);
        break;

    case 'save_cabang':
    case 'del_cabang':
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => "Action '$action' tidak dikenali di mode lokal."]);
        break;
}
?>