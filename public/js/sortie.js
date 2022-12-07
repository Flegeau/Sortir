window.onload = () => {
    let minutes = document.createElement('span');
    minutes.innerHTML = 'minutes';
    document.getElementById('sortie_duree').after(minutes);

    $(document).on('change', '#sortie_ville', function() {
        $.ajax({
            method: "GET",
            url: "sortie_ville/" + $('#sortie_ville').val(),
            dataType: "json",
            success: function(data) {
                afficherInfosVille(data);
            }
        });
    });
    $(document).on('change', '#sortie_lieu', function() {
        $.ajax({
            method: "GET",
            url: "sortie_lieu/" + $('#sortie_lieu').val(),
            dataType: "json",
            success: function(data) {
                afficherInfosLieu(data);
            }
        });
    });
}

function afficherInfosVille(data) {
    $('#sortie_codePostal').val(data['codePostal']);
}

function afficherInfosLieu(data) {
    $('#sortie_rue').val(data['rue']);
    $('#sortie_latitude').val(data['latitude']);
    $('#sortie_longitude').val(data['longitude']);
}