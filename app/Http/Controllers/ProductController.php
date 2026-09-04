<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('branches')->latest()->paginate(10);

        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = 'PRD-'.strtoupper(substr(uniqid(), -6));
        }

        $validated['unit'] = $validated['unit'] ?? 'kg';
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;

        $product = Product::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke katalog!',
                'product' => $product,
            ], 201);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }
}
