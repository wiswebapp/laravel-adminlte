<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\MasterInterface;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CategoryRepository implements MasterInterface
{
    public function getAll()
    {
        return Category::all();
    }

    /**
     * @param int $except [Single Id to be ignored]
     */
    public function getParentCategory($igore = '')
    {
        $data = Category::whereNull('parent_id');
        if ($igore) {
            $data->where('id', '!=', $igore);
        }

        return $data->get();
    }

    public function getRaw($filterData = "")
    {
        $query = Category::query();
        if (isset($filterData['status'])) {
            $query = $query->where('status', $filterData['status']);
        }
        if ($filterData['category']) {
            $query = $query->where('parent_id', $filterData['category']);
        }

        return $query;
    }

    public function getById($id)
    {
        return Category::findOrFail($id);
    }

    public function delete($id)
    {
        Category::destroy($id);
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update($id, array $newDetails)
    {
        return Category::whereId($id)->update($newDetails);
    }

    public function getAsyncListingData(Request $request)
    {
        $data = $this->getRaw($request?->filterData);
        if (empty($request->order)) {
            $data->latest('id');
        }

        return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', function($row) {
                    return '<a href="'. route('admin.category.edit', $row->id) .'">'. $row->name .'</a>';
                })
                ->editColumn('status', function($row) {
                    return '<button
                        data-id="'. $row->id .'"
                        data-value="'. $row->status .'"
                        data-url="'. route("admin.category.index") .'"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="'. config('constants.default_status_change_txt') .'"
                        class="changestatus btn btn-sm btn-outline-'. ($row->status == "1" ? "success" : "danger") .'"
                    >'. ($row->status == "1" ? "Active" : "InActive") .'</button>' .
                    PHP_EOL;
                })
                ->addColumn('parent', function($row) {
                    return ($row?->parent->name ? $row->parent->name : 'N/A');
                })
                ->addColumn('action', function($row) {
                        return '<div style="width: 150px">' .
                        '<a data-toggle="tooltip" title="'. config('constants.default_edit_txt') .'" href="'. route('admin.category.edit', $row->id) .'" class="edit btn btn-success btn-sm"><i class="fa fa-edit"></i></a>&nbsp;' .
                        '<button data-toggle="tooltip" title="'. config('constants.default_delete_txt') .'" onclick="removeData('. $row->id. ')" class="edit btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>' .
                        '<div>' .
                        PHP_EOL;
                })
                ->rawColumns(['name', 'status', 'action'])
                ->make(true);
    }
}
