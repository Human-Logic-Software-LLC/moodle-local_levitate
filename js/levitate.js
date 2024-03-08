function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    console.log(vars);
    return vars;
}
function update_token(){
    params = getUrlVars();
    
    if (params.hasOwnProperty('token')) {
        document.getElementById("id_s_local_levitate_secret").value = params.token;
        
    }
   
}
function selected_course_js(){
     var course_type_name=0;
    $('input[type=radio][name=course_type]').change(function() {
        
        if (this.value == 0) {
            $('#fitem_id_coursefullname').css('display','none');
            $('#fitem_id_courseshortname').css('display','none');
            $('#fgroup_id_radioar1').css('display','flex');

        }
        else if (this.value == 1) {
            $('#fitem_id_coursefullname').css('display','flex');
            $('#fitem_id_courseshortname').css('display','flex');
            $('#fgroup_id_radioar1').css('display','none');
        }
    });
    
    if (course_type_name == 0) {
            $('#fitem_id_coursefullname').css('display','none');
            $('#fitem_id_courseshortname').css('display','none');
            $('#fgroup_id_radioar1').css('display','flex');

        }
    else if (course_type_name == 1) {

        $('#fitem_id_coursefullname').css('display','flex');
        $('#fitem_id_courseshortname').css('display','flex');
        $('#fgroup_id_radioar1').css('display','none');
    }
}
