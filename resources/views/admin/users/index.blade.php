@extends('adminlte::page')
@section('title', 'Users')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)
@section('plugins.AdminCustom', true)

@section('content_header')
    <h1>User Module</h1>
    {{ Breadcrumbs::render('users_list') }}
@stop

@section('content')
    <div class="card">
        <form action="{{route('admin.users.index')}}">
            <div class="card-body row">
                <div class="form-group col-md-3">
                    <label>Filter By Status</label>
                    <select name="status" id="filter_status" class="form-control select2">
                        <option value="">Select Status</option>
                        <option {{ request()->query('status') == "1" ? 'selected' : '' }} value="1">Filter By Active</option>
                        <option {{ request()->query('status') == "0" ? 'selected' : '' }} value="0">Filter By InActive</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <button title="Filter Records" class="btn btn-default filter-search"><i class="fa fa-search"></i></button>
                    <a title="Reset" href="{{ route('admin.users.index') }}" class="btn btn-default filter-search"><i class="fas fa-undo"></i> </a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="float-right">
                <a href="{{ route('admin.users.create') }}" class="btn btn-default"><i class="fa fa-plus"></i> Add Data</a>
            </div>
        </div>
        <div class="card-body">
            <table class="ui celled table" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                </tbody>
        </table>
        </div>
    </div>
@stop

@section('js')
<x-alert-msg />

<script>
$(document).ready(function() {
    generateDataTable('{{ route("admin.users.index") }}', [
            {data: 'DT_RowIndex', name: 'id'},
            {data: 'name', name: 'first_name'},
            {data: 'phone', name: 'phone'},
            {data: 'email', name: 'email'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action'},
    ], {'status' : $("#filter_status").val()});

});

function removeData(id) {
    removeDataFromDatabase('{{route("admin.users.index")}}', id);
}
</script>
@stop
