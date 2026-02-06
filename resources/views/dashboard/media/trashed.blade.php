@extends('dashboard.master')
@section('title', 'Trashed Media')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Trashed Media</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.media.index') }}">All Media</a></li>
                            <li class="breadcrumb-item active">Trashed Media</li>
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
                            <div class="card-header">
                                <h3 class="card-title">Trashed Media</h3>
                                <div class="card-tools">
                                    <button class="btn btn-danger btn-sm" id="empty-trash-btn">
                                        <i class="fas fa-trash mr-1"></i> Empty Trash
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
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
                                <div class="row">
                                    @forelse ($media as $item)
                                        <div class="col-md-3 mx-auto border p-1 d-flex flex-column justify-content-between">
                                            <div class="image position-relative">
                                                <img class="img-fluid"
                                                    src="{{ route('dashboard.media.view_trash', $item->id) }}" />
                                            </div>
                                            <div class="p-2 text-center text-muted" style="font-size: 0.8rem;">
                                                <div class="text-truncate" title="{{ $item->file_name }}">
                                                    <strong>{{ $item->file_name }}</strong>
                                                </div>
                                                <div>{{ $item->file_size }}</div>
                                            </div>
                                            <div class="image-footer d-flex justify-content-center text-center mt-2">
                                                <a href="{{ route('dashboard.media.restore', $item->id) }}"
                                                    class="btn btn-success btn-sm mr-1">Restore</a>
                                                <form action="{{ route("dashboard.media.delete", $item->id) }}" method="POST">
                                                    @csrf
                                                    @method("DELETE")
                                                    <button class="btn btn-danger btn-sm deletebtn">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="alert alert-danger w-100">No trashed media found!</div>
                                    @endforelse
                                </div>
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
    <script src="{{ asset("assets/dashboard/plugins/sweetalert2/sweetalert2.all.js") }}"></script>
    <script>
        $('.deletebtn').on('click', function (e) {
            e.preventDefault();
            var form = $(this).parents('form');
            Swal.fire({
                title: 'Are you sure?',
                type: 'warning',
                icon: 'warning',
                text: "You won't be able to revert this!",
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

        $('#empty-trash-btn').on('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete all files in the trash! This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, empty trash!'
            }).then((result) => {
                if (result.value) {
                    var form = $('<form action="{{ route("dashboard.media.empty_trash") }}" method="POST">' +
                        '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                        '<input type="hidden" name="_method" value="DELETE">' +
                        '</form>');
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    </script>
@endsection