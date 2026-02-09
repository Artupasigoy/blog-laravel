@extends('dashboard.master')
@section('title', 'All Media')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">All Media</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">All Media</li>
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
                                <div id="selection-actions">
                                    <div class="btn-group mb-3">
                                        <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                            data-toggle="dropdown">
                                            Action
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" id="select-all-item">
                                                <i class="far fa-square mr-1"></i> Select All
                                            </a>
                                            <a class="dropdown-item" href="#" id="unselect-all-item">
                                                <i class="far fa-square mr-1"></i> Unselect All
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#" id="delete-selected-item">
                                                <i class="fas fa-trash mr-1"></i> Delete Selected
                                            </a>
                                        </div>
                                    </div>
                                </div>
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
                                <form action="{{ route('dashboard.media.bulk_destroy') }}" method="POST"
                                    id="bulk-delete-form">
                                    @csrf
                                    <div class="row">
                                        @forelse ($media as $item)
                                            <div
                                                class="col-md-3 mx-auto border p-1 d-flex flex-column justify-content-between position-relative mb-4">
                                                <div class="position-absolute checkbox-wrapper"
                                                    style="top: 5px; left: 5px; z-index: 10;">
                                                    <div class="custom-control custom-checkbox"
                                                        style="transform: scale(1.5); transform-origin: top left;">
                                                        <input class="custom-control-input media-checkbox" type="checkbox"
                                                            name="ids[]" id="media-{{ $item->id }}" value="{{ $item->id }}">
                                                        <label for="media-{{ $item->id }}" class="custom-control-label"></label>
                                                    </div>
                                                </div>
                                                <div class="image position-relative d-flex align-items-center justify-content-center bg-light"
                                                    style="height: 200px; overflow: hidden;">
                                                    <img class="img-fluid" style="max-height: 100%; width: auto;"
                                                        src="{{ route('dashboard.media.thumbnail', $item->id) }}" />
                                                </div>
                                                <div class="p-2 text-center text-muted" style="font-size: 0.8rem;">
                                                    <div class="text-truncate" title="{{ $item->file_name }}">
                                                        <strong>{{ $item->file_name }}</strong>
                                                    </div>
                                                    <div>{{ $item->file_size }}</div>
                                                </div>
                                                <div class="image-footer d-flex justify-content-center text-center mt-2">
                                                    <button type="button" class="btn btn-info btn-sm viewbtn mr-1"
                                                        data-src="{{ asset("uploads/media/" . $item->file_name) }}"
                                                        data-title="{{ $item->file_name }}">
                                                        Lihat
                                                    </button>
                                                    <button type="button" class="btn btn-primary btn-sm copybtn mr-1"
                                                        data-clipboard-text="{{ asset("uploads/media/" . $item->file_name) }}">Copy</button>

                                                    <button type="button" class="btn btn-danger btn-sm deletebtn-single"
                                                        data-id="{{ $item->id }}">Delete</button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="alert alert-danger w-100">No media found!</div>
                                        @endforelse
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer clearfix">
                                <ul class="pagination pagination-sm m-0 float-right">
                                    {{ $media->links() }}
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
    <style>
        .swal2-custom-popup {
            width: auto !important;
            max-width: 60vw !important;
            /* Desktop width */
            padding: 1rem !important;
        }

        .swal2-custom-popup .swal2-image {
            max-height: 80vh !important;
            object-fit: contain !important;
            margin: 0 auto 10px auto !important;
        }

        @media (max-width: 768px) {
            .swal2-custom-popup {
                max-width: 95vw !important;
                /* Mobile width */
                padding: 0.5rem !important;
            }

            .swal2-custom-popup .swal2-image {
                max-height: 60vh !important;
                /* Smaller max-height on mobile */
            }

            .swal2-custom-popup .swal2-title {
                font-size: 1.2rem !important;
            }
        }
    </style>
    <script src="{{ asset("assets/dashboard/plugins/sweetalert2/sweetalert2.all.js") }}"></script>
    <script src="{{ asset("assets/dashboard/plugins/clipboardjs/clipboard.min.js") }}"></script>
    <script>
        var clipboard = new ClipboardJS('.copybtn');
        clipboard.on('success', function (e) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({
                icon: 'success',
                title: 'Link copied to clipboard!'
            });
        });

        // Single Delete
        $('.deletebtn-single').on('click', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a hidden form to submit delete request
                    var form = $('<form action="{{ route("dashboard.media.index") }}/' + id + '" method="POST">' +
                        '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                        '<input type="hidden" name="_method" value="DELETE">' +
                        '</form>');
                    $('body').append(form);
                    form.submit();
                }
            });
        });

        // Selection Mode Logic

        const selectionActions = $('#selection-actions');
        const checkboxes = $('.media-checkbox');
        const selectAllItem = $('#select-all-item');
        const unselectAllItem = $('#unselect-all-item');
        const deleteSelectedItem = $('#delete-selected-item');
        const bulkForm = $('#bulk-delete-form');


        function updateSelectAllIcon() {
            const total = $('.media-checkbox').length;
            const checked = $('.media-checkbox:checked').length;
            const icon = $('#select-all-item i');

            if (total > 0 && total === checked) {
                icon.removeClass('far fa-square').addClass('fas fa-check-square text-primary');
            } else {
                icon.removeClass('fas fa-check-square text-primary').addClass('far fa-square');
            }
        }

        checkboxes.on('change', function () {
            updateSelectAllIcon();
        });

        selectAllItem.on('click', function (e) {
            e.preventDefault();
            checkboxes.prop('checked', true);
            updateSelectAllIcon();
        });

        unselectAllItem.on('click', function (e) {
            e.preventDefault();
            checkboxes.prop('checked', false);
            updateSelectAllIcon();
        });

        deleteSelectedItem.on('click', function (e) {
            e.preventDefault();
            const checkedCount = $('.media-checkbox:checked').length;
            if (checkedCount === 0) {
                Swal.fire({
                    title: 'No items selected',
                    text: "Please select at least one item to delete.",
                    icon: 'warning',
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Delete " + checkedCount + " selected items?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete selected!'
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkForm.submit();
                }
            });
        });


        // View Image
        $('.viewbtn').on('click', function (e) {
            e.preventDefault();
            var src = $(this).data('src');
            var title = $(this).data('title');
            Swal.fire({
                title: title,
                imageUrl: src,
                imageAlt: title,
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal2-custom-popup'
                }
            });
        });
    </script>
@endsection