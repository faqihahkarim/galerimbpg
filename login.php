<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MBPG</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', sans-serif; }
    .serif { font-family: 'Playfair Display', serif; }
    .gold-line {
        background: linear-gradient(90deg, transparent, #f5d38b, transparent);
    }
</style>
</head>

<body class="min-h-screen bg-[#15110d] text-white flex items-center justify-center p-4 overflow-hidden">

<div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(202,164,102,.25),transparent_30%),radial-gradient(circle_at_80%_70%,rgba(255,255,255,.08),transparent_25%)]"></div>
<div class="absolute inset-0 opacity-10 bg-[linear-gradient(45deg,transparent_25%,#f5d38b_25%,#f5d38b_26%,transparent_26%,transparent_74%,#f5d38b_74%,#f5d38b_75%,transparent_75%)] bg-[length:70px_70px]"></div>

<div class="relative w-full max-w-6xl grid lg:grid-cols-2 rounded-[2rem] overflow-hidden bg-[#211a14]/90 border border-[#caa466]/30 shadow-2xl">

    <!-- LEFT ART GALLERY PANEL -->
    <div class="hidden lg:flex flex-col justify-between p-10 bg-[#120f0b] relative overflow-hidden">
        <div class="absolute inset-0 opacity-30 bg-[radial-gradient(circle_at_50%_20%,rgba(202,164,102,.35),transparent_35%)]"></div>

        <div class="relative">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#caa466]/40 text-[#f5d38b] text-sm mb-8">
                ✦ Halaman Pengguna Dalaman
            </div>

            <h1 class="serif text-5xl leading-tight text-[#f8ead1]">
                Galeri Seramik &nbsp; Majlis Bandaraya Pasir Gudang
            </h1>

            <div class="gold-line h-px w-64 my-7"></div>

            <p class="mt-5 text-stone-300 leading-relaxed max-w-md">
                Sistem pengurusan dalaman untuk koleksi seramik, rekod galeri, paparan bahan pameran dan pentadbiran kandungan.
            </p>
        </div>

        <div class="relative grid grid-cols-3 gap-4 mt-10">
    
            <div class="h-40 rounded-t-full border border-[#f5d38b]/30 shadow-xl flex items-end p-4 text-xs text-white bg-cover bg-center"
                style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('assets/images/banner2.jpg');">
                Vase
            </div>

            <div class="h-52 rounded-t-full border border-[#f5d38b]/30 shadow-xl flex items-end p-4 text-xs text-white bg-cover bg-center"
                style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('assets/images/galeri.jpg');">
                Porcelain
            </div>

            <div class="h-36 rounded-t-full border border-[#f5d38b]/30 shadow-xl flex items-end p-4 text-xs text-white bg-cover bg-center"
                style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('assets/images/lawatan.jpg');">
                Terracotta
            </div>

        </div>

    </div>

    <!-- RIGHT LOGIN PANEL -->
    <div class="p-8 sm:p-12 flex items-center bg-[#1d1711]/80">
        <div class="w-full max-w-md mx-auto">

            <div class="mb-8 text-center">
                <div class="w-20 h-20 mx-auto rounded-full border border-[#caa466]/60 bg-[#120f0b] flex items-center justify-center mb-5 shadow-lg overflow-hidden p-2">
                    <img src="assets/images/mbpg.png" alt="Logo MBPG" class="w-full h-full object-contain rounded-full">
                </div>
                <h2 class="serif text-4xl text-[#f8ead1]">Selamat Datang</h2>
                <p class="text-stone-400 mt-2">Log Masuk ke Halaman Admin</p>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="mb-4 p-3 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 text-sm">
                    Emel atau kata laluan salah.
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST" class="space-y-5">

                <div>
                    <label class="text-sm text-[#f5d38b]">Emel</label>
                    <input type="email" name="email" required
                        class="w-full mt-2 p-4 rounded-xl bg-[#120f0b] border border-[#caa466]/30 text-white placeholder-stone-500 focus:ring-2 focus:ring-[#caa466] outline-none"
                        placeholder="admin@galeriseramik.com">
                </div>

                <div>
                    <label class="text-sm text-[#f5d38b]">Kata Laluan</label>
                    <input type="password" name="password" required
                        class="w-full mt-2 p-4 rounded-xl bg-[#120f0b] border border-[#caa466]/30 text-white placeholder-stone-500 focus:ring-2 focus:ring-[#caa466] outline-none"
                        placeholder="Masukkan kata laluan">
                </div>

                <div class="flex justify-between text-sm text-stone-400">
                    <label>
                        <input type="checkbox" class="accent-[#caa466]"> Ingat saya
                    </label>
                    <a href="#" class="text-[#f5d38b] hover:underline">Lupa password?</a>
                </div>

                <button type="submit"
                    class="w-full p-4 rounded-xl bg-gradient-to-r from-[#caa466] to-[#f5d38b] text-[#15110d] font-semibold hover:opacity-90 transition shadow-lg">
                    Log Masuk
                </button>

            </form>

            <p class="text-center text-xs text-stone-500 mt-8">
                © 2026 Hak Milik Unit Teknologi Maklumat - MBPG
            </p>

        </div>
    </div>

</div>

</body>
</html>