@extends('dashboard.master')
@php
    $pageTitle = match ($currentView ?? 'all') {
        'published' => 'Published Posts',
        'draft' => 'Draft Posts',
        default => 'All Posts'
    };
@endphp
@section('title', $pageTitle)

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 d-inline-block">{{ $pageTitle }}</h1>
                        <a href="{{ route('dashboard.posts.create') }}" class="btn btn-primary btn-sm ml-2"
                            style="vertical-align: middle;">
                            <i class="fas fa-plus"></i> Add New
                        </a>
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
                                        <a href="{{ route('dashboard.posts.index') }}"
                                            class="nav-link {{ ($currentView ?? 'all') == 'all' ? 'active font-weight-bold' : 'text-secondary' }}"
                                            style="border: none; background: transparent;">
                                            All <span class="text-muted">({{ $countAll ?? 0 }})</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <span class="nav-link text-muted px-1">|</span>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('dashboard.posts.index', ['view' => 'published']) }}"
                                            class="nav-link {{ ($currentView ?? 'all') == 'published' ? 'active font-weight-bold' : 'text-secondary' }}"
                                            style="border: none; background: transparent;">
                                            Published <span class="text-muted">({{ $countPublished ?? 0 }})</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <span class="nav-link text-muted px-1">|</span>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('dashboard.posts.index', ['view' => 'draft']) }}"
                                            class="nav-link {{ ($currentView ?? 'all') == 'draft' ? 'active font-weight-bold' : 'text-secondary' }}"
                                            style="border: none; background: transparent;">
                                            Draft <span class="text-muted">({{ $countDraft ?? 0 }})</span>
                                        </a>
                                    </li>
                                </ul>
                                <div id="selection-actions" class="{{ $posts->count() > 0 ? '' : 'd-none' }}">
                                    <div class="btn-group mb-3">
                                        <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                            data-toggle="dropdown">
                                            Action
                                        </button>
                                        <div class="dropdown-menu">
                                            @if(Auth::user()->role != 1)
                                                @if(($currentView ?? 'all') == 'draft')
                                                    <a class="dropdown-item" href="#" id="publish-selected-item">
                                                        <i class="fas fa-check mr-1 text-success"></i> Publish Selected
                                                    </a>
                                                @endif
                                                @if(($currentView ?? 'all') == 'published')
                                                    <a class="dropdown-item" href="#" id="draft-selected-item">
                                                        <i class="fas fa-times mr-1 text-warning"></i> Draft Selected
                                                    </a>
                                                @endif
                                            @endif
                                            <a class="dropdown-item text-danger" href="#" id="delete-selected-item">
                                                <i class="fas fa-trash mr-1"></i> Delete Selected
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ route('dashboard.posts.bulk_action') }}" method="POST"
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
                                                <th class="text-center">Title</th>
                                                <th class="text-center">Author</th>
                                                <th class="text-center">Category</th>
                                                <th class="text-center">Tags</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Featured</th>
                                                <th class="text-center">Comment Status</th>
                                                <th class="text-center">Views</th>
                                                <th class="text-center">Comment Count</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($posts as $post)
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input class="custom-control-input post-checkbox" type="checkbox"
                                                                id="post-{{ $post->id }}" value="{{ $post->id }}">
                                                            <label for="post-{{ $post->id }}"
                                                                class="custom-control-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">{{ $loop->index + $posts->firstItem() }}</td>
                                                    <td>{{ $post->title }}</td>
                                                    <td class="text-center">{{ $post->user->name }}</td>
                                                    <td class="text-center">{{ $post->category->title }}</td>
                                                    <td class="text-center">
                                                        @forelse ($post->tags as $tag)
                                                            <span class="badge bg-primary">{{ $tag->name }}</span>
                                                        @empty
                                                            <span class="badge bg-danger">Empty</span>
                                                        @endforelse
                                                    </td>
                                                    <td class="text-center"><a
                                                            href="{{ route("dashboard.posts.status", $post->id) }}"><span
                                                                class="badge bg-{{ $post->status ? "success" : "danger" }}">{{ $post->status ? "Published" : "Draft" }}</span></a>
                                                    </td>
                                                    <td class="text-center"><a
                                                            href="{{ route("dashboard.posts.featured", $post->id) }}"><span
                                                                class="badge bg-{{ $post->is_featured ? "success" : "danger" }}">{{ $post->is_featured ? "Yes" : "No" }}</span></a>
                                                    </td>
                                                    <td class="text-center"><a
                                                            href="{{ route("dashboard.posts.comment", $post->id) }}"><span
                                                                class="badge bg-{{ $post->enable_comment ? "success" : "danger" }}">{{ $post->enable_comment ? "Enable" : "Disable" }}</span></a>
                                                    </td>
                                                    <td class="text-center">{{ $post->views }}</td>
                                                    <td class="text-center">{{ $post->comments_count }}</td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center">
                                                            <a target="_blank"
                                                                href="{{ $post->status ? route("frontend.post", $post->slug) : "" }}"
                                                                class="btn btn-success {{ $post->status ? "" : " disabled" }}">View</a>
                                                            <a href="{{ route("dashboard.posts.edit", $post->id) }}"
                                                                class="btn btn-warning">Edit</a>
                                                            <form action="{{ route("dashboard.posts.destroy", $post->id) }}"
                                                                method="POST">
                                                                @method("DELETE")
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-danger deletebtn">Delete</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center">No post found!</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer clearfix">
                                <ul class="pagination pagination-sm m-0 float-right">
                                    {{ $posts->links() }}
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
        $('.deletebtn').on('click', function (e) {
            e.preventDefault();
            var form = $(this).parents('form');
            Swal.fire({
                title: 'Are you sure?',
                type: 'warning',
                icon: 'warning',
                text: 'All comments of this post will delete!',
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
        $('#master-checkbox').click(function () {
            $('.post-checkbox').prop('checked', this.checked);
        });

        $('.post-checkbox').change(function () {
            if ($('.post-checkbox:checked').length == $('.post-checkbox').length) {
                $('#master-checkbox').prop('checked', true);
            } else {
                $('#master-checkbox').prop('checked', false);
            }
        });

        $('#select-all-item').click(function (e) {
            e.preventDefault();
            $('.post-checkbox').prop('checked', true);
            $('#master-checkbox').prop('checked', true);
        });

        $('#unselect-all-item').click(function (e) {
            e.preventDefault();
            $('.post-checkbox').prop('checked', false);
            $('#master-checkbox').prop('checked', false);
        });

        function submitBulkAction(action) {
            var selectedIds = [];
            $('.post-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Post Selected',
                    text: 'Please select at least one post!',
                });
                return;
            }

            var actionText = action.charAt(0).toUpperCase() + action.slice(1);
            var confirmText = 'Yes, ' + action + ' ' + selectedIds.length + ' posts!';
            var confirmColor = '#3085d6';

            if (action === 'delete') {
                confirmColor = '#d33';
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to " + action + " " + selectedIds.length + " selected posts?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#3085d6',
                confirmButtonText: confirmText
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    var form = $('#bulk-action-form');
                    $('#bulk-action-input').val(action);

                    // Clear previous inputs
                    $('#bulk-ids-container').html('');

                    selectedIds.forEach(function (id) {
                        $('#bulk-ids-container').append('<input type="hidden" name="ids[]" value="' + id + '">');
                    });
                    form.submit();
                }
            });
        }

        $('#delete-selected-item').click(function (e) {
            e.preventDefault();
            submitBulkAction('delete');
        });

        $('#publish-selected-item').click(function (e) {
            e.preventDefault();
            submitBulkAction('publish');
        });

        $('#draft-selected-item').click(function (e) {
            e.preventDefault();
            submitBulkAction('draft');
        });
    </script>
@endsection