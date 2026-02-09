@extends('dashboard.master')
@section('title', 'All Categories - ' . config('app.name'))

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 d-inline-block">All Categories</h1>
                    <a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary btn-sm ml-2"
                        style="vertical-align: middle;">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">All Categories</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="selection-actions" class="{{ $categories->count() > 0 ? '' : 'd-none' }}">
                                <div class="btn-group mb-3">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                        data-toggle="dropdown">
                                        Action
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#" id="activate-selected-item">
                                            <i class="fas fa-check mr-1 text-success"></i> Activate Selected
                                        </a>
                                        <a class="dropdown-item" href="#" id="deactivate-selected-item">
                                            <i class="fas fa-ban mr-1 text-warning"></i> Deactivate Selected
                                        </a>
                                        <a class="dropdown-item text-danger" href="#" id="delete-selected-item">
                                            <i class="fas fa-trash mr-1"></i> Delete Selected
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <form action="{{ route('dashboard.categories.bulk_action') }}" method="POST"
                                id="bulk-action-form" class="d-none">
                                @csrf
                                <input type="hidden" name="action" id="bulk-action-input">
                                <div id="bulk-ids-container"></div>
                            </form>
                            @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                @foreach ($errors->all() as $error)
                                <p class="m-0">{{ $error }}</p>
                                @endforeach
                            </div>
                            @endif
                            @if (session("success"))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-check"></i> Success!</h5>
                                <p class="m-0">{{ session("success") }}</p>
                            </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;" class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox"
                                                        id="master-checkbox">
                                                    <label for="master-checkbox" class="custom-control-label"></label>
                                                </div>
                                            </th>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Image</th>
                                            <th class="text-center">Title</th>
                                            <th class="text-center">Total Posts</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($categories as $category)
                                        <tr>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input item-checkbox" type="checkbox"
                                                        id="cat-{{ $category->id }}" value="{{ $category->id }}">
                                                    <label for="cat-{{ $category->id }}"
                                                        class="custom-control-label"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $loop->index + $categories->firstItem() }}</td>
                                            <td class="text-center">
                                                <img width="100px" height="100px" src="{{ asset("uploads/category/".($category->image ?? "default.webp")) }}" alt="{{ $category->title }}"/>
                                            </td>
                                            <td class="text-center">{{ $category->title }}</td>
                                            <td class="text-center">{{ $category->posts_count }}</td>
                                            <td class="text-center"><a href="{{ route("dashboard.categories.status", $category->id) }}"><span class="badge bg-{{ $category->status ? "success" : "warning" }}">{{ $category->status ? "Active" : "Inactive" }}</span></a></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    <a target="_blank" href="{{ $category->status ? route("frontend.category", $category->slug) : "" }}" class="btn btn-success {{ $category->status ? "" : " disabled" }}">View</a>
                                                    <a href="{{ route("dashboard.categories.edit", $category->id) }}" class="btn btn-warning">Edit</a>
                                                    <form action="{{ route("dashboard.categories.destroy", $category->id) }}" method="POST">
                                                        @method("DELETE")
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger deletebtn">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No category found!</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <ul class="pagination pagination-sm m-0 float-right">
                            {{ $categories->links() }}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section("script")
<script src="{{ asset("assets/dashboard/plugins/sweetalert2/sweetalert2.all.js") }}"></script>
<script>
$(document).ready(function() {
    var checkboxes = $('.item-checkbox');
    var masterCheckbox = $('#master-checkbox');
    var bulkForm = $('#bulk-action-form');
    var bulkActionInput = $('#bulk-action-input');
    var deleteSelectedItem = $('#delete-selected-item');
    var activateSelectedItem = $('#activate-selected-item');
    var deactivateSelectedItem = $('#deactivate-selected-item');

    // Master checkbox toggle
    masterCheckbox.on('change', function() {
        checkboxes.prop('checked', $(this).is(':checked'));
    });

    // Update master checkbox when individual checkboxes are changed
    checkboxes.on('change', function() {
        var allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        masterCheckbox.prop('checked', allChecked);
    });

    function submitBulkAction(action, confirmMessage, confirmButtonText, confirmButtonColor) {
        var selectedCount = checkboxes.filter(':checked').length;
        if (selectedCount === 0) {
            Swal.fire('No categories selected', 'Please select at least one category.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: confirmMessage.replace('{count}', selectedCount),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor,
            cancelButtonColor: '#3085d6',
            confirmButtonText: confirmButtonText.replace('{count}', selectedCount)
        }).then((result) => {
            if (result.value) {
                bulkActionInput.val(action);

                // Collect IDs
                const idsContainer = $('#bulk-ids-container');
                idsContainer.empty();

                checkboxes.filter(':checked').each(function () {
                    idsContainer.append(`<input type="hidden" name="ids[]" value="${$(this).val()}">`);
                });

                bulkForm.submit();
            }
        });
    }

    deleteSelectedItem.on('click', function (e) {
        e.preventDefault();
        submitBulkAction('delete', "You want to delete {count} selected categories?", 'Yes, delete {count} categories!', '#d33');
    });

    activateSelectedItem.on('click', function (e) {
        e.preventDefault();
        submitBulkAction('activate', "You want to activate {count} selected categories?", 'Yes, activate {count} categories!', '#28a745');
    });

    deactivateSelectedItem.on('click', function (e) {
        e.preventDefault();
        submitBulkAction('deactivate', "You want to deactivate {count} selected categories?", 'Yes, deactivate {count} categories!', '#ffc107');
    });

    // Single delete confirmation
    $('.deletebtn').on('click',function(e){
        e.preventDefault();
        var form = $(this).parents('form');
        Swal.fire({
            title: 'Are you sure?',
            type: 'warning',
            icon: 'warning',
            text: 'All posts of this category will be deleted!',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.value) {
                form.submit();
            }
        });
    });
});
</script>
@endsection