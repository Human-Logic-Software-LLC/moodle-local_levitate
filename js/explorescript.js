function toHoursAndMinutes(totalMinutes) {
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h ${String(minutes).padStart(2, '0')}m`;
}


function get_courses_data(title = "", filter_params = "") {
    var form = new FormData();
    form.append("title", title);
    form.append("filter_params", filter_params);
    var settings = {
        "url": "https://levitate.human-logic.com/webservice/rest/server.php?wstoken="+tokenid+"&wsfunction=mod_levitateserver_get_explore_courses&moodlewsrestformat=json",
        "method": "POST",
        "timeout": 0,
        "processData": false,
        "mimeType": "multipart/form-data",
        "contentType": false,
        "data": form
    };

    $.ajax(settings).done(function (response) {
    
        $(".coursecard").remove();
        $(".nocourse").remove();
        
        var data = $.parseJSON($.parseJSON(response));
        if(Object.keys(data).length>0){
            $(".filterd_courses").text(Object.keys(data).length);
            

            $.each(data, function (index, courses) {
        

                // const cardcontainer = document.queryselector('.explorecourses');
                durationtext = toHoursAndMinutes(courses.learning_time);

                let language = (courses.lang === 'en') ? 'English' : (courses.lang === 'ar') ? 'Arabic' : '';



                var imageURL = courses.imageURL;
                imageURL = imageURL.replace(/ /g, '%20');
                if (imageURL == '') {
                    imageURL =
                        'https://cdn.elearningindustry.com/wp-content/uploads/2020/08/5-ways-to-improve-your-course-cover-design-1024x575.png'
                }

                var text = `
                <div class='coursecard' >
                <div class='courseimage'>
                <img class='imageurl' src=${imageURL}>
                <input class=coursename${courses.course_id} type='hidden' id='coursename' name ='coursename[${courses.course_id}]' value=${courses.title} disabled>
                <input class=imageURL${courses.course_id} type='hidden' id='imageURL' name ='image_urls[${courses.course_id}]' value=${courses.imageURL} disabled>
                <input class=itemId${courses.course_id} type='hidden' id='itemId' name ='image_item_id[${courses.course_id}]' value=${courses.image_itemid} disabled>
                <input class=contextId${courses.course_id} type='hidden' id='contextId' name ='context_id[${courses.course_id}]' value=${courses.course_id} disabled>
                <input class=wsToken type='hidden' id='wsToken' name ='wstoken' value="`+tokenid+`" disabled>
                <input class=checkboc_cards type=checkbox id=${courses.course_id} name='enrollusers[${courses.course_id}]' value="${encodeURIComponent(courses.title)}">
                </div>
                <div class='coursebody ${courses.lang}'>
                <h2 class='coursetitle'>${courses.title}</h2>
                <div class='coursedescription'><p>${courses.course_description}</p></div>
                <div class='learning_objectivies' style="display:none"><p>${courses.learning_objectives}</p></div>
                <div class='coursefooter'>
                <div class='courselanguage'>${language}</div>
                <div class='coursetime'>${durationtext}</div>
                </div>
                </div>
                </div>`;
                $(".explorecourses").append(text);

            });
        }
        else{
            var text = `
            <div class='nocourse' >
                <h4 class='nocoursetext'> No courses found for the selected filters <h4>
            </div>`;
                $(".explorecourses").append(text);
        }
    });
}


function closewrapper() {
    $('.explore-details-wrapper').css('display', 'none');
}

let tokenid='';

function createinti(Y,phpvalues){
    $(".total_courses").text($("#total_course_value").text())
    let minval = parseInt(phpvalues.minval);
    let maxval = parseInt(phpvalues.maxval);
    tokenid = phpvalues.tokenid;
    for (let i = minval; i <= maxval; i += 5) {
        const isMinSelected = i === minval ? 'selected' : '';
        const isMaxSelected = i === maxval ? 'selected' : '';
        const isMinSelectedvalue = i === minval ? 'Min' : i;
        const isMaxSelectedvalue = i === maxval ? 'Max' : i;
        $("#minDval").append(`
            <option value="${i}" ${isMinSelected}>${isMinSelectedvalue}</option>
        `);
        $("#maxDval").append(`
            <option value="${i}" ${isMaxSelected}>${isMaxSelectedvalue}</option>
        `);
    }
    get_courses_data();

    $(".explorecourses").on("click", ".coursecard", function (e) {
        if(e.target.nodeName !== 'INPUT' ){

            $('.coursename').html($(this).find(".coursetitle").text());
            $('.explore-details-delivery').html($(this).find(".coursetime").text() +
                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + $(this).find(".courselanguage").text());
            $('.explore-details-description div').html($(this).find(".coursedescription").html());
            $('.explore-learning-objectives div').html($(this).find(".learning_objectivies").html());
            $('.explore-thumbnail-img').css('background-image', 'url(' + $(this).find(".imageurl").attr('src') +
                ')');
            $('#explore-details-wrapper-actual').css('display', 'block');
            $('.explore-details-pointer').css('left', ($(this).position().left + ($(this).width() / 2) - 80) +
            'px');
            $('.explore-details-wrapper').css('top', ($(this).position().top + $(this).height() + 25) + 'px');
        }

    });


    $('#minDval').on('change', function () {
        if (this.value < parseInt($("#maxDval_input").val())) {
            this.value = Math.min(this.value, parseInt($("#maxDval_input").val()) - 1);
            var minvalue = (100 / (parseInt($("#minDval_input").attr('max')) - parseInt($(
                "#minDval_input").attr('min')))) * parseInt(this.value) - (100 / (parseInt($(
                "#minDval_input").attr('max')) - parseInt($("#minDval_input").attr(
                'min')))) * parseInt($("#minDval_input").attr('min'));
            $("#minDval_input").val(this.value);
            $("[inverse-left]").css("width", minvalue + '%');
            $("[range]").css("left", minvalue + '%');
            $("[minthumb]").css("left", minvalue + '%');
            $('.error').hide();
            $(".errortext").text("");
        } else {
            $('.error').show();
            $(".errortext").text("the value should be less than max value");
            $("#minDval").val($("#minDval_input").val());
        }
    });
    $('#maxDval').on('change', function () {
        if (this.value > parseInt($("#minDval_input").val())) {
            this.value = Math.max(this.value, parseInt($("#minDval_input").val()) - (-1));
            var maxvalue = (100 / (parseInt($("#maxDval_input").attr('max')) - parseInt($(
                "#maxDval_input").attr('min')))) * parseInt(this.value) - (100 / (parseInt($(
                "#maxDval_input").attr('max')) - parseInt($("#maxDval_input").attr(
                'min')))) * parseInt($("#maxDval_input").attr('min'));
            $("#maxDval_input").val(this.value);
            $("[inverse-right]").css("width", 100 - maxvalue + '%');
            $("[range]").css("right", 100 - maxvalue + '%');
            $("[maxthumb]").css("left", maxvalue + '%');
            $(".errortext").text("");
            $('.error').hide();
        } else {
            $('.error').show();
            $(".errortext").text("the value should be greater than min value");
            $("#maxDval").val($("#maxDval_input").val());
        }

    });

    $(".courseimage [type=checkbox]").click(function (e) {
     e.stopPropagation();
    });

    $(".explorecourses").on("click", ".checkboc_cards", function () {
        var checkboxes = document.getElementsByClassName('checkboc_cards');
        let enable = false;
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked == true) {
                var id = $(this).attr('id');
                $(':input[class=imageURL' + id + ']').prop('disabled', false);
                $(':input[class=itemId' + id + ']').prop('disabled', false);
                $(':input[class=contextId' + id + ']').prop('disabled', false);
                $(':input[class=wsToken]').prop('disabled', false);
                // checkboxes[i].checked = true;
                enable = true;

            }
        }
        if (enable == true) {
            $(':input[type="submit"]').prop('disabled', false);
        } else {
            $(':input[type="submit"]').prop('disabled', true);
        }

    });
    $("input[name='hlfilters']").click(function () {
        if ($(this).attr('class').indexOf("hl-ShowContainer") != -1) {

            $(this).prop("checked", false);
            $('.' + this.value).removeClass("hl-ShowContainer");
            $('#' + $(this).parent().attr('id')).removeClass("hl-selected");
        }
    });
    $("input[name='hlfilters']").change(function () {
        $("input[name='hlfilters']").each(function () {
            if (this.checked == true) {
                $('.' + this.value).addClass("hl-ShowContainer");
                $('#' + $(this).parent().attr('id')).addClass("hl-selected");
            } else {
                $('.' + this.value).removeClass("hl-ShowContainer");
                $('#' + $(this).parent().attr('id')).removeClass("hl-selected");
            }
        });
    });

    $("input[name='filter_checkbox']").change(function () {
        onchange_filters();
    });

    // on-change time filter
    $("#slider-distance").on("change", function () {
        onchange_filters();
        // alert( $("#minDval_input").val() );
    });
    $('.searchTerm').keypress(function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            onchange_filters();
        }
    });
    $(".searchButton").click(function (e) {
        onchange_filters();
    });


    function onchange_filters() {
        var filter_params = {};
        $("input[name='filter_checkbox']").each(function () {

            if (filter_params.hasOwnProperty($(this).attr("data-filtername"))) {} else {
                filter_params[$(this).attr("data-filtername")] = '';
            }
            if (this.checked == true) {
                if (filter_params[$(this).attr("data-filtername")] == '') {
                    filter_params[$(this).attr("data-filtername")] = this.value;
                } else {
                    filter_params[$(this).attr("data-filtername")] = filter_params[$(this).attr(
                        "data-filtername")] + ',' + this.value;
                }
            }
            if ($(this).attr("data-filtername") == 'time_params') {
                filter_params[$(this).attr("data-filtername")] = $("#minDval_input").val() + ',' +
                    $("#maxDval_input").val();
            }
        });
        display_selected_filters(filter_params);
    }

    function display_selected_filters(filter_params) {
        $(".filter-summary-filterList").html("");
        $.each(filter_params, function (key, value) {
            if (value != '') {
                const array = value.split(',');
                $.each(array, function (arrkey, arrvalue) {
                    if (key !== 'time_params') {
                        $(".filter-summary-filterList").append(`
                    <li>
                        <div class="filter-summary-filter">
                        ` + arrvalue + `
                            <label class="filter-summary-removeFilter">
                                <input type="checkbox" value="` + arrvalue + `" data-group="` + key + `" name="selected-filters">
                                <i class="fa fa-times" aria-hidden="true"></i>
                                </input>
                            </label>
                        </div>
                    </li>`);
                    }
                });




            }
        });
        $("input[name='selected-filters']").click(function () {
            $('input:checkbox[value="' + this.value + '"]').prop('checked', false);
            onchange_filters();

        });
        $("#id_clearfilter").click(function () {
            $("input[name='filter_checkbox']").each(function () {
                if(this.checked){
                        $('input:checkbox[value="' + this.value + '"]').prop('checked', false);
                }
            
            });
            // $("#id_clearfilter").removeClass("clearfilter");
            // $("#id_clearfilter").removeClass("addBtnClear");
            
            // $("#id_clearfilter").addClass("clearfilter");
            
            onchange_filters();
        });
        if ( $('.filter-summary-selectedFilterContainer ul li').length > 0 ) {
            $("#id_clearfilter").removeClass("clearfilter");
            $("#id_clearfilter").addClass("addBtnClear");
        }
        else{
             $("#id_clearfilter").addClass("clearfilter");
            $("#id_clearfilter").removeClass("addBtnClear");
        }
        
        if (filter_params) {
            $.each(filter_params, function (key, value) {
                filter_params[key] = filter_params[key].replace(/\r?\n|\r/g, "");
            });
        }
        let searchtext = $('.searchTerm').val();
        get_courses_data(searchtext, JSON.stringify(filter_params));
    }
};

function changemaxDval(value) {
    $('#maxDval option[value="' + value + '"]').prop("selected", true);
}

function changeminDval(value) {
    $('#minDval option[value="' + value + '"]').prop("selected", true);
}