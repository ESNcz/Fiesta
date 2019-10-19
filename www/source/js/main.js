window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());

gtag('config', 'UA-49681841-5');


$(function () {
    $('.datetimepickerfull').datetimepicker({
        useStrict: true,
        format: 'DD. MM. YYYY HH:mm',
        extraFormats: [ 'DD. MM. YYYY HH:mm', 'DD.MM.YYYY HH:mm' ]

    });

    $('.datetimepicker').datetimepicker({
        useStrict: true,
        format: 'DD. MM. YYYY',
        extraFormats: [ 'DD. MM. YYYY', 'DD.MM.YYYY' ]

    });

    $('#datetimepickerfrom').datetimepicker({
        format: 'DD. MM. YYYY HH:mm',
        extraFormats: [ 'DD. MM. YYYY HH:mm', 'DD.MM.YYYY HH:mm' ]
    });

    $('#datetimepickerstart').datetimepicker({
        format: 'DD. MM. YYYY',
        useCurrent: false //Important! See issue #1075
    });

    $("#datetimepickerstart").on("dp.change", function (e) {
        $('#datetimepickerfrom').data("DateTimePicker").minDate(e.date);
    });

    $("#datetimepickerfrom").on("dp.change", function (e) {
        $('#datetimepickerstart').data("DateTimePicker").maxDate(e.date);
    });

});

$(document).ready(function() {
    $('#summernote').summernote({
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            ['para', ['ul', 'ol', 'paragraph']],
            ["table", ["table", "hr"]],
            ['insert', ['picture', 'link', 'video']],
            ['height', ['height']]
        ]
    });
});


    $('#frm-signUpForm-faculty').select2();
    // language=JQuery-CSS
    $(".default-select2").select2();

    $(".user-autocomplete").select2({
        ajax: {
            url: "/internal/users",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    input: params.term, // search term
                    type: 'all'
                };
            },

            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 2) < data.total_count
                    }
                };
            },
            cache: true
        },
        placeholder: "Search users",
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 2,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

    $(".section-autocomplete").select2({
        ajax: {
            url: "/internal/users",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    input: params.term, // search term
                    type: 'section'
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 2) < data.total_count
                    }
                };
            },
            cache: true
        },
        placeholder: "Search users",
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 2,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

    $(".member-autocomplete").select2({
        ajax: {
            url: "/internal/users",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    input: params.term, // search term
                    type: 'members'
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 2) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 2,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

    function formatRepo (repo) {
        if (repo.loading) {
            return repo.text;
        }

        var markup = "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-profile__avatar'><img src='../" + repo.avatar_url + "' /></div>" +
            "<div class='select2-result-title'>" + repo.full_name + "</div>" +
            "<div class='select2-result-description'>" + repo.email + "</div>";


        return markup;
    }

    function formatRepoSelection (repo) {
        return repo.full_name;
    }



$("#upload_link").click(function(){
        $("#frm-uploadImage-upload").trigger('click');
});

$("#frm-uploadImage-upload").change(function (e) {
    e.preventDefault();
    $(document).ajaxStart(function () {
        $(".loader").removeClass('hidden').show();
        $("#img-hide").hide();
    });

    $(document).ajaxStop(function () {
        $(".loader").hide();
        $("#img-hide").show();
    });

    $("#sendUpload").click();
});

$("#upload_link").click(function(){
    $("#frm-uploadMyImage-upload").trigger('click');
});

$("#frm-uploadMyImage-upload").change(function (e) {
    e.preventDefault();
    $(document).ajaxStart(function () {
        $(".loader").removeClass('hidden').show();
        $("#img-hide").hide();
    });

    $(document).ajaxStop(function () {
        $(".loader").hide();
        $("#img-hide").show();
    });
    
    $("#sendUpload").click();
});

$(function() {
    if ($('.datagrid').length) {
        return $.nette.ajax({
            type: 'GET',
            url: $('.datagrid').first().data('refresh-state')
        });
    }
});

// datagrid spinner
$.nette.ext("ublaboo-spinners",{before:function(e,i){var t,a,r,o;if(i.nette){if(t=i.nette.el,o=$('<div class="ublaboo-spinner ublaboo-spinner-small"><i></i><i></i><i></i><i></i></div>'),t.is('.datagrid [name="group_action[submit]"]'))return t.after(o);if(t.is(".datagrid a")&&t.data("toggle-detail")){if(a=i.nette.el.attr("data-toggle-detail"),r=$(".item-detail-"+a),!r.hasClass("loaded"))return t.addClass("ublaboo-spinner-icon")}else{if(t.is(".datagrid .col-pagination a"))return t.closest(".row-grid-bottom").find(".col-per-page").prepend(o);if(t.is(".datagrid .datagrid-per-page-submit"))return t.closest(".row-grid-bottom").find(".col-per-page").prepend(o);if(t.is(".datagrid .reset-filter"))return t.closest(".row-grid-bottom").find(".col-per-page").prepend(o)}}},complete:function(){return $(".ublaboo-spinner").remove(),$(".ublaboo-spinner-icon").removeClass("ublaboo-spinner-icon")}});

//switchery
var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));

elems.forEach(function(html) {
    var switchery = new Switchery(html, { size: 'small' });
});

FontAwesomeConfig = { searchPseudoElements: true };

$(function() {
    $( ".circle" ).click(function() {
        if($(this).hasClass("check")){
            $(this).removeClass("check",1000)
        } else {
            $(this).addClass("check", 1000)
        }
    });
});


$.nette.ext({
    load: function () {
        $('[data-confirm]').click(function (event) {
            var obj = this;
            event.preventDefault();
            event.stopImmediatePropagation();
            $("<div id='dConfirm' class='modal fade'></div>").appendTo('body');
            $('#dConfirm').html("<div id='dConfirmDialog' class='modal-dialog'></div>");
            $('#dConfirmDialog').html("<div id='dConfirmContent' class='modal-content'></div>");
            $('#dConfirmContent').html("<div id='dConfirmHeader' class='modal-header'></div><div id='dConfirmBody' class='modal-body'></div><div id='dConfirmFooter' class='modal-footer'></div>");
            $('#dConfirmHeader').html("<a class='close' data-dismiss='modal' aria-hidden='true'>×</a><h4 class='modal-title' id='dConfirmTitle'></h4>");
            $('#dConfirmTitle').html($(obj).data('confirm-title'));
            $('#dConfirmBody').html("<p>" + $(obj).data('confirm-text') + "</p>");
            $('#dConfirmFooter').html("<a id='dConfirmCancel' class='btn btn-default' data-dismiss='modal'>Cancel</a><a id='dConfirmOk' class='btn btn-danger' data-dismiss='modal'>OK</a>");
            if ($(obj).data('confirm-header-class')) {
                $('#dConfirmHeader').addClass($(obj).data('confirm-header-class'));
            }
            if ($(obj).data('confirm-ok-text')) {
                $('#dConfirmOk').html($(obj).data('confirm-ok-text'));
            }
            if ($(obj).data('confirm-cancel-text')) {
                $('#dConfirmCancel').html($(obj).data('confirm-cancel-text'));
            }
            $('#dConfirmOk').on('click', function () {
                var tagName = $(obj).prop("tagName");
                if (tagName === 'INPUT') {
                    var form = $(obj).closest('form');
                    form.submit();
                } else {
                    if ($(obj).data('ajax') === 'on') {
                        $.nette.ajax({
                            url: obj.href
                        });
                    } else {
                        document.location = obj.href;
                    }
                }
            });
            $('#dConfirm').on('hidden.bs.modal', function () {
                $('#dConfirm').remove();
            });
            $('#dConfirm').modal('show');
            return false;
        })
    }
});

$.nette.ext({
    success: function() {
        $('[data-toggle="tooltip"]').tooltip();
        $('.modal').modal('hide');
        $(".buddy-manual").select2({
            ajax: {
                url: "/internal/users",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        input: params.term, // search term
                        type: 'members'
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 2) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            minimumInputLength: 2,
            templateResult: formatRepo,
            templateSelection: formatRepoSelection
        });
        $.nette.load();
    }
});
