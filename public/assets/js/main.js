$(document).ready(function() {
    $("#inscription_form").submit(function(event) {
        let button = $('#inscription');
        // disable button
        button.prop("disabled", true);
        // add spinner to button
        button.html(
            `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...`
        );
    });
});