<?php

namespace App\Http\Controllers;

use App\Models\ProductionRequest;

class ProductionRequestController extends Controller
{
    public function index()
    {
        $productionRequests = ProductionRequest::with(['salesOrder.customer'])->latest()->paginate(10);
        return view('production-requests.index', compact('productionRequests'));
    }

    public function show(ProductionRequest $productionRequest)
    {
        $productionRequest->load(['salesOrder.customer', 'salesOrder.items.product']);
        return view('production-requests.show', compact('productionRequest'));
    }
}
