<?php
session_start();
$conn = mysqli_connect("localhost","root","","perpus");

$menu = isset($_GET['menu']) ? $_GET['menu'] : 'dashboard';

if(isset($_POST['daftar'])){
    mysqli_query($conn,"INSERT INTO users VALUES(
    NULL,'$_POST[nama]',
    '$_POST[username]',
    '$_POST[password]','user')");
    header("Location:index.php?menu=login&msg=daftar_berhasil");
}

if(isset($_POST['login'])){
    $q=mysqli_query($conn,"SELECT * FROM users WHERE username='$_POST[username]' AND password='$_POST[password]'");
    if(mysqli_num_rows($q)>0){
        $d=mysqli_fetch_assoc($q);
        $_SESSION['login']=true; 
        $_SESSION['id']=$d['id']; 
        $_SESSION['role']=$d['role']; 
        $_SESSION['nama']=$d['nama'];
        header("Location:index.php");
    }
}

if($menu=="logout"){ 
    session_destroy(); 
    header("Location:index.php?menu=login"); 
}

if(isset($_GET['pinjam'])){
    $id_buku = $_GET['pinjam'];
    $cek = mysqli_query($conn, "SELECT stok FROM buku WHERE id=$id_buku");
    $b = mysqli_fetch_assoc($cek);
    if($b['stok'] > 0){
        mysqli_query($conn,"INSERT INTO transaksi VALUES(
        NULL,$_SESSION[id],
        $id_buku,CURDATE(),NULL,'dipinjam')");
        mysqli_query($conn,"UPDATE buku SET stok = stok - 1 WHERE id=$id_buku");
        header("Location:index.php?menu=transaksi");
    }
}

if(isset($_GET['kembali'])){
    $id_t = $_GET['kembali'];
    $q_t = mysqli_query($conn, "SELECT buku_id FROM transaksi WHERE id=$id_t");
    $dt = mysqli_fetch_assoc($q_t);
    $id_buku = $dt['buku_id'];
    mysqli_query($conn,"UPDATE transaksi SET status='dikembalikan',tgl_kembali=CURDATE() WHERE id=$id_t");
    mysqli_query($conn,"UPDATE buku SET stok = stok + 1 WHERE id=$id_buku");
    header("Location:index.php?menu=transaksi");
}

if(isset($_POST['tambah_buku'])){
    mysqli_query($conn,"INSERT INTO buku VALUES(
    NULL,'$_POST[judul]',
    '$_POST[pengarang]',
    '$_POST[stok]')");
}

if(isset($_POST['edit_buku'])){
    mysqli_query($conn,"UPDATE buku SET 
    judul='$_POST[judul]', 
    pengarang='$_POST[pengarang]', 
    stok='$_POST[stok]' WHERE id=$_POST[id]");
    header("Location:index.php?menu=buku");
}
if(isset($_GET['hapus_buku'])){
    mysqli_query($conn,"DELETE FROM buku WHERE id=$_GET[hapus_buku]");
}


if(isset($_POST['edit_user'])){
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah password diisi atau tidak
    if(!empty($password)){
        mysqli_query($conn,"UPDATE users SET nama='$nama', username='$username', password='$password' WHERE id=$id");
    } else {
        mysqli_query($conn,"UPDATE users SET nama='$nama', username='$username' WHERE id=$id");
    }
    header("Location:index.php?menu=anggota");
}

if(isset($_GET['hapus_user'])){
    mysqli_query($conn,"DELETE FROM users WHERE id=$_GET[hapus_user]");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A - Peminjaman Buku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">

<nav class="bg-white border-b sticky top-0 z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2 font-bold text-2xl text-indigo-600">
            E-<span>Peminjaman<span class="text-slate-800">Buku</span></span>
        </div>
        <div class="hidden md:flex items-center gap-6 text-slate-600 font-medium">
            <?php if(isset($_SESSION['login'])){ ?>
                <a href="?menu=dashboard" class="hover:text-indigo-600">Dashboard</a>
                <a href="?menu=buku" class="hover:text-indigo-600">Data Buku</a>
                <a href="?menu=transaksi" class="hover:text-indigo-600">Transaksi</a>
                <?php if($_SESSION['role']=='admin'){ ?>
                    <a href="?menu=anggota" class="hover:text-indigo-600">Anggota</a>
     <?php } ?>
        <a href="?menu=logout" class="bg-red-50 text-red-600 px-4 py-2 rounded-full hover:bg-red-100 transition">Logout</a>
            <?php } else { ?>
         <a href="?menu=login" class="hover:text-indigo-600">Masuk</a>
      <a href="?menu=daftar" class="bg-indigo-600 text-white px-6 py-2 rounded-full hover:shadow-lg transition">Gabung Gratis</a>
            <?php } ?>
        </div>
    </div>
</nav>

<div class="container mx-auto px-6 py-10">

    <?php if($menu=="login"){ ?>
    <div class="max-w-md mx-auto bg-white p-10 rounded-3xl shadow-xl border border-slate-100">
        <h3 class="text-3xl font-bold text-slate-800 mb-2 text-center">Masuk</h3>
        <p class="text-slate-500 mb-8 text-center text-sm">Akses koleksi buku digital Anda sekarang.</p>
        <form method="post" class="space-y-4">
      <input name="username" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Username" required>
     <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Password" required>
            <button name="login" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition">Masuk</button>
        </form>
    </div>

    <?php } elseif($menu=="daftar"){ ?>
    <div class="max-w-md mx-auto bg-white p-10 rounded-3xl shadow-xl border border-slate-100">
        <h3 class="text-3xl font-bold text-slate-800 mb-2 text-center">Daftar Akun</h3>
        <form method="post" class="space-y-4">
   <input name="nama" placeholder="Nama Lengkap" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500" required>
   <input name="username" placeholder="Username" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500" required>
  <input type="password" name="password" placeholder="Password" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500" required>
    <button name="daftar" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition">Buat Akun Sekarang</button>
        </form>
    </div>

    <?php } elseif(isset($_SESSION['login']) && $menu=="dashboard"){ ?>
    <div class="bg-indigo-600 rounded-3xl p-10 text-white flex flex-col md:flex-row items-center justify-between shadow-2xl">
        <div>
            <h2 class="text-4xl font-extrabold mb-4">Halo, <?php echo $_SESSION['nama']; ?></h2>
            <p class="text-indigo-100 text-lg max-w-md italic">"Buku adalah jendela dunia. Mari baca buku hari ini."</p>
        </div>
        <div class="mt-8 md:mt-0 flex gap-4">
            <div class="bg-white/10 p-6 rounded-2xl text-center backdrop-blur-md border border-white/20">
                <span class="block text-sm uppercase tracking-widest opacity-80">Hak Akses</span>
                <span class="block text-2xl font-bold uppercase"><?php echo $_SESSION['role']; ?></span>
            </div>
        </div>
    </div>


    <?php } elseif(isset($_SESSION['login']) && $menu=="buku"){ ?>
    <div class="flex flex-col gap-6">
        <?php if($_SESSION['role']=='admin'){ 
            if(isset($_GET['edit'])){
             $res = mysqli_query($conn, "SELECT * FROM buku WHERE id=$_GET[edit]");
         $edit_data = mysqli_fetch_assoc($res);
            ?>
   <div class="bg-indigo-50 p-6 rounded-2xl shadow-sm border border-indigo-200">
     <h4 class="font-bold text-lg mb-4 flex items-center gap-2 text-indigo-700">
      <i class="fas fa-edit"></i> Edit Buku</h4>
  <form method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
  <input name="judul" value="<?= $edit_data['judul'] ?>" class="border p-3 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500" required>
   <input name="pengarang" value="<?= $edit_data['pengarang'] ?>" class="border p-3 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500" required>
   <input name="stok" type="number" value="<?= $edit_data['stok'] ?>" class="border p-3 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500" required>
      <div class="flex gap-2">
         <button name="edit_buku" class="bg-indigo-600 text-white px-4 rounded-xl font-bold flex-1">Update</button>
   <a href="?menu=buku" class="bg-slate-400 text-white px-4 py-3 rounded-xl text-center">Batal</a>
      </div>
         </form>
           </div>
            <?php } else { ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <h4 class="font-bold text-lg mb-4 flex items-center gap-2"> Tambah Buku</h4>
     <form method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input name="judul" placeholder="Judul Buku" class="border p-3 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500" required>
    <input name="pengarang" placeholder="Pengarang" class="border p-3 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500" required>
   <input name="stok" type="number" placeholder="Stok" class="border p-3 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500" required>
  <button name="tambah_buku" class="bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">Simpan</button>
                </form>
            </div>
            <?php } ?>
        <?php } ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-50/50">
                <h3 class="font-bold text-xl">Katalog Buku</h3>
        <form method="get" class="flex gap-2 w-full md:w-auto">
      <input type="hidden" name="menu" value="buku">
   <input name="cari" placeholder="Cari judul/pengarang..." class="border px-4 py-2 rounded-full text-sm outline-none focus:ring-2 focus:ring-indigo-500 w-full">
      <button class="bg-indigo-600 text-white px-4 py-2 rounded-full text-sm"><i class="fas fa-search"></i></button>
         </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4">Info Buku</th>
                            <th class="px-6 py-4 text-center">Stok</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
    <?php
      $cari = isset($_GET['cari']) ? "WHERE judul LIKE '%$_GET[cari]%' OR pengarang LIKE '%$_GET[cari]%'" : "";
   $q = mysqli_query($conn,"SELECT * FROM buku $cari");
     while($b = mysqli_fetch_assoc($q)){
       ?>
         <tr class="hover:bg-slate-50/80 transition">
           <td class="px-6 py-4">
      <div class="font-bold text-slate-800"><?php echo $b['judul']; ?></div>
       <div class="text-sm text-slate-500"><?php echo $b['pengarang']; ?></div>
             </td>
         <td class="px-6 py-4 text-center">
   <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $b['stok'] > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
     <?php echo $b['stok']; ?> Tersedia
              </span>
            </td>
      <td class="px-6 py-4 text-center flex justify-center gap-3">
   <?php if($_SESSION['role']=='user'){ ?>
        <?php if($b['stok'] > 0){ ?>
            <a href="?pinjam=<?php echo $b['id']; ?>" class="bg-indigo-600 text-white px-5 py-2 rounded-full text-sm font-bold hover:bg-indigo-700 transition">Pinjam</a>
              <?php } else { echo "<span class='text-slate-400 text-sm italic'>Habis</span>"; } ?>
   <?php } else { ?>
      <a href="?menu=buku&edit=<?php echo $b['id']; ?>" class="text-indigo-600 hover:text-indigo-800">
    <i class="fas fa-edit"></i></a>
  <a href="?menu=buku&hapus_buku=<?php echo $b['id']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Hapus buku ini?')"><i class="fas fa-trash"></i></a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <?php } elseif(isset($_SESSION['login']) && $menu=="transaksi"){ ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b bg-slate-50/50">
            <h3 class="font-bold text-xl text-slate-800"> Riwayat Pinjam</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">Buku</th>
                        <?php if($_SESSION['role']=='admin') echo "<th class='px-6 py-4'>Peminjam</th>"; ?>
                        <th class="px-6 py-4">Tgl Pinjam</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
   <?php
      $user_cond = $_SESSION['role'] == 'user' ? "WHERE transaksi.user_id = $_SESSION[id]" : "";
    $q = mysqli_query($conn,"SELECT transaksi.*, users.nama, buku.judul FROM transaksi JOIN users ON transaksi.user_id=users.id JOIN buku ON transaksi.buku_id=buku.id $user_cond ORDER BY transaksi.id DESC");
      while($t = mysqli_fetch_assoc($q)){
        $tgl_kembali = ($t['tgl_kembali'] != null) ? $t['tgl_kembali'] : '-';
          ?>
    <tr class="hover:bg-slate-50 transition">
 <td class="px-6 py-4 font-medium"><?php echo $t['judul']; ?></td>
<?php if($_SESSION['role']=='admin') echo "<td class='px-6 py-4 text-slate-600 font-bold'>$t[nama]</td>"; ?>
  <td class="px-6 py-4 text-sm text-slate-500">P: <?php echo $t['tgl_pinjam']; ?><br>K: <?php echo $tgl_kembali; ?></td>
      <td class="px-6 py-4">
          <span class="px-3 py-1 rounded-full text-[10px] font-bold <?php echo $t['status']=='dipinjam' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'; ?>">
        <?php echo strtoupper($t['status']); ?>
             </span>
          </td>
      <td class="px-6 py-4 text-center">
    <?php if($t['status']=='dipinjam'){ ?>
   <a href="?kembali=<?php echo $t['id']; ?>" class="bg-emerald-500 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-emerald-600 transition">KEMBALI</a>
        <?php } else { echo "<span class='text-slate-400 text-xs italic'><i class='fas fa-check-circle'></i> Selesai</span>"; } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>


    <?php } elseif(isset($_SESSION['login']) && $menu=="anggota" && $_SESSION['role']=="admin"){ ?>
    <div class="flex flex-col gap-6">
        <?php 
        if(isset($_GET['edit'])){
            $res = mysqli_query($conn, "SELECT * FROM users WHERE id=$_GET[edit]");
            $edit_user = mysqli_fetch_assoc($res);
        ?>
        <div class="bg-orange-50 p-6 rounded-2xl shadow-sm border border-orange-200">
            <h4 class="font-bold text-lg mb-4 flex items-center gap-2 text-orange-700">
            <i class="fas fa-user-edit"></i> Edit Anggota</h4>
            <form method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
                <input name="nama" value="<?= $edit_user['nama'] ?>" class="border p-3 rounded-xl outline-none" placeholder="Nama Lengkap" required>
                <input name="username" value="<?= $edit_user['username'] ?>" class="border p-3 rounded-xl outline-none" placeholder="Username" required>
                <input type="password" name="password" class="border p-3 rounded-xl outline-none" placeholder="Isi jika ingin ganti password">
                <div class="flex gap-2">
                    <button name="edit_user" class="bg-orange-600 text-white px-4 rounded-xl font-bold flex-1">Update</button>
                    <a href="?menu=anggota" class="bg-slate-400 text-white px-4 py-3 rounded-xl text-center flex-1">Batal</a>
                </div>
            </form>
            <p class="text-xs text-orange-600 mt-2">* Kosongkan password jika tidak ingin mengubahnya.</p>
        </div>
        <?php } ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b bg-slate-50/50 font-bold text-xl">Kelola Anggota</div>
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $q = mysqli_query($conn,"SELECT * FROM users WHERE role='user'");
                    while($u = mysqli_fetch_assoc($q)){
                    ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold"><?php echo $u['nama']; ?></td>
                        <td class="px-6 py-4 text-slate-600">@<?php echo $u['username']; ?></td>
                        <td class="px-6 py-4 text-center flex justify-center gap-4">
     <a href="?menu=anggota&edit=<?php echo $u['id']; ?>" class="text-indigo-600 font-bold text-sm hover:underline"><i class="fas fa-edit"></i></a>
    <a href="?menu=anggota&hapus_user=<?php echo $u['id']; ?>" class="text-red-500 font-bold text-sm hover:underline" onclick="return confirm('Hapus anggota ini?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>

</div>

</body>
</html>