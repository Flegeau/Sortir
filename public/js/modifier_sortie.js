window.onload = () => {
    parDefault();
    $(document).on('change', '#sortie_dateHeureDebut', function (e) {
        $('#sortie_dateLimiteInscription').attr({'max' : e.target.value.substring(0, 10)});
    });
    $(document).on('change', '#sortie_dateLimiteInscription', function (e) {
        $('#sortie_dateHeureDebut').attr({'min' : e.target.value + ' 00:00'});
    });
    $(document).on('change', '#sortie_lieu', function() {
        ajaxLieu();
    });
}

function parDefault() {
    let dateSortie = $('#sortie_dateHeureDebut');
    let dateFin = $('#sortie_dateLimiteInscription');
    dateSortie.attr({'min' : dateFin.val() + ' 00:00'});
    dateFin.attr({'max' : dateSortie.val().substring(0, 10)});
    $('#sortie_ville').parent().parent().remove();
    ajaxLieu();
}

//revoir url
function ajaxLieu() {
    $.ajax({
        method: "GET",
        url: "/Sortir/public/sortie/sortie_lieu/" + $('#sortie_lieu').val(),
        dataType: "json",
        success: function(data) {
            afficherInfosLieu(data);
        }
    });
}

function afficherInfosLieu(data) {
    if (data != null) {
        $('#sortie_rue').val(data['rue']);
        $('#sortie_latitude').val(data['latitude']);
        $('#sortie_longitude').val(data['longitude']);
    }
}

function ajouterUnNouveauLieu() {
    alert('création nouveau lieu');
}