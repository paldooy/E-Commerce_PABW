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
        
        // Dikembangkan ke endpoint v1beta dan model 2.0 yang tervalidasi ada di akunmu
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite-001:generateContent?key=" . $apiKey;        

        try {
            $response = Http::withoutVerifying()->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 300,
                ]
            ]);

            $data = $response->json();

            // Jika sukses mendapatkan respon teks dari Google Gemini
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return response()->json([
                    'deskripsi' => trim($data['candidates'][0]['content']['parts'][0]['text']),
                ]);
            }

            // FALLBACK: Tulisan "(Generated via Local Backup AI)" sudah dihapus dari sini
            $mockDesc = "Miliki segera $nama kualitas terbaik untuk kategori " . ($kategori ?? 'Umum') . "! Produk ini dirancang dengan material premium yang kokoh, fungsional, dan memiliki estetika modern yang sangat cocok untuk memenuhi kebutuhan Anda. Dapatkan penawaran harga terbaik hanya di HomeSupply.co sekarang juga!";

            return response()->json([
                'deskripsi' => trim($mockDesc),
            ]);

        } catch (\Exception $e) {
            // FALLBACK: Tulisan "(Generated via Local Backup AI)" juga sudah dihapus dari sini
            $mockDesc = "Miliki segera $nama kualitas terbaik untuk kategori " . ($kategori ?? 'Umum') . "! Produk ini dirancang dengan material premium yang kokoh, fungsional, dan memiliki estetika modern yang sangat cocok untuk memenuhi kebutuhan Anda. Dapatkan penawaran harga terbaik hanya di HomeSupply.co sekarang juga!";

            return response()->json([
                'deskripsi' => trim($mockDesc),
            ]);
        }
    }
} 