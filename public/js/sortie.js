window.onload = () => {
    parDefault();
    ajaxLieu();

    $(document).on('change', '#sortie_ville', function() {
        ajaxVille();
    });
    $(document).on('change', '#sortie_lieu', function() {
        ajaxLieu();
    });
}

function parDefault() {
    let minutes = $('<span>').html('minutes');
    $('#sortie_duree').after(minutes);
    creerElementLieu();
    ajaxVille();
}

function ajaxVille() {
    let id = $('#sortie_ville').val();
    $.ajax({
        method: "GET",
        url: "sortie_ville/" + id,
        dataType: "json",
        success: function(data) {
            afficherInfosVille(data);
        }
    });
    $.ajax({
        method: "GET",
        url: "sortie_ville_lieu/" + id,
        dataType: "json",
        success: function(data) {
            afficherListeLieus(data);
        }
    });
}

function ajaxLieu() {
    $.ajax({
        method: "GET",
        url: "sortie_lieu/" + $('#sortie_lieu').val(),
        dataType: "json",
        success: function(data) {
            afficherInfosLieu(data);
        }
    });
}

function afficherInfosVille(data) {
    $('#sortie_codePostal').val(data['codePostal']);
}

function afficherListeLieus(data) {
    let select = $('#sortie_lieu');
    select.find('option').remove().end();
    if (data.length > 0) {
        for (let i = 0; i < data.length; i++) {
            let option = document.createElement("option");
            console.log(data[i]);
            option.text = data[i]['nom'];
            option.value = data[i]['id'];
            select.append(option);
        }
    }
}

function creerElementLieu() {
    let parent = $('#sortie_ville').parent().parent();
    let div = $('<div>', {'class': 'mb-3 row'});
    let label = $('<label>', {'class': 'col-form-label col-sm-2 required', 'htmlFor': 'sortie_lieu'}).html('Lieu : ');
    let divCol = $('<div>', {'class': 'col-sm-10'});
    let select = $('<select>', {'id': 'sortie_lieu', 'name': 'sortie[lieu]', 'class': 'form-select'});
    parent.after(div);
    div.append(label);
    div.append(divCol);
    divCol.append(select);
}

function afficherInfosLieu(data) {
    $('#sortie_rue').val(data['rue']);
    $('#sortie_latitude').val(data['latitude']);
    $('#sortie_longitude').val(data['longitude']);
}