@extends('dashboard.master')
@section('title', 'New Category - ' . config('app.name'))

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">New Category</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route("dashboard.home") }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route("dashboard.categories.index") }}">All
                                    Categories</a></li>
                            <li class="breadcrumb-item active">New Category</li>
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
                                <h3 class="card-title">New Category</h3>
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
                                <form action="{{ route("dashboard.categories.store") }}" enctype="multipart/form-data"
                                    method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-8 mx-auto">
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <input type="text" class="form-control" id="title" name="title"
                                                    placeholder="Enter title" value="{{ old('title') }}" />
                                            </div>
                                            <div class="form-group">
                                                <label for="slug">Slug</label>
                                                <input type="text" class="form-control" id="slug" name="slug"
                                                    placeholder="Enter slug" value="{{ old('slug') }}" />
                                            </div>
                                            <div class="form-group">
                                                <label for="description">Description</label>
                                                <textarea id="description" name="description"
                                                    placeholder="Enter description" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mx-auto">
                                            <div class="form-group">
                                                <label for="image">Image</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="image-text" readonly
                                                        placeholder="Pilih gambar...">
                                                    <input type="hidden" name="image" id="image">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                                            data-target="#mediaManagerModal" onclick="$('#mediaManagerModal').data('origin', 'thumbnail')">
                                                            <i class="fas fa-image"></i> Pilih Gambar
                                                        </button>
                                                    </div>
                                                </div>
                                                <img id="imagepreview" class="img-fluid img-thumbnail mt-3 d-none"
                                                    style="max-height: 200px" />
                                            </div>
                                            <div class="form-group">
                                                <label for="status">Status</label>
                                                <select class="form-control" name="status" id="status">
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" type="submit">Create</button>
                                </form>
                                {{-- Include Media Manager Modal --}}
                                @include('dashboard.inc.media_modal')
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
    <script src="{{ asset("assets/dashboard/plugins/speakingurl/speakingurl.min.js") }}"></script>
    <script src="{{ asset("assets/dashboard/plugins/slugify/slugify.min.js") }}"></script>
    <script>
        $(document).ready(function () {
            $('#title').on("input", () => {
                $('#slug').val($.slugify($('#title').val()));
            });
        });

        // ==============================================
        // LOGIKA MEDIA MANAGER
        // ==============================================
        let selectedMediaId = null;
        let selectedMediaFile = null;

        $('#mediaManagerModal').on('show.bs.modal', function (e) {
            loadMediaLibrary();
        });

        function loadMediaLibrary() {
            $('#media-grid').html('');
            $('#media-loader').show();

            $.ajax({
                url: "{{ route('dashboard.media.api') }}",
                type: "GET",
                success: function (response) {
                    $('#media-loader').hide();
                    if (response.length > 0) {
                        response.forEach(function (media) {
                            let mediaHtml = `
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="media-item" onclick="selectMedia(this, '${media.file_name}')">
                                            <img src="{{ asset('uploads/media') }}/${media.file_name}" class="media-img" loading="lazy">
                                        </div>
                                    </div>
                                `;
                            $('#media-grid').append(mediaHtml);
                        });
                    } else {
                        $('#media-grid').html('<div class="col-12 text-center text-muted py-5">Belum ada media. Silakan upload baru.</div>');
                    }
                },
                error: function () {
                    $('#media-loader').hide();
                    $('#media-grid').html('<div class="col-12 text-center text-danger">Gagal memuat library.</div>');
                }
            });
        }

        window.selectMedia = function (element, fileName) {
            $('.media-item').removeClass('selected');
            $(element).addClass('selected');
            selectedMediaFile = fileName;

            $('#selected-image-name').text("Terpilih: " + fileName);
            $('#btn-insert-media').prop('disabled', false);
        }

        $('#btn-insert-media').click(function () {
            if (selectedMediaFile) {
                // Kategori hanya punya satu input image, jadi logicnya sederhana
                // Tapi untuk konsistensi kita cek originnya meski saat ini pasti thumbnail
                $('#image').val(selectedMediaFile);
                $('#image-text').val(selectedMediaFile);

                $('#imagepreview').attr('src', "{{ asset('uploads/media') }}/" + selectedMediaFile);
                $('#imagepreview').removeClass('d-none');

                $('#mediaManagerModal').modal('hide');
            }
        });

        $('#mm-file-input').change(function () {
            let formData = new FormData($('#media-upload-form')[0]);

            $('#upload-progress-container').removeClass('d-none');
            $('#upload-progress').css('width', '0%');

            $.ajax({
                url: "{{ route('dashboard.media.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);
                            $('#upload-progress').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function (response) {
                    $('#upload-progress-container').addClass('d-none');
                    $('#media-upload-form')[0].reset();
                    $('#library-tab').tab('show');
                    loadMediaLibrary();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Media berhasil diupload!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function () {
                    $('#upload-progress-container').addClass('d-none');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal mengupload gambar.'
                    });
                }
            });
        });
        // End Logic
    </script>
@endsection