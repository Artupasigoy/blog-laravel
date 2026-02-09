@extends('dashboard.master')
@php
    $pageTitle = match ($currentView ?? 'all') {
        'published' => 'Published Comments',
        'pending' => 'Pending Comments',
        default => 'All Comments'
    };
@endphp
@section('title', $pageTitle)

@section('content')
    <div class="modal fade" id="modal-lg">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Comment Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $pageTitle }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $pageTitle }}</li>
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
                                {{-- WordPress-style tabs --}}
                                <ul class="nav nav-tabs mb-3" style="border-bottom: none;">
                                    <li class="nav-item">
                                        <a href="{{ route('dashboard.comments.index') }}"
                                            class="nav-link {{ ($currentView ?? 'all') == 'all' ? 'active font-weight-bold' : 'text-secondary' }}"
                                            style="border: none; background: transparent;">
                                            All <span class="text-muted">({{ $countAll ?? 0 }})</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <span class="nav-link text-muted px-1">|</span>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('dashboard.comments.index', ['view' => 'published']) }}"
                                            class="nav-link {{ ($currentView ?? 'all') == 'published' ? 'active font-weight-bold' : 'text-secondary' }}"
                                            style="border: none; background: transparent;">
                                            Published <span class="text-muted">({{ $countPublished ?? 0 }})</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <span class="nav-link text-muted px-1">|</span>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('dashboard.comments.index', ['view' => 'pending']) }}"
                                            class="nav-link {{ ($currentView ?? 'all') == 'pending' ? 'active font-weight-bold' : 'text-secondary' }}"
                                            style="border: none; background: transparent;">
                                            Pending <span class="text-muted">({{ $countPending ?? 0 }})</span>
                                        </a>
                                    </li>
                                </ul>
                                <div id="selection-actions" class="{{ $comments->count() > 0 ? '' : 'd-none' }}">
                                    <div class="btn-group mb-3">
                                        <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                            data-toggle="dropdown">
                                            Action
                                        </button>
                                        <div class="dropdown-menu">
                                            @if(($currentView ?? 'all') == 'pending')
                                                <a class="dropdown-item" href="#" id="approve-selected-item">
                                                    <i class="fas fa-check mr-1 text-success"></i> Approve Selected
                                                </a>
                                            @endif
                                            @if(($currentView ?? 'all') == 'published')
                                                <a class="dropdown-item" href="#" id="pending-selected-item">
                                                    <i class="fas fa-clock mr-1 text-warning"></i> Pending Selected
                                                </a>
                                            @endif
                                            <a class="dropdown-item text-danger" href="#" id="delete-selected-item">
                                                <i class="fas fa-trash mr-1"></i> Delete Selected
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ route('dashboard.comments.bulk_action') }}" method="POST"
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
                                                <th class="text-center">Name</th>
                                                <th class="text-center">Type</th>
                                                <th class="text-center">Post</th>
                                                <th class="text-center">Comment</th>
                                                <th class="text-center">Submitted On</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($comments as $comment)
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input class="custom-control-input comment-checkbox" type="checkbox"
                                                                name="ids[]" id="comment-{{ $comment->id }}"
                                                                value="{{ $comment->id }}">
                                                            <label for="comment-{{ $comment->id }}"
                                                                class="custom-control-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">{{ $loop->index + $comments->firstItem() }}</td>
                                                    <td>{{ $comment->user ? $comment->user->name : $comment->name }}</td>
                                                    <td class="text-center"><span
                                                            class="badge bg-{{ $comment->user ? "success" : "warning" }}">{{ $comment->user ? "User" : "Guest" }}</span>
                                                    </td>
                                                    <td class="text-center">{{ $comment->post->title ?? "Deleted" }}</td>
                                                    <td class="text-center">{{ $comment->message }}</td>
                                                    <td class="text-center">
                                                        <div>{{ $comment->created_at->format("d M, Y") }}</div>
                                                        <div>{{ $comment->created_at->format("h:i:s A") }}</div>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('dashboard.comments.status', $comment->id) }}">
                                                            <span
                                                                class="badge bg-{{ $comment->status ? "success" : "warning" }}">{{ $comment->status ? "Published" : "Pending" }}</span>
                                                        </a>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center">
                                                            @if($comment->status == 0)
                                                                <a href="{{ route('dashboard.comments.status', $comment->id) }}"
                                                                    class="btn btn-success btn-sm mr-1">Approve</a>
                                                            @endif
                                                            <a data-href="{{ route("dashboard.comments.show", $comment->id) }}"
                                                                class="btn btn-primary btn-sm mr-1 commentdetails">Details</a>
                                                            <form
                                                                action="{{ route("dashboard.comments.destroy", $comment->id) }}"
                                                                method="POST">
                                                                @method("DELETE")
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-danger btn-sm deletebtn">Delete</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No comments found!</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer clearfix">
                                <ul class="pagination pagination-sm m-0 float-right">
                                    {{ $comments->links() }}
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
        $(".commentdetails").on("click", (e) => {
            $.ajax({
                type: "GET",
                url: e.target.getAttribute("data-href"),
                headers: {
                    Accept: "text/plain; charset=utf-8",
                    "Content-Type": "text/plain; charset=utf-8"
                },
            })
                .done((d) => {
                    $('#modal-lg .modal-body').html(d);
                    $('#modal-lg').modal('show');
                })
                .fail((d) => {
                    $('#modal-lg .modal-body').html(d.responseText);
                    $('#modal-lg').modal('show');
                })
        });
        $('#modal-lg').on('hidden.bs.modal', function () {
            $('#modal-lg .modal-body').html("");
        })
        $('.deletebtn').on('click', function (e) {
            e.preventDefault();
            var form = $(this).parents('form');
            Swal.fire({
                title: 'Are you sure?',
                type: 'warning',
                icon: 'warning',
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

        // Bulk Action Logic
        const masterCheckbox = $('#master-checkbox');
        const checkboxes = $('.comment-checkbox');
        const selectAllItem = $('#select-all-item');
        const unselectAllItem = $('#unselect-all-item');
        const deleteSelectedItem = $('#delete-selected-item');
        const approveSelectedItem = $('#approve-selected-item');
        const bulkForm = $('#bulk-action-form');
        const bulkActionInput = $('#bulk-action-input');

        function updateMasterCheckbox() {
            const allChecked = checkboxes.length > 0 && checkboxes.length === checkboxes.filter(':checked').length;
            masterCheckbox.prop('checked', allChecked);
            updateSelectAllIcon();
        }

        function updateSelectAllIcon() {
            const allChecked = checkboxes.length > 0 && checkboxes.length === checkboxes.filter(':checked').length;
            const icon = selectAllItem.find('i');
            if (allChecked) {
                icon.removeClass('far fa-square').addClass('fas fa-check-square text-primary');
            } else {
                icon.removeClass('fas fa-check-square text-primary').addClass('far fa-square');
            }
        }

        masterCheckbox.on('change', function () {
            checkboxes.prop('checked', $(this).prop('checked'));
        });

        checkboxes.on('change', function () {
            updateMasterCheckbox();
        });

        function submitBulkAction(action, confirmMessage, confirmButtonText, confirmButtonColor) {
            var selectedCount = checkboxes.filter(':checked').length;
            if (selectedCount === 0) {
                Swal.fire('No comments selected', 'Please select at least one comment.', 'warning');
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
            submitBulkAction('delete', "You want to delete {count} selected comments?", 'Yes, delete {count} comments!', '#d33');
        });

        approveSelectedItem.on('click', function (e) {
            e.preventDefault();
            submitBulkAction('approve', "You want to approve {count} selected comments?", 'Yes, approve {count} comments!', '#28a745');
        });

        $('#pending-selected-item').on('click', function (e) {
            e.preventDefault();
            submitBulkAction('pending', "You want to set {count} selected comments to pending?", 'Yes, pending {count} comments!', '#ffc107');
        });

    </script>
@endsection