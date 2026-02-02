@extends('dashboard.master')
@section('title', 'Edit Post')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Edit Post</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route("dashboard.home") }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route("dashboard.posts.index") }}">All Posts</a></li>
                            <li class="breadcrumb-item active">Edit Post</li>
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
                                <h3 class="card-title">Edit Post</h3>
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
                                <form action="{{ route("dashboard.posts.update", $post->id) }}"
                                    enctype="multipart/form-data" method="POST">
                                    @csrf
                                    @method("PUT")
                                    <div class="row">
                                        <div class="col-md-8 mx-auto">
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <input type="text" class="form-control" id="title" name="title"
                                                    placeholder="Enter title" value="{{ $post->title }}" />
                                            </div>
                                            <div class="form-group">
                                                <label for="slug">Slug</label>
                                                <input type="text" class="form-control" id="slug" name="slug"
                                                    placeholder="Enter slug" value="{{ $post->slug }}" />
                                            </div>
                                            <div class="form-group">
                                                <label for="content">Content</label>
                                                <textarea class="form-control" id="content" name="content"
                                                    placeholder="Write content">{{ $post->content }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mx-auto">
                                            <div class="form-group">
                                                <label for="category">Category</label>
                                                <select class="form-control" name="category" id="category"
                                                    style="width: 100%;">
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" {{ $post->category_id == $category->id ? "selected" : "" }}>{{ $category->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="tags">Tags</label>
                                                <div class="select2-purple">
                                                    <select multiple="multiple" data-placeholder="Select tag"
                                                        data-dropdown-css-class="select2-purple" class="form-control"
                                                        name="tags[]" id="tags" style="width: 100%;">
                                                        @foreach ($tags as $tag)
                                                            <option value="{{ $tag->name }}">{{ $tag->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="thumbnail">Thumbnail</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="thumbnail-text" readonly
                                                        placeholder="Pilih gambar..." value="{{ $post->thumbnail }}">
                                                    <input type="hidden" name="thumbnail" id="thumbnail"
                                                        value="{{ $post->thumbnail }}">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                                            data-target="#mediaManagerModal">
                                                            <i class="fas fa-image"></i> Pilih Gambar
                                                        </button>
                                                    </div>
                                                </div>
                                                @if($post->thumbnail)
                                                    <img id="thumbnailpreview" class="img-fluid img-thumbnail mt-3"
                                                        src="{{ asset("uploads/media/" . $post->thumbnail) }}"
                                                        style="max-height: 200px" />
                                                @else
                                                    <img id="thumbnailpreview" class="img-fluid img-thumbnail mt-3 d-none"
                                                        style="max-height: 200px" />
                                                @endif
                                            </div>
                                            <div class="align-items-center d-flex form-group justify-content-between">
                                                <label for="featured">Featured</label>
                                                <div class="icheck-success d-inline">
                                                    <input type="checkbox" name="featured" id="featured" value="1" {{ $post->is_featured ? "checked" : "" }} />
                                                    <label for="featured"></label>
                                                </div>
                                            </div>
                                            <div class="align-items-center d-flex form-group justify-content-between">
                                                <label for="comment">Enable Comment</label>
                                                <div class="icheck-success d-inline">
                                                    <input type="checkbox" name="comment" id="comment" value="1" {{ $post->enable_comment ? "checked" : "" }} />
                                                    <label for="comment"></label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="status">Status</label>
                                                <select class="form-control" name="status" id="status">
                                                    @if (auth()->user()->role != 1)
                                                        <option value="1" {{ $post->status ? "selected" : "" }}>Publish</option>
                                                    @endif
                                                    <option value="0" {{ !$post->status ? "selected" : "" }}>Draft</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">Update</button>
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

@section("style")
    <link rel="stylesheet" href="{{ asset("assets/dashboard/plugins/select2/css/select2.min.css") }}" />
    <link rel="stylesheet"
        href="{{ asset("assets/dashboard/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css") }}" />
@endsection

@section("script")
    <script src="{{ asset("assets/dashboard/plugins/sweetalert2/sweetalert2.all.js") }}"></script>
    <script src="{{ asset("assets/dashboard/plugins/select2/js/select2.full.min.js") }}"></script>
    <script src="{{ asset("assets/dashboard/plugins/speakingurl/speakingurl.min.js") }}"></script>
    <script src="{{ asset("assets/dashboard/plugins/slugify/slugify.min.js") }}"></script>
    <script>
        $(document).ready(function () {
            $('#title').on("input", () => {
                $('#slug').val($.slugify($('#title').val()));
            });
            $('#category').select2({
                theme: 'bootstrap4'
            });

            $('#tags').select2({
                tags: true,
            });
            @if ($post->tags_count > 0)
                var tags = [];
                @foreach ($post->tags as $tag)
                    tags.push('{{ $tag->name }}');
                @endforeach
                $('#tags').val(tags).trigger('change');
            @endif
                    // Custom Button untuk Summernote
                    var MediaButton = function (context) {
                var ui = $.summernote.ui;
                var button = ui.button({
                    contents: '<i class="fas fa-image"/> Media',
                    tooltip: 'Media Manager',
                    click: function () {
                        // Buka Modal & Set Flag bahwa ini dari Editor
                        $('#mediaManagerModal').data('origin', 'editor');
                        $('#mediaManagerModal').modal('show');
                    }
                });
                return button.render();
            }

            $("#content").summernote({
                placeholder: 'Write content...',
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'mediaManager']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                buttons: {
                    mediaManager: MediaButton
                }
            });


            // ==============================================
            // LOGIKA MEDIA MANAGER
            // ==============================================
            let selectedMediaId = null;
            let selectedMediaFile = null;

            // 1. Saat Modal Dibuka
            $('#mediaManagerModal').on('show.bs.modal', function (e) {
                // Jika tidak ada data origin, default ke 'thumbnail'
                if (!$(this).data('origin')) {
                    $(this).data('origin', 'thumbnail');
                }
                loadMediaLibrary();
            });

            // Reset origin saat modal ditutup
            $('#mediaManagerModal').on('hidden.bs.modal', function (e) {
                $(this).removeData('origin');
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
                                                    <div class="media-item border p-1" onclick="selectMedia(this, '${media.file_name}')" style="cursor: pointer;">
                                                        <img src="{{ asset('uploads/media') }}/${media.file_name}" class="media-img img-fluid" loading="lazy" style="height: 100px; object-fit: cover; width: 100%;">
                                                        <div class="p-1 text-center text-muted" style="font-size: 0.75rem;">
                                                            <div class="text-truncate" title="${media.file_name}"><strong>${media.file_name}</strong></div>
                                                            <div>${media.file_size}</div>
                                                        </div>
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

            // 2. Fungsi Pilih Media (Klik Gambar)
            window.selectMedia = function (element, fileName) {
                $('.media-item').removeClass('selected');
                $(element).addClass('selected');
                selectedMediaFile = fileName;

                $('#selected-image-name').text("Terpilih: " + fileName);
                $('#btn-insert-media').prop('disabled', false);
            }

            // 3. Tombol "Pilih Gambar" di Modal diklik
            $('#btn-insert-media').click(function () {
                if (selectedMediaFile) {
                    let origin = $('#mediaManagerModal').data('origin');

                    if (origin === 'editor') {
                        // Insert ke Summernote
                        let imageUrl = "{{ asset('uploads/media') }}/" + selectedMediaFile;
                        let imgNode = $('<img>').attr('src', imageUrl).addClass('img-fluid');
                        $('#content').summernote('insertNode', imgNode[0]);
                    } else {
                        // Insert ke Input Thumbnail
                        $('#thumbnail').val(selectedMediaFile);
                        $('#thumbnail-text').val(selectedMediaFile);
                        $('#thumbnailpreview').attr('src', "{{ asset('uploads/media') }}/" + selectedMediaFile);
                        $('#thumbnailpreview').removeClass('d-none');
                    }

                    // Tutup Modal
                    $('#mediaManagerModal').modal('hide');
                }
            });

            // 4. Upload Gambar Baru via AJAX
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
                        // Reset form
                        $('#media-upload-form')[0].reset();

                        // Pindah ke tab library
                        $('#library-tab').tab('show');

                        // Reload library
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
        });
    </script>
@endsection