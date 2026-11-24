<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function page(Request $request): View
    {
        $filters = $request->validate([
            'busca' => ['nullable', 'string', 'max:100'],
            'situacao' => ['nullable', Rule::in([...Warehouse::SITUATIONS, 'Todas'])],
        ]);
        $search = $filters['busca'] ?? '';
        $situation = $filters['situacao'] ?? Warehouse::ACTIVE;
        $warehouses = Warehouse::query()->with('requester:id,name')
            ->when($situation !== 'Todas', fn (Builder $query) => $query->where('warehouse_situation', $situation))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $match) use ($search): void {
                $match->whereLike('warehouse_name', '%'.$search.'%')->orWhereLike('warehouse_description', '%'.$search.'%');
                if (ctype_digit($search)) {
                    $match->orWhere('warehouse_id', $search);
                }
            }))
            ->orderByDesc('warehouse_id')->paginate(10)->withQueryString();

        return view('warehouses', [
            'warehouses' => $warehouses, 'search' => $search, 'situation' => $situation,
            'situations' => array_combine(Warehouse::SITUATIONS, Warehouse::SITUATIONS),
        ]);
    }

    public function index(): JsonResponse
    {
        return response()->json(Warehouse::with('inventories.product')->get());
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return response()->json($warehouse->load('inventories.product'));
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse|JsonResponse
    {
        $warehouse = new Warehouse($request->validated());
        $warehouse->warehouse_situation = Warehouse::REQUESTED;
        $warehouse->requester()->associate($request->user());
        $warehouse->save();

        $message = 'Solicitação de armazém enviada para aprovação.';
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message, 'data' => new WarehouseResource($warehouse)], 201);
        }

        return to_route('warehouses.index', ['situacao' => Warehouse::REQUESTED])->with('status', $message);
    }
}
