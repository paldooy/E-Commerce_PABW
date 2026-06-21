@extends('layouts.app')

@section('content')
<div class="glass-card mx-auto max-w-xl rounded-2xl p-6 shadow-sm">
    <h1 class="mb-4 text-2xl font-semibold">Tambah Produk</h1>
    <form method="POST" action="{{ route('seller.products.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="text-sm font-medium text-slate-300">Foto Produk</label>
            <div id="drop-area" class="mt-1 flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-white/20 bg-white/5 p-6 transition-colors hover:border-emerald-500/50 hover:bg-white/10">
                <svg class="mb-2 h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                <p class="text-sm text-slate-400"><span class="font-semibold text-emerald-400">Klik untuk upload</span> atau drag and drop</p>
                <p class="mt-1 text-xs text-slate-500">PNG, JPG, GIF up to 2MB</p>
                <input id="file-input" type="file" name="foto_produk" accept="image/*" class="hidden" required />
            </div>
            <div id="preview-container" class="mt-4 hidden">
                <img id="image-preview" class="h-48 w-full rounded-lg object-cover" />
                <button type="button" id="remove-image" class="mt-2 text-sm text-rose-400 hover:text-rose-300">Hapus Gambar</button>
            </div>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Nama Produk</label>
            <input name="nama_produk" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50" required />
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Deskripsi</label>
            <div class="flex gap-2">
                <textarea id="deskripsi" name="deskripsi" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50" rows="4" placeholder="Gambaran kasar tentang produk" required></textarea>
                <button type="button" id="generate-desc" class="glass-button glass-button-emerald h-fit px-3 py-1 text-xs font-semibold">AI Generate</button>
            </div>
            <p id="desc-loading" class="text-xs text-emerald-400 mt-1 hidden">Menghasilkan deskripsi persuasif AI...</p>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Kategori</label>
            <select name="kategori" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                <option value="Furnitur" style="background:#0f172a">Furnitur</option>
                <option value="Dapur" style="background:#0f172a">Dapur</option>
                <option value="Dekorasi" style="background:#0f172a">Dekorasi</option>
                <option value="Kamar Mandi" style="background:#0f172a">Kamar Mandi</option>
                <option value="Taman" style="background:#0f172a">Taman</option>
                <option value="Lainnya" style="background:#0f172a">Lainnya</option>
            </select>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-slate-300">Harga</label>
                <input type="number" name="harga" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" required />
            </div>
            <div>
                <label class="text-sm font-medium text-slate-300">Stok</label>
                <input type="number" name="stok" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" required />
            </div>
        </div>
        <button class="glass-button glass-button-emerald w-full rounded-lg px-4 py-2 font-semibold" type="submit">Simpan</button>
    </form>
</div>

<script>
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const removeImage = document.getElementById('remove-image');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('border-emerald-500/50', 'bg-white/10'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('border-emerald-500/50', 'bg-white/10'), false);
    });

    dropArea.addEventListener('drop', handleDrop, false);
    dropArea.addEventListener('click', () => fileInput.click());

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if(files.length) {
            fileInput.files = files;
            showPreview(files[0]);
        }
    }

    fileInput.addEventListener('change', function() {
        if(this.files && this.files[0]) {
            showPreview(this.files[0]);
        }
    });

    function showPreview(file) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                previewContainer.classList.remove('hidden');
                dropArea.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    removeImage.addEventListener('click', () => {
        fileInput.value = '';
        imagePreview.src = '';
        previewContainer.classList.add('hidden');
        dropArea.classList.remove('hidden');
    });
// AI Generate Deskripsi dengan Tombol
const namaInput = document.querySelector('input[name="nama_produk"]');
const textarea = document.getElementById('deskripsi');
const loading = document.getElementById('desc-loading');
const csrfToken = document.querySelector('meta[name="csrf-token"]');
const kategoriInput = document.querySelector('select[name="kategori"]');
const generateBtn = document.getElementById('generate-desc');

generateBtn.addEventListener('click', async function() {
    const nama = namaInput.value.trim();
    const deskripsiKasar = textarea.value.trim();
    const file = fileInput.files.length > 0 ? fileInput.files[0] : null;

    if (!file) {
        alert('Tolong unggah Foto Produk terlebih dahulu!');
        return;
    }
    if (!nama) {
        alert('Tolong isi Nama Produk terlebih dahulu!');
        namaInput.focus();
        return;
    }
    if (!deskripsiKasar) {
        alert('Tolong isi gambaran kasar di Deskripsi terlebih dahulu!');
        textarea.focus();
        return;
    }

    loading.classList.remove('hidden');
    textarea.disabled = true;
    generateBtn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('nama_produk', nama);
        formData.append('deskripsi_kasar', deskripsiKasar);
        formData.append('kategori', kategoriInput.value);
        formData.append('foto_produk', file);

        const res = await fetch('{{ route("seller.products.generateAI") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();

        if (res.ok) {
            textarea.value = data.deskripsi || '';
        } else {
            console.error('Gagal:', data.message);
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan sistem AI.'));
        }
    } catch (e) {
        console.error(e);
        alert('Gagal menghubungkan ke server. Cek koneksi internet atau server Laravel kamu.');
    } finally {
        loading.classList.add('hidden');
        textarea.disabled = false;
        generateBtn.disabled = false;
    }
});

</script>
@endsection
