<div class="modal fade" id="mediaManagerModal" tabindex="-1" role="dialog" aria-labelledby="mediaManagerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaManagerModalLabel">Media Manager</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="mediaTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="library-tab" data-toggle="tab" href="#library" role="tab"
                            aria-controls="library" aria-selected="true">Library</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="upload-tab" data-toggle="tab" href="#upload" role="tab"
                            aria-controls="upload" aria-selected="false">Upload Baru</a>
                    </li>
                </ul>
                <div class="tab-content" id="mediaTabContent">
                    <!-- Library Tab -->
                    <div class="tab-pane fade show active p-3" id="library" role="tabpanel"
                        aria-labelledby="library-tab">
                        <div id="media-loader" class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                        </div>
                        <div id="media-grid" class="row" style="max-height: 400px; overflow-y: auto;">
                            <!-- Images will be loaded here via AJAX -->
                        </div>
                    </div>

                    <!-- Upload Tab -->
                    <div class="tab-pane fade p-3" id="upload" role="tabpanel" aria-labelledby="upload-tab">
                        <form id="media-upload-form" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group text-center border p-5 bg-light rounded dashed-border">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h5>Drag & Drop gambar di sini atau klik untuk memilih</h5>
                                <input type="file" name="image" id="mm-file-input" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-primary mt-2"
                                    onclick="document.getElementById('mm-file-input').click()">Pilih File</button>
                                <p class="text-muted mt-2 small">Maksimal ukuran file: 2MB. Format: JPG, PNG, GIF.</p>
                            </div>
                            <!-- Progress Bar -->
                            <div class="progress d-none" id="upload-progress-container">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    id="upload-progress" style="width: 0%"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <span id="selected-image-name" class="text-muted font-italic">Belum ada gambar yang dipilih</span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-insert-media" disabled>Pilih Gambar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .media-item {
        cursor: pointer;
        position: relative;
        border: 2px solid transparent;
        transition: all 0.2s;
    }

    .media-item:hover {
        border-color: #007bff;
        transform: scale(1.02);
    }

    .media-item.selected {
        border-color: #007bff;
        background-color: #f0f8ff;
    }

    .media-item.selected::after {
        content: '\f00c';
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        top: 5px;
        right: 5px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }

    .media-img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 4px;
    }

    .dashed-border {
        border: 2px dashed #dee2e6 !important;
    }
</style>