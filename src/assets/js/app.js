$(document).ready(function() {
    $('.print').on("click", function () {
        window.print();
    });

    $('textarea').each(function () {
        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px; overflow-y:hidden;');
    }).on('input', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    $(document).on("change", '#chbxServiceHint', function () {
        $('.td_Leistung .service').toggleClass('hide');
    });

    $(document).on("change", '#chbxRxHint', function () {
        $('.rxHint').toggleClass('hide');
    });

    $(document).on("change", '#chbxCovidHint', function () {
        $('.covidHint').toggleClass('hide');
    });

    $(document).on("change", '#chbxDiabHint', function () {
        $('.rxDiab').toggleClass('hide');
    });

    $(document).on("change", ".n", function () {
        var id = $(this).attr('id');
        $('.' + id).toggleClass('hide');
    });

    $(document).on("change", ".m", function () {
        var id = $(this).attr('id');
        $('.' + id).toggleClass('hide');
    });

    $(document).on("change", ".no", function () {
        var id = $(this).attr('id');
        $('.' + id).toggleClass('hide');
    });

    $(document).on("change", ".v", function () {
        var id = $(this).attr('id');
        $('.' + id).toggleClass('hide-for-print');
    });
});