<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script src="{{ asset('assets/plugins/switchery/switchery.min.js') }}"></script>

<script src="{{ asset('assets/plugins/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>

<script src="{{ asset('assets/plugins/datatables/js/jquery.dataTables.min.js') }}"></script>

<script src="{{ asset('assets/plugins/datatables/js/dataTables.bootstrap4.min.js') }}"></script>

<script src="{{ asset('assets/plugins/datatables/js/dataTables.responsive.min.js') }}"></script>

<script src="{{ asset('assets/plugins/datatables/js/responsive.bootstrap4.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<script src="{{ asset('assets/vendors/scripts/core.js') }}"></script>

<script src="{{ asset('assets/vendors/scripts/script.min.js') }}"></script>

<script src="{{ asset('assets/vendors/scripts/process.js') }}"></script>

<script src="{{ asset('assets/vendors/scripts/layout-settings.js') }}"></script>

<script src="{{ asset('assets/vendors/scripts/datatable-setting.js') }}"></script>

<script src="{{ asset('assets/vendors/scripts/advanced-components.js') }}"></script>

<script>
    window.addEventListener("DOMContentLoaded", function() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        if (main_loading_screen) {
            main_loading_screen.style.display = "none";
        }
        var tagsInputContainer = document.querySelector('.bootstrap-tagsinput');
        if(tagsInputContainer){
            var tagsInput = tagsInputContainer.querySelector('input[type="text"]');
            if (tagsInput) {
                tagsInput.setAttribute('list', 'related_tag_list');
                tagsInput.setAttribute('autocomplete', 'on');
                tagsInput.style.width = '100%';
                tagsInput.style.height = '45px';
                tagsInput.style.border = '1px solid #000000';
                tagsInput.style.borderRadius = '5px';
                tagsInput.style.marginTop = '5px';
            }
        }
    });
</script>

<script src="{{ asset('assets/js/sortable.min.js') }}"></script>

<script src="{{ asset('assets/js/sortable.js') }}"></script>

<script src="{{ asset('assets/js/sorting.js') }}"></script>

<script src="{{ asset('assets/js/colorpicker.js') }}"></script>

<script src="{{ asset('assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>

<script src="{{ asset('assets/js/create.js') }}"></script>

<script src="{{ asset('assets/js/dynamicfile.js') }}"></script>

<script src="{{ asset('assets/js/role_access.js') }}"></script>
