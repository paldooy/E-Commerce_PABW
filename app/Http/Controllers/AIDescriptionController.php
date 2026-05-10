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
            'kategori' => 'nullable',
            'ukuran' => 'nullable',
        ]);

        $nama = $request->input('nama_produk');
        $kategori = $request->input('kategori');
        $ukuran = $request->input('ukuran');

        $prompt = "Buatkan deskripsi produk yang persuasif dan menarik untuk marketplace. Info produk: Nama: $nama, Kategori: $kategori, Ukuran: $ukuran. Buat dalam 1 paragraf saja dalam Bahasa Indonesia.";

        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite-001:generateContent?key=" . $apiKey;
        

        try {
            $response = Http::withoutVerifying()->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7, // Sedikit lebih rendah agar lebih konsisten
                    'maxOutputTokens' => 300,
                ],
                // Tambahkan ini untuk mencegah pemblokiran konten standar
                'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_NONE'
                ],
            ]
            ]);

            $data = $response->json();

            // DEBUG: Jika masih gagal, aktifkan baris di bawah ini untuk melihat error asli dari Google
            // return response()->json($data); 

            // Cara ambil teks yang lebih teliti
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $desc = $data['candidates'][0]['content']['parts'][0]['text'];
            } elseif (isset($data['error'])) {
                $desc = "API Error: " . ($data['error']['message'] ?? 'Kesalahan tidak diketahui');
            } else {
                $desc = "AI tidak memberikan jawaban. Cek filter keamanan atau kuota API.";
            }

            return response()->json([
                'deskripsi' => trim($desc),
            ]);

        } catch (\Exception $e) {
            \Log::error("Gemini AI Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke AI. Pastikan internet stabil.',
            ], 500);
        }
    }
}