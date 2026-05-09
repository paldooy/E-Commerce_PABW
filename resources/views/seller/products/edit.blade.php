@extends('layouts.app')

@section('content')
<div class="glass-card mx-auto max-w-xl rounded-2xl p-6 shadow-sm">
    <h1 class="mb-4 text-2xl font-semibold">Edit Produk</h1>
    <form method="POST" action="{{ route('seller.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="text-sm font-medium text-slate-300">Nama Produk</label>
            <input name="nama_produk" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50" value="{{ $product->nama_produk }}" required />
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Deskripsi</label>
            <textarea name="deskripsi" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50" rows="4" required>{{ $product->deskripsi }}</textarea>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Kategori</label>
            <select name="kategori" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                <option value="Furnitur" style="background:#0f172a" {{ $product->kategori == 'Furnitur' ? 'selected' : '' }}>Furnitur</option>
                <option value="Dapur" style="background:#0f172a" {{ $product->kategori == 'Dapur' ? 'selected' : '' }}>Dapur</option>
                <option value="Dekorasi" style="background:#0f172a" {{ $product->kategori == 'Dekorasi' ? 'selected' : '' }}>Dekorasi</option>
                <option value="Kamar Mandi" style="background:#0f172a" {{ $product->kategori == 'Kamar Mandi' ? 'selected' : '' }}>Kamar Mandi</option>
                <option value="Taman" style="background:#0f172a" {{ $product->kategori == 'Taman' ? 'selected' : '' }}>Taman</option>
                <option value="Lainnya" style="background:#0f172a" {{ $product->kategori == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Foto Produk</label>
            <div id="drop-area" class="mt-1 flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-white/20 bg-white/5 p-6 transition-colors hover:border-emerald-500/50 hover:bg-white/10 {{ $product->url_gambar ? 'hidden' : '' }}">
                <svg class="mb-2 h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                <p class="text-sm text-slate-400"><span class="font-semibold text-emerald-400">Klik untuk upload</span> atau drag and drop</p>
                <p class="mt-1 text-xs text-slate-500">PNG, JPG, GIF up to 2MB. Biarkan kosong jika tidak ingin mengubah.</p>
                <input id="file-input" type="file" name="foto_produk" accept="image/*" class="hidden" />
            </div>
            <div id="preview-container" class="mt-4 {{ $product->url_gambar ? '' : 'hidden' }}">
                <img id="image-preview" src="{{ $product->url_gambar ?? '' }}" class="h-48 w-full rounded-lg object-cover" />
                <button type="button" id="remove-image" class="mt-2 text-sm text-rose-400 hover:text-rose-300">Ubah Gambar</button>
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-slate-300">Harga</label>
                <input type="number" name="harga" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" value="{{ $product->harga }}" required />
            </div>
            <div>
                <label class="text-sm font-medium text-slate-300">Stok</label>
                <input type="number" name="stok" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" value="{{ $product->stok }}" required />
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
</script>
@endsection
