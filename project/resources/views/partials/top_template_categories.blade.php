@php

    $topKeywords = $top_keywords;

@endphp

<div class="form-group rounded-3" style="border: dashed; padding: 10px;">
    <div style="display: flex; flex-direction: row; gap: 10px; flex-wrap: wrap;">
        <h6>Top Template Categories</h6>
        <button type="button" class="btn btn-primary"
            style="width: 100px; height: 30px; padding: 0; transform: translateY(-5px);" data-backdrop="static"
            data-toggle="modal" data-target="#top_keywords_modal" onclick="resetModal()">Add</button>
    </div>

    <div id="sortable" class="connectedSortable" style="display: flex; flex-wrap: wrap; gap: 5px;">

    </div>
</div>

<div class="modal fade" id="top_keywords_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Top Template Keyowrd</h5>
                <button id="closeModal" type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <h7>Keyword</h7>
                    <div class="input-group custom">
                        <input type="text" class="form-control" placeholder="Name" id="keywordName">
                    </div>
                </div>

                <div class="form-group">
                    <h7>Link</h7>
                    <div class="input-group custom">
                        <input type="text" class="form-control" placeholder="Link" id="keywordLink">
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check mt-4 mb-2">
                        <input type="checkbox" id="keywordTarget" class="form-check-input">
                        <label class="form-check-label" for="keywordTarget">Open in new tab</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="keywordRel" class="form-check-input">
                        <label class="form-check-label" for="keywordRel">Add rel="nofollow"</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-primary" onclick="onSubmit()">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var topKeywords = @json($topKeywords);
    var editingElement = null;

    function loadTopKeywords() {
        topKeywords.forEach(function(keyword) {
            editingElement = null;
            addKeyword(keyword.value, keyword.link, keyword.openinnewtab, keyword.nofollow);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadTopKeywords();
    });

    function onSubmit() {
        var keywordName = $('#keywordName').val();
        var keywordLink = $('#keywordLink').val();
        var keywordTarget = $('#keywordTarget').is(':checked');
        var keywordRel = $('#keywordRel').is(':checked');

        if (!keywordName.trim() || !keywordLink.trim()) {
            window.alert('Add name and link!');
            return;
        }

        if (!isValidUrl(keywordLink)) {
            window.alert('Enter Valid URL');
            return;
        }

        addKeyword(keywordName, keywordLink, keywordTarget ? 1 : 0, keywordRel ? 1 : 0);
    }

    function addKeyword(keywordName, keywordLink, keywordTarget, keywordRel) {
        if (editingElement) {
            $(editingElement).closest('.sortable-row').find('a').text(keywordName).attr('href', keywordLink);
            $(editingElement).closest('.sortable-row').find('input[name="keyword_name[]"]').val(keywordName);
            $(editingElement).closest('.sortable-row').find('input[name="keyword_link[]"]').val(keywordLink);
            $(editingElement).closest('.sortable-row').find('input[name="keyword_target[]"]').val(keywordTarget);
            $(editingElement).closest('.sortable-row').find('input[name="keyword_rel[]"]').val(keywordRel);
        } else {
            html =
                '<div class="sortable-row" style="border:1px solid grey;background:white;border-radius:4px;padding-left:5px;padding-right:5px;display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;">';

            html += '<input class="form-control" type="textname" name="keyword_name[]" value="' + keywordName +
                '" style="display: none;"/>';
            html += '<input class="form-control" type="textname" name="keyword_link[]" value="' + keywordLink +
                '" style="display: none;"/>';
            html += '<input class="form-control" type="textname" name="keyword_target[]" value="' + keywordTarget +
                '" style="display: none;"/>';
            html += '<input class="form-control" type="textname" name="keyword_rel[]" value="' + keywordRel +
                '" style="display: none;"/>';

            html += '<a href="' + keywordLink + '" target="_blank" style="color: black;">' + keywordName + '</a>';

            html +=
                '<button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" data-backdrop="static" data-toggle="modal" data-target="#top_keywords_modal" onclick="editModal(this)"><i class="dw dw-edit2"></i></button>';

            html +=
                '<button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" onclick="deleteKeyword(this)"><i class="dw dw-delete-3"></i></button>';

            $('#sortable').append(html);
        }

        hideModal();
    }

    function deleteKeyword(element) {
        $(element).closest('.sortable-row').remove();
    }

    function editModal(element) {
        editingElement = element;

        var keywordName = $(editingElement).closest('.sortable-row').find('input[name="keyword_name[]"]').val();
        var keywordLink = $(editingElement).closest('.sortable-row').find('input[name="keyword_link[]"]').val();
        var keywordTarget = Number($(editingElement).closest('.sortable-row').find('input[name="keyword_target[]"]')
            .val());
        var keywordRel = Number($(editingElement).closest('.sortable-row').find('input[name="keyword_rel[]"]').val());

        $('#myLargeModalLabel').text('Edit Top Template Keyowrd');
        $('#keywordName').val(keywordName);
        $('#keywordLink').val(keywordLink);
        $('#keywordTarget').prop('checked', keywordTarget == 1 ? true : false);
        $('#keywordRel').prop('checked', keywordRel == 1 ? true : false);
    }

    function resetModal() {
        editingElement = null;
        $('#myLargeModalLabel').text('Add Top Template Keyowrd');
        $('#keywordName').val('');
        $('#keywordLink').val('');
        $('#keywordTarget').prop('checked', false);
        $('#keywordRel').prop('checked', false);
    }

    function hideModal() {
        editingElement = null;
        $('#closeModal').click();
    }

    function isValidUrl(url) {
        const pattern = /^(https?:\/\/)?([\w-]+\.)+[a-z]{2,6}(\/[\w-]*)*\/?$/;
        return pattern.test(url);
    }
</script>
