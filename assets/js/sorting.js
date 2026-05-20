$(document).ready(function() {
    function initializeSortable() {
        try {
            $('#sortable').sortable({
                animation: 150,
                ghostClass: 'blue-background-class'
            });
        } catch (e) {
            setTimeout(initializeSortable, 1000);
        }
    }
    initializeSortable();
});