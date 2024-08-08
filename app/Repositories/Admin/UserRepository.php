<?php

namespace App\Repositories\Admin;

use App\Interfaces\BaseAdminModules;
use App\Jobs\WelcomeUserJob;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseAdminModules
{
    public function getAll()
    {
        return User::all();
    }

    public function getRaw($filterData = [])
    {
        $query = User::query();

        if (isset($filterData['status'])) {
            $query = $query->where('status', $filterData['status']);
        }

        return $query;
    }

    public function getById($id)
    {
        return User::findOrFail($id);
    }

    public function delete($id)
    {
        User::destroy($id);
    }

    public function sanitizeData(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if (isset($data['country'])) {
            $country = $data['country'];
            $countryLookup = Country::where('iso2', $country)->first();
            if ($countryLookup) {
                $data['country_id'] = $countryLookup->id;
            }
        }

        unset($data['country']);

        return $data;
    }

    public function create(array $data) {
        try {
            $user = User::create($this->sanitizeData($data));

            if (config('mail.enable_user_mail_on_register')) {
                WelcomeUserJob::dispatch($user);
            }

            return redirect(route('admin.users.index'))->with('success', config('constants.default_data_insert_msg'));
        } catch (\Throwable $th) {
            return redirect(route('admin.users.index'))->with('error', config('constants.default_data_failed_msg'));
        }
    }

    public function update($id, array $newDetails)
    {
        return User::whereId($id)->update($this->sanitizeData($newDetails));
    }

    public function getAsyncListingData(Request $request)
    {
        $data = $this->getRaw($request?->filterData);
        if (empty($request->order)) {
            $data->latest('id');
        }

        return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('cb', function($row) {
                    return '<input type="checkbox" name="multi-select-cb" class="multi-select" data-id="'. $row->id .'">';
                })
                ->addColumn('name', function($row) {
                    return '<a href="'. route('admin.users.edit', $row->id) .'">'. $row->name .'</a>';
                })
                ->editColumn('status', function($row) {
                    return '<button
                        data-id="'. $row->id .'"
                        data-value="'. $row->status .'"
                        data-url="'. route("admin.users.index") .'"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="'. config('constants.default_status_change_txt') .'"
                        class="changestatus btn btn-sm btn-outline-'. ($row->status == "1" ? "success" : "danger") .'"
                    >'. ($row->status == "1" ? "Active" : "InActive") .'</button>' .
                    PHP_EOL;
                })
                ->addColumn('action', function($row){
                        return '<div>' .
                        '<a data-toggle="tooltip" title="'. config('constants.default_edit_txt') .'" href="'. route('admin.users.edit', $row->id) .'" class="edit btn btn-success btn-sm"><i class="fa fa-edit"></i></a>&nbsp;' .
                        '<button data-toggle="tooltip" title="Delete Data" onclick="removeData('. $row->id. ')" class="edit btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>' .
                        '<div>' .
                        PHP_EOL;
                })
                ->rawColumns(['cb', 'name', 'status', 'action'])
                ->make(true);
    }

    public function getTotalCount(array $where = [])
    {
        return User::where($where)->count();
    }
}
