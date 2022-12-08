window.onload = () => {
    parDefault();
    $(document).on('change', '#sortie_ville', function() {
        viderInfosLieu();
        ajaxVille();
    });
    $(document).on('change', '#sortie_lieu', function() {
        ajaxLieu();
    });
    $(document).on('change', '#sortie_dateHeureDebut', function (e) {
        controlerDateSortie(e);
    });
    $(document).on('change', '#sortie_dateLimiteInscription', function (e) {
        controlerDateFin(e);
    });
}

function parDefault() {
    $('#sortie_ville').attr('required', 'required');
}

function ajaxVille() {
    let id = $('#sortie_ville').val();
    $.ajax({
        method: "GET",
        url: "sortie_ville/" + id,
        dataType: "json",
        success: function(data) {
            $('#sortie_codePostal').val(data['codePostal']);
        }
    });
    $.ajax({
        method: "GET",
        url: "sortie_ville_lieu/" + id,
        dataType: "json",
        success: function(data) {
            afficherListeLieus(data);
            ajaxLieu();
        }
    });
}

function ajaxLieu() {
    let id = $('#sortie_lieu').val();
    if (id != null) {
        $.ajax({
            method: "GET",
            url: "sortie_lieu/" + id,
            dataType: "json",
            success: function(data) {
                afficherInfosLieu(data);
            }
        });
    }
}

function afficherListeLieus(data) {
    let select = $('#sortie_lieu');
    select.find('option').remove().end();
    if (data.length > 0) {
        for (let i = 0; i < data.length; i++) {
            let option = document.createElement("option");
            option.text = data[i]['nom'];
            option.value = data[i]['id'];
            select.append(option);
        }
    }
}

function afficherInfosLieu(data) {
    if (data != null) {
        $('#sortie_rue').val(data['rue']);
        $('#sortie_latitude').val(data['latitude']);
        $('#sortie_longitude').val(data['longitude']);
    }
}

function viderInfosLieu() {
    $('#sortie_rue').val('');
    $('#sortie_latitude').val('');
    $('#sortie_longitude').val('');
}

function controlerDateSortie(e) {
    let dateFin = $('#sortie_dateLimiteInscription');
    dateFin.remove('max');
    dateFin.attr({'max' : e.target.value.substring(0, 10)});
}

function controlerDateFin(e) {
    let dateSortie = $('#sortie_dateHeureDebut');
    dateSortie.remove('min');
    dateSortie.attr({'min' : e.target.value + ' 00:00'});
}

function ajouterUnNouveauLieu() {
    alert('création nouveau lieu');
}