<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsExport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    /**
     * Handle index and search requests.
     * 
     * Author: Amruta Gajanan Shinde
     * Date: 11-11-2024
     * Description: This method handles filtering, sorting, pagination, and caching of item data.
     * It first checks if the requested data is cached. If cached, it retrieves it; otherwise,
     * it fetches data from the database, applies filters, and stores the result in the cache.
     */
    public function index(Request $request)
{
    $cacheKey = $this->getCacheKey($request);
    Log::info('Index Request Parameters:', $request->all());

    if (Cache::has($cacheKey)) {
        Log::info('Cache hit for key: ' . $cacheKey);
        $items = Cache::get($cacheKey);
        Log::info('Data retrieved from Cache: ' . count($items) . ' items');
    } else {
        Log::info('Cache miss for key: ' . $cacheKey);
        $query = Item::query();

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->category . '%');
            Log::info('Applying Category Filter: ' . $request->category);
        }

        // Fetch items with the exact price 7079
        if ($request->filled('price')) {
            $query->where('price', '=', $request->price); // Use '=' for exact match
            Log::info('Applying Price Filter: ' . $request->price);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%')
                  ->orWhere('status', 'like', '%' . $request->search . '%');
            });
            Log::info('Applying Search Filter: ' . $request->search);
        }

        if ($request->filled('sort_by')) {
            $sortDirection = $request->input('sort_direction', 'asc');
            $query->orderBy($request->input('sort_by'), $sortDirection);
            Log::info('Applying Sort: ' . $request->input('sort_by') . ' ' . $sortDirection);
        }

        Log::info('Database Query: ' . $query->toSql(), $query->getBindings());
        $items = $query->paginate(10);
        Log::info('Data fetched from Database: ' . count($items) . ' items');

        Cache::put($cacheKey, $items, now()->addMinutes(60));
        Log::info('Data stored in Cache for key: ' . $cacheKey);
    }

    return view('items.index', compact('items'));
}


    /**
     * Export items to Excel.
     * 
     * Author: Amruta Gajanan Shinde
     * Date: 11-11-2024
     * Description: This method exports the list of items to an Excel file using the ItemsExport class.
     */
    public function exportExcel()
    {
        return Excel::download(new ItemsExport, 'items.xlsx');
    }

    /**
     * Generate a unique cache key based on filters, pagination, and sorting.
     * 
     * Author: Amruta Gajanan Shinde
     * Date: 11-11-2024
     * Description: This helper method generates a unique cache key by serializing the request's query parameters.
     */
    private function getCacheKey(Request $request)
    {
        return 'items_' . md5(serialize($request->query()));
    }

    /**
     * Clear the cache for the current request's filters.
     * 
     * Author: Amruta Gajanan Shinde
     * Date: 11-11-2024
     * Description: This method clears the cache for a given cache key, allowing fresh data to be retrieved on the next request.
     */
    public function clearCache(Request $request)
    {
        $cacheKey = $this->getCacheKey($request);
        Cache::forget($cacheKey);
        Log::info('Cache cleared for key: ' . $cacheKey);

        return response()->json(['status' => 'Cache cleared successfully']);
    }

    /**
     * Export items to CSV based on filters and search criteria.
     * 
     * Author: Amruta Gajanan Shinde
     * Date: 11-11-2024
     * Description: This method generates a CSV file containing items filtered by category, price, and search criteria.
     * It returns the CSV content as a downloadable response.
     */
    public function exportCsv(Request $request)
    {
        $category = $request->input('category');
        $price = $request->input('price');
        $search = $request->input('search');

        $query = Item::query();

        if ($category) {
            $query->where('category', 'LIKE', '%' . $category . '%');
        }

        if ($price) {
            $query->where('price', '=', $price);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('category', 'LIKE', '%' . $search . '%')
                  ->orWhere('status', 'LIKE', '%' . $search . '%');
            });
        }

        $items = $query->get();
        $csvData = "ID,Name,Category,Price,Status\n";
        
        foreach ($items as $item) {
            $csvData .= "{$item->id},{$item->name},{$item->category},{$item->price},{$item->status}\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="items.csv"');
    }
}
