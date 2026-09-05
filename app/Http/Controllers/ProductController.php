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

    /**
     * Perbarui data katalog produk dan stok fisik.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,'.$product->id],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $product->update([
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'price' => (float) $validated['price'],
            'unit' => strtolower(trim($validated['unit'])),
            'stock_quantity' => (int) $validated['stock_quantity'],
        ]);

        $primaryBranch = $product->branches()->first();
        if ($primaryBranch) {
            $primaryBranch->update(['quantity' => (int) $validated['stock_quantity']]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data katalog & stok produk [{$product->sku}] {$product->name} berhasil diperbarui.",
                'product' => $product->fresh(['branches']),
            ]);
        }

        return redirect()->route('products.index')->with('success', "Data katalog & stok produk [{$product->sku}] {$product->name} berhasil diperbarui.");
    }
}
