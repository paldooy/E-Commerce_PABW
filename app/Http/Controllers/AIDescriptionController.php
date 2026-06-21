<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIDescriptionController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required',
            'deskripsi_kasar' => 'required',
            'kategori' => 'nullable',
            'foto_produk' => 'required|image|max:2048',
        ]);

        $nama = $request->input('nama_produk');
        $deskripsiKasar = $request->input('deskripsi_kasar');
        $kategori = $request->input('kategori');

        $prompt = "Tulis ulang dan parafrase gambaran kasar berikut menjadi deskripsi produk marketplace yang sangat menarik, rapi, dan persuasif. Hindari kalimat template yang kaku atau pasaran seperti 'Miliki segera' atau 'Dapatkan penawaran'. Gunakan gaya bahasa yang natural, luwes, dan menonjolkan nilai jual produk berdasarkan informasi berikut:\n\nNama Produk: $nama\nKategori: $kategori\nGambaran Kasar: $deskripsiKasar\n\n(Tolong perhatikan foto produk jika ada untuk menambah detail). Buat menjadi 1 paragraf utuh yang mengalir dengan baik dalam Bahasa Indonesia. jangan hanya copy paste deskripsi dari input pengguna, tetapi dibuat ulang dengan makna yang sama, gaya bahasa yang menarik pembeli";

        $apiKey = env('GEMINI_API_KEY');
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key=" . $apiKey;        

        $parts = [
            ['text' => $prompt]
        ];

        if ($request->hasFile('foto_produk')) {
            $file = $request->file('foto_produk');
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $file->getMimeType(),
                    'data' => base64_encode(file_get_contents($file->getRealPath()))
                ]
            ];
        }

        try {
            $response = Http::withoutVerifying()->post($url, [
                'contents' => [
                    ['parts' => $parts]
                ],
                'generationConfig' => [
                    'temperature' => 0.8,
                    'maxOutputTokens' => 300,
                ]
            ]);

            $data = $response->json();

            \Log::info('Gemini status: ' . $response->status());
            \Log::info('Gemini response: ' . json_encode($data));

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return response()->json([
                    'deskripsi' => trim($data['candidates'][0]['content']['parts'][0]['text']),
                ]);
            }


            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return response()->json([
                    'deskripsi' => trim($data['candidates'][0]['content']['parts'][0]['text']),
                ]);
            }

            // Jika API merespon tetapi format salah / limit
            $templates = [
                "Hadirkan sentuhan baru dengan $nama. Keunggulan produk ini: $deskripsiKasar. Sangat cocok untuk Anda yang mengutamakan kualitas di kategori " . strtolower($kategori ?? 'ini') . ".",
                "Jadikan $nama sebagai pilihan utama Anda. Detail produk: $deskripsiKasar. Didesain secara khusus untuk memberikan fungsionalitas dan nilai estetika yang tinggi.",
                "Produk $nama ini merupakan solusi yang tepat. Spesifikasi singkat: $deskripsiKasar. Jangan lewatkan kesempatan untuk mendapatkan produk pilihan ini."
            ];
            $mockDesc = $templates[array_rand($templates)];

            return response()->json([
                'deskripsi' => trim($mockDesc),
            ]);

        } catch (\Exception $e) {
            // Jika API gagal dihubungi sama sekali (tanpa koneksi / tanpa API key)
            $templates = [
                "Hadirkan sentuhan baru dengan $nama. Keunggulan produk ini: $deskripsiKasar. Sangat cocok untuk Anda yang mengutamakan kualitas di kategori " . strtolower($kategori ?? 'ini') . ".",
                "Jadikan $nama sebagai pilihan utama Anda. Detail produk: $deskripsiKasar. Didesain secara khusus untuk memberikan fungsionalitas dan nilai estetika yang tinggi.",
                "Produk $nama ini merupakan solusi yang tepat. Spesifikasi singkat: $deskripsiKasar. Jangan lewatkan kesempatan untuk mendapatkan produk pilihan ini."
            ];
            $mockDesc = $templates[array_rand($templates)];

            return response()->json([
                'deskripsi' => trim($mockDesc),
            ]);
        }
    }
} 