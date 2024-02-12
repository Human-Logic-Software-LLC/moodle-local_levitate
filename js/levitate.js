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
