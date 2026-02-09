@extends('dashboard.master')
@section('title', 'Header Menu')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Header Menu</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route("dashboard.home") }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Settings</li>
                            <li class="breadcrumb-item">Menus</li>
                            <li class="breadcrumb-item active">Header Menu</li>
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
                                <h3 class="card-title">Header Menu</h3>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                                        @foreach ($errors->all() as $error)
                                            <p class="m-0">{{ $error }}</p>
                                        @endforeach
                                    </div>
                                @endif
                                @if (session("success"))
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                                        <p class="m-0">{{ session("success") }}</p>
                                    </div>
                                @endif
                                <form method="POST" id="menuform"
                                    action="{{ route("dashboard.settings.menus.header.update") }}">
                                    @csrf
                                    <input type="hidden" id="menudata" name="menudata" value="" />
                                </form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <div class="d-flex flex-row justify-content-between">
                                                    <span>Header Menu Structure</span>
                                                    <button class="btn btn-sm btn-primary" id="storebutton">Save
                                                        Menu</button>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div id="element-id"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Card Add Custom Link --}}
                                        <div class="card mb-4">
                                            <div class="card-header">Add Custom Link</div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="txtText" class="form-label">Link Text</label>
                                                    <input type="text" class="form-control" id="txtText"
                                                        placeholder="e.g. Home">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="txtHref" class="form-label">URL</label>
                                                    <input type="text" class="form-control" id="txtHref"
                                                        placeholder="e.g. http://example.com">
                                                </div>
                                            </div>
                                            <div class="card-footer text-right">
                                                <button id="btnUpdate" class="btn btn-secondary" disabled="">
                                                    Update Item
                                                </button>
                                                <button id="btnAdd" class="btn btn-success">
                                                    Add to Menu
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Card Add Pages --}}
                                        <div class="card mb-4">
                                            <div class="card-header">Add Page</div>
                                            <div class="card-body p-0">
                                                <ul class="list-group list-group-flush"
                                                    style="max-height: 300px; overflow-y: auto;">
                                                    @forelse($pages as $page)
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            <span>{{ $page->title }}</span>
                                                            <button class="btn btn-sm btn-outline-primary btn-add-page"
                                                                data-text="{{ $page->title }}"
                                                                data-href="{{ route('frontend.page', $page->slug) }}">
                                                                Add
                                                            </button>
                                                        </li>
                                                    @empty
                                                        <li class="list-group-item text-center text-muted">No pages found.</li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section("style")
    <link rel="stylesheet" href="{{ asset("assets/dashboard/plugins/menu-editor/css/styles.min.css") }}" />
@endsection

@section("script")
    <script src="{{ asset("assets/dashboard/plugins/menu-editor/js/menu-editor.min.js") }}"></script>
    <script>
        var itemTextInput = document.getElementById("txtText"),
            itemHrefInput = document.getElementById("txtHref"),
            R = document.getElementById("btnUpdate"),
            menudata = document.getElementById("menudata"),
            storebutton = document.getElementById("storebutton");
        form = document.querySelector("#menuform");

        // Inisialisasi Menu Editor dengan maxLevel 2 untuk nesting
        var menuEditor = new MenuEditor('element-id', { maxLevel: 2 });
        var nestedData = {!! $menu !!}

        menuEditor.onClickDelete((event) => {
            if (confirm('Are you sure you want to remove ' + event.item.getDataset().text + '?')) {
                event.item.remove();
            }
        });

        menuEditor.onClickEdit((t) => {
            let e = t.item,
                n = e.getDataset();
            itemTextInput.value = n.text,
                itemHrefInput.value = n.href,
                menuEditor.edit(t.item),
                R == null || R.removeAttribute("disabled");
        });

        function resetForm() {
            itemHrefInput.value = "";
            itemTextInput.value = "";
            R.setAttribute("disabled", "true");
            menuEditor.form.reset(); // Reset internal status editor jika perlu
        }

        var btnAdd;
        (btnAdd = document.getElementById("btnAdd")) == null || btnAdd.addEventListener("click", () => {
            if (!itemTextInput.value || !itemHrefInput.value) {
                alert("Please enter both text and URL");
                return;
            }
            let t = {
                text: itemTextInput.value,
                href: itemHrefInput.value,
                icon: "",
                tooltip: ""
            };
            menuEditor.add(t);
            resetForm();
        });

        R == null || R.addEventListener("click", () => {
            if (!itemTextInput.value || !itemHrefInput.value) {
                alert("Please enter both text and URL");
                return;
            }
            let t = {
                text: itemTextInput.value,
                href: itemHrefInput.value,
                icon: "",
                tooltip: ""
            };
            menuEditor.update(t);
            resetForm();
        });

        // Add Page Button Handler
        document.querySelectorAll('.btn-add-page').forEach(button => {
            button.addEventListener('click', function () {
                let text = this.getAttribute('data-text');
                let href = this.getAttribute('data-href');

                let t = {
                    text: text,
                    href: href,
                    icon: "",
                    tooltip: ""
                };
                menuEditor.add(t);
            });
        });

        menuEditor.setArray(nestedData);
        menuEditor.mount();

        storebutton.addEventListener("click", (e) => {
            e.preventDefault();
            menudata.value = menuEditor.getString();
            form.submit();
        });
    </script>
@endsection